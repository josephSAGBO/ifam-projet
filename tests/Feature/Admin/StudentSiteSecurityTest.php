<?php

namespace Tests\Feature\Admin;

use App\Models\AcademicSession;
use App\Models\Enrollment;
use App\Models\Formation;
use App\Models\Site;
use App\Models\StudentProfile;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Database\Seeders\SiteSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class StudentSiteSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected Site $cotonou;

    protected Site $bohicon;

    protected User $superAdmin;

    protected User $cotonouAdmin;

    protected User $bohiconAdmin;

    protected User $dgUser;

    protected User $cotonouStudent;

    protected User $bohiconStudent;

    protected User $unassignedStudent;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(SiteSeeder::class);
        $this->seed(RoleSeeder::class);

        $this->cotonou = Site::where('nom', 'Cotonou')->firstOrFail();
        $this->bohicon = Site::create([
            'nom' => 'Bohicon',
            'ville' => 'Bohicon',
            'est_actif' => true,
        ]);

        // 1. SuperAdmin
        $this->superAdmin = User::create([
            'first_name' => 'Super',
            'last_name' => 'Admin',
            'email' => 'superadmin.student@ifam.test',
            'password' => Hash::make('Password123!'),
            'is_active' => true,
        ]);
        $this->superAdmin->assignRole('administrateur');

        // 2. Administrateur Cotonou
        $this->cotonouAdmin = User::create([
            'first_name' => 'Admin',
            'last_name' => 'Cotonou',
            'email' => 'admin.cotonou.student@ifam.test',
            'password' => Hash::make('Password123!'),
            'is_active' => true,
        ]);
        $this->cotonouAdmin->assignRole('responsable_site');
        $this->cotonouAdmin->sites()->attach($this->cotonou->id);

        // 3. Administrateur Bohicon
        $this->bohiconAdmin = User::create([
            'first_name' => 'Admin',
            'last_name' => 'Bohicon',
            'email' => 'admin.bohicon.student@ifam.test',
            'password' => Hash::make('Password123!'),
            'is_active' => true,
        ]);
        $this->bohiconAdmin->assignRole('responsable_site');
        $this->bohiconAdmin->sites()->attach($this->bohicon->id);

        // 4. Direction Générale (accès transversal)
        $this->dgUser = User::create([
            'first_name' => 'Directeur',
            'last_name' => 'Général',
            'email' => 'dg.student@ifam.test',
            'password' => Hash::make('Password123!'),
            'is_active' => true,
        ]);
        $this->dgUser->assignRole('direction');

        // 5. Étudiant Cotonou
        $this->cotonouStudent = User::create([
            'first_name' => 'Aïcha',
            'last_name' => 'Cotonou',
            'email' => 'aicha.cotonou@ifam.test',
            'matricule' => 'ETU-2026-COT001',
            'password' => Hash::make('Password123!'),
            'is_active' => true,
        ]);
        $this->cotonouStudent->assignRole('etudiant');
        $this->cotonouStudent->sites()->attach($this->cotonou->id);
        StudentProfile::create([
            'user_id' => $this->cotonouStudent->id,
            'birth_place' => 'Cotonou',
            'nationality' => 'Béninoise',
            'city' => 'Cotonou',
            'entry_year' => 2026,
        ]);

        // 6. Étudiant Bohicon
        $this->bohiconStudent = User::create([
            'first_name' => 'Koffi',
            'last_name' => 'Bohicon',
            'email' => 'koffi.bohicon@ifam.test',
            'matricule' => 'ETU-2026-BOH001',
            'password' => Hash::make('Password123!'),
            'is_active' => true,
        ]);
        $this->bohiconStudent->assignRole('etudiant');
        $this->bohiconStudent->sites()->attach($this->bohicon->id);
        StudentProfile::create([
            'user_id' => $this->bohiconStudent->id,
            'birth_place' => 'Bohicon',
            'nationality' => 'Béninoise',
            'city' => 'Bohicon',
            'entry_year' => 2026,
        ]);

        // 7. Étudiant sans site ni inscription
        $this->unassignedStudent = User::create([
            'first_name' => 'Orphelin',
            'last_name' => 'SansSite',
            'email' => 'orphelin@ifam.test',
            'matricule' => 'ETU-2026-NOSITE',
            'password' => Hash::make('Password123!'),
            'is_active' => true,
        ]);
        $this->unassignedStudent->assignRole('etudiant');
        StudentProfile::create([
            'user_id' => $this->unassignedStudent->id,
            'birth_place' => 'Inconnu',
            'nationality' => 'Béninoise',
            'city' => 'Inconnue',
            'entry_year' => 2026,
        ]);
    }

    /**
     * Le responsable de site ne voit dans l'index et les compteurs que les étudiants de son site.
     */
    public function test_responsable_site_sees_only_students_from_managed_site_in_index(): void
    {
        $response = $this->actingAs($this->cotonouAdmin)->get(route('admin.students.index'));

        $response->assertOk();
        $response->assertSee('Aïcha Cotonou');
        $response->assertDontSee('Koffi Bohicon');
        $response->assertDontSee('Orphelin SansSite');

        $students = $response->viewData('students');
        $this->assertSame(1, $students->total());
        $this->assertTrue($students->contains($this->cotonouStudent));
        $this->assertFalse($students->contains($this->bohiconStudent));
        $this->assertFalse($students->contains($this->unassignedStudent));
    }

    /**
     * La recherche est strictement cloisonnée au site géré, empêchant toute fuite d'information.
     */
    public function test_search_is_strictly_scoped_to_managed_site(): void
    {
        // Recherche du nom d'un étudiant d'un autre site
        $responseBohiconSearch = $this->actingAs($this->cotonouAdmin)
            ->get(route('admin.students.index', ['search' => 'Koffi']));

        $responseBohiconSearch->assertOk();
        $responseBohiconSearch->assertDontSee('Koffi Bohicon');
        $this->assertSame(0, $responseBohiconSearch->viewData('students')->total());

        // Recherche du matricule d'un étudiant d'un autre site
        $responseMatriculeSearch = $this->actingAs($this->cotonouAdmin)
            ->get(route('admin.students.index', ['search' => 'ETU-2026-BOH001']));

        $responseMatriculeSearch->assertOk();
        $responseMatriculeSearch->assertDontSee('Koffi Bohicon');
        $this->assertSame(0, $responseMatriculeSearch->viewData('students')->total());

        // Recherche de son propre étudiant
        $responseCotonouSearch = $this->actingAs($this->cotonouAdmin)
            ->get(route('admin.students.index', ['search' => 'Aïcha']));

        $responseCotonouSearch->assertOk();
        $responseCotonouSearch->assertSee('Aïcha Cotonou');
        $this->assertSame(1, $responseCotonouSearch->viewData('students')->total());
    }

    /**
     * Tentative de consultation directe (show) d'un étudiant d'un autre site rejetée par HTTP 403.
     */
    public function test_responsable_site_cannot_view_student_from_another_site(): void
    {
        $response = $this->actingAs($this->cotonouAdmin)
            ->get(route('admin.students.show', $this->bohiconStudent));

        $response->assertForbidden();
    }

    /**
     * Tentative d'accès au formulaire d'édition (edit) d'un étudiant d'un autre site rejetée par HTTP 403.
     */
    public function test_responsable_site_cannot_edit_student_from_another_site(): void
    {
        $response = $this->actingAs($this->cotonouAdmin)
            ->get(route('admin.students.edit', $this->bohiconStudent));

        $response->assertForbidden();
    }

    /**
     * Tentative de mise à jour (update) d'un étudiant d'un autre site rejetée par HTTP 403 sans altération de données.
     */
    public function test_responsable_site_cannot_update_student_from_another_site_and_data_is_preserved(): void
    {
        $originalCity = $this->bohiconStudent->studentProfile->city;

        $response = $this->actingAs($this->cotonouAdmin)
            ->put(route('admin.students.update', $this->bohiconStudent), [
                'city' => 'Ville Piratée',
                'birth_place' => 'Lieu Piraté',
                'admin_notes' => 'Piraté par admin Cotonou',
            ]);

        $response->assertForbidden();

        // Vérification stricte en base de données
        $this->bohiconStudent->studentProfile->refresh();
        $this->assertSame($originalCity, $this->bohiconStudent->studentProfile->city);
        $this->assertNotSame('Ville Piratée', $this->bohiconStudent->studentProfile->city);
        $this->assertNull($this->bohiconStudent->studentProfile->admin_notes);
    }

    /**
     * Un responsable de site peut consulter et modifier un étudiant de son propre site.
     */
    public function test_responsable_site_can_view_and_update_student_of_own_site(): void
    {
        $showResponse = $this->actingAs($this->cotonouAdmin)
            ->get(route('admin.students.show', $this->cotonouStudent));
        $showResponse->assertOk();
        $showResponse->assertSee('Aïcha Cotonou');

        $editResponse = $this->actingAs($this->cotonouAdmin)
            ->get(route('admin.students.edit', $this->cotonouStudent));
        $editResponse->assertOk();

        $updateResponse = $this->actingAs($this->cotonouAdmin)
            ->put(route('admin.students.update', $this->cotonouStudent), [
                'city' => 'Cotonou Akpakpa',
                'birth_place' => 'Cotonou',
                'nationality' => 'Béninoise',
                'entry_year' => 2026,
                'admin_notes' => 'Dossier vérifié par admin Cotonou.',
            ]);

        $updateResponse->assertRedirect(route('admin.students.show', $this->cotonouStudent));

        $this->cotonouStudent->studentProfile->refresh();
        $this->assertSame('Cotonou Akpakpa', $this->cotonouStudent->studentProfile->city);
        $this->assertSame('Dossier vérifié par admin Cotonou.', $this->cotonouStudent->studentProfile->admin_notes);
    }

    /**
     * Accès d'un responsable local à un étudiant inscrit sur son site,
     * même si son compte utilisateur n'est pas lié à ce site dans site_user.
     */
    public function test_responsable_site_can_access_student_enrolled_on_site_even_without_site_user_link(): void
    {
        // Création d'une session et d'une formation à Cotonou
        $session = AcademicSession::create([
            'name' => 'Session Cotonou 2026',
            'normalized_name' => 'session cotonou 2026',
            'site_id' => $this->cotonou->id,
            'start_date' => '2026-09-01',
            'end_date' => '2027-06-30',
            'status' => 'en_cours',
        ]);

        $formation = Formation::create([
            'name' => 'Gestion de Projet',
            'prix' => 150000,
            'is_active' => true,
        ]);

        // Étudiant initialement rattaché uniquement à Bohicon dans site_user
        $mobileStudent = User::create([
            'first_name' => 'Mireille',
            'last_name' => 'Mobile',
            'email' => 'mireille.mobile@ifam.test',
            'matricule' => 'ETU-2026-MOB001',
            'password' => Hash::make('Password123!'),
            'is_active' => true,
        ]);
        $mobileStudent->assignRole('etudiant');
        $mobileStudent->sites()->attach($this->bohicon->id);

        $mobileProfile = StudentProfile::create([
            'user_id' => $mobileStudent->id,
            'city' => 'Bohicon',
            'entry_year' => 2026,
        ]);

        // Inscription académique sur le site de Cotonou
        Enrollment::create([
            'student_profile_id' => $mobileProfile->id,
            'formation_id' => $formation->id,
            'academic_session_id' => $session->id,
            'status' => 'active',
            'enrollment_date' => '2026-09-15',
        ]);

        // L'admin Cotonou doit maintenant voir Mireille dans sa liste
        $indexResponse = $this->actingAs($this->cotonouAdmin)->get(route('admin.students.index'));
        $indexResponse->assertOk();
        $this->assertTrue($indexResponse->viewData('students')->contains($mobileStudent));

        // L'admin Cotonou doit pouvoir consulter et modifier son profil
        $showResponse = $this->actingAs($this->cotonouAdmin)->get(route('admin.students.show', $mobileStudent));
        $showResponse->assertOk();
        $showResponse->assertSee('Mireille Mobile');

        $editResponse = $this->actingAs($this->cotonouAdmin)->get(route('admin.students.edit', $mobileStudent));
        $editResponse->assertOk();

        $updateResponse = $this->actingAs($this->cotonouAdmin)->put(route('admin.students.update', $mobileStudent), [
            'city' => 'Cotonou Haie Vive',
            'entry_year' => 2026,
        ]);
        $updateResponse->assertRedirect(route('admin.students.show', $mobileStudent));

        $mobileProfile->refresh();
        $this->assertSame('Cotonou Haie Vive', $mobileProfile->city);
    }

    /**
     * Un étudiant sans site et sans inscription est strictement inaccessible au responsable local.
     */
    public function test_student_without_any_site_or_enrollment_is_strictly_forbidden_to_local_admin(): void
    {
        // Absent de la liste
        $indexResponse = $this->actingAs($this->cotonouAdmin)->get(route('admin.students.index'));
        $indexResponse->assertOk();
        $this->assertFalse($indexResponse->viewData('students')->contains($this->unassignedStudent));

        // 403 sur show
        $showResponse = $this->actingAs($this->cotonouAdmin)->get(route('admin.students.show', $this->unassignedStudent));
        $showResponse->assertForbidden();

        // 403 sur edit
        $editResponse = $this->actingAs($this->cotonouAdmin)->get(route('admin.students.edit', $this->unassignedStudent));
        $editResponse->assertForbidden();

        // 403 sur update
        $updateResponse = $this->actingAs($this->cotonouAdmin)->put(route('admin.students.update', $this->unassignedStudent), [
            'city' => 'Tentative Cotonou',
        ]);
        $updateResponse->assertForbidden();
    }

    /**
     * L'administrateur général conserve un accès global et peut filtrer par site.
     */
    public function test_superadmin_has_global_access_and_can_filter_by_site(): void
    {
        // 1. Vue globale sans filtre
        $response = $this->actingAs($this->superAdmin)->get(route('admin.students.index'));
        $response->assertOk();
        $this->assertSame(3, $response->viewData('students')->total());
        $response->assertSee('Aïcha Cotonou');
        $response->assertSee('Koffi Bohicon');
        $response->assertSee('Orphelin SansSite');

        // 2. Filtre sur Cotonou
        $responseCotonou = $this->actingAs($this->superAdmin)
            ->get(route('admin.students.index', ['site_id' => $this->cotonou->id]));
        $responseCotonou->assertOk();
        $this->assertSame(1, $responseCotonou->viewData('students')->total());
        $responseCotonou->assertSee('Aïcha Cotonou');
        $responseCotonou->assertDontSee('Koffi Bohicon');

        // 3. Filtre sur Bohicon
        $responseBohicon = $this->actingAs($this->superAdmin)
            ->get(route('admin.students.index', ['site_id' => $this->bohicon->id]));
        $responseBohicon->assertOk();
        $this->assertSame(1, $responseBohicon->viewData('students')->total());
        $responseBohicon->assertSee('Koffi Bohicon');
        $responseBohicon->assertDontSee('Aïcha Cotonou');

        // 4. Accès show à n'importe quel étudiant
        $this->actingAs($this->superAdmin)->get(route('admin.students.show', $this->cotonouStudent))->assertOk();
        $this->actingAs($this->superAdmin)->get(route('admin.students.show', $this->bohiconStudent))->assertOk();
        $this->actingAs($this->superAdmin)->get(route('admin.students.show', $this->unassignedStudent))->assertOk();
    }

    /**
     * La Direction Générale conserve son accès transversal via son espace Direction et hasSiteAccess.
     */
    public function test_direction_retains_transversal_access(): void
    {
        // DG a un accès transversal à tous les sites
        $this->assertTrue($this->dgUser->hasSiteAccess($this->cotonou));
        $this->assertTrue($this->dgUser->hasSiteAccess($this->bohicon));

        // Consultation de la liste transversale Direction
        $response = $this->actingAs($this->dgUser)->get(route('direction.students.index'));
        $response->assertOk();
        $response->assertSee('Aïcha');
        $response->assertSee('Koffi');

        // Consultation du détail étudiant côté Direction
        $this->actingAs($this->dgUser)->get(route('direction.students.show', $this->cotonouStudent->studentProfile))->assertOk();
        $this->actingAs($this->dgUser)->get(route('direction.students.show', $this->bohiconStudent->studentProfile))->assertOk();
    }

    /**
     * Les routes orphelines (create, store, destroy) ne sont plus exposées par la ressource.
     */
    public function test_orphan_routes_are_not_registered(): void
    {
        // create n'existe plus sur la ressource
        $this->actingAs($this->superAdmin)->get('/admin/students/create')->assertNotFound();

        // store n'existe plus
        $this->actingAs($this->superAdmin)->post('/admin/students', [])->assertStatus(405);

        // destroy n'existe plus
        $this->actingAs($this->superAdmin)->delete('/admin/students/'.$this->cotonouStudent->id)->assertStatus(405);
    }
}
