<?php

namespace Tests\Feature\Admin;

use App\Models\AcademicSession;
use App\Models\Discount;
use App\Models\Document;
use App\Models\Enrollment;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Fee;
use App\Models\Formation;
use App\Models\Payment;
use App\Models\Room;
use App\Models\Site;
use App\Models\StudentProfile;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Database\Seeders\SiteSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DafOrganizationAndSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected Site $cotonou;

    protected Site $bohicon;

    protected User $superAdmin;

    protected User $directionDaf;

    protected User $rafCotonou;

    protected User $agentAdminCotonou;

    protected User $caissierCotonou;

    protected User $comptableCotonou;

    protected User $caissierBohicon;

    protected User $studentUserCotonou;

    protected StudentProfile $studentProfileCotonou;

    protected User $studentUserBohicon;

    protected StudentProfile $studentProfileBohicon;

    protected AcademicSession $sessionCotonou;

    protected AcademicSession $sessionBohicon;

    protected Formation $formation;

    protected Enrollment $enrollmentCotonou;

    protected Enrollment $enrollmentBohicon;

    protected Payment $paymentCotonou;

    protected Payment $paymentBohicon;

    protected ExpenseCategory $expenseCategory;

    protected Expense $expenseCotonou;

    protected Expense $expenseBohicon;

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
            'email' => 'superadmin@ifam.test',
            'password' => Hash::make('Password123!'),
            'is_active' => true,
        ]);
        $this->superAdmin->assignRole('administrateur');

        // 2. Direction DAF (transversale)
        $this->directionDaf = User::create([
            'first_name' => 'Directeur',
            'last_name' => 'DAF',
            'email' => 'direction.daf@ifam.test',
            'password' => Hash::make('Password123!'),
            'is_active' => true,
        ]);
        $this->directionDaf->assignRole('direction_daf');

        // 3. RAF Cotonou
        $this->rafCotonou = User::create([
            'first_name' => 'RAF',
            'last_name' => 'Cotonou',
            'email' => 'raf.cotonou@ifam.test',
            'password' => Hash::make('Password123!'),
            'is_active' => true,
        ]);
        $this->rafCotonou->assignRole('responsable_administratif_financier');
        $this->rafCotonou->sites()->attach($this->cotonou->id);

        // 4. Agent administratif Cotonou
        $this->agentAdminCotonou = User::create([
            'first_name' => 'Agent',
            'last_name' => 'AdminCotonou',
            'email' => 'admin.cotonou@ifam.test',
            'password' => Hash::make('Password123!'),
            'is_active' => true,
        ]);
        $this->agentAdminCotonou->assignRole('agent_administratif');
        $this->agentAdminCotonou->sites()->attach($this->cotonou->id);

        // 5. Caissier Cotonou
        $this->caissierCotonou = User::create([
            'first_name' => 'Caissier',
            'last_name' => 'Cotonou',
            'email' => 'caissier.cotonou@ifam.test',
            'password' => Hash::make('Password123!'),
            'is_active' => true,
        ]);
        $this->caissierCotonou->assignRole('caissier');
        $this->caissierCotonou->sites()->attach($this->cotonou->id);

        // 6. Comptable Cotonou
        $this->comptableCotonou = User::create([
            'first_name' => 'Comptable',
            'last_name' => 'Cotonou',
            'email' => 'comptable.cotonou@ifam.test',
            'password' => Hash::make('Password123!'),
            'is_active' => true,
        ]);
        $this->comptableCotonou->assignRole('comptable');
        $this->comptableCotonou->sites()->attach($this->cotonou->id);

        // 7. Caissier Bohicon
        $this->caissierBohicon = User::create([
            'first_name' => 'Caissier',
            'last_name' => 'Bohicon',
            'email' => 'caissier.bohicon@ifam.test',
            'password' => Hash::make('Password123!'),
            'is_active' => true,
        ]);
        $this->caissierBohicon->assignRole('caissier');
        $this->caissierBohicon->sites()->attach($this->bohicon->id);

        // Étudiants et inscriptions
        $this->studentUserCotonou = User::create([
            'first_name' => 'Aïcha',
            'last_name' => 'Cotonou',
            'email' => 'aicha.cotonou@ifam.test',
            'password' => Hash::make('Password123!'),
            'is_active' => true,
        ]);
        $this->studentUserCotonou->assignRole('etudiant');
        $this->studentUserCotonou->sites()->attach($this->cotonou->id);
        $this->studentProfileCotonou = StudentProfile::create(['user_id' => $this->studentUserCotonou->id]);

        $this->studentUserBohicon = User::create([
            'first_name' => 'Koffi',
            'last_name' => 'Bohicon',
            'email' => 'koffi.bohicon@ifam.test',
            'password' => Hash::make('Password123!'),
            'is_active' => true,
        ]);
        $this->studentUserBohicon->assignRole('etudiant');
        $this->studentUserBohicon->sites()->attach($this->bohicon->id);
        $this->studentProfileBohicon = StudentProfile::create(['user_id' => $this->studentUserBohicon->id]);

        $this->formation = Formation::create([
            'name' => 'Gestion Commerciale',
            'code' => 'GC-01',
            'prix' => 100000,
        ]);

        $this->sessionCotonou = AcademicSession::create([
            'name' => 'Session Cotonou 2026',
            'start_date' => '2026-09-01',
            'end_date' => '2027-06-30',
            'site_id' => $this->cotonou->id,
            'is_current' => true,
        ]);

        $this->sessionBohicon = AcademicSession::create([
            'name' => 'Session Bohicon 2026',
            'start_date' => '2026-09-01',
            'end_date' => '2027-06-30',
            'site_id' => $this->bohicon->id,
            'is_current' => true,
        ]);

        $this->enrollmentCotonou = Enrollment::create([
            'student_profile_id' => $this->studentProfileCotonou->id,
            'academic_session_id' => $this->sessionCotonou->id,
            'site_id' => $this->cotonou->id,
            'formation_id' => $this->formation->id,
            'statut' => 'validee',
            'total_amount' => 100000,
            'final_amount' => 100000,
            'montant_du' => 100000,
        ]);

        $this->enrollmentBohicon = Enrollment::create([
            'student_profile_id' => $this->studentProfileBohicon->id,
            'academic_session_id' => $this->sessionBohicon->id,
            'site_id' => $this->bohicon->id,
            'formation_id' => $this->formation->id,
            'statut' => 'validee',
            'total_amount' => 100000,
            'final_amount' => 100000,
            'montant_du' => 100000,
        ]);

        $this->paymentCotonou = Payment::create([
            'inscription_id' => $this->enrollmentCotonou->id,
            'montant' => 25000,
            'mode' => 'especes',
            'reference' => 'PAY-COT-001',
            'date_paiement' => '2026-10-01',
            'status' => 'valide',
            'enregistre_par' => $this->caissierCotonou->id,
        ]);

        $this->paymentBohicon = Payment::create([
            'inscription_id' => $this->enrollmentBohicon->id,
            'montant' => 30000,
            'mode' => 'virement',
            'reference' => 'PAY-BOH-001',
            'date_paiement' => '2026-10-02',
            'status' => 'valide',
            'enregistre_par' => $this->caissierBohicon->id,
        ]);

        $this->expenseCategory = ExpenseCategory::create([
            'name' => 'Fournitures de bureau',
            'code' => 'FB-01',
        ]);

        $this->expenseCotonou = Expense::create([
            'site_id' => $this->cotonou->id,
            'expense_category_id' => $this->expenseCategory->id,
            'title' => 'Papier Cotonou',
            'amount' => 15000,
            'expense_date' => '2026-10-05',
        ]);

        $this->expenseBohicon = Expense::create([
            'site_id' => $this->bohicon->id,
            'expense_category_id' => $this->expenseCategory->id,
            'title' => 'Papier Bohicon',
            'amount' => 18000,
            'expense_date' => '2026-10-06',
        ]);
    }

    /**
     * 1. Un agent administratif ne peut modifier que les informations autorisées.
     *    - Peut consulter et éditer les étudiants de son site.
     *    - Refus côté serveur si tentative d'accès à un autre site.
     *    - Refus côté serveur si tentative d'accès aux évaluations / notes.
     */
    public function test_administrative_agent_can_only_modify_authorized_info(): void
    {
        // Accès étudiant de son site : OK
        $response = $this->actingAs($this->agentAdminCotonou)->get(route('admin.students.show', $this->studentUserCotonou));
        $response->assertOk();

        // Accès étudiant d'un autre site : 403
        $responseCross = $this->actingAs($this->agentAdminCotonou)->get(route('admin.students.show', $this->studentUserBohicon));
        $responseCross->assertForbidden();

        // Modification profil étudiant de son site : OK
        $updateResp = $this->actingAs($this->agentAdminCotonou)->put(route('admin.students.update', $this->studentUserCotonou), [
            'admin_notes' => 'Dossier administratif complet et vérifié',
            'city' => 'Cotonou',
        ]);
        $updateResp->assertRedirect();
        $this->assertSame('Dossier administratif complet et vérifié', $this->studentProfileCotonou->fresh()->admin_notes);
        $this->assertSame('Cotonou', $this->studentProfileCotonou->fresh()->city);

        // Tentative de gestion des évaluations pédagogiques : 403
        $evalResp = $this->actingAs($this->agentAdminCotonou)->get(route('admin.evaluations.create'));
        $evalResp->assertForbidden();
    }

    /**
     * 2. Un agent financier ne peut consulter que les opérations de son site.
     */
    public function test_financial_agent_can_only_view_and_manage_operations_of_own_site(): void
    {
        // Caissier Cotonou sur index des paiements : voit Cotonou, ne voit pas Bohicon
        $response = $this->actingAs($this->caissierCotonou)->get(route('admin.payments.index'));
        $response->assertOk();
        $response->assertSee('PAY-COT-001');
        $response->assertDontSee('PAY-BOH-001');

        // Caissier Cotonou tente d'accéder au reçu d'un paiement de Bohicon : 403
        $receiptCross = $this->actingAs($this->caissierCotonou)->get(route('admin.payments.receipt', $this->paymentBohicon));
        $receiptCross->assertForbidden();

        // Caissier Cotonou sur les dépenses : voit Cotonou, ne voit pas Bohicon
        $expenseResp = $this->actingAs($this->caissierCotonou)->get(route('admin.expenses.index'));
        $expenseResp->assertOk();
        $expenseResp->assertSee('Papier Cotonou');
        $expenseResp->assertDontSee('Papier Bohicon');

        // Caissier Cotonou tente d'éditer une dépense de Bohicon : 403
        $expenseEditCross = $this->actingAs($this->caissierCotonou)->get(route('admin.expenses.edit', $this->expenseBohicon));
        $expenseEditCross->assertForbidden();
    }

    /**
     * 3. Un agent financier ne peut pas modifier de notes, ni d'évaluations, ni les dossiers étudiants (HTTP 403).
     */
    public function test_financial_agent_cannot_modify_grades_nor_evaluations_nor_student_dossiers(): void
    {
        // 1. Accès création / modification évaluations : 403
        $evalCreate = $this->actingAs($this->caissierCotonou)->get(route('admin.evaluations.create'));
        $evalCreate->assertForbidden();

        $evalStore = $this->actingAs($this->caissierCotonou)->post(route('admin.evaluations.store'), [
            'titre' => 'Examen illégal',
        ]);
        $evalStore->assertForbidden();

        // 2. Publication de bulletin : 403
        $bulletinPublish = $this->actingAs($this->comptableCotonou)->post(route('admin.bulletins.publish', $this->studentProfileCotonou));
        $bulletinPublish->assertForbidden();

        // 3. Modification de dossier étudiant administratif : 403
        $studentEdit = $this->actingAs($this->caissierCotonou)->get(route('admin.students.edit', $this->studentUserCotonou));
        $studentEdit->assertForbidden();

        $studentUpdate = $this->actingAs($this->caissierCotonou)->put(route('admin.students.update', $this->studentUserCotonou), [
            'first_name' => 'Piratage',
            'last_name' => 'Cotonou',
            'email' => 'pirate@ifam.test',
        ]);
        $studentUpdate->assertForbidden();
    }

    /**
     * 4. Un RAF supervise l'administratif et le financier de son propre site.
     */
    public function test_raf_supervises_administrative_and_financial_of_own_site(): void
    {
        // Supervision administrative du site (étudiants, inscriptions)
        $studentResp = $this->actingAs($this->rafCotonou)->get(route('admin.students.show', $this->studentUserCotonou));
        $studentResp->assertOk();

        $enrollmentResp = $this->actingAs($this->rafCotonou)->get(route('admin.enrollments.show', $this->enrollmentCotonou));
        $enrollmentResp->assertOk();

        // Supervision financière du site (paiements, dépenses)
        $paymentResp = $this->actingAs($this->rafCotonou)->get(route('admin.payments.index'));
        $paymentResp->assertOk();
        $paymentResp->assertSee('PAY-COT-001');
        $paymentResp->assertDontSee('PAY-BOH-001');

        $receiptResp = $this->actingAs($this->rafCotonou)->get(route('admin.payments.receipt', $this->paymentCotonou));
        $receiptResp->assertOk();

        // Tentative d'accès au site de Bohicon : 403
        $crossStudent = $this->actingAs($this->rafCotonou)->get(route('admin.students.show', $this->studentUserBohicon));
        $crossStudent->assertForbidden();

        $crossReceipt = $this->actingAs($this->rafCotonou)->get(route('admin.payments.receipt', $this->paymentBohicon));
        $crossReceipt->assertForbidden();

        $crossEnrollment = $this->actingAs($this->rafCotonou)->get(route('admin.enrollments.show', $this->enrollmentBohicon));
        $crossEnrollment->assertForbidden();
    }

    /**
     * 5. Le SuperAdmin ou la Direction DAF accède aux données de tous les sites (transversalité).
     */
    public function test_superadmin_and_direction_daf_access_all_sites_transversally(): void
    {
        // Direction DAF voit les paiements de tous les sites
        $dafPayments = $this->actingAs($this->directionDaf)->get(route('admin.payments.index'));
        $dafPayments->assertOk();
        $dafPayments->assertSee('PAY-COT-001');
        $dafPayments->assertSee('PAY-BOH-001');

        // Direction DAF accède aux reçus des deux sites
        $dafReceiptCot = $this->actingAs($this->directionDaf)->get(route('admin.payments.receipt', $this->paymentCotonou));
        $dafReceiptCot->assertOk();
        $dafReceiptBoh = $this->actingAs($this->directionDaf)->get(route('admin.payments.receipt', $this->paymentBohicon));
        $dafReceiptBoh->assertOk();

        // Direction DAF voit les dépenses des deux sites
        $dafExpenses = $this->actingAs($this->directionDaf)->get(route('admin.expenses.index'));
        $dafExpenses->assertOk();
        $dafExpenses->assertSee('Papier Cotonou');
        $dafExpenses->assertSee('Papier Bohicon');

        // SuperAdmin accède également de façon transversale
        $superAdminPayments = $this->actingAs($this->superAdmin)->get(route('admin.payments.index'));
        $superAdminPayments->assertOk();
        $superAdminPayments->assertSee('PAY-COT-001');
        $superAdminPayments->assertSee('PAY-BOH-001');
    }

    public function test_site_manager_dashboard_only_shows_data_for_assigned_sites(): void
    {
        $this->travelTo(now()->setDate(2026, 10, 21));

        $siteManager = User::create([
            'first_name' => 'Responsable',
            'last_name' => 'Cotonou',
            'email' => 'responsable.cotonou@ifam.test',
            'password' => Hash::make('Password123!'),
            'is_active' => true,
        ]);
        $siteManager->assignRole('responsable_site');
        $siteManager->sites()->attach($this->cotonou->id);

        $roomCotonou = Room::create([
            'name' => 'Salle Cotonou - site dashboard',
            'site_id' => $this->cotonou->id,
            'capacity' => 30,
            'type' => 'Théorie',
            'is_active' => true,
        ]);
        Room::create([
            'name' => 'Salle Bohicon - site dashboard',
            'site_id' => $this->bohicon->id,
            'capacity' => 25,
            'type' => 'Informatique',
            'is_active' => true,
        ]);

        $response = $this->actingAs($siteManager)->get(route('admin.dashboard'));

        $response->assertOk()
            ->assertSee('Tableau de bord — Cotonou')
            ->assertSee('Utilisateurs du site')
            ->assertSee('Salles du site')
            ->assertSee('Activité pédagogique')
            ->assertSee('Suivi financier')
            ->assertSee('Insertion & partenariats', false)
            ->assertSee('Salle Cotonou - site dashboard')
            ->assertSee('Cotonou')
            ->assertDontSee('Salle Bohicon - site dashboard')
            ->assertDontSee('Paiements récents')
            ->assertDontSee('Revenus totaux')
            ->assertDontSee('Dépenses totales')
            ->assertDontSee('Session en cours')
            ->assertDontSee('Présences aujourd’hui')
            ->assertViewHas('totalUsers', 6)
            ->assertViewHas('activeUsers', 6)
            ->assertViewHas('totalRooms', 1)
            ->assertViewHas('activeRooms', 1);
        $this->assertSame(1, $response->viewData('peopleSupervision')['students']);
        $this->assertSame(1, $response->viewData('academicSupervision')['sessions']);
        $this->assertSame(1, $response->viewData('academicSupervision')['enrollments']);
        $this->assertSame(25000.0, (float) $response->viewData('financialSupervision')['totalRevenue']);
        $this->assertSame(15000.0, (float) $response->viewData('financialSupervision')['totalExpenses']);
        $this->assertSame([$roomCotonou->id], $response->viewData('rooms')->modelKeys());
        $this->assertNotContains($this->studentUserBohicon->id, $response->viewData('siteUsers')->modelKeys());
        $this->assertArrayNotHasKey('totalRevenue', $response->viewData());
        $this->assertArrayNotHasKey('recentPayments', $response->viewData());

        $globalResponse = $this->actingAs($this->superAdmin)->get(route('admin.dashboard'));
        $globalResponse->assertOk()
            ->assertViewHas('totalStudents', 2)
            ->assertViewHas('totalUsers', 10)
            ->assertViewHas('totalRevenue', 55000)
            ->assertViewHas('totalExpenses', 33000);
        $this->assertCount(2, $globalResponse->viewData('recentPayments'));
    }

    public function test_direction_daf_dashboard_only_shows_financial_and_administrative_supervision(): void
    {
        $this->travelTo(now()->setDate(2026, 10, 21));
        Payment::create([
            'inscription_id' => $this->enrollmentCotonou->id,
            'montant' => 5000,
            'mode' => 'especes',
            'reference' => 'PAY-PENDING-DAF',
            'date_paiement' => '2026-10-20',
            'status' => 'en_attente',
            'enregistre_par' => $this->caissierCotonou->id,
        ]);
        Fee::create([
            'student_profile_id' => $this->studentProfileCotonou->id,
            'academic_session_id' => $this->sessionCotonou->id,
            'title' => 'Frais de dossier DAF',
            'amount' => 10000,
            'status' => 'pending',
        ]);
        Document::create([
            'documentable_type' => StudentProfile::class,
            'documentable_id' => $this->studentProfileCotonou->id,
            'title' => 'Pièce étudiant DAF',
            'type' => 'identity',
            'file_path' => 'documents/test.pdf',
            'original_name' => 'piece.pdf',
        ]);

        $response = $this->actingAs($this->directionDaf)->get(route('admin.dashboard'));

        $response->assertOk()
            ->assertSee('Supervision DAF Globale')
            ->assertSee('Supervision Financière')
            ->assertSee('Supervision Administrative')
            ->assertSee('Paiements à valider')
            ->assertSee('Dépenses & achats')
            ->assertSee('Bourses / réductions')
            ->assertSee('Rentrées académiques')
            ->assertSee('Documents & pièces')
            ->assertDontSee('Paiements récents')
            ->assertDontSee('Présences aujourd’hui')
            ->assertDontSee('Accès rapides')
            ->assertViewHas('financialSupervision.totalRevenue', 55000)
            ->assertViewHas('financialSupervision.revenueThisMonth', 55000)
            ->assertViewHas('financialSupervision.paymentsToValidate', 1)
            ->assertViewHas('financialSupervision.totalExpenses', 33000)
            ->assertViewHas('financialSupervision.feesToRecover', 1)
            ->assertViewHas('financialSupervision.discounts', 0)
            ->assertViewHas('administrativeSupervision.sessions', 2)
            ->assertViewHas('administrativeSupervision.currentSessions', 2)
            ->assertViewHas('administrativeSupervision.enrollments', 2)
            ->assertViewHas('administrativeSupervision.students', 2)
            ->assertViewHas('administrativeSupervision.documents', 1);
        $this->assertArrayNotHasKey('recentPayments', $response->viewData());
        $this->assertArrayNotHasKey('paymentsChart', $response->viewData());
    }

    public function test_direction_daf_sees_all_academic_sessions_and_can_filter_them_by_site(): void
    {
        $response = $this->actingAs($this->directionDaf)->get(route('admin.sessions.index'));

        $response->assertOk()
            ->assertSee('Session Cotonou 2026')
            ->assertSee('Session Bohicon 2026')
            ->assertSee('Filtrer les rentrées par site')
            ->assertViewHas('sessions', fn ($sessions) => $sessions->total() === 2);

        $filteredResponse = $this->actingAs($this->directionDaf)->get(route('admin.sessions.index', [
            'site_id' => $this->bohicon->id,
        ]));

        $filteredResponse->assertOk()
            ->assertSee('Session Bohicon 2026')
            ->assertViewHas('sessions', fn ($sessions) => $sessions->total() === 1);
        $this->assertSame(
            [$this->sessionBohicon->id],
            $filteredResponse->viewData('sessions')->getCollection()->modelKeys()
        );
    }

    public function test_direction_daf_can_filter_financial_and_enrollment_lists_by_site(): void
    {
        $enrollmentsResponse = $this->actingAs($this->directionDaf)->get(route('admin.enrollments.index', [
            'site_id' => $this->bohicon->id,
        ]));

        $enrollmentsResponse->assertOk()
            ->assertViewHas('enrollments', fn ($enrollments) => $enrollments->getCollection()->modelKeys() === [$this->enrollmentBohicon->id]);

        $expensesResponse = $this->actingAs($this->directionDaf)->get(route('admin.expenses.index', [
            'site_id' => $this->bohicon->id,
        ]));

        $expensesResponse->assertOk()
            ->assertViewHas('expenses', fn ($expenses) => $expenses->getCollection()->modelKeys() === [$this->expenseBohicon->id]);

        $paymentsResponse = $this->actingAs($this->directionDaf)->get(route('admin.payments.index', [
            'site_id' => $this->bohicon->id,
        ]));

        $paymentsResponse->assertOk()
            ->assertViewHas('payments', fn ($payments) => $payments->getCollection()->modelKeys() === [$this->paymentBohicon->id]);

        Document::create([
            'documentable_type' => StudentProfile::class,
            'documentable_id' => $this->studentProfileCotonou->id,
            'title' => 'Pièce Cotonou',
            'type' => 'identity',
            'file_path' => 'documents/cotonou.pdf',
            'original_name' => 'cotonou.pdf',
        ]);
        $bohiconDocument = Document::create([
            'documentable_type' => StudentProfile::class,
            'documentable_id' => $this->studentProfileBohicon->id,
            'title' => 'Pièce Bohicon',
            'type' => 'identity',
            'file_path' => 'documents/bohicon.pdf',
            'original_name' => 'bohicon.pdf',
        ]);
        $documentsResponse = $this->actingAs($this->directionDaf)->get(route('admin.documents.index', [
            'site_id' => $this->bohicon->id,
        ]));

        $documentsResponse->assertOk()
            ->assertViewHas('documents', fn ($documents) => $documents->getCollection()->modelKeys() === [$bohiconDocument->id]);
    }

    public function test_administrative_service_dashboard_only_shows_scoped_administrative_data(): void
    {
        $serviceAdmin = User::create([
            'first_name' => 'Responsable',
            'last_name' => 'Administratif Cotonou',
            'email' => 'responsable.administratif.cotonou@ifam.test',
            'password' => Hash::make('Password123!'),
            'is_active' => true,
        ]);
        $serviceAdmin->assignRole('responsable_administratif');
        $serviceAdmin->sites()->attach($this->cotonou->id);

        $roomCotonou = Room::create([
            'name' => 'Salle Cotonou - dashboard administratif',
            'site_id' => $this->cotonou->id,
            'capacity' => 30,
            'type' => 'Théorie',
            'is_active' => true,
        ]);
        Room::create([
            'name' => 'Salle Bohicon - dashboard administratif',
            'site_id' => $this->bohicon->id,
            'capacity' => 25,
            'type' => 'Informatique',
            'is_active' => true,
        ]);
        Document::create([
            'documentable_type' => StudentProfile::class,
            'documentable_id' => $this->studentProfileCotonou->id,
            'title' => 'Document Cotonou',
            'type' => 'identity',
            'file_path' => 'documents/cotonou.pdf',
            'original_name' => 'cotonou.pdf',
        ]);
        Document::create([
            'documentable_type' => StudentProfile::class,
            'documentable_id' => $this->studentProfileBohicon->id,
            'title' => 'Document Bohicon',
            'type' => 'identity',
            'file_path' => 'documents/bohicon.pdf',
            'original_name' => 'bohicon.pdf',
        ]);

        $response = $this->actingAs($serviceAdmin)->get(route('admin.dashboard'));

        $response->assertOk()
            ->assertSee('Scolarité & Dossiers')
            ->assertSee('Rentrées académiques')
            ->assertSee('Inscriptions')
            ->assertSee('Dossiers étudiants')
            ->assertSee('Documents & attestations')
            ->assertSee('Salles')
            ->assertDontSee('Supervision Financière')
            ->assertDontSee('Paiements récents')
            ->assertDontSee('Dépenses totales')
            ->assertViewHas('administrativeSupervision.sessions', 1)
            ->assertViewHas('administrativeSupervision.currentSessions', 1)
            ->assertViewHas('administrativeSupervision.enrollments', 1)
            ->assertViewHas('administrativeSupervision.activeEnrollments', 1)
            ->assertViewHas('administrativeSupervision.students', 1)
            ->assertViewHas('administrativeSupervision.documents', 1)
            ->assertViewHas('administrativeSupervision.rooms', 1)
            ->assertViewHas('administrativeSupervision.activeRooms', 1);

        $response->assertDontSee('Document Bohicon');
    }

    public function test_financial_service_dashboard_only_shows_financial_data_for_assigned_sites(): void
    {
        $financialUser = User::create([
            'first_name' => 'Responsable',
            'last_name' => 'Financier Cotonou',
            'email' => 'responsable.financier.cotonou@ifam.test',
            'password' => Hash::make('Password123!'),
            'is_active' => true,
        ]);
        $financialUser->assignRole('responsable_financier');
        $financialUser->sites()->attach($this->cotonou->id);

        $feeCotonou = Fee::create([
            'student_profile_id' => $this->studentProfileCotonou->id,
            'academic_session_id' => $this->sessionCotonou->id,
            'title' => 'Frais Cotonou',
            'amount' => 10000,
            'status' => 'pending',
        ]);
        $feeBohicon = Fee::create([
            'student_profile_id' => $this->studentProfileBohicon->id,
            'academic_session_id' => $this->sessionBohicon->id,
            'title' => 'Frais Bohicon',
            'amount' => 12000,
            'status' => 'partial',
        ]);
        Discount::create([
            'student_profile_id' => $this->studentProfileCotonou->id,
            'fee_id' => $feeCotonou->id,
            'label' => 'Bourse Cotonou',
            'type' => 'fixed',
            'value' => 1000,
        ]);
        Discount::create([
            'student_profile_id' => $this->studentProfileBohicon->id,
            'fee_id' => $feeBohicon->id,
            'label' => 'Bourse Bohicon',
            'type' => 'fixed',
            'value' => 1500,
        ]);
        Payment::create([
            'inscription_id' => $this->enrollmentCotonou->id,
            'montant' => 5000,
            'mode' => 'especes',
            'reference' => 'PAY-PENDING-COT',
            'date_paiement' => '2026-10-20',
            'status' => 'en_attente',
            'enregistre_par' => $this->caissierCotonou->id,
        ]);
        Payment::create([
            'inscription_id' => $this->enrollmentBohicon->id,
            'montant' => 7000,
            'mode' => 'especes',
            'reference' => 'PAY-PENDING-BOH',
            'date_paiement' => '2026-10-20',
            'status' => 'en_attente',
            'enregistre_par' => $this->caissierBohicon->id,
        ]);

        $response = $this->actingAs($financialUser)->get(route('admin.dashboard'));

        $response->assertOk()
            ->assertSee('Caisse & Recouvrement')
            ->assertSee('Encaissements validés')
            ->assertSee('Paiements à valider')
            ->assertSee('Dépenses')
            ->assertSee('Frais à recouvrer')
            ->assertSee('Bourses & réductions')
            ->assertDontSee('Supervision Administrative')
            ->assertDontSee('Paiements récents')
            ->assertDontSee('Derniers inscrits')
            ->assertViewHas('financialSupervision.totalRevenue', 25000)
            ->assertViewHas('financialSupervision.revenueThisMonth', 25000)
            ->assertViewHas('financialSupervision.paymentsToValidate', 1)
            ->assertViewHas('financialSupervision.totalExpenses', 15000)
            ->assertViewHas('financialSupervision.feesToRecover', 1)
            ->assertViewHas('financialSupervision.discounts', 1);

        $this->assertArrayNotHasKey('administrativeSupervision', $response->viewData());
        $this->assertArrayNotHasKey('recentPayments', $response->viewData());
    }

    /**
     * 6. Seul un profil habilité à la validation financière peut valider un paiement.
     *    - Le caissier saisit un paiement -> status 'en_attente'.
     *    - Le caissier ne peut pas valider son propre paiement (403).
     *    - La Direction DAF valide le paiement -> status 'valide', validateur et date enregistrés.
     */
    public function test_only_authorized_profile_can_validate_financial_operations(): void
    {
        // Le caissier saisit un versement
        $storeResp = $this->actingAs($this->caissierCotonou)->post(route('admin.payments.store'), [
            'inscription_id' => $this->enrollmentCotonou->id,
            'montant' => 15000,
            'mode' => 'especes',
            'date_paiement' => '2026-10-20',
        ]);
        $storeResp->assertRedirect();

        $newPayment = Payment::where('inscription_id', $this->enrollmentCotonou->id)
            ->where('montant', 15000)
            ->firstOrFail();

        $this->assertSame('en_attente', $newPayment->status);
        $this->assertTrue($newPayment->estEnAttente());
        $this->assertNull($newPayment->validated_by);

        // Tentative de validation par le caissier : 403
        $caissierValResp = $this->actingAs($this->caissierCotonou)->patch(route('admin.payments.validate', $newPayment));
        $caissierValResp->assertForbidden();

        // Tentative de validation par un agent administratif : 403
        $adminAgentValResp = $this->actingAs($this->agentAdminCotonou)->patch(route('admin.payments.validate', $newPayment));
        $adminAgentValResp->assertForbidden();

        // Validation par la Direction DAF : Succès
        $valResp = $this->actingAs($this->directionDaf)->patch(route('admin.payments.validate', $newPayment), [
            'validation_notes' => 'Vérifié conforme en caisse',
        ]);
        $valResp->assertRedirect();

        $newPayment->refresh();
        $this->assertSame('valide', $newPayment->status);
        $this->assertTrue($newPayment->estValide());
        $this->assertSame($this->directionDaf->id, $newPayment->validated_by);
        $this->assertNotNull($newPayment->validated_at);
        $this->assertSame('Vérifié conforme en caisse', $newPayment->validation_notes);
    }

    /**
     * 7. Les reçus, paiements et opérations historiques continuent de fonctionner sans régression.
     */
    public function test_receipts_payments_and_historical_operations_continue_without_regression(): void
    {
        // Reçu d'un paiement historique validé
        $response = $this->actingAs($this->superAdmin)->get(route('admin.payments.receipt', $this->paymentCotonou));
        $response->assertOk();
        $response->assertSee('REÇU DE PAIEMENT DE SCOLARITÉ');
        $response->assertSee('25 000 FCFA');
        $response->assertSee($this->studentUserCotonou->full_name);

        // Rapport imprimé des paiements
        $printResp = $this->actingAs($this->superAdmin)->get(route('admin.payments.print'));
        $printResp->assertOk();
        $printResp->assertSee('État des paiements enregistrés');
        $printResp->assertSee('PAY-COT-001');
    }
}
