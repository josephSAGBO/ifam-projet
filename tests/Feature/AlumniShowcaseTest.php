<?php

namespace Tests\Feature;

use App\Models\AcademicSession;
use App\Models\Alumni;
use App\Models\AlumniPost;
use App\Models\Enrollment;
use App\Models\Formation;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AlumniShowcaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_parent_dashboard_renders_only_approved_visible_alumni_with_public_consent(): void
    {
        $parent = $this->parentUser();
        $formation = Formation::create(['name' => 'Stylisme', 'prix' => 100000]);
        $visibleAlumni = $this->createAlumni($formation, [
            'prenom' => 'Amina',
            'nom' => 'Kouassi',
            'is_public' => true,
            'email' => 'private-email@example.test',
            'telephone' => 'private-phone',
            'lieu_residence' => 'private-address',
            'rapports_donnees' => 'private-notes',
            'bio' => 'Mon témoignage visible.',
        ]);
        $alumniWithoutConsent = $this->createAlumni($formation, [
            'prenom' => 'Noah',
            'nom' => 'NonConsentant',
        ]);
        $unapprovedAlumni = $this->createAlumni($formation, [
            'prenom' => 'Mina',
            'nom' => 'NonApprouvee',
            'is_public' => true,
            'is_approved' => false,
        ]);

        foreach (range(1, 4) as $number) {
            AlumniPost::create([
                'alumni_id' => $visibleAlumni->id,
                'title' => "Création {$number}",
                'content' => 'Présentation de la création.',
                'status' => 'published',
            ]);
        }

        $response = $this->actingAs($parent)->get(route('parent.dashboard'));

        $response->assertSee("Ils ont réussi à l'IFAM")
            ->assertSee('Amina Kouassi')
            ->assertSee('Mon témoignage visible.')
            ->assertSee('Création 4')
            ->assertSee('Création 3')
            ->assertSee('Création 2')
            ->assertDontSee('Création 1')
            ->assertDontSee('Noah NonConsentant')
            ->assertDontSee('Mina NonApprouvee')
            ->assertDontSee('private-email@example.test')
            ->assertDontSee('private-phone')
            ->assertDontSee('private-address')
            ->assertDontSee('private-notes');
    }

    public function test_connected_alumni_directory_filters_by_formation_and_year(): void
    {
        $parent = $this->parentUser();
        $stylisme = Formation::create(['name' => 'Stylisme', 'prix' => 100000]);
        $modelisme = Formation::create(['name' => 'Modélisme', 'prix' => 100000]);
        $this->createAlumni($stylisme, [
            'prenom' => 'Amina',
            'nom' => 'Styliste',
            'promotion_year' => 2024,
            'is_public' => true,
        ]);
        $this->createAlumni($modelisme, [
            'prenom' => 'Noah',
            'nom' => 'Modeliste',
            'promotion_year' => 2024,
            'is_public' => true,
        ]);
        $this->createAlumni($stylisme, [
            'prenom' => 'Mina',
            'nom' => 'Ancienne',
            'promotion_year' => 2023,
            'is_public' => true,
        ]);

        $response = $this->actingAs($parent)->get(route('alumni.directory.index', [
            'formation_id' => $stylisme->id,
            'year' => 2024,
        ]));

        $response->assertSee('Tous les alumni')
            ->assertSee('Amina Styliste')
            ->assertDontSee('Noah Modeliste')
            ->assertDontSee('Mina Ancienne');
    }

    public function test_student_sees_alumni_from_their_formation_first(): void
    {
        Role::firstOrCreate(['name' => 'etudiant', 'guard_name' => 'web']);
        $studentUser = User::create([
            'first_name' => 'Étudiant',
            'last_name' => 'Test',
            'email' => 'student@example.test',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
        $studentUser->assignRole('etudiant');
        $studentProfile = StudentProfile::create([
            'user_id' => $studentUser->id,
            'matricule' => 'ETU-TEST-001',
            'status' => 'active',
        ]);
        $preferredFormation = Formation::create(['name' => 'Formation Préférée', 'prix' => 100000]);
        $otherFormation = Formation::create(['name' => 'Autre Formation', 'prix' => 100000]);
        $academicSession = AcademicSession::create([
            'name' => '2025-2026',
            'start_date' => '2025-10-01',
            'end_date' => '2026-07-31',
            'status' => 'en_cours',
            'is_current' => true,
        ]);
        Enrollment::create([
            'student_profile_id' => $studentProfile->id,
            'formation_id' => $preferredFormation->id,
            'academic_session_id' => $academicSession->id,
            'status' => 'active',
        ]);
        $this->createAlumni($otherFormation, [
            'prenom' => 'Amina',
            'nom' => 'Autre',
            'is_public' => true,
        ]);
        $this->createAlumni($preferredFormation, [
            'prenom' => 'Mina',
            'nom' => 'Préférée',
            'is_public' => true,
        ]);

        $response = $this->actingAs($studentUser)->get(route('student.dashboard'));
        $content = $response->assertSee('Ils ont commencé comme toi')->getContent();

        $this->assertLessThan(
            strpos($content, 'Amina Autre'),
            strpos($content, 'Mina Préférée')
        );
    }

    public function test_direction_dashboard_shows_alumni_totals_and_admin_link(): void
    {
        Role::firstOrCreate(['name' => 'direction', 'guard_name' => 'web']);
        $director = User::create([
            'first_name' => 'Direction',
            'last_name' => 'Test',
            'email' => 'direction@example.test',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
        $director->assignRole('direction');
        $formation = Formation::create(['name' => 'Stylisme', 'prix' => 100000]);
        $this->createAlumni($formation, [
            'prenom' => 'Visible',
            'nom' => 'Alumni',
            'is_public' => true,
        ]);
        $this->createAlumni($formation, [
            'prenom' => 'Masqué',
            'nom' => 'Alumni',
        ]);

        $response = $this->actingAs($director)->get(route('direction.dashboard'));

        $response->assertSee('Profils alumni visibles')
            ->assertSee('Derniers alumni')
            ->assertSee('Gérer la publication des profils alumni')
            ->assertSee(route('admin.alumnis.index'))
            ->assertSee('Visible Alumni')
            ->assertDontSee('Masqué Alumni');
    }

    private function parentUser(): User
    {
        Role::firstOrCreate(['name' => 'parent', 'guard_name' => 'web']);

        $parent = User::create([
            'first_name' => 'Parent',
            'last_name' => 'Test',
            'email' => 'parent@example.test',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);
        $parent->assignRole('parent');

        return $parent;
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function createAlumni(Formation $formation, array $attributes = []): Alumni
    {
        return Alumni::create(array_merge([
            'prenom' => 'Alumni',
            'nom' => 'Visible',
            'formation_id' => $formation->id,
            'is_approved' => true,
            'is_visible_directory' => true,
        ], $attributes));
    }
}
