<?php

namespace Tests\Feature;

use App\Models\AcademicSession;
use App\Models\BulletinPublication;
use App\Models\Enrollment;
use App\Models\Evaluation;
use App\Models\Formation;
use App\Models\Grade;
use App\Models\Module;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PublishedBulletinAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_view_only_their_published_bulletins(): void
    {
        $studentData = $this->createStudentData('student-bulletin@ifam.test', 'Étudiant Bulletin');
        $otherStudentData = $this->createStudentData('other-student-bulletin@ifam.test', 'Autre Étudiant');
        $publication = $this->createPublication($studentData, true);
        $unpublishedPublication = $this->createPublication($studentData, false);
        $otherStudentPublication = $this->createPublication($otherStudentData, true);

        $indexResponse = $this->actingAs($studentData['user'])->get(route('student.bulletins.index'));

        $indexResponse->assertOk();
        $indexResponse->assertSee('Formation de Étudiant Bulletin');
        $indexResponse->assertDontSee('Formation de Autre Étudiant');
        $indexResponse->assertSee(route('student.bulletins.show', $publication));

        $bulletinResponse = $this->actingAs($studentData['user'])
            ->get(route('student.bulletins.show', $publication));
        $bulletinResponse->assertOk();
        $bulletinResponse->assertSee('BULLETIN DE NOTE');
        $bulletinResponse->assertSee('Étudiant Bulletin');
        $bulletinResponse->assertSee('16,00');
        $bulletinResponse->assertSee(asset('img/watermark_ifam.png'));
        $bulletinResponse->assertSee(asset('img/cachet_dg_transparent.png'));
        $bulletinResponse->assertSee(asset('img/signature_dg_transparent.png'));
        $bulletinResponse->assertSee('IFAM BENIN');
        $bulletinResponse->assertSee('M. Thomas Koffi TETEH');
        $bulletinResponse->assertSee('MOY.INT');
        $bulletinResponse->assertDontSee('Contrôles continus');

        $pdfResponse = $this->actingAs($studentData['user'])
            ->get(route('student.bulletins.pdf', $publication));
        $pdfResponse->assertOk();
        $this->assertStringContainsString('application/pdf', $pdfResponse->headers->get('content-type'));

        // L'étudiant ne peut pas voir ni télécharger un bulletin non publié
        $this->actingAs($studentData['user'])
            ->get(route('student.bulletins.show', $unpublishedPublication))
            ->assertNotFound();
        $this->actingAs($studentData['user'])
            ->get(route('student.bulletins.pdf', $unpublishedPublication))
            ->assertNotFound();

        // L'étudiant ne peut pas voir ni télécharger le bulletin d'un autre étudiant
        $this->actingAs($studentData['user'])
            ->get(route('student.bulletins.show', $otherStudentPublication))
            ->assertNotFound();
        $this->actingAs($studentData['user'])
            ->get(route('student.bulletins.pdf', $otherStudentPublication))
            ->assertNotFound();
    }

    public function test_parent_can_view_only_a_linked_childs_published_bulletins(): void
    {
        $childData = $this->createStudentData('child-bulletin@ifam.test', 'Enfant Bulletin');
        $otherChildData = $this->createStudentData('other-child-bulletin@ifam.test', 'Autre Enfant');
        $parent = $this->createUser('parent', 'parent-bulletin@ifam.test', 'Parent', 'Bulletin');
        $parent->children()->attach($childData['user']->id, ['relationship' => 'Parent']);
        $publication = $this->createPublication($childData, true);
        $unpublishedPublication = $this->createPublication($childData, false);
        $otherChildPublication = $this->createPublication($otherChildData, true);

        $bulletinResponse = $this->actingAs($parent)->get(route('parent.bulletins.show', [
            'child' => $childData['user']->id,
            'publication' => $publication->id,
        ]));

        $bulletinResponse->assertOk();
        $bulletinResponse->assertSee('BULLETIN DE NOTE');
        $bulletinResponse->assertSee('Enfant Bulletin');
        $bulletinResponse->assertSee('16,00');
        $bulletinResponse->assertSee(asset('img/watermark_ifam.png'));
        $bulletinResponse->assertSee(asset('img/cachet_dg_transparent.png'));
        $bulletinResponse->assertSee(asset('img/signature_dg_transparent.png'));

        $pdfResponse = $this->actingAs($parent)->get(route('parent.bulletins.pdf', [
            'child' => $childData['user']->id,
            'publication' => $publication->id,
        ]));
        $pdfResponse->assertOk();
        $this->assertStringContainsString('application/pdf', $pdfResponse->headers->get('content-type'));

        $dashboardResponse = $this->actingAs($parent)->get(route('parent.dashboard'));
        $dashboardResponse->assertOk();
        $dashboardResponse->assertSee(route('parent.bulletins.show', [
            'child' => $childData['user']->id,
            'publication' => $publication->id,
        ]), false);

        // Le parent ne peut pas voir ni télécharger le bulletin d'un enfant non relié
        $this->actingAs($parent)->get(route('parent.bulletins.show', [
            'child' => $otherChildData['user']->id,
            'publication' => $otherChildPublication->id,
        ]))->assertNotFound();
        $this->actingAs($parent)->get(route('parent.bulletins.pdf', [
            'child' => $otherChildData['user']->id,
            'publication' => $otherChildPublication->id,
        ]))->assertNotFound();

        // Le parent ne peut pas voir ni télécharger un bulletin non publié
        $this->actingAs($parent)->get(route('parent.bulletins.show', [
            'child' => $childData['user']->id,
            'publication' => $unpublishedPublication->id,
        ]))->assertNotFound();
        $this->actingAs($parent)->get(route('parent.bulletins.pdf', [
            'child' => $childData['user']->id,
            'publication' => $unpublishedPublication->id,
        ]))->assertNotFound();
    }

    public function test_admin_can_view_and_download_any_bulletin_published_or_not(): void
    {
        $studentData = $this->createStudentData('admin-test-student@ifam.test', 'Admin Étudiant');
        $admin = $this->createUser('administrateur', 'admin-bulletin@ifam.test', 'Admin', 'IFAM');

        // Bulletin non publié (aucune publication créée)
        $showResponse = $this->actingAs($admin)->get(route('admin.bulletins.show', [
            'student' => $studentData['profile']->id,
            'session_id' => $studentData['session']->id,
            'formation_id' => $studentData['formation']->id,
        ]));
        $showResponse->assertOk();
        $showResponse->assertSee('BULLETIN DE NOTE');
        $showResponse->assertSee('Admin Étudiant');

        // Téléchargement PDF par l'admin
        $pdfResponse = $this->actingAs($admin)->get(route('admin.bulletins.pdf', [
            'student' => $studentData['profile']->id,
            'session_id' => $studentData['session']->id,
            'formation_id' => $studentData['formation']->id,
        ]));
        $pdfResponse->assertOk();
        $this->assertStringContainsString('application/pdf', $pdfResponse->headers->get('content-type'));
    }

    public function test_bulletin_render_components_and_pdf_are_identical_across_all_three_spaces(): void
    {
        $studentData = $this->createStudentData('parity-student@ifam.test', 'Parité Étudiant');
        $parent = $this->createUser('parent', 'parity-parent@ifam.test', 'Parent', 'Parité');
        $parent->children()->attach($studentData['user']->id, ['relationship' => 'Parent']);
        $admin = $this->createUser('administrateur', 'parity-admin@ifam.test', 'Admin', 'Parité');
        $publication = $this->createPublication($studentData, true);

        // 1. Vue Admin
        $adminView = $this->actingAs($admin)->get(route('admin.bulletins.show', [
            'student' => $studentData['profile']->id,
            'session_id' => $studentData['session']->id,
            'formation_id' => $studentData['formation']->id,
        ]));
        $adminView->assertOk();

        // 2. Vue Parent
        $parentView = $this->actingAs($parent)->get(route('parent.bulletins.show', [
            'child' => $studentData['user']->id,
            'publication' => $publication->id,
        ]));
        $parentView->assertOk();

        // 3. Vue Étudiant
        $studentView = $this->actingAs($studentData['user'])->get(route('student.bulletins.show', $publication));
        $studentView->assertOk();

        // Extraction du composant unique official-bulletin
        $extractBulletin = function (string $html): string {
            if (preg_match('/<article class="official-bulletin"[\s\S]*?<\/article>/', $html, $matches)) {
                return trim($matches[0]);
            }

            return '';
        };

        $adminBulletin = $extractBulletin($adminView->getContent());
        $parentBulletin = $extractBulletin($parentView->getContent());
        $studentBulletin = $extractBulletin($studentView->getContent());

        $this->assertNotEmpty($adminBulletin, 'Le composant bulletin doit être présent dans la vue admin');
        $this->assertNotEmpty($parentBulletin, 'Le composant bulletin doit être présent dans la vue parent');
        $this->assertNotEmpty($studentBulletin, 'Le composant bulletin doit être présent dans la vue étudiant');

        // Les trois rendus HTML du composant officiel doivent être rigoureusement identiques
        $this->assertSame($parentBulletin, $studentBulletin, 'Le bulletin étudiant doit être strictement identique au bulletin parent');
        $this->assertSame($parentBulletin, $adminBulletin, 'Le bulletin admin doit être strictement identique au bulletin parent');

        // 4. Téléchargements PDF
        $adminPdf = $this->actingAs($admin)->get(route('admin.bulletins.pdf', [
            'student' => $studentData['profile']->id,
            'session_id' => $studentData['session']->id,
            'formation_id' => $studentData['formation']->id,
        ]));
        $adminPdf->assertOk();
        $this->assertStringStartsWith('%PDF-', $adminPdf->getContent());

        $parentPdf = $this->actingAs($parent)->get(route('parent.bulletins.pdf', [
            'child' => $studentData['user']->id,
            'publication' => $publication->id,
        ]));
        $parentPdf->assertOk();
        $this->assertStringStartsWith('%PDF-', $parentPdf->getContent());

        $studentPdf = $this->actingAs($studentData['user'])->get(route('student.bulletins.pdf', $publication));
        $studentPdf->assertOk();
        $this->assertStringStartsWith('%PDF-', $studentPdf->getContent());
    }

    /**
     * @return array{user: User, profile: StudentProfile, session: AcademicSession, formation: Formation}
     */
    private function createStudentData(string $email, string $name): array
    {
        [$firstName, $lastName] = explode(' ', $name, 2);
        $user = $this->createUser('etudiant', $email, $firstName, $lastName);
        $profile = StudentProfile::create(['user_id' => $user->id]);
        $session = AcademicSession::create([
            'name' => 'Session '.$lastName,
            'start_date' => '2026-09-01',
            'end_date' => '2027-06-30',
        ]);
        $formation = Formation::create(['name' => 'Formation de '.$name]);
        $module = Module::create(['name' => 'Module de '.$lastName, 'code' => 'MOD-'.strtoupper($lastName)]);
        $formation->modules()->attach($module->id);

        Enrollment::create([
            'student_profile_id' => $profile->id,
            'formation_id' => $formation->id,
            'academic_session_id' => $session->id,
            'status' => 'active',
        ]);

        $evaluation = Evaluation::create([
            'type' => 'devoir',
            'formation_id' => $formation->id,
            'module_id' => $module->id,
            'session_id' => $session->id,
            'titre' => 'Devoir de '.$lastName,
            'statut' => 'cloturee',
        ]);
        Grade::create([
            'evaluation_id' => $evaluation->id,
            'student_profile_id' => $profile->id,
            'score' => 16,
            'is_absent' => false,
        ]);

        return compact('user', 'profile', 'session', 'formation');
    }

    /**
     * @param  array{profile: StudentProfile, session: AcademicSession, formation: Formation}  $studentData
     */
    private function createPublication(array $studentData, bool $isPublished): BulletinPublication
    {
        return BulletinPublication::create([
            'student_profile_id' => $studentData['profile']->id,
            'formation_id' => $studentData['formation']->id,
            'academic_session_id' => $studentData['session']->id,
            'is_published' => $isPublished,
            'published_at' => $isPublished ? now() : null,
        ]);
    }

    private function createUser(string $role, string $email, string $firstName, string $lastName): User
    {
        Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        $user = User::create([
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
            'password' => Hash::make('Secret123!'),
            'is_active' => true,
        ]);
        $user->assignRole($role);

        return $user;
    }
}
