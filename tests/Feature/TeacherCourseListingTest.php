<?php

namespace Tests\Feature;

use App\Models\AcademicSession;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Formation;
use App\Models\Module;
use App\Models\Room;
use App\Models\StudentProfile;
use App\Models\TeacherProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TeacherCourseListingTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_sees_all_assigned_courses_across_sessions_and_not_other_teachers_courses(): void
    {
        Role::create(['name' => 'enseignant', 'guard_name' => 'web']);
        $teacher = $this->createTeacher('professeur@ifam.test');
        $otherTeacher = $this->createTeacher('collegue@ifam.test');
        $teacherProfile = TeacherProfile::create(['user_id' => $teacher->id]);
        $otherTeacherProfile = TeacherProfile::create(['user_id' => $otherTeacher->id]);

        $formation = Formation::create(['name' => 'Formation Mode']);
        $module = Module::create(['name' => 'Coupe', 'code' => 'MOD-COUPE']);
        $room = Room::create(['name' => 'Atelier 2']);
        $activeSessionOne = $this->createSession('Session A', true, '2026-09-01');
        $activeSessionTwo = $this->createSession('Session B', true, '2027-01-01');
        $inactiveSession = $this->createSession('Session inactive', false, '2025-01-01');

        $courseWithoutMatchingSession = $this->createCourse($teacherProfile, $formation, $module, $activeSessionOne, $room, [
            'name' => 'Cours Session A',
            'date' => '2026-10-12',
            'heure_debut' => '08:30:00',
            'heure_fin' => '10:00:00',
        ]);
        $this->createCourse($teacherProfile, $formation, $module, $activeSessionTwo, $room, [
            'name' => 'Cours Session B',
            'date' => '2027-02-12',
            'heure_debut' => '13:00:00',
            'heure_fin' => '15:00:00',
        ]);
        $closedCourse = $this->createCourse($teacherProfile, $formation, $module, $inactiveSession, $room, [
            'name' => 'Cours session inactive',
            'statut' => 'termine',
        ]);
        Role::firstOrCreate(['name' => 'etudiant', 'guard_name' => 'web']);
        $completedStudent = $this->createStudent('termine@ifam.test');
        $completedProfile = StudentProfile::create(['user_id' => $completedStudent->id]);
        Enrollment::create([
            'student_profile_id' => $completedProfile->id,
            'formation_id' => $formation->id,
            'academic_session_id' => $inactiveSession->id,
            'status' => 'completed',
        ]);
        $suspendedStudent = $this->createStudent('suspendu@ifam.test');
        $suspendedProfile = StudentProfile::create(['user_id' => $suspendedStudent->id]);
        Enrollment::create([
            'student_profile_id' => $suspendedProfile->id,
            'formation_id' => $formation->id,
            'academic_session_id' => $inactiveSession->id,
            'status' => 'suspended',
        ]);
        $this->createCourse($otherTeacherProfile, $formation, $module, $activeSessionOne, $room, [
            'name' => 'Cours du collègue',
        ]);

        $response = $this->actingAs($teacher)->get(route('teacher.courses.index'));

        $response->assertOk();
        $response->assertSee('Session A');
        $response->assertSee('Session B');
        $response->assertSee('Cours Session A');
        $response->assertSee('Cours Session B');
        $response->assertSee('Cours session inactive');
        $response->assertSee('Session inactive');
        $response->assertSee('history=1');
        $response->assertSee('12/10/2026');
        $response->assertSee('08:30 - 10:00');
        $response->assertSee('Atelier 2');
        $response->assertSee('Formation Mode');
        $response->assertDontSee('Cours du collègue');

        $detailsResponse = $this->actingAs($teacher)->get(route('teacher.courses.show', $closedCourse));

        $detailsResponse->assertSee('Historique des présences');
        $detailsResponse->assertSee('Historique des notes');
        $detailsResponse->assertSee('Etudiant IFAM');
        $detailsResponse->assertDontSee('suspendu@ifam.test');
        $detailsResponse->assertSee('history=1');

        $mismatchedDetailsResponse = $this->actingAs($teacher)->get(
            route('teacher.courses.show', $courseWithoutMatchingSession)
        );

        $mismatchedDetailsResponse->assertSee('Des inscriptions existent pour cette formation dans une autre session.');
        $mismatchedDetailsResponse->assertDontSee('Etudiant IFAM');
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

    private function createStudent(string $email): User
    {
        $student = User::create([
            'first_name' => 'Etudiant',
            'last_name' => 'IFAM',
            'email' => $email,
            'password' => Hash::make('Secret123!'),
            'is_active' => true,
        ]);
        $student->assignRole('etudiant');

        return $student;
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

    /**
     * @param  array<string, string>  $attributes
     */
    private function createCourse(
        TeacherProfile $teacher,
        Formation $formation,
        Module $module,
        AcademicSession $session,
        Room $room,
        array $attributes = [],
    ): Course {
        return Course::create(array_merge([
            'formation_id' => $formation->id,
            'academic_session_id' => $session->id,
            'module_id' => $module->id,
            'salle_id' => $room->id,
            'enseignant_id' => $teacher->id,
            'teacher_profile_id' => $teacher->id,
        ], $attributes));
    }
}
