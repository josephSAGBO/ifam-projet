<?php

namespace Tests\Feature;

use App\Models\AcademicSession;
use App\Models\Attendance;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Formation;
use App\Models\Module;
use App\Models\StudentProfile;
use App\Models\TeacherProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TeacherAttendanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_can_open_call_for_a_course_in_any_active_session_and_save_attendance(): void
    {
        Role::create(['name' => 'enseignant', 'guard_name' => 'web']);
        $teacher = $this->createTeacher('professeur@ifam.test');
        $teacherProfile = TeacherProfile::create(['user_id' => $teacher->id]);
        $formation = Formation::create(['name' => 'Formation Mode']);
        $module = Module::create(['name' => 'Coupe', 'code' => 'MOD-COUPE']);
        $sessionOne = $this->createSession('Session A', true, '2026-09-01');
        $sessionTwo = $this->createSession('Session B', true, '2027-01-01');
        $inactiveSession = $this->createSession('Session inactive', false, '2025-01-01');

        $courseOne = $this->createCourse($teacherProfile, $formation, $module, $sessionOne, 'Cours Session A');
        $courseTwo = $this->createCourse($teacherProfile, $formation, $module, $sessionTwo, 'Cours Session B');
        $this->createCourse($teacherProfile, $formation, $module, $inactiveSession, 'Cours session inactive');

        $student = User::create([
            'first_name' => 'Paul',
            'last_name' => 'Etudiant',
            'email' => 'paul.etudiant@ifam.test',
            'password' => Hash::make('Secret123!'),
            'is_active' => true,
        ]);
        $studentProfile = StudentProfile::create(['user_id' => $student->id]);
        Enrollment::create([
            'student_profile_id' => $studentProfile->id,
            'formation_id' => $formation->id,
            'academic_session_id' => $sessionTwo->id,
            'status' => 'active',
        ]);

        $response = $this->actingAs($teacher)->get(route('teacher.attendance.index', [
            'course_id' => $courseTwo->id,
        ]));

        $response->assertOk();
        $response->assertSee('Cours Session A');
        $response->assertSee('Cours Session B');
        $response->assertSee('Formation Mode');
        $response->assertSee('Paul Etudiant');
        $response->assertDontSee('Cours session inactive');

        $saveResponse = $this->actingAs($teacher)->post(route('teacher.attendance.store'), [
            'course_id' => $courseTwo->id,
            'attendances' => [
                $studentProfile->id => [
                    'status' => 'absent',
                    'remarks' => 'Absence signalée',
                ],
            ],
        ]);

        $saveResponse->assertRedirect(route('teacher.attendance.index', ['course_id' => $courseTwo->id]));
        $this->assertDatabaseHas('attendances', [
            'course_id' => $courseTwo->id,
            'student_profile_id' => $studentProfile->id,
            'date' => now()->startOfDay()->toDateTimeString(),
            'status' => 'absent',
            'remarks' => 'Absence signalée',
        ]);
    }

    public function test_admin_can_close_a_session_and_teacher_cannot_change_its_attendance(): void
    {
        Role::create(['name' => 'enseignant', 'guard_name' => 'web']);
        Role::create(['name' => 'administrateur', 'guard_name' => 'web']);
        $teacher = $this->createTeacher('professeur-cloture@ifam.test');
        $teacherProfile = TeacherProfile::create(['user_id' => $teacher->id]);
        $admin = User::create([
            'first_name' => 'Admin',
            'last_name' => 'IFAM',
            'email' => 'admin-presences@ifam.test',
            'password' => Hash::make('Secret123!'),
            'is_active' => true,
        ]);
        $admin->assignRole('administrateur');
        $formation = Formation::create(['name' => 'Formation Mode']);
        $module = Module::create(['name' => 'Coupe', 'code' => 'MOD-COUPE']);
        $session = $this->createSession('Session présence', true, '2026-09-01');
        $course = $this->createCourse($teacherProfile, $formation, $module, $session, 'Cours de présence');
        $studentUser = User::create([
            'first_name' => 'Paul',
            'last_name' => 'Etudiant',
            'email' => 'paul-presence-cloturee@ifam.test',
            'password' => Hash::make('Secret123!'),
            'is_active' => true,
        ]);
        $student = StudentProfile::create(['user_id' => $studentUser->id]);
        Enrollment::create([
            'student_profile_id' => $student->id,
            'formation_id' => $formation->id,
            'academic_session_id' => $session->id,
            'status' => 'active',
        ]);
        $attendance = Attendance::create([
            'course_id' => $course->id,
            'formation_id' => $formation->id,
            'student_profile_id' => $student->id,
            'assigned_teacher_profile_id' => $teacherProfile->id,
            'date' => '2026-10-07',
            'status' => 'present',
        ]);

        $closeResponse = $this->actingAs($admin)->patch(route('admin.attendance.sheets.update', $course), [
            'date' => '2026-10-07',
            'statut' => 'cloturee',
        ]);

        $closeResponse->assertRedirect(route('admin.attendance.index', [
            'session_id' => $session->id,
            'formation_id' => $formation->id,
            'course_id' => $course->id,
            'date' => '2026-10-07',
        ]));
        $this->assertDatabaseHas('attendance_sheets', [
            'course_id' => $course->id,
            'date' => '2026-10-07 00:00:00',
            'statut' => 'cloturee',
        ]);

        $historyResponse = $this->actingAs($teacher)->get(route('teacher.attendance.index', [
            'course_id' => $course->id,
            'date' => '2026-10-07',
            'history' => 1,
        ]));
        $historyResponse->assertSee('Historique — lecture seule');
        $historyResponse->assertSee('Paul Etudiant');
        $historyResponse->assertDontSee('Enregistrer les présences');

        $saveResponse = $this->actingAs($teacher)->post(route('teacher.attendance.store'), [
            'course_id' => $course->id,
            'date' => '2026-10-07',
            'attendances' => [$student->id => ['status' => 'absent']],
        ]);

        $saveResponse->assertForbidden();
        $this->assertDatabaseHas('attendances', ['id' => $attendance->id, 'status' => 'present']);

        $updateClosedResponse = $this->actingAs($teacher)->put(
            route('teacher.attendance.update', $attendance),
            ['status' => 'absent'],
        );
        $updateClosedResponse->assertForbidden();
        $this->assertDatabaseHas('attendances', ['id' => $attendance->id, 'status' => 'present']);

        $reopenResponse = $this->actingAs($admin)->patch(route('admin.attendance.sheets.update', $course), [
            'date' => '2026-10-07',
            'statut' => 'ouverte',
        ]);
        $reopenResponse->assertRedirect();
        $this->assertDatabaseHas('attendance_sheets', [
            'course_id' => $course->id,
            'date' => '2026-10-07 00:00:00',
            'statut' => 'ouverte',
        ]);
        $course->update(['statut' => 'termine']);

        $completeCourseResponse = $this->actingAs($teacher)->post(route('teacher.attendance.store'), [
            'course_id' => $course->id,
            'date' => '2026-10-07',
            'attendances' => [$student->id => ['status' => 'absent']],
        ]);

        $completeCourseResponse->assertForbidden();
        $this->assertDatabaseHas('attendances', ['id' => $attendance->id, 'status' => 'present']);

        $updateCompletedResponse = $this->actingAs($teacher)->put(
            route('teacher.attendance.update', $attendance),
            ['status' => 'absent'],
        );
        $updateCompletedResponse->assertForbidden();
        $this->assertDatabaseHas('attendances', ['id' => $attendance->id, 'status' => 'present']);
    }

    private function createTeacher(string $email): User
    {
        $teacher = User::create([
            'first_name' => 'Enseignant',
            'last_name' => 'IFAM',
            'email' => $email,
            'password' => Hash::make('Secret123!'),
            'is_active' => true,
        ]);
        $teacher->assignRole('enseignant');

        return $teacher;
    }

    private function createSession(string $name, bool $isCurrent, string $startDate): AcademicSession
    {
        return AcademicSession::create([
            'name' => $name,
            'start_date' => $startDate,
            'end_date' => date('Y-m-d', strtotime($startDate.' +1 year')),
            'is_current' => $isCurrent,
        ]);
    }

    private function createCourse(
        TeacherProfile $teacher,
        Formation $formation,
        Module $module,
        AcademicSession $session,
        string $name,
    ): Course {
        return Course::create([
            'formation_id' => $formation->id,
            'academic_session_id' => $session->id,
            'module_id' => $module->id,
            'enseignant_id' => $teacher->id,
            'teacher_profile_id' => $teacher->id,
            'name' => $name,
        ]);
    }
}
