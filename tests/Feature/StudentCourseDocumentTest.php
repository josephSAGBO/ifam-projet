<?php

namespace Tests\Feature;

use App\Models\AcademicSession;
use App\Models\Course;
use App\Models\DocumentCours;
use App\Models\Enrollment;
use App\Models\Formation;
use App\Models\Module;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class StudentCourseDocumentTest extends TestCase
{
    use RefreshDatabase;

    public function test_enrolled_student_can_see_and_download_course_documents(): void
    {
        Storage::fake('public');
        $this->createStudentRole();
        $student = $this->createStudent('student@ifam.test');
        [$formation, $session, $course] = $this->createCourse();
        $this->enrollStudent($student, $formation, $session);
        $document = $this->createCourseDocument($course);

        $response = $this->actingAs($student)->get(route('student.documents.index'));

        $response->assertOk();
        $response->assertSee('Documents des cours');
        $response->assertSee('Support du cours');
        $response->assertSee(route('student.course-documents.download', $document));

        $downloadResponse = $this->actingAs($student)->get(route('student.course-documents.download', $document));

        $downloadResponse->assertOk();
        $downloadResponse->assertHeader('content-disposition', 'attachment; filename=support-cours.pdf');
    }

    public function test_student_cannot_see_or_download_documents_for_a_different_session(): void
    {
        Storage::fake('public');
        $this->createStudentRole();
        $student = $this->createStudent('student@ifam.test');
        [$formation, $session, $course] = $this->createCourse();
        $differentSession = AcademicSession::create([
            'name' => 'Autre session',
            'start_date' => '2027-09-01',
            'end_date' => '2028-06-30',
            'is_current' => true,
        ]);
        $this->enrollStudent($student, $formation, $differentSession);
        $document = $this->createCourseDocument($course);

        $response = $this->actingAs($student)->get(route('student.documents.index'));

        $response->assertOk();
        $response->assertDontSee('Support du cours');

        $downloadResponse = $this->actingAs($student)->get(route('student.course-documents.download', $document));

        $downloadResponse->assertForbidden();
    }

    private function createStudentRole(): void
    {
        Role::firstOrCreate(['name' => 'etudiant', 'guard_name' => 'web']);
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

        StudentProfile::create(['user_id' => $student->id]);

        return $student;
    }

    /**
     * @return array{Formation, AcademicSession, Course}
     */
    private function createCourse(): array
    {
        $formation = Formation::create(['name' => 'Formation Mode']);
        $session = AcademicSession::create([
            'name' => 'Session 2026-2027',
            'start_date' => '2026-09-01',
            'end_date' => '2027-06-30',
            'is_current' => true,
        ]);
        $module = Module::create(['name' => 'Coupe', 'code' => 'MOD-COUPE']);
        $course = Course::create([
            'formation_id' => $formation->id,
            'academic_session_id' => $session->id,
            'module_id' => $module->id,
            'name' => 'Cours de coupe',
        ]);

        return [$formation, $session, $course];
    }

    private function enrollStudent(User $student, Formation $formation, AcademicSession $session): void
    {
        Enrollment::create([
            'student_profile_id' => $student->studentProfile->id,
            'formation_id' => $formation->id,
            'academic_session_id' => $session->id,
            'status' => 'active',
        ]);
    }

    private function createCourseDocument(Course $course): DocumentCours
    {
        $path = 'courses/documents/support-cours.pdf';
        Storage::disk('public')->put($path, 'pdf test content');

        return $course->documents()->create([
            'title' => 'Support du cours',
            'file_path' => $path,
            'original_name' => 'support-cours.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => 16,
        ]);
    }
}
