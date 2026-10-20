<?php

namespace Tests\Feature;

use App\Models\AcademicSession;
use App\Models\Alumni;
use App\Models\AlumniPost;
use App\Models\Enrollment;
use App\Models\Formation;
use App\Models\Parcours;
use App\Models\StudentProfile;
use App\Models\User;
use App\Services\AlumniEligibilityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AlumniModuleTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected User $alumniUser;

    protected Alumni $alumniProfile;

    protected AcademicSession $academicSession;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        Role::firstOrCreate(['name' => 'administrateur', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'direction', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'alumni', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'etudiant', 'guard_name' => 'web']);

        // Session académique de référence
        $this->academicSession = AcademicSession::create([
            'name' => '2023-2024',
            'start_date' => '2023-10-01',
            'end_date' => '2024-07-31',
            'status' => 'terminee',
            'is_current' => false,
        ]);

        // Admin User
        $this->adminUser = User::create([
            'first_name' => 'Admin',
            'last_name' => 'Système',
            'email' => 'admin@ifam.bj',
            'password' => Hash::make('password123'),
            'is_active' => true,
        ]);
        $this->adminUser->assignRole('administrateur');

        // Alumni User & Profile
        $this->alumniUser = User::create([
            'first_name' => 'Amina',
            'last_name' => 'KOUDOU',
            'email' => 'amina@gmail.com',
            'password' => Hash::make('password123'),
            'is_active' => true,
        ]);
        $this->alumniUser->assignRole('alumni');

        $this->alumniProfile = Alumni::create([
            'nom' => 'KOUDOU',
            'prenom' => 'Amina',
            'email' => 'amina@gmail.com',
            'current_position' => 'Styliste Modéliste',
            'company' => 'Amina Créations',
            'is_approved' => true,
            'is_visible_directory' => true,
            'is_public' => true,
            'promotion_year' => 2024,
            'bio' => 'Créatrice passionée de mode éco-responsable.',
        ]);
    }

    public function test_admin_can_create_historical_alumni_without_student_profile(): void
    {
        $response = $this->actingAs($this->adminUser)->post(route('admin.alumnis.store'), [
            'nom' => 'SOSSOU',
            'prenom' => 'Koffi',
            'email' => 'koffi.sossou@example.com',
            'current_position' => 'Directeur d\'Atelier',
            'company' => 'Atelier Sossou',
            'promotion_year' => 2020,
            'is_approved' => 1,
            'is_visible_directory' => 1,
        ]);

        $response->assertRedirect(route('admin.alumnis.index'));
        $this->assertDatabaseHas('alumnis', [
            'nom' => 'SOSSOU',
            'prenom' => 'Koffi',
            'email' => 'koffi.sossou@example.com',
            'student_profile_id' => null,
            'is_approved' => true,
            'is_visible_directory' => true,
        ]);
    }

    public function test_student_eligibility_service_checks_terminal_parcours_step(): void
    {
        $service = new AlumniEligibilityService;

        $studentUser = User::create([
            'first_name' => 'Jean',
            'last_name' => 'MENSAH',
            'email' => 'jean.mensah@ifam.bj',
            'password' => Hash::make('password123'),
            'is_active' => true,
        ]);
        $studentUser->assignRole('etudiant');

        $studentProfile = StudentProfile::create([
            'user_id' => $studentUser->id,
            'matricule' => 'ETU-2024-001',
            'status' => 'active',
        ]);

        // Parcours avec 2 formations ordonnées (1 puis 2)
        $parcours = Parcours::create([
            'nom' => 'Stylisme & Modélisme 2 Ans',
        ]);

        $f1 = Formation::create([
            'name' => 'Année 1 - Stylisme Fondamental',
            'parcours_id' => $parcours->id,
            'ordre' => 1,
            'prix' => 100000,
        ]);

        $f2 = Formation::create([
            'name' => 'Année 2 - Modélisme Haute Couture',
            'parcours_id' => $parcours->id,
            'ordre' => 2,
            'prix' => 100000,
        ]);

        // L'étudiant a fini l'Année 1 seulement
        Enrollment::create([
            'student_profile_id' => $studentProfile->id,
            'formation_id' => $f1->id,
            'academic_session_id' => $this->academicSession->id,
            'status' => 'completed',
        ]);

        // Vérification 1: Non éligible car ce n'est pas l'étape terminale du parcours
        $this->assertFalse($service->isEligible($studentProfile));

        // L'étudiant termine l'Année 2
        Enrollment::create([
            'student_profile_id' => $studentProfile->id,
            'formation_id' => $f2->id,
            'academic_session_id' => $this->academicSession->id,
            'status' => 'completed',
        ]);

        // Vérification 2: Éligible maintenant !
        $this->assertTrue($service->isEligible($studentProfile));
    }

    public function test_admin_can_convert_eligible_student_and_assigns_alumni_role(): void
    {
        $studentUser = User::create([
            'first_name' => 'Elvire',
            'last_name' => 'DOSSOU',
            'email' => 'elvire@ifam.bj',
            'password' => Hash::make('password123'),
            'is_active' => true,
        ]);
        $studentUser->assignRole('etudiant');

        $studentProfile = StudentProfile::create([
            'user_id' => $studentUser->id,
            'matricule' => 'ETU-2023-099',
            'status' => 'active',
        ]);

        $formation = Formation::create([
            'name' => 'Formation Autonome Broderie d\'Art',
            'parcours_id' => null,
            'prix' => 150000,
        ]);

        Enrollment::create([
            'student_profile_id' => $studentProfile->id,
            'formation_id' => $formation->id,
            'academic_session_id' => $this->academicSession->id,
            'status' => 'completed',
        ]);

        $response = $this->actingAs($this->adminUser)->post(
            route('admin.alumnis.convert', $studentProfile),
            [
                'current_position' => 'Créatrice Brodeuse',
                'company' => 'Maison Elvire',
                'is_approved' => 1,
                'is_visible_directory' => 1,
            ]
        );

        $response->assertRedirect(route('admin.alumnis.index'));
        $this->assertDatabaseHas('alumnis', [
            'student_profile_id' => $studentProfile->id,
            'nom' => 'DOSSOU',
            'prenom' => 'Elvire',
            'email' => 'elvire@ifam.bj',
            'is_approved' => true,
        ]);

        // L'utilisateur possède maintenant le rôle alumni ET conserve son rôle étudiant
        $studentUser->refresh();
        $this->assertTrue($studentUser->hasRole('alumni'));
        $this->assertTrue($studentUser->hasRole('etudiant'));

        // Une deuxième conversion doit échouer pour éviter les doublons
        $responseDuplicate = $this->actingAs($this->adminUser)->post(
            route('admin.alumnis.convert', $studentProfile)
        );
        $responseDuplicate->assertRedirect();
        $responseDuplicate->assertSessionHas('error');
    }

    public function test_alumni_can_access_dashboard_and_edit_profile(): void
    {
        $response = $this->actingAs($this->alumniUser)->get(route('alumni.dashboard'));
        $response->assertOk();
        $response->assertSee('Amina');

        $responseUpdate = $this->actingAs($this->alumniUser)->put(route('alumni.profile.update'), [
            'current_position' => 'Designer Textile Senior',
            'company' => 'Atelier Koudou International',
            'city' => 'Cotonou',
            'bio' => 'Nouvelle biographie mise à jour avec soin.',
            'linkedin_url' => 'https://linkedin.com/in/amina-koudou',
            'is_visible_directory' => 1,
            'is_public' => 1,
            'allow_public_email' => 1,
        ]);

        $responseUpdate->assertRedirect(route('alumni.profile.edit'));
        $this->assertDatabaseHas('alumnis', [
            'id' => $this->alumniProfile->id,
            'current_position' => 'Designer Textile Senior',
            'company' => 'Atelier Koudou International',
            'city' => 'Cotonou',
            'linkedin_url' => 'https://linkedin.com/in/amina-koudou',
            'is_public' => true,
        ]);

        $responseRevokeConsent = $this->actingAs($this->alumniUser)->put(route('alumni.profile.update'), [
            'is_public' => 0,
        ]);

        $responseRevokeConsent->assertRedirect(route('alumni.profile.edit'));
        $this->assertDatabaseHas('alumnis', [
            'id' => $this->alumniProfile->id,
            'is_public' => false,
        ]);
    }

    public function test_alumni_post_workflow_draft_pending_published(): void
    {
        // 1. Création d'un brouillon avec image
        $image = UploadedFile::fake()->image('robe_ceremonie.jpg', 600, 600);

        $responseCreate = $this->actingAs($this->alumniUser)->post(route('alumni.posts.store'), [
            'title' => 'Collection Été 2026 - Soie & Indigo',
            'content' => 'Présentation de ma nouvelle ligne de vêtements utilisant les teintures naturelles béninoises.',
            'status' => 'draft',
            'photos' => [$image],
            'captions' => ['Robe en soie artisanale'],
            'cover_photo' => 0,
        ]);

        $responseCreate->assertRedirect();
        $this->assertDatabaseHas('alumni_posts', [
            'alumni_id' => $this->alumniProfile->id,
            'title' => 'Collection Été 2026 - Soie & Indigo',
            'status' => 'draft',
        ]);

        $post = AlumniPost::where('title', 'Collection Été 2026 - Soie & Indigo')->first();
        $this->assertNotNull($post);
        $this->assertCount(1, $post->media);

        // 2. Soumission à la modération
        $responseSubmit = $this->actingAs($this->alumniUser)->post(route('alumni.posts.submit', $post));
        $responseSubmit->assertRedirect();
        $post->refresh();
        $this->assertEquals('pending', $post->status);

        // 3. Validation par l'administrateur
        $responseApprove = $this->actingAs($this->adminUser)->post(route('admin.alumnis.posts.approve', $post));
        $responseApprove->assertRedirect();
        $post->refresh();
        $this->assertEquals('published', $post->status);
        $this->assertNotNull($post->published_at);
        $this->assertEquals($this->adminUser->id, $post->validated_by);
    }

    public function test_post_rejection_requires_mandatory_reason(): void
    {
        $post = AlumniPost::create([
            'alumni_id' => $this->alumniProfile->id,
            'title' => 'Titre Test Modération',
            'slug' => 'titre-test-moderation',
            'content' => 'Contenu du test',
            'status' => 'pending',
        ]);

        // Refus sans motif -> échec de validation
        $responseNoReason = $this->actingAs($this->adminUser)->post(route('admin.alumnis.posts.reject', $post), [
            'rejection_reason' => '',
        ]);
        $responseNoReason->assertSessionHasErrors(['rejection_reason']);
        $this->assertEquals('pending', $post->fresh()->status);

        // Refus avec motif explicite valide
        $responseValidReject = $this->actingAs($this->adminUser)->post(route('admin.alumnis.posts.reject', $post), [
            'rejection_reason' => 'Veuillez ajouter plus de détails techniques sur votre création.',
        ]);
        $responseValidReject->assertRedirect();
        $post->refresh();
        $this->assertEquals('rejected', $post->status);
        $this->assertEquals('Veuillez ajouter plus de détails techniques sur votre création.', $post->rejection_reason);
    }

    public function test_public_directory_and_privacy_protection(): void
    {
        // 1. Profil visible et approuvé -> visible dans l'annuaire
        $responsePublic = $this->get(route('alumni.public.index'));
        $responsePublic->assertOk();
        $responsePublic->assertSee('KOUDOU');

        // 2. Profil masqué -> 404 sur l'accès direct et absent de l'annuaire
        $hiddenAlumni = Alumni::create([
            'nom' => 'SECRET',
            'prenom' => 'Marc',
            'email' => 'marc.secret@ifam.bj',
            'is_approved' => false,
            'is_visible_directory' => false,
        ]);

        $responseHiddenShow = $this->get(route('alumni.public.show', $hiddenAlumni));
        $responseHiddenShow->assertNotFound();

        // 3. Publication brouillon ou en attente -> 404 sur l'accès public
        $pendingPost = AlumniPost::create([
            'alumni_id' => $this->alumniProfile->id,
            'title' => 'Publication Non Validée',
            'slug' => 'publication-non-validee',
            'content' => 'Texte confidentiel',
            'status' => 'pending',
        ]);

        $responsePendingShow = $this->get(route('alumni.public.posts.show', $pendingPost->slug));
        $responsePendingShow->assertNotFound();

        $responsePendingShowById = $this->get(route('alumni.public.posts.show', $pendingPost->id));
        $responsePendingShowById->assertNotFound();
    }

    public function test_public_alumni_post_details_accept_slugs_and_legacy_ids(): void
    {
        $post = AlumniPost::create([
            'alumni_id' => $this->alumniProfile->id,
            'title' => 'Publication Publiée',
            'slug' => 'publication-publiee',
            'content' => 'Contenu public',
            'status' => 'published',
            'published_at' => now(),
        ]);

        $this->get(route('alumni.public.posts.show', $post->slug))
            ->assertSee('Publication Publiée');

        $this->get(route('alumni.public.posts.show', $post->id))
            ->assertSee('Publication Publiée');

        $this->get(route('alumni.public.posts.index'))
            ->assertSee(route('alumni.public.posts.show', $post->slug), false)
            ->assertDontSee(route('alumni.public.posts.show', $post->id), false);

        $this->get(route('alumni.public.show', $this->alumniProfile))
            ->assertSee(route('alumni.public.posts.show', $post->slug), false)
            ->assertDontSee(route('alumni.public.posts.show', $post->id), false);
    }

    public function test_student_without_alumni_role_cannot_access_alumni_space(): void
    {
        $simpleStudent = User::create([
            'first_name' => 'Paul',
            'last_name' => 'ADAN',
            'email' => 'paul.adan@ifam.bj',
            'password' => Hash::make('password123'),
            'is_active' => true,
        ]);
        $simpleStudent->assignRole('etudiant');

        $response = $this->actingAs($simpleStudent)->get(route('alumni.dashboard'));
        $response->assertForbidden();
    }

    public function test_alumni_cannot_modify_or_delete_another_alumnis_post(): void
    {
        // Second alumni
        $otherAlumniUser = User::create([
            'first_name' => 'Berthe',
            'last_name' => 'HOUNWANOU',
            'email' => 'berthe@gmail.com',
            'password' => Hash::make('password123'),
            'is_active' => true,
        ]);
        $otherAlumniUser->assignRole('alumni');

        $otherAlumni = Alumni::create([
            'nom' => 'HOUNWANOU',
            'prenom' => 'Berthe',
            'email' => 'berthe@gmail.com',
            'is_approved' => true,
            'is_visible_directory' => true,
        ]);

        $post = AlumniPost::create([
            'alumni_id' => $otherAlumni->id,
            'title' => 'Post de Berthe',
            'content' => 'Contenu confidentiel de Berthe',
            'status' => 'draft',
        ]);

        // Amina tente de modifier ou supprimer le post de Berthe
        $responseEdit = $this->actingAs($this->alumniUser)->get(route('alumni.posts.edit', $post));
        $responseEdit->assertForbidden();

        $responseUpdate = $this->actingAs($this->alumniUser)->put(route('alumni.posts.update', $post), [
            'title' => 'Titre piraté',
            'content' => 'Contenu piraté',
            'action' => 'draft',
        ]);
        $responseUpdate->assertForbidden();

        $responseDelete = $this->actingAs($this->alumniUser)->delete(route('alumni.posts.destroy', $post));
        $responseDelete->assertForbidden();
    }

    public function test_alumni_can_cancel_pending_submission_back_to_draft(): void
    {
        $post = AlumniPost::create([
            'alumni_id' => $this->alumniProfile->id,
            'title' => 'Post à annuler',
            'content' => 'Contenu du post',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->alumniUser)->post(route('alumni.posts.cancel', $post));
        $response->assertRedirect();
        $this->assertEquals('draft', $post->fresh()->status);
    }

    public function test_direction_can_moderate_alumni_posts(): void
    {
        $directionUser = User::create([
            'first_name' => 'Directeur',
            'last_name' => 'Pédagogique',
            'email' => 'direction@ifam.bj',
            'password' => Hash::make('password123'),
            'is_active' => true,
        ]);
        $directionUser->assignRole('direction');

        $post = AlumniPost::create([
            'alumni_id' => $this->alumniProfile->id,
            'title' => 'Collection automne pour modération direction',
            'content' => 'Contenu pour test direction',
            'status' => 'pending',
        ]);

        // Direction approuve via la route direction
        $response = $this->actingAs($directionUser)->post(route('direction.alumni-posts.approve', $post));
        $response->assertRedirect();
        $post->refresh();
        $this->assertEquals('published', $post->status);
        $this->assertEquals($directionUser->id, $post->validated_by);
    }
}
