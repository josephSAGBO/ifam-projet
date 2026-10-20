<?php

namespace Tests\Feature;

use App\Models\AcademicSession;
use App\Models\Enrollment;
use App\Models\Formation;
use App\Models\Payment;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminFeeIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_fee_index_shows_enrollment_balances_and_filters_by_session(): void
    {
        Role::create(['name' => 'administrateur', 'guard_name' => 'web']);
        $admin = User::create([
            'first_name' => 'Admin',
            'last_name' => 'Finance',
            'email' => 'admin-finance@ifam.test',
            'password' => Hash::make('Secret123!'),
            'is_active' => true,
        ]);
        $admin->assignRole('administrateur');

        $studentUser = User::create([
            'first_name' => 'Awa',
            'last_name' => 'Etudiante',
            'email' => 'awa-finance@ifam.test',
            'password' => Hash::make('Secret123!'),
            'is_active' => true,
        ]);
        $studentProfile = StudentProfile::create(['user_id' => $studentUser->id]);
        $sessionOne = $this->createSession('Session Juin 2026', '2026-01-01');
        $sessionTwo = $this->createSession('Session Octobre 2026', '2026-09-01');
        $formationOne = Formation::create([
            'name' => 'Stylisme Modélisme Année 1',
            'prix' => 12000,
        ]);
        $formationTwo = Formation::create([
            'name' => 'Stylisme Modélisme Année 2',
            'prix' => 4000,
        ]);
        $enrollmentOne = $this->createEnrollment($studentProfile, $formationOne, $sessionOne, 12000, 10000);
        $enrollmentTwo = $this->createEnrollment($studentProfile, $formationTwo, $sessionTwo, 4000, 4000);

        Payment::create([
            'inscription_id' => $enrollmentOne->id,
            'montant' => 2000,
            'date_paiement' => '2026-02-01',
            'mode' => 'especes',
        ]);

        $sessionOneResponse = $this->actingAs($admin)->get(route('admin.fees.index', [
            'session_id' => $sessionOne->id,
        ]));

        $sessionOneResponse->assertOk();
        $sessionOneResponse->assertSee('Stylisme Modélisme Année 1');
        $sessionOneResponse->assertSee('Session Juin 2026');
        $sessionOneResponse->assertSee('12 000 FCFA');
        $sessionOneResponse->assertSee('10 000 FCFA');
        $sessionOneResponse->assertSee('2 000 FCFA');
        $sessionOneResponse->assertSee('8 000 FCFA');
        $sessionOneResponse->assertDontSee('Stylisme Modélisme Année 2');
        $sessionOneResponse->assertSee(route('admin.payments.create', ['inscription_id' => $enrollmentOne->id]));

        $sessionTwoResponse = $this->actingAs($admin)->get(route('admin.fees.index', [
            'session_id' => $sessionTwo->id,
        ]));
        $sessionTwoResponse->assertSee('Stylisme Modélisme Année 2');
        $sessionTwoResponse->assertDontSee('Stylisme Modélisme Année 1');

        $partialOnlyResponse = $this->actingAs($admin)->get(route('admin.fees.index', [
            'status' => 'partiel',
        ]));
        $partialOnlyResponse->assertSee('Stylisme Modélisme Année 1');
        $partialOnlyResponse->assertDontSee('Stylisme Modélisme Année 2');

        $this->actingAs($admin)->get('/admin/fees/create')->assertNotFound();
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
        StudentProfile $studentProfile,
        Formation $formation,
        AcademicSession $session,
        float $price,
        float $amountDue,
    ): Enrollment {
        return Enrollment::create([
            'student_profile_id' => $studentProfile->id,
            'formation_id' => $formation->id,
            'academic_session_id' => $session->id,
            'prix_formation' => $price,
            'bourse_type' => 'montant',
            'bourse_valeur' => $price - $amountDue,
            'montant_du' => $amountDue,
            'status' => 'active',
            'enrollment_date' => $session->start_date,
        ]);
    }
}
