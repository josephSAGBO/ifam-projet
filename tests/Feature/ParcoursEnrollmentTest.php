<?php

namespace Tests\Feature;

use App\Models\AcademicSession;
use App\Models\Enrollment;
use App\Models\Formation;
use App\Models\Parcours;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ParcoursEnrollmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_must_complete_previous_path_stage_but_can_advance_with_an_unpaid_balance(): void
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
        $studentProfile = StudentProfile::create(['user_id' => $student->id]);
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

        $form = $this->actingAs($admin)->get(route('admin.enrollments.create'));
        $form->assertOk();
        $form->assertSee('Parcours');
        $form->assertSee('Stylisme-Modélisme');
        $form->assertSee('Étape 1 · Stylisme année 1');
        $form->assertSee('Étape 2 · Stylisme année 2');

        $skippedStage = $this->actingAs($admin)->post(route('admin.enrollments.store'), [
            'student_profile_id' => $studentProfile->id,
            'parcours_id' => $parcours->id,
            'formation_id' => $secondFormation->id,
            'academic_session_id' => $secondSession->id,
            'status' => 'active',
            'enrollment_date' => '2026-09-01',
        ]);

        $skippedStage->assertSessionHasErrors('formation_id');
        $this->assertDatabaseMissing('enrollments', [
            'student_profile_id' => $studentProfile->id,
            'formation_id' => $secondFormation->id,
        ]);

        $firstYearEnrollment = Enrollment::create([
            'student_profile_id' => $studentProfile->id,
            'formation_id' => $firstFormation->id,
            'academic_session_id' => $firstSession->id,
            'prix_formation' => 1000,
            'montant_du' => 1000,
            'status' => 'completed',
            'enrollment_date' => '2025-09-01',
        ]);

        $nextStage = $this->actingAs($admin)->post(route('admin.enrollments.store'), [
            'student_profile_id' => $studentProfile->id,
            'parcours_id' => $parcours->id,
            'formation_id' => $secondFormation->id,
            'academic_session_id' => $secondSession->id,
            'status' => 'active',
            'enrollment_date' => '2026-09-01',
        ]);

        $nextStage->assertRedirect(route('admin.enrollments.index'));
        $this->assertDatabaseHas('enrollments', [
            'id' => $firstYearEnrollment->id,
            'status' => 'completed',
            'montant_du' => 1000,
        ]);
        $this->assertDatabaseHas('enrollments', [
            'student_profile_id' => $studentProfile->id,
            'formation_id' => $secondFormation->id,
            'academic_session_id' => $secondSession->id,
            'status' => 'active',
        ]);
    }

    public function test_latest_retake_must_be_completed_before_advancing_to_next_path_stage(): void
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
            'first_name' => 'Mina',
            'last_name' => 'Etudiante',
            'email' => 'mina@ifam.test',
            'password' => Hash::make('Secret123!'),
            'is_active' => true,
        ]);
        $studentProfile = StudentProfile::create(['user_id' => $student->id]);
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
        $previousSession = $this->createSession('2024-2025', '2024-09-01');
        $retakeSession = $this->createSession('2025-2026', '2025-09-01');
        $nextSession = $this->createSession('2026-2027', '2026-09-01');
        Enrollment::create([
            'student_profile_id' => $studentProfile->id,
            'formation_id' => $firstFormation->id,
            'academic_session_id' => $previousSession->id,
            'prix_formation' => 1000,
            'montant_du' => 1000,
            'status' => 'completed',
        ]);
        Enrollment::create([
            'student_profile_id' => $studentProfile->id,
            'formation_id' => $firstFormation->id,
            'academic_session_id' => $retakeSession->id,
            'prix_formation' => 1000,
            'montant_du' => 1000,
            'status' => 'active',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.enrollments.store'), [
            'student_profile_id' => $studentProfile->id,
            'parcours_id' => $parcours->id,
            'formation_id' => $secondFormation->id,
            'academic_session_id' => $nextSession->id,
            'status' => 'active',
            'enrollment_date' => '2026-09-01',
        ]);

        $response->assertSessionHasErrors('formation_id');
        $this->assertDatabaseMissing('enrollments', [
            'student_profile_id' => $studentProfile->id,
            'formation_id' => $secondFormation->id,
        ]);
    }

    private function createSession(string $name, string $startDate): AcademicSession
    {
        return AcademicSession::create([
            'name' => $name,
            'start_date' => $startDate,
            'end_date' => date('Y-m-d', strtotime($startDate.' +1 year')),
        ]);
    }
}
