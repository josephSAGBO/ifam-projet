<?php

namespace Tests\Feature;

use App\Models\AcademicSession;
use App\Models\Enrollment;
use App\Models\Formation;
use App\Models\Parcours;
use App\Models\Payment;
use App\Models\StudentProfile;
use App\Models\User;
use App\Services\PaiementService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ParcoursPaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_the_printable_receipt_for_an_enrollment_payment(): void
    {
        Role::create(['name' => 'administrateur', 'guard_name' => 'web']);
        $admin = User::create([
            'first_name' => 'Admin',
            'last_name' => 'IFAM',
            'email' => 'receipt-admin@ifam.test',
            'password' => Hash::make('Secret123!'),
            'is_active' => true,
        ]);
        $admin->assignRole('administrateur');

        $student = User::create([
            'first_name' => 'Awa',
            'last_name' => 'Etudiante',
            'email' => 'receipt-student@ifam.test',
            'matricule' => 'ETU-RECEIPT-01',
            'password' => Hash::make('Secret123!'),
            'is_active' => true,
        ]);
        $studentProfile = $student->studentProfile()->create([]);
        $formation = Formation::create([
            'name' => 'Stylisme année 1',
            'prix' => 1000,
            'is_active' => true,
        ]);
        $session = $this->createSession('Session 2026-2027', '2026-09-01');
        $enrollment = $this->createEnrollment($studentProfile->id, $formation, $session, 'active');

        $storeResponse = $this->actingAs($admin)->post(route('admin.payments.store'), [
            'inscription_id' => $enrollment->id,
            'montant' => 400,
            'date_paiement' => '2026-10-15',
            'mode' => 'especes',
        ]);
        $payment = Payment::where('inscription_id', $enrollment->id)->firstOrFail();

        $storeResponse->assertRedirect(route('admin.payments.receipt', $payment));
        $this->assertStringStartsWith('IFAM-REC-', $payment->reference);
        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'montant' => 400,
            'reference' => $payment->reference,
        ]);

        $response = $this->get(route('admin.payments.receipt', $payment));

        $response->assertOk();
        $response->assertSee('Reçu de paiement');
        $response->assertSee($payment->reference);
        $response->assertSee('Awa Etudiante');
        $response->assertSee('ETU-RECEIPT-01');
        $response->assertSee('Stylisme année 1');
        $response->assertSee('Session 2026-2027');
        $response->assertSee('400 FCFA');
        $response->assertSee('Quatre cents francs CFA');
        $response->assertSee('600 FCFA');
        $response->assertSee('Canevas officiel du reçu IFAM');
        $response->assertSee(asset('img/reçu.png'));
        $response->assertDontSee('Matricule : ETU-RECEIPT-01');
        $response->assertSee(asset('img/catchetComptable.jpg'));
        $response->assertDontSee('CachetDir.jpg');
        $response->assertDontSee('Signaturenum.jpg');
    }

    public function test_admin_can_filter_payments_by_inclusive_dates_and_print_all_matching_rows(): void
    {
        Role::create(['name' => 'administrateur', 'guard_name' => 'web']);
        $admin = User::create([
            'first_name' => 'Admin',
            'last_name' => 'IFAM',
            'email' => 'payment-filter-admin@ifam.test',
            'password' => Hash::make('Secret123!'),
            'is_active' => true,
        ]);
        $admin->assignRole('administrateur');

        $student = User::create([
            'first_name' => 'Awa',
            'last_name' => 'Paiement',
            'email' => 'payment-filter-student@ifam.test',
            'password' => Hash::make('Secret123!'),
            'is_active' => true,
        ]);
        $studentProfile = $student->studentProfile()->create([]);
        $formation = Formation::create(['name' => 'Formation paiement', 'prix' => 50000, 'is_active' => true]);
        $session = $this->createSession('Session paiement', '2026-09-01');
        $enrollment = $this->createEnrollment($studentProfile->id, $formation, $session, 'active', 50000);

        for ($paymentNumber = 1; $paymentNumber <= 15; $paymentNumber++) {
            Payment::create([
                'inscription_id' => $enrollment->id,
                'montant' => 100,
                'date_paiement' => '2026-10-01',
                'mode' => 'especes',
                'reference' => 'IN-RANGE-'.$paymentNumber,
            ]);
        }

        Payment::create([
            'inscription_id' => $enrollment->id,
            'montant' => 100,
            'date_paiement' => '2026-10-31',
            'mode' => 'especes',
            'reference' => 'END-RANGE',
        ]);
        Payment::create([
            'inscription_id' => $enrollment->id,
            'montant' => 900,
            'date_paiement' => '2026-09-30',
            'mode' => 'especes',
            'reference' => 'OUTSIDE-RANGE',
        ]);
        Payment::create([
            'inscription_id' => $enrollment->id,
            'montant' => 800,
            'date_paiement' => '2026-10-15',
            'mode' => 'virement',
            'reference' => 'OTHER-MODE',
        ]);

        $filters = [
            'student_id' => $studentProfile->id,
            'mode' => 'especes',
            'date_from' => '2026-10-01',
            'date_to' => '2026-10-31',
        ];
        $indexResponse = $this->actingAs($admin)->get(route('admin.payments.index', $filters));

        $indexResponse->assertSee('16 paiements trouvés');
        $indexResponse->assertSee('1 600 FCFA');
        $indexResponse->assertViewHas('payments', fn ($payments) => $payments->total() === 16);

        $printResponse = $this->get(route('admin.payments.print', $filters));

        $printResponse->assertSee('END-RANGE');
        $printResponse->assertDontSee('OUTSIDE-RANGE');
        $printResponse->assertDontSee('OTHER-MODE');
        $printResponse->assertSee('1 600 FCFA');
        $printResponse->assertViewHas('payments', fn ($payments) => $payments->count() === 16);
    }

    public function test_admin_payment_filters_reject_a_date_range_with_end_before_start(): void
    {
        Role::create(['name' => 'administrateur', 'guard_name' => 'web']);
        $admin = User::create([
            'first_name' => 'Admin',
            'last_name' => 'IFAM',
            'email' => 'payment-invalid-filter-admin@ifam.test',
            'password' => Hash::make('Secret123!'),
            'is_active' => true,
        ]);
        $admin->assignRole('administrateur');

        $response = $this->actingAs($admin)->get(route('admin.payments.index', [
            'date_from' => '2026-11-01',
            'date_to' => '2026-10-01',
        ]));

        $response->assertSessionHasErrors(['date_from', 'date_to']);
    }

    public function test_payment_form_keeps_the_previous_completed_year_debt_available(): void
    {
        Role::create(['name' => 'administrateur', 'guard_name' => 'web']);
        $admin = User::create([
            'first_name' => 'Admin',
            'last_name' => 'IFAM',
            'email' => 'admin@ifam.test',
            'password' => Hash::make('Secret123!'),
            'is_active' => true,
        ]);
        $admin->assignRole('administrateur');

        $student = User::create([
            'first_name' => 'Awa',
            'last_name' => 'Etudiante',
            'email' => 'awa@ifam.test',
            'password' => Hash::make('Secret123!'),
            'is_active' => true,
        ]);
        $studentProfile = $student->studentProfile()->create([]);
        $parcours = Parcours::create(['nom' => 'Stylisme-Modélisme']);
        $firstFormation = Formation::create([
            'name' => 'Stylisme année 1',
            'parcours_id' => $parcours->id,
            'ordre' => 1,
            'prix' => 1000,
            'is_active' => true,
        ]);
        $secondFormation = Formation::create([
            'name' => 'Stylisme année 2',
            'parcours_id' => $parcours->id,
            'ordre' => 2,
            'prix' => 2000,
            'is_active' => true,
        ]);
        $firstSession = $this->createSession('2025-2026', '2025-09-01');
        $secondSession = $this->createSession('2026-2027', '2026-09-01');
        $previousEnrollment = $this->createEnrollment($studentProfile->id, $firstFormation, $firstSession, 'completed');
        $currentEnrollment = $this->createEnrollment($studentProfile->id, $secondFormation, $secondSession, 'active');

        $otherStudent = User::create([
            'first_name' => 'Mina',
            'last_name' => 'Autre',
            'email' => 'mina@ifam.test',
            'password' => Hash::make('Secret123!'),
            'is_active' => true,
        ]);
        $otherStudentProfile = StudentProfile::create(['user_id' => $otherStudent->id]);
        $this->createEnrollment($otherStudentProfile->id, $firstFormation, $firstSession, 'completed', 7000);

        $response = $this->actingAs($admin)->get(route('admin.payments.create', [
            'inscription_id' => $currentEnrollment->id,
        ]));

        $response->assertOk();
        $response->assertSee('Stylisme année 1');
        $response->assertSee('Stylisme année 2');
        $response->assertSee('1 000,00');
        $response->assertSee('Solde total du parcours');
        $response->assertSee('3 000,00 FCFA');
        $response->assertSee('7 000,00 FCFA');
        $this->assertSame(3000.0, app(PaiementService::class)->soldeParcours($currentEnrollment));
    }

    private function createSession(string $name, string $startDate): AcademicSession
    {
        return AcademicSession::create([
            'name' => $name,
            'start_date' => $startDate,
            'end_date' => date('Y-m-d', strtotime($startDate.' +1 year')),
        ]);
    }

    private function createEnrollment(
        int $studentProfileId,
        Formation $formation,
        AcademicSession $session,
        string $status,
        ?float $amountDue = null,
    ): Enrollment {
        return Enrollment::create([
            'student_profile_id' => $studentProfileId,
            'formation_id' => $formation->id,
            'academic_session_id' => $session->id,
            'prix_formation' => $amountDue ?? $formation->prix,
            'montant_du' => $amountDue ?? $formation->prix,
            'status' => $status,
        ]);
    }
}
