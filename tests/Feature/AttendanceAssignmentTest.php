<?php

namespace Tests\Feature;

use App\Models\AcademicSession;
use App\Models\Attendance;
use App\Models\Course;
use App\Models\Evaluation;
use App\Models\Formation;
use App\Models\Module;
use App\Models\StudentProfile;
use App\Models\TeacherProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AttendanceAssignmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_see_teacher_assignment_for_attendance_records(): void
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

        $teacherUser = User::create([
            'first_name' => 'Prof',
            'last_name' => 'Affecte',
            'email' => 'prof@ifam.test',
            'password' => Hash::make('Secret123!'),
            'is_active' => true,
        ]);
        $teacher = TeacherProfile::create(['user_id' => $teacherUser->id]);

        $formation = Formation::create(['name' => 'Stylisme année 1']);
        $session = AcademicSession::create([
            'name' => '2026-2027',
            'start_date' => '2026-09-01',
            'end_date' => '2027-06-30',
        ]);
        $module = Module::create(['name' => 'Coupe', 'code' => 'COUPE']);
        $course = Course::create([
            'formation_id' => $formation->id,
            'academic_session_id' => $session->id,
            'module_id' => $module->id,
            'enseignant_id' => $teacher->id,
            'teacher_profile_id' => $teacher->id,
            'name' => 'Cours coupe',
        ]);

        $studentOne = StudentProfile::create(['user_id' => User::create([
            'first_name' => 'Étudiant',
            'last_name' => 'Test',
            'email' => 'etudiant@ifam.test',
            'password' => Hash::make('Secret123!'),
            'is_active' => true,
        ])->id]);
        $studentTwo = StudentProfile::create(['user_id' => User::create([
            'first_name' => 'Deux',
            'last_name' => 'Étudiant',
            'email' => 'etudiant2@ifam.test',
            'password' => Hash::make('Secret123!'),
            'is_active' => true,
        ])->id]);

        $studentOne->enrollments()->create([
            'formation_id' => $formation->id,
            'academic_session_id' => $session->id,
            'status' => 'active',
        ]);
        $studentTwo->enrollments()->create([
            'formation_id' => $formation->id,
            'academic_session_id' => $session->id,
            'status' => 'active',
        ]);

        Attendance::create([
            'course_id' => $course->id,
            'student_profile_id' => $studentOne->id,
            'formation_id' => $formation->id,
            'assigned_teacher_profile_id' => $teacher->id,
            'date' => '2026-10-07',
            'status' => 'present',
        ]);
        Attendance::create([
            'course_id' => $course->id,
            'student_profile_id' => $studentTwo->id,
            'formation_id' => $formation->id,
            'assigned_teacher_profile_id' => $teacher->id,
            'date' => '2026-10-07',
            'status' => 'absent',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.attendance.index', [
            'session_id' => $session->id,
            'formation_id' => $formation->id,
            'course_id' => $course->id,
            'date' => '2026-10-07',
        ]));

        $response->assertOk();
        $response->assertSee('Cours coupe');
        $response->assertSee('Présent');
        $response->assertSee('Absent');
        $this->assertDatabaseHas('attendances', [
            'course_id' => $course->id,
            'formation_id' => $formation->id,
            'assigned_teacher_profile_id' => $teacher->id,
        ]);
    }

    public function test_teacher_can_filter_attendance_by_inactive_session_formation_and_module(): void
    {
        Role::create(['name' => 'enseignant', 'guard_name' => 'web']);

        $teacherUser = User::create([
            'first_name' => 'Prof',
            'last_name' => 'Filtre',
            'email' => 'filtre@ifam.test',
            'password' => Hash::make('Secret123!'),
            'is_active' => true,
        ]);
        $teacherUser->assignRole('enseignant');
        $teacher = TeacherProfile::create(['user_id' => $teacherUser->id]);

        $sessionOne = AcademicSession::create([
            'name' => '2026-2027',
            'start_date' => '2026-09-01',
            'end_date' => '2027-06-30',
        ]);
        $sessionTwo = AcademicSession::create([
            'name' => '2027-2028',
            'start_date' => '2027-09-01',
            'end_date' => '2028-06-30',
        ]);

        $formationOne = Formation::create(['name' => 'Formation A']);
        $formationTwo = Formation::create(['name' => 'Formation B']);

        $moduleOne = Module::create(['name' => 'Module A', 'code' => 'MA']);
        $moduleTwo = Module::create(['name' => 'Module B', 'code' => 'MB']);

        Course::create([
            'formation_id' => $formationOne->id,
            'academic_session_id' => $sessionOne->id,
            'module_id' => $moduleOne->id,
            'enseignant_id' => $teacher->id,
            'teacher_profile_id' => $teacher->id,
            'name' => 'Cours visible',
        ]);

        Course::create([
            'formation_id' => $formationTwo->id,
            'academic_session_id' => $sessionTwo->id,
            'module_id' => $moduleTwo->id,
            'enseignant_id' => $teacher->id,
            'teacher_profile_id' => $teacher->id,
            'name' => 'Cours caché',
        ]);

        $response = $this->actingAs($teacherUser)->get(route('teacher.attendance.index', [
            'session_id' => $sessionOne->id,
            'formation_id' => $formationOne->id,
            'module_id' => $moduleOne->id,
        ]));

        $response->assertOk();
        $response->assertSee('Cours visible');
        $response->assertDontSee('Cours caché');
    }

    public function test_admin_can_select_course_and_see_students_with_presence_statuses(): void
    {
        Role::create(['name' => 'administrateur', 'guard_name' => 'web']);
        $admin = User::create([
            'first_name' => 'Admin',
            'last_name' => 'IFAM',
            'email' => 'admin2@ifam.test',
            'password' => Hash::make('Secret123!'),
            'is_active' => true,
        ]);
        $admin->assignRole('administrateur');

        $teacherUser = User::create([
            'first_name' => 'Prof',
            'last_name' => 'Présence',
            'email' => 'presence@ifam.test',
            'password' => Hash::make('Secret123!'),
            'is_active' => true,
        ]);
        $teacher = TeacherProfile::create(['user_id' => $teacherUser->id]);

        $session = AcademicSession::create([
            'name' => '2026-2027',
            'start_date' => '2026-09-01',
            'end_date' => '2027-06-30',
        ]);
        $formation = Formation::create(['name' => 'Formation Présence']);
        $module = Module::create(['name' => 'Module Présence', 'code' => 'PRES']);
        $course = Course::create([
            'formation_id' => $formation->id,
            'academic_session_id' => $session->id,
            'module_id' => $module->id,
            'enseignant_id' => $teacher->id,
            'teacher_profile_id' => $teacher->id,
            'name' => 'Cours présence',
        ]);

        $studentOne = StudentProfile::create(['user_id' => User::create([
            'first_name' => 'Alice',
            'last_name' => 'Étudiante',
            'email' => 'alice@ifam.test',
            'password' => Hash::make('Secret123!'),
            'is_active' => true,
        ])->id]);
        $studentTwo = StudentProfile::create(['user_id' => User::create([
            'first_name' => 'Bob',
            'last_name' => 'Étudiant',
            'email' => 'bob@ifam.test',
            'password' => Hash::make('Secret123!'),
            'is_active' => true,
        ])->id]);

        $studentOne->enrollments()->create([
            'formation_id' => $formation->id,
            'academic_session_id' => $session->id,
            'status' => 'active',
        ]);
        $studentTwo->enrollments()->create([
            'formation_id' => $formation->id,
            'academic_session_id' => $session->id,
            'status' => 'active',
        ]);

        Attendance::create([
            'course_id' => $course->id,
            'student_profile_id' => $studentOne->id,
            'formation_id' => $formation->id,
            'assigned_teacher_profile_id' => $teacher->id,
            'date' => '2026-10-07',
            'status' => 'present',
        ]);
        Attendance::create([
            'course_id' => $course->id,
            'student_profile_id' => $studentTwo->id,
            'formation_id' => $formation->id,
            'assigned_teacher_profile_id' => $teacher->id,
            'date' => '2026-10-07',
            'status' => 'absent',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.attendance.index', [
            'session_id' => $session->id,
            'formation_id' => $formation->id,
            'course_id' => $course->id,
            'date' => '2026-10-07',
        ]));

        $response->assertOk();
        $response->assertSee('Cours présence');
        $response->assertSee('Alice');
        $response->assertSee('Bob');
        $response->assertSee('Présent');
        $response->assertSee('Absent');
    }

    public function test_admin_cannot_submit_attendance_for_teacher_managed_course(): void
    {
        Role::create(['name' => 'administrateur', 'guard_name' => 'web']);
        $admin = User::create([
            'first_name' => 'Admin',
            'last_name' => 'Write',
            'email' => 'adminwrite@ifam.test',
            'password' => Hash::make('Secret123!'),
            'is_active' => true,
        ]);
        $admin->assignRole('administrateur');

        $teacherUser = User::create([
            'first_name' => 'Prof',
            'last_name' => 'Saisie',
            'email' => 'saisie@ifam.test',
            'password' => Hash::make('Secret123!'),
            'is_active' => true,
        ]);
        $teacher = TeacherProfile::create(['user_id' => $teacherUser->id]);

        $session = AcademicSession::create([
            'name' => '2026-2027',
            'start_date' => '2026-09-01',
            'end_date' => '2027-06-30',
        ]);
        $formation = Formation::create(['name' => 'Formation verrouillée']);
        $module = Module::create(['name' => 'Module verrouillé', 'code' => 'LOCK']);
        $course = Course::create([
            'formation_id' => $formation->id,
            'academic_session_id' => $session->id,
            'module_id' => $module->id,
            'enseignant_id' => $teacher->id,
            'teacher_profile_id' => $teacher->id,
            'name' => 'Cours verrouillé',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.attendance.store', ['course_id' => $course->id]), [
            'course_id' => $course->id,
            'date' => '2026-10-07',
            'attendances' => [1 => ['status' => 'present', 'remarks' => 'Test']],
        ]);

        $response->assertStatus(403);
    }

    public function test_admin_cannot_submit_grades_for_teacher_managed_evaluation(): void
    {
        Role::create(['name' => 'administrateur', 'guard_name' => 'web']);
        $admin = User::create([
            'first_name' => 'Admin',
            'last_name' => 'Grade',
            'email' => 'admingrade@ifam.test',
            'password' => Hash::make('Secret123!'),
            'is_active' => true,
        ]);
        $admin->assignRole('administrateur');

        $teacherUser = User::create([
            'first_name' => 'Prof',
            'last_name' => 'Note',
            'email' => 'note@ifam.test',
            'password' => Hash::make('Secret123!'),
            'is_active' => true,
        ]);
        $teacher = TeacherProfile::create(['user_id' => $teacherUser->id]);

        $session = AcademicSession::create([
            'name' => '2026-2027',
            'start_date' => '2026-09-01',
            'end_date' => '2027-06-30',
        ]);
        $formation = Formation::create(['name' => 'Formation note']);
        $module = Module::create(['name' => 'Module note', 'code' => 'NOTE']);
        $student = StudentProfile::create(['user_id' => User::create([
            'first_name' => 'Étudiant',
            'last_name' => 'Note',
            'email' => 'student-note@ifam.test',
            'password' => Hash::make('Secret123!'),
            'is_active' => true,
        ])->id]);
        $student->enrollments()->create([
            'formation_id' => $formation->id,
            'academic_session_id' => $session->id,
            'status' => 'active',
        ]);

        $evaluation = Evaluation::create([
            'type' => 'devoir',
            'session_id' => $session->id,
            'formation_id' => $formation->id,
            'module_id' => $module->id,
            'assigned_teacher_profile_id' => $teacher->id,
            'created_by' => $admin->id,
            'titre' => 'Devoir test',
            'date' => '2026-10-07',
            'statut' => 'ouverte',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.grades.store', $evaluation), [
            'grades' => [
                $student->id => ['score' => 18, 'comments' => 'Note admin'],
            ],
        ]);

        $response->assertStatus(403);
    }
}
