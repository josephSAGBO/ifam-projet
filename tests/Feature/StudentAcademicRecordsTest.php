<?php

namespace Tests\Feature;

use App\Models\AcademicSession;
use App\Models\Attendance;
use App\Models\Course;
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

class StudentAcademicRecordsTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_sees_notes_for_active_and_completed_enrollments_separated_by_session(): void
    {
        [$student, $profile] = $this->createStudent();
        [$firstFormation, $firstSession, $firstCourse, $firstModule] = $this->createEnrolledCourse($profile, 'Mode', 'Session A', '2026-09-01', 'MOD-A');
        [$secondFormation, $secondSession, $secondCourse, $secondModule] = $this->createEnrolledCourse($profile, 'Stylisme', 'Session B', '2027-01-01', 'MOD-B');
        [, $completedSession, $completedCourse, $completedModule] = $this->createEnrolledCourse($profile, 'Ancienne formation', 'Session terminée', '2025-01-01', 'MOD-C', 'completed');
        [, $suspendedSession, $suspendedCourse, $suspendedModule] = $this->createEnrolledCourse($profile, 'Formation suspendue', 'Session suspendue', '2024-01-01', 'MOD-D', 'suspended');

        $firstEvaluation = $this->createEvaluation($firstCourse, $firstModule, $firstSession, 'Évaluation Session A', 'ouverte');
        $secondEvaluation = $this->createEvaluation($secondCourse, $secondModule, $secondSession, 'Évaluation Session B');
        $completedEvaluation = $this->createEvaluation($completedCourse, $completedModule, $completedSession, 'Évaluation session terminée');
        $suspendedEvaluation = $this->createEvaluation($suspendedCourse, $suspendedModule, $suspendedSession, 'Évaluation inscription suspendue');
        $draftEvaluation = $this->createEvaluation($firstCourse, $firstModule, $firstSession, 'Évaluation brouillon', 'brouillon');
        Grade::create(['evaluation_id' => $firstEvaluation->id, 'student_profile_id' => $profile->id, 'score' => 15]);
        Grade::create(['evaluation_id' => $secondEvaluation->id, 'student_profile_id' => $profile->id, 'score' => 12]);
        Grade::create(['evaluation_id' => $completedEvaluation->id, 'student_profile_id' => $profile->id, 'score' => 19]);
        Grade::create(['evaluation_id' => $suspendedEvaluation->id, 'student_profile_id' => $profile->id, 'score' => 18]);
        Grade::create(['evaluation_id' => $draftEvaluation->id, 'student_profile_id' => $profile->id, 'score' => 20]);

        $response = $this->actingAs($student)->get(route('student.grades'));

        $response->assertOk();
        $response->assertSee('Évaluation Session A');
        $response->assertSee('Évaluation Session B');
        $response->assertSee('15,00');
        $response->assertSee('12,00');
        $response->assertSee('19,00');
        $response->assertSee('Session A');
        $response->assertSee('Session B');
        $response->assertSee('Évaluation session terminée');
        $response->assertSee('Session terminée');
        $response->assertDontSee('Évaluation inscription suspendue');
        $response->assertDontSee('Évaluation brouillon');
    }

    public function test_student_sees_attendance_for_active_and_completed_enrollments_but_not_suspended_enrollments(): void
    {
        [$student, $profile] = $this->createStudent();
        [, $firstSession, $firstCourse] = $this->createEnrolledCourse($profile, 'Mode', 'Session A', '2026-09-01');
        [, $secondSession, $secondCourse] = $this->createEnrolledCourse($profile, 'Stylisme', 'Session B', '2027-01-01');
        [, $completedSession, $completedCourse] = $this->createEnrolledCourse($profile, 'Ancienne formation', 'Session terminée', '2025-01-01', 'MOD-C', 'completed');
        [, $suspendedSession, $suspendedCourse] = $this->createEnrolledCourse($profile, 'Formation suspendue', 'Session suspendue', '2024-01-01', 'MOD-D', 'suspended');
        Attendance::create([
            'course_id' => $firstCourse->id,
            'student_profile_id' => $profile->id,
            'date' => '2026-10-02',
            'status' => 'present',
            'remarks' => 'Session A présente',
        ]);
        Attendance::create([
            'course_id' => $secondCourse->id,
            'student_profile_id' => $profile->id,
            'date' => '2027-02-03',
            'status' => 'late',
            'remarks' => 'Session B retard',
        ]);
        Attendance::create([
            'course_id' => $completedCourse->id,
            'student_profile_id' => $profile->id,
            'date' => '2025-11-04',
            'status' => 'absent',
            'remarks' => 'Présence ancienne',
        ]);
        Attendance::create([
            'course_id' => $suspendedCourse->id,
            'student_profile_id' => $profile->id,
            'date' => '2024-11-04',
            'status' => 'absent',
            'remarks' => 'Présence inscription suspendue',
        ]);

        $response = $this->actingAs($student)->get(route('student.attendance'));

        $response->assertOk();
        $response->assertSee('Session A');
        $response->assertSee('Session B');
        $response->assertSee('Session A présente');
        $response->assertSee('Session B retard');
        $response->assertSee('Présence ancienne');
        $response->assertSee('Session terminée');
        $response->assertDontSee('Présence inscription suspendue');
    }

    /**
     * @return array{0: User, 1: StudentProfile}
     */
    private function createStudent(): array
    {
        Role::firstOrCreate(['name' => 'etudiant', 'guard_name' => 'web']);
        $student = User::create([
            'first_name' => 'Etudiant',
            'last_name' => 'IFAM',
            'email' => 'etudiant@ifam.test',
            'password' => Hash::make('Secret123!'),
            'is_active' => true,
        ]);
        $student->assignRole('etudiant');

        return [$student, StudentProfile::create(['user_id' => $student->id])];
    }

    /**
     * @return array{0: Formation, 1: AcademicSession, 2: Course, 3: Module}
     */
    private function createEnrolledCourse(
        StudentProfile $profile,
        string $formationName,
        string $sessionName,
        string $startDate,
        string $moduleCode = '',
        string $enrollmentStatus = 'active',
    ): array {
        $formation = Formation::create(['name' => $formationName]);
        $session = AcademicSession::create([
            'name' => $sessionName,
            'start_date' => $startDate,
            'end_date' => date('Y-m-d', strtotime($startDate.' +1 year')),
        ]);
        $moduleCode = $moduleCode ?: 'MOD-'.strtoupper(str_replace(' ', '-', $formationName));
        $module = Module::create(['name' => 'Module '.$formationName, 'code' => $moduleCode]);
        Enrollment::create([
            'student_profile_id' => $profile->id,
            'formation_id' => $formation->id,
            'academic_session_id' => $session->id,
            'status' => $enrollmentStatus,
        ]);
        $course = Course::create([
            'formation_id' => $formation->id,
            'academic_session_id' => $session->id,
            'module_id' => $module->id,
            'name' => 'Cours '.$sessionName,
        ]);

        return [$formation, $session, $course, $module];
    }

    private function createEvaluation(
        Course $course,
        Module $module,
        AcademicSession $session,
        string $title,
        string $status = 'cloturee',
    ): Evaluation {
        return Evaluation::create([
            'cours_id' => $course->id,
            'module_id' => $module->id,
            'session_id' => $session->id,
            'titre' => $title,
            'type' => 'devoir',
            'date' => '2026-10-01',
            'statut' => $status,
        ]);
    }
}
