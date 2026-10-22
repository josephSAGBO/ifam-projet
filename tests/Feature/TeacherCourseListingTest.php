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
        $inactiveSession = $this->createSession('Session inactive', false, '2025-01-01', 'terminee');

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
        $response->assertSee('Historique des rentrées terminées');
        $response->assertSee('Retirer de mon historique');
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

    public function test_teacher_can_remove_an_archived_course_from_only_their_interface(): void
    {
        Role::create(['name' => 'enseignant', 'guard_name' => 'web']);
        Role::create(['name' => 'etudiant', 'guard_name' => 'web']);
        $teacher = $this->createTeacher('teacher.archive@ifam.test');
        $teacherProfile = TeacherProfile::create(['user_id' => $teacher->id]);
        $otherTeacher = $this->createTeacher('teacher.peer.archive@ifam.test');
        $otherTeacherProfile = TeacherProfile::create(['user_id' => $otherTeacher->id]);
        $formation = Formation::create(['name' => 'Formation Archive']);
        $module = Module::create(['name' => 'Module Archive', 'code' => 'ARC101']);
        $room = Room::create(['name' => 'Salle archive']);
        $session = $this->createSession('Rentrée terminée', false, '2025-09-01', 'terminee');
        $course = $this->createCourse($teacherProfile, $formation, $module, $session, $room, [
            'academic_session_id' => $session->id,
            'name' => 'Cours à masquer',
            'teacher_profile_id' => $otherTeacherProfile->id,
        ]);
        $student = $this->createStudent('student.archive@ifam.test');
        $studentProfile = StudentProfile::create(['user_id' => $student->id]);
        Enrollment::create([
            'student_profile_id' => $studentProfile->id,
            'formation_id' => $formation->id,
            'academic_session_id' => $session->id,
            'status' => 'active',
        ]);

        $this->actingAs($teacher)
            ->get(route('teacher.courses.index'))
            ->assertSee('Historique des rentrées terminées')
            ->assertSee('Retirer de mon historique');

        $response = $this->actingAs($teacher)
            ->delete(route('teacher.courses.archive.destroy', $course));

        $response->assertRedirect(route('teacher.courses.index'));
        $this->assertDatabaseHas('teacher_hidden_courses', [
            'teacher_profile_id' => $teacherProfile->id,
            'course_id' => $course->id,
        ]);
        $this->assertModelExists($course);

        $this->actingAs($teacher)
            ->get(route('teacher.courses.index'))
            ->assertDontSee('Cours à masquer');
        $this->actingAs($teacher)
            ->get(route('teacher.courses.show', $course))
            ->assertNotFound();
        $this->actingAs($otherTeacher)
            ->get(route('teacher.courses.index'))
            ->assertSee('Cours à masquer');
        $this->actingAs($student)
            ->get(route('student.schedule'))
            ->assertSee('Cours à masquer')
            ->assertSee('Historique des cours');
    }

    public function test_teacher_cannot_remove_an_active_or_unassigned_course(): void
    {
        Role::create(['name' => 'enseignant', 'guard_name' => 'web']);
        $teacher = $this->createTeacher('teacher.archive.guard@ifam.test');
        $teacherProfile = TeacherProfile::create(['user_id' => $teacher->id]);
        $otherTeacher = $this->createTeacher('teacher.archive.owner@ifam.test');
        $otherTeacherProfile = TeacherProfile::create(['user_id' => $otherTeacher->id]);
        $formation = Formation::create(['name' => 'Formation sécurisée']);
        $module = Module::create(['name' => 'Module sécurisé', 'code' => 'SEC101']);
        $room = Room::create(['name' => 'Salle sécurisée']);
        $activeSession = $this->createSession('Rentrée en cours', true, '2026-09-01');
        $completedSession = $this->createSession('Rentrée terminée', false, '2025-09-01', 'terminee');
        $activeCourse = $this->createCourse($teacherProfile, $formation, $module, $activeSession, $room, [
            'academic_session_id' => $activeSession->id,
            'name' => 'Cours actif',
        ]);
        $unassignedArchivedCourse = $this->createCourse($otherTeacherProfile, $formation, $module, $completedSession, $room, [
            'academic_session_id' => $completedSession->id,
            'name' => 'Cours d’un autre enseignant',
        ]);

        $this->actingAs($teacher)
            ->delete(route('teacher.courses.archive.destroy', $activeCourse))
            ->assertForbidden();
        $this->actingAs($teacher)
            ->delete(route('teacher.courses.archive.destroy', $unassignedArchivedCourse))
            ->assertForbidden();

        $this->assertDatabaseMissing('teacher_hidden_courses', ['course_id' => $activeCourse->id]);
        $this->assertDatabaseMissing('teacher_hidden_courses', ['course_id' => $unassignedArchivedCourse->id]);
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

    private function createSession(string $name, bool $isCurrent, string $startDate, string $status = 'en_cours'): AcademicSession
    {
        return AcademicSession::create([
            'name' => $name,
            'start_date' => $startDate,
            'end_date' => date('Y-m-d', strtotime($startDate.' +1 year')),
            'status' => $status,
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
