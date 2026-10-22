<?php

namespace Tests\Feature\Admin;

use App\Models\AcademicSession;
use App\Models\Alumni;
use App\Models\Attendance;
use App\Models\BulletinPublication;
use App\Models\Course;
use App\Models\Evaluation;
use App\Models\Formation;
use App\Models\Grade;
use App\Models\Module;
use App\Models\Partnership;
use App\Models\Site;
use App\Models\StudentProfile;
use App\Models\TeacherProfile;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Database\Seeders\SiteSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DaaOrganizationAndSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected Site $cotonou;

    protected Site $bohicon;

    protected User $superAdmin;

    protected User $directionDaa;

    protected User $respDaaCotonou;

    protected User $respEtudesCotonou;

    protected User $respInsertionCotonou;

    protected User $respDaaBohicon;

    protected AcademicSession $sessionCotonou;

    protected AcademicSession $sessionBohicon;

    protected Formation $formation;

    protected Module $module;

    protected Course $courseCotonou;

    protected Course $courseBohicon;

    protected Evaluation $evalCotonou;

    protected Evaluation $evalBohicon;

    protected Alumni $alumniCotonou;

    protected Alumni $alumniBohicon;

    protected Partnership $partnershipCotonou;

    protected Partnership $partnershipBohicon;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(SiteSeeder::class);
        $this->seed(RoleSeeder::class);

        $this->cotonou = Site::where('nom', 'Cotonou')->firstOrFail();
        $this->bohicon = Site::create(['nom' => 'Bohicon', 'ville' => 'Bohicon', 'est_actif' => true]);

        // SuperAdmin
        $this->superAdmin = User::create([
            'first_name' => 'Super',
            'last_name' => 'Admin',
            'email' => 'superadmin.daa@ifam.test',
            'password' => Hash::make('Password123!'),
            'is_active' => true,
        ]);
        $this->superAdmin->assignRole('administrateur');

        // Direction Générale DAA (vue transversale)
        $this->directionDaa = User::create([
            'first_name' => 'DG',
            'last_name' => 'DAA',
            'email' => 'dg.daa@ifam.test',
            'password' => Hash::make('Password123!'),
            'is_active' => true,
        ]);
        $this->directionDaa->assignRole('direction_daa');

        // Responsable DAA Cotonou
        $this->respDaaCotonou = User::create([
            'first_name' => 'Resp',
            'last_name' => 'DAA Cotonou',
            'email' => 'resp.daa.cotonou@ifam.test',
            'password' => Hash::make('Password123!'),
            'is_active' => true,
        ]);
        $this->respDaaCotonou->assignRole('responsable_daa');
        $this->respDaaCotonou->sites()->attach($this->cotonou->id);

        // Responsable Études Cotonou
        $this->respEtudesCotonou = User::create([
            'first_name' => 'Resp',
            'last_name' => 'Études Cotonou',
            'email' => 'resp.etudes.cotonou@ifam.test',
            'password' => Hash::make('Password123!'),
            'is_active' => true,
        ]);
        $this->respEtudesCotonou->assignRole('responsable_etudes');
        $this->respEtudesCotonou->sites()->attach($this->cotonou->id);

        // Responsable Insertion Cotonou
        $this->respInsertionCotonou = User::create([
            'first_name' => 'Resp',
            'last_name' => 'Insertion Cotonou',
            'email' => 'resp.insertion.cotonou@ifam.test',
            'password' => Hash::make('Password123!'),
            'is_active' => true,
        ]);
        $this->respInsertionCotonou->assignRole('responsable_insertion');
        $this->respInsertionCotonou->sites()->attach($this->cotonou->id);

        // Responsable DAA Bohicon
        $this->respDaaBohicon = User::create([
            'first_name' => 'Resp',
            'last_name' => 'DAA Bohicon',
            'email' => 'resp.daa.bohicon@ifam.test',
            'password' => Hash::make('Password123!'),
            'is_active' => true,
        ]);
        $this->respDaaBohicon->assignRole('responsable_daa');
        $this->respDaaBohicon->sites()->attach($this->bohicon->id);

        // Sessions académiques
        $this->sessionCotonou = AcademicSession::create([
            'name' => 'Session 2026 Cotonou',
            'start_date' => '2026-09-01',
            'end_date' => '2027-06-30',
            'status' => 'en_cours',
            'site_id' => $this->cotonou->id,
        ]);
        $this->sessionBohicon = AcademicSession::create([
            'name' => 'Session 2026 Bohicon',
            'start_date' => '2026-09-01',
            'end_date' => '2027-06-30',
            'status' => 'en_cours',
            'site_id' => $this->bohicon->id,
        ]);

        // Formations & Modules (Catalogue partagé)
        $this->formation = Formation::create(['name' => 'Génie Logiciel', 'is_active' => true]);
        $this->module = Module::create(['name' => 'Algorithmique & C', 'code' => 'GL101']);
        $this->formation->modules()->attach($this->module->id);

        // Enseignant
        $teacherUser = User::create([
            'first_name' => 'Prof',
            'last_name' => 'Test',
            'email' => 'prof.test@ifam.test',
            'password' => Hash::make('Password123!'),
            'is_active' => true,
        ]);
        $teacherUser->assignRole('enseignant');
        $teacherProfile = TeacherProfile::create(['user_id' => $teacherUser->id]);
        $this->module->teachers()->attach($teacherProfile->id);

        // Cours Cotonou & Bohicon
        $this->courseCotonou = Course::create([
            'formation_id' => $this->formation->id,
            'academic_session_id' => $this->sessionCotonou->id,
            'module_id' => $this->module->id,
            'statut' => 'en_cours',
        ]);
        $this->courseBohicon = Course::create([
            'formation_id' => $this->formation->id,
            'academic_session_id' => $this->sessionBohicon->id,
            'module_id' => $this->module->id,
            'statut' => 'en_cours',
        ]);

        // Évaluations
        $this->evalCotonou = Evaluation::create([
            'type' => 'devoir',
            'session_id' => $this->sessionCotonou->id,
            'formation_id' => $this->formation->id,
            'module_id' => $this->module->id,
            'assigned_teacher_profile_id' => $teacherProfile->id,
            'titre' => 'Devoir 1 Cotonou',
            'statut' => 'ouverte',
        ]);
        $this->evalBohicon = Evaluation::create([
            'type' => 'devoir',
            'session_id' => $this->sessionBohicon->id,
            'formation_id' => $this->formation->id,
            'module_id' => $this->module->id,
            'assigned_teacher_profile_id' => $teacherProfile->id,
            'titre' => 'Devoir 1 Bohicon',
            'statut' => 'ouverte',
        ]);

        // Alumni
        $this->alumniCotonou = Alumni::create([
            'nom' => 'Koffi',
            'prenom' => 'Jean',
            'email' => 'jean.koffi@alumni.test',
            'site_id' => $this->cotonou->id,
            'is_approved' => true,
        ]);
        $this->alumniBohicon = Alumni::create([
            'nom' => 'Sossou',
            'prenom' => 'Paul',
            'email' => 'paul.sossou@alumni.test',
            'site_id' => $this->bohicon->id,
            'is_approved' => true,
        ]);

        // Partenariats
        $this->partnershipCotonou = Partnership::create([
            'nom' => 'MTN Bénin',
            'type_partenariat' => 'Stage',
            'statut' => 'Actif',
            'site_id' => $this->cotonou->id,
        ]);
        $this->partnershipBohicon = Partnership::create([
            'nom' => 'Moov Bohicon',
            'type_partenariat' => 'Stage',
            'statut' => 'Actif',
            'site_id' => $this->bohicon->id,
        ]);
    }

    /**
     * 1. Redirection /dashboard et accès au tableau de bord pour les rôles DAA.
     */
    public function test_daa_roles_redirect_to_admin_dashboard(): void
    {
        $this->actingAs($this->directionDaa)->get(route('dashboard'))
            ->assertRedirect(route('admin.dashboard'));

        $this->actingAs($this->respDaaCotonou)->get(route('dashboard'))
            ->assertRedirect(route('admin.dashboard'));

        $this->actingAs($this->respEtudesCotonou)->get(route('dashboard'))
            ->assertRedirect(route('admin.dashboard'));

        $this->actingAs($this->respInsertionCotonou)->get(route('dashboard'))
            ->assertRedirect(route('admin.dashboard'));
    }

    /**
     * 2. Direction Générale DAA a une vision globale sur tous les sites.
     */
    public function test_direction_daa_sees_all_sites_data(): void
    {
        $response = $this->actingAs($this->directionDaa)->get(route('admin.courses.index'));
        $response->assertOk();
        $response->assertSee('Session 2026 Cotonou');
        $response->assertSee('Session 2026 Bohicon');

        $responseAlumni = $this->actingAs($this->directionDaa)->get(route('admin.alumnis.index'));
        $responseAlumni->assertOk();
        $responseAlumni->assertSee('Koffi');
        $responseAlumni->assertSee('Sossou');

        $filteredAlumni = $this->actingAs($this->directionDaa)->get(route('admin.alumnis.index', [
            'site_id' => $this->bohicon->id,
        ]));
        $filteredAlumni->assertOk()
            ->assertViewHas('alumnis', fn ($alumnis) => $alumnis->getCollection()->modelKeys() === [$this->alumniBohicon->id]);

        $filteredEvaluations = $this->actingAs($this->directionDaa)->get(route('admin.evaluations.index', [
            'site_id' => $this->bohicon->id,
        ]));
        $filteredEvaluations->assertOk()
            ->assertViewHas('evaluations', fn ($evaluations) => $evaluations->getCollection()->modelKeys() === [$this->evalBohicon->id]);

        $siteFilter = ['site_id' => $this->bohicon->id];
        $this->actingAs($this->directionDaa)->get(route('admin.attendance.index', $siteFilter))
            ->assertOk()
            ->assertViewHas('courses', fn ($courses) => $courses->modelKeys() === [$this->courseBohicon->id]);
        $this->actingAs($this->directionDaa)->get(route('admin.grades.index', $siteFilter))
            ->assertOk()
            ->assertViewHas('sessions', fn ($sessions) => $sessions->modelKeys() === [$this->sessionBohicon->id]);
        $this->actingAs($this->directionDaa)->get(route('admin.results.index', $siteFilter))
            ->assertOk()
            ->assertViewHas('sessions', fn ($sessions) => $sessions->modelKeys() === [$this->sessionBohicon->id]);
        $this->actingAs($this->directionDaa)->get(route('admin.bulletins.index', $siteFilter))
            ->assertOk()
            ->assertViewHas('sessions', fn ($sessions) => $sessions->modelKeys() === [$this->sessionBohicon->id]);

        $responsePartnerships = $this->actingAs($this->directionDaa)->get(route('admin.partnerships.index'));
        $responsePartnerships->assertOk();
        $responsePartnerships->assertSee('MTN Bénin');
        $responsePartnerships->assertSee('Moov Bohicon');
    }

    /**
     * 3. Direction Générale DAA peut gérer le catalogue partagé des formations et modules.
     */
    public function test_direction_daa_can_manage_shared_catalog(): void
    {
        $response = $this->actingAs($this->directionDaa)->post(route('admin.formations.store'), [
            'name' => 'Data Science & IA',
            'is_active' => true,
        ]);
        $response->assertRedirect(route('admin.formations.index'));
        $this->assertDatabaseHas('formations', ['name' => 'Data Science & IA']);

        $responseModule = $this->actingAs($this->directionDaa)->post(route('admin.modules.store'), [
            'formation_id' => $this->formation->id,
            'name' => 'Machine Learning',
            'code' => 'DS201',
        ]);
        $responseModule->assertRedirect(route('admin.modules.index'));
        $this->assertDatabaseHas('modules', ['code' => 'DS201']);
    }

    public function test_direction_daa_dashboard_shows_academic_and_insertion_supervision_only(): void
    {
        $studentUser = User::create([
            'first_name' => 'Étudiant',
            'last_name' => 'Dashboard',
            'email' => 'student.dashboard.daa@ifam.test',
            'password' => Hash::make('Password123!'),
            'is_active' => true,
        ]);
        $studentProfile = StudentProfile::create(['user_id' => $studentUser->id]);

        Grade::create([
            'evaluation_id' => $this->evalCotonou->id,
            'student_profile_id' => $studentProfile->id,
            'score' => 16,
        ]);
        Attendance::create([
            'course_id' => $this->courseCotonou->id,
            'student_profile_id' => $studentProfile->id,
            'date' => today(),
            'status' => 'present',
        ]);
        BulletinPublication::create([
            'formation_id' => $this->formation->id,
            'academic_session_id' => $this->sessionCotonou->id,
            'student_profile_id' => $studentProfile->id,
            'is_published' => true,
        ]);

        $response = $this->actingAs($this->directionDaa)->get(route('admin.dashboard'));

        $response->assertOk()
            ->assertSee('Affaires Académiques')
            ->assertSee('Catalogue Commun')
            ->assertSee('Supervision Pédagogique')
            ->assertSee('Insertion & Alumni')
            ->assertSee('Formations')
            ->assertSee('Sessions académiques')
            ->assertSee('Résultats')
            ->assertSee('Partenariats')
            ->assertDontSee('Supervision Financière')
            ->assertDontSee('Caisse & Recouvrement')
            ->assertDontSee('Paiements récents')
            ->assertViewHas('catalogueSupervision.formations', 1)
            ->assertViewHas('catalogueSupervision.modules', 1)
            ->assertViewHas('pedagogicalSupervision.sessions', 2)
            ->assertViewHas('pedagogicalSupervision.currentSessions', 0)
            ->assertViewHas('pedagogicalSupervision.courses', 2)
            ->assertViewHas('pedagogicalSupervision.evaluations', 2)
            ->assertViewHas('pedagogicalSupervision.grades', 1)
            ->assertViewHas('pedagogicalSupervision.attendances', 1)
            ->assertViewHas('pedagogicalSupervision.bulletins', 1)
            ->assertViewHas('pedagogicalSupervision.publishedBulletins', 1)
            ->assertViewHas('insertionSupervision.alumni', 2)
            ->assertViewHas('insertionSupervision.partnerships', 2);
        $this->assertArrayNotHasKey('financialSupervision', $response->viewData());
        $this->assertArrayNotHasKey('totalRevenue', $response->viewData());
    }

    public function test_responsable_daa_dashboard_only_shows_academic_and_insertion_data_for_assigned_sites(): void
    {
        $studentUser = User::create([
            'first_name' => 'Étudiant',
            'last_name' => 'Dashboard local',
            'email' => 'student.dashboard.local.daa@ifam.test',
            'password' => Hash::make('Password123!'),
            'is_active' => true,
        ]);
        $studentProfile = StudentProfile::create(['user_id' => $studentUser->id]);

        Grade::create([
            'evaluation_id' => $this->evalCotonou->id,
            'student_profile_id' => $studentProfile->id,
            'score' => 14,
        ]);
        Attendance::create([
            'course_id' => $this->courseCotonou->id,
            'student_profile_id' => $studentProfile->id,
            'date' => today(),
            'status' => 'present',
        ]);
        BulletinPublication::create([
            'formation_id' => $this->formation->id,
            'academic_session_id' => $this->sessionCotonou->id,
            'student_profile_id' => $studentProfile->id,
            'is_published' => true,
        ]);
        Partnership::create([
            'nom' => 'Partenariat partagé',
            'type_partenariat' => 'Stage',
            'statut' => 'Actif',
            'site_id' => null,
        ]);

        $response = $this->actingAs($this->respDaaCotonou)->get(route('admin.dashboard'));

        $response->assertOk()
            ->assertSee('DAA (Cotonou)')
            ->assertSee('Pédagogie du site')
            ->assertSee('Insertion du site')
            ->assertDontSee('Supervision Financière')
            ->assertDontSee('Caisse & Recouvrement')
            ->assertDontSee('Catalogue Commun')
            ->assertViewHas('pedagogicalSupervision.sessions', 1)
            ->assertViewHas('pedagogicalSupervision.courses', 1)
            ->assertViewHas('pedagogicalSupervision.evaluations', 1)
            ->assertViewHas('pedagogicalSupervision.grades', 1)
            ->assertViewHas('pedagogicalSupervision.attendances', 1)
            ->assertViewHas('pedagogicalSupervision.bulletins', 1)
            ->assertViewHas('pedagogicalSupervision.publishedBulletins', 1)
            ->assertViewHas('insertionSupervision.alumni', 1)
            ->assertViewHas('insertionSupervision.partnerships', 2);

        $this->assertArrayNotHasKey('financialSupervision', $response->viewData());
        $this->assertArrayNotHasKey('totalRevenue', $response->viewData());
    }

    public function test_responsable_etudes_dashboard_only_shows_pedagogical_data_for_assigned_sites(): void
    {
        $studentUser = User::create([
            'first_name' => 'Étudiant',
            'last_name' => 'Dashboard études',
            'email' => 'student.dashboard.etudes@ifam.test',
            'password' => Hash::make('Password123!'),
            'is_active' => true,
        ]);
        $studentProfile = StudentProfile::create(['user_id' => $studentUser->id]);

        foreach ([
            [$this->evalCotonou, $this->courseCotonou],
            [$this->evalBohicon, $this->courseBohicon],
        ] as [$evaluation, $course]) {
            Grade::create([
                'evaluation_id' => $evaluation->id,
                'student_profile_id' => $studentProfile->id,
                'score' => 15,
            ]);
            Attendance::create([
                'course_id' => $course->id,
                'student_profile_id' => $studentProfile->id,
                'date' => today(),
                'status' => 'present',
            ]);
        }

        foreach ([$this->sessionCotonou, $this->sessionBohicon] as $session) {
            BulletinPublication::create([
                'formation_id' => $this->formation->id,
                'academic_session_id' => $session->id,
                'student_profile_id' => $studentProfile->id,
                'is_published' => true,
            ]);
        }

        $response = $this->actingAs($this->respEtudesCotonou)->get(route('admin.dashboard'));

        $response->assertOk()
            ->assertSee('Études (Cotonou)')
            ->assertSee('Gestion pédagogique')
            ->assertDontSee('Insertion du site')
            ->assertDontSee('Catalogue Commun')
            ->assertDontSee('Supervision Financière')
            ->assertDontSee('Caisse & Recouvrement')
            ->assertViewHas('pedagogicalSupervision.sessions', 1)
            ->assertViewHas('pedagogicalSupervision.courses', 1)
            ->assertViewHas('pedagogicalSupervision.evaluations', 1)
            ->assertViewHas('pedagogicalSupervision.grades', 1)
            ->assertViewHas('pedagogicalSupervision.attendances', 1)
            ->assertViewHas('pedagogicalSupervision.bulletins', 1)
            ->assertViewHas('pedagogicalSupervision.publishedBulletins', 1);

        $this->assertArrayNotHasKey('financialSupervision', $response->viewData());
        $this->assertArrayNotHasKey('insertionSupervision', $response->viewData());
        $this->assertArrayNotHasKey('totalRevenue', $response->viewData());
    }

    public function test_responsable_insertion_dashboard_only_shows_insertion_data_for_assigned_sites(): void
    {
        Alumni::create([
            'nom' => 'En attente',
            'prenom' => 'Alumni',
            'email' => 'pending.cotonou@alumni.test',
            'site_id' => $this->cotonou->id,
            'is_approved' => false,
        ]);
        Alumni::create([
            'nom' => 'Autre site',
            'prenom' => 'Alumni',
            'email' => 'pending.bohicon@alumni.test',
            'site_id' => $this->bohicon->id,
            'is_approved' => false,
        ]);
        Partnership::create([
            'nom' => 'Partenariat en discussion',
            'type_partenariat' => 'Stage',
            'statut' => 'En discussion',
            'site_id' => $this->cotonou->id,
        ]);

        $response = $this->actingAs($this->respInsertionCotonou)->get(route('admin.dashboard'));

        $response->assertOk()
            ->assertSee('Insertion (Cotonou)')
            ->assertSee('Insertion professionnelle')
            ->assertSee('À approuver')
            ->assertDontSee('Gestion pédagogique')
            ->assertDontSee('Supervision Financière')
            ->assertViewHas('insertionDashboard.alumni', 2)
            ->assertViewHas('insertionDashboard.approvedAlumni', 1)
            ->assertViewHas('insertionDashboard.pendingAlumni', 1)
            ->assertViewHas('insertionDashboard.partnerships', 2)
            ->assertViewHas('insertionDashboard.activePartnerships', 1);

        $this->assertArrayNotHasKey('pedagogicalSupervision', $response->viewData());
        $this->assertArrayNotHasKey('financialSupervision', $response->viewData());
    }

    /**
     * 4. Les rôles DAA locaux ne peuvent pas modifier le catalogue partagé des formations et modules.
     */
    public function test_local_daa_roles_cannot_modify_shared_catalog(): void
    {
        $response = $this->actingAs($this->respDaaCotonou)->post(route('admin.formations.store'), [
            'name' => 'Formation Interdite',
        ]);
        $response->assertForbidden();

        $responseModule = $this->actingAs($this->respEtudesCotonou)->post(route('admin.modules.store'), [
            'formation_id' => $this->formation->id,
            'name' => 'Module Interdit',
            'code' => 'INT999',
        ]);
        $responseModule->assertForbidden();
    }

    /**
     * 5. Cloisonnement multi-sites : Responsable DAA Cotonou ne voit que les cours de son site.
     */
    public function test_responsable_daa_sees_only_assigned_site_courses(): void
    {
        $response = $this->actingAs($this->respDaaCotonou)->get(route('admin.courses.index'));
        $response->assertOk();
        $response->assertSee('Session 2026 Cotonou');
        $response->assertDontSee('Session 2026 Bohicon');

        // Accès direct au cours de Bohicon -> 403
        $this->actingAs($this->respDaaCotonou)->get(route('admin.courses.edit', $this->courseBohicon))
            ->assertForbidden();
    }

    /**
     * 6. Cloisonnement multi-sites : Évaluations.
     */
    public function test_responsable_daa_evaluations_multi_site_isolation(): void
    {
        $response = $this->actingAs($this->respDaaCotonou)->get(route('admin.evaluations.index'));
        $response->assertOk();
        $response->assertSee('Devoir 1 Cotonou');
        $response->assertDontSee('Devoir 1 Bohicon');

        $this->actingAs($this->respDaaCotonou)->get(route('admin.evaluations.edit', $this->evalBohicon))
            ->assertForbidden();
    }

    /**
     * 7. Cloisonnement multi-sites : Alumni et Partenariats.
     */
    public function test_alumni_and_partnerships_multi_site_isolation(): void
    {
        // Index Alumni
        $responseAlumni = $this->actingAs($this->respInsertionCotonou)->get(route('admin.alumnis.index'));
        $responseAlumni->assertOk();
        $responseAlumni->assertSee('Koffi');
        $responseAlumni->assertDontSee('Sossou');

        // Fiche Alumni d'un autre site -> 403
        $this->actingAs($this->respInsertionCotonou)->get(route('admin.alumnis.show', $this->alumniBohicon))
            ->assertForbidden();

        // Index Partenariats
        $responsePart = $this->actingAs($this->respInsertionCotonou)->get(route('admin.partnerships.index'));
        $responsePart->assertOk();
        $responsePart->assertSee('MTN Bénin');
        $responsePart->assertDontSee('Moov Bohicon');

        // Fiche Partenariat d'un autre site -> 403
        $this->actingAs($this->respInsertionCotonou)->get(route('admin.partnerships.show', $this->partnershipBohicon))
            ->assertForbidden();
    }

    /**
     * 8. Interdiction stricte : Les profils DAA ne peuvent pas accéder aux données financières.
     */
    public function test_daa_roles_cannot_access_financial_modules(): void
    {
        // Tentative d'accès aux paiements
        $this->actingAs($this->directionDaa)->get(route('admin.payments.index'))
            ->assertForbidden();

        $this->actingAs($this->respDaaCotonou)->get(route('admin.payments.index'))
            ->assertForbidden();

        // Tentative d'accès aux frais
        $this->actingAs($this->respEtudesCotonou)->get(route('admin.fees.index'))
            ->assertForbidden();

        // Tentative d'accès aux dépenses
        $this->actingAs($this->respInsertionCotonou)->get(route('admin.expenses.index'))
            ->assertForbidden();

        // Tentative d'accès aux bourses & réductions
        $this->actingAs($this->respDaaCotonou)->get(route('admin.discounts.index'))
            ->assertForbidden();
    }

    /**
     * 9. Interdiction stricte : Les profils financiers (DAF) ne peuvent pas modifier les évaluations ou résultats.
     */
    public function test_financial_roles_cannot_modify_pedagogical_data(): void
    {
        $comptable = User::create([
            'first_name' => 'Comptable',
            'last_name' => 'DAF',
            'email' => 'comptable.test.daf@ifam.test',
            'password' => Hash::make('Password123!'),
            'is_active' => true,
        ]);
        $comptable->assignRole('comptable');
        $comptable->sites()->attach($this->cotonou->id);

        // Tentative de création d'évaluation
        $this->actingAs($comptable)->post(route('admin.evaluations.store'), [
            'formation_id' => $this->formation->id,
            'session_id' => $this->sessionCotonou->id,
            'module_id' => $this->module->id,
            'assigned_teacher_profile_id' => 1,
            'titre' => 'Devoir Illégitime',
            'statut' => 'ouverte',
        ])->assertForbidden();

        // Tentative de publication de bulletin
        $studentUser = User::create([
            'first_name' => 'Student',
            'last_name' => 'Pedago',
            'email' => 'student.pedago@ifam.test',
            'password' => Hash::make('Password123!'),
            'is_active' => true,
        ]);
        $studentProfile = StudentProfile::create(['user_id' => $studentUser->id]);

        $this->actingAs($comptable)->post(route('admin.bulletins.publish', [
            'student' => $studentProfile->id,
            'session_id' => $this->sessionCotonou->id,
            'formation_id' => $this->formation->id,
        ]))->assertForbidden();
    }
}
