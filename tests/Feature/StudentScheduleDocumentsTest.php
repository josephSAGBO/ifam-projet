<?php

namespace Tests\Feature;

use App\Models\AcademicSession;
use App\Models\Course;
use App\Models\DocumentCours;
use App\Models\Enrollment;
use App\Models\Formation;
use App\Models\Module;
use App\Models\Room;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class StudentScheduleDocumentsTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_schedule_shows_active_and_completed_enrollments_but_not_unenrolled_courses(): void
    {
        Role::create(['name' => 'etudiant', 'guard_name' => 'web']);
        [$student, $studentProfile] = $this->createStudent('student@ifam.test');

        $firstFormation = Formation::create(['name' => 'Formation Couture']);
        $secondFormation = Formation::create(['name' => 'Formation Stylisme']);
        $activeSession = $this->createSession('Session A', '2026-09-01');
        $secondSession = $this->createSession('Session B', '2027-01-01');
        $completedSession = $this->createSession('Session terminée', '2025-01-01');
        $inactiveSession = $this->createSession('Session inactive', '2025-01-01');
        $firstModule = Module::create(['name' => 'Coupe', 'code' => 'MOD-COUPE']);
        $secondModule = Module::create(['name' => 'Dessin', 'code' => 'MOD-DESSIN']);
        $room = Room::create(['name' => 'Atelier 4']);

        $this->createEnrollment($studentProfile, $firstFormation, $activeSession);
        $this->createEnrollment($studentProfile, $secondFormation, $secondSession);
        $this->createEnrollment($studentProfile, $firstFormation, $completedSession, 'completed');

        $firstCourse = $this->createCourse($firstFormation, $activeSession, $firstModule, $room, [
            'name' => 'Cours de coupe',
            'date' => '2026-10-12',
            'heure_debut' => '08:30:00',
            'heure_fin' => '10:00:00',
        ]);
        $this->createCourse($secondFormation, $secondSession, $secondModule, $room, [
            'name' => 'Cours de dessin',
        ]);
        $completedCourse = $this->createCourse($firstFormation, $completedSession, $firstModule, $room, [
            'name' => 'Cours de coupe ancienne session',
        ]);
        $this->createCourse($firstFormation, $inactiveSession, $firstModule, $room, [
            'name' => 'Cours sans inscription active',
        ]);

        Storage::fake('public');
        Storage::disk('public')->put('courses/documents/support-coupe.pdf', 'support content');
        $document = DocumentCours::create([
            'cours_id' => $firstCourse->id,
            'title' => 'Support de coupe',
            'file_path' => 'courses/documents/support-coupe.pdf',
            'original_name' => 'support-coupe.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => 15,
        ]);
        Storage::disk('public')->put('courses/documents/support-ancien.pdf', 'old course support');
        $completedDocument = DocumentCours::create([
            'cours_id' => $completedCourse->id,
            'title' => 'Support ancienne session',
            'file_path' => 'courses/documents/support-ancien.pdf',
            'original_name' => 'support-ancien.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => 18,
        ]);

        $response = $this->actingAs($student)->get(route('student.schedule'));

        $response->assertOk();
        $response->assertSee('Cours de coupe');
        $response->assertSee('Cours de dessin');
        $response->assertSee('Cours de coupe ancienne session');
        $response->assertSee('Formation Couture');
        $response->assertSee('Formation Stylisme');
        $response->assertSee('Session A');
        $response->assertSee('Session B');
        $response->assertSee('12/10/2026');
        $response->assertSee('08:30 - 10:00');
        $response->assertSee('Atelier 4');
        $response->assertSee('Support de coupe');
        $response->assertSee('Support ancienne session');
        $response->assertSee(route('student.course-documents.download', $document));
        $response->assertSee(route('student.course-documents.download', $completedDocument));
        $response->assertDontSee('Cours sans inscription active');
        $response->assertDontSee('Session inactive');

        $documentsResponse = $this->actingAs($student)->get(route('student.documents.index'));

        $documentsResponse->assertSee('Support ancienne session');
    }

    public function test_only_a_student_with_an_active_or_completed_matching_enrollment_can_download_course_documents(): void
    {
        Role::create(['name' => 'etudiant', 'guard_name' => 'web']);
        [$enrolledStudent, $enrolledProfile] = $this->createStudent('enrolled@ifam.test');
        [$otherStudent] = $this->createStudent('other@ifam.test');

        $formation = Formation::create(['name' => 'Formation Couture']);
        $session = $this->createSession('Session A', '2026-09-01');
        $module = Module::create(['name' => 'Coupe', 'code' => 'MOD-COUPE']);
        $course = $this->createCourse($formation, $session, $module);
        $this->createEnrollment($enrolledProfile, $formation, $session);
        [$completedStudent, $completedProfile] = $this->createStudent('completed@ifam.test');
        $this->createEnrollment($completedProfile, $formation, $session, 'completed');

        Storage::fake('public');
        Storage::disk('public')->put('courses/documents/support.pdf', 'private course support');
        $document = DocumentCours::create([
            'cours_id' => $course->id,
            'title' => 'Support du cours',
            'file_path' => 'courses/documents/support.pdf',
            'original_name' => 'support.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => 22,
        ]);

        $download = $this->actingAs($enrolledStudent)
            ->get(route('student.course-documents.download', $document));

        $download->assertDownload('support.pdf');

        $completedDownload = $this->actingAs($completedStudent)
            ->get(route('student.course-documents.download', $document));

        $completedDownload->assertDownload('support.pdf');

        $forbidden = $this->actingAs($otherStudent)
            ->get(route('student.course-documents.download', $document));

        $forbidden->assertForbidden();
    }

    public function test_schedule_explains_when_courses_exist_only_in_another_enrolled_session(): void
    {
        Role::create(['name' => 'etudiant', 'guard_name' => 'web']);
        [$student, $studentProfile] = $this->createStudent('session-mismatch@ifam.test');

        $formation = Formation::create(['name' => 'Formation Couture']);
        $enrollmentSession = $this->createSession('Session inscription', '2026-09-01');
        $courseSession = $this->createSession('Session du cours', '2027-09-01');
        $this->createEnrollment($studentProfile, $formation, $enrollmentSession);
        $this->createCourse(
            $formation,
            $courseSession,
            Module::create(['name' => 'Coupe', 'code' => 'MOD-COUPE']),
            attributes: ['name' => 'Cours autre session'],
        );

        $response = $this->actingAs($student)->get(route('student.schedule'));

        $response->assertSee('rattachés à une autre session');
        $response->assertSee('vérifier ces associations');
        $response->assertDontSee('Cours autre session');
    }

    /**
     * @return array{0: User, 1: StudentProfile}
     */
    private function createStudent(string $email): array
    {
        $user = User::create([
            'first_name' => 'Etudiant',
            'last_name' => 'IFAM',
            'email' => $email,
            'password' => Hash::make('Secret123!'),
            'is_active' => true,
        ]);
        $user->assignRole('etudiant');

        return [$user, StudentProfile::create(['user_id' => $user->id])];
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
        StudentProfile $student,
        Formation $formation,
        AcademicSession $session,
        string $status = 'active',
    ): Enrollment {
        return Enrollment::create([
            'student_profile_id' => $student->id,
            'formation_id' => $formation->id,
            'academic_session_id' => $session->id,
            'status' => $status,
        ]);
    }

    /**
     * @param  array<string, string>  $attributes
     */
    private function createCourse(
        Formation $formation,
        AcademicSession $session,
        Module $module,
        ?Room $room = null,
        array $attributes = [],
    ): Course {
        return Course::create(array_merge([
            'formation_id' => $formation->id,
            'academic_session_id' => $session->id,
            'module_id' => $module->id,
            'salle_id' => $room?->id,
        ], $attributes));
    }
}
