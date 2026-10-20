<?php

namespace Tests\Feature;

use App\Models\AcademicSession;
use App\Models\Course;
use App\Models\DocumentCours;
use App\Models\Formation;
use App\Models\Module;
use App\Models\TeacherProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TeacherCourseDocumentTest extends TestCase
{
    use RefreshDatabase;

    public function test_assigned_teacher_can_upload_a_document_to_the_course(): void
    {
        Storage::fake('public');
        [$teacher, $teacherProfile] = $this->createTeacher('teacher@ifam.test');
        $course = $this->createCourseFor($teacherProfile);

        $response = $this->actingAs($teacher)->post(route('teacher.courses.documents.store', $course), [
            'title' => 'Support du chapitre 1',
            'document' => UploadedFile::fake()->create('support.pdf', 120, 'application/pdf'),
        ]);

        $response->assertRedirect(route('teacher.courses.show', $course));
        $response->assertSessionHas('success');

        $document = DocumentCours::query()->sole();
        $this->assertSame($course->id, $document->cours_id);
        $this->assertSame($teacher->id, $document->uploaded_by);
        $this->assertSame('Support du chapitre 1', $document->title);
        Storage::disk('public')->assertExists($document->file_path);
    }

    public function test_teacher_cannot_upload_to_a_course_assigned_to_another_teacher(): void
    {
        Storage::fake('public');
        [$teacher] = $this->createTeacher('teacher@ifam.test');
        [, $courseOwnerProfile] = $this->createTeacher('colleague@ifam.test');
        $course = $this->createCourseFor($courseOwnerProfile);

        $response = $this->actingAs($teacher)->post(route('teacher.courses.documents.store', $course), [
            'title' => 'Document non autorisé',
            'document' => UploadedFile::fake()->create('support.pdf', 120, 'application/pdf'),
        ]);

        $response->assertForbidden();
        $this->assertDatabaseCount('documents_cours', 0);
    }

    public function test_teacher_upload_rejects_unsupported_file_types(): void
    {
        Storage::fake('public');
        [$teacher, $teacherProfile] = $this->createTeacher('teacher@ifam.test');
        $course = $this->createCourseFor($teacherProfile);

        $response = $this->actingAs($teacher)->post(route('teacher.courses.documents.store', $course), [
            'title' => 'Fichier non autorisé',
            'document' => UploadedFile::fake()->create('script.exe', 10, 'application/x-msdownload'),
        ]);

        $response->assertSessionHasErrors('document');
        $this->assertDatabaseCount('documents_cours', 0);
    }

    /**
     * @return array{0: User, 1: TeacherProfile}
     */
    private function createTeacher(string $email): array
    {
        Role::firstOrCreate(['name' => 'enseignant', 'guard_name' => 'web']);

        $teacher = User::create([
            'first_name' => 'Enseignant',
            'last_name' => 'IFAM',
            'email' => $email,
            'password' => Hash::make('Secret123!'),
            'is_active' => true,
        ]);
        $teacher->assignRole('enseignant');

        $teacherProfile = TeacherProfile::create(['user_id' => $teacher->id]);

        return [$teacher, $teacherProfile];
    }

    private function createCourseFor(TeacherProfile $teacherProfile): Course
    {
        $formation = Formation::create(['name' => 'Formation IFAM']);
        $session = AcademicSession::create([
            'name' => '2026-2027',
            'start_date' => '2026-09-01',
            'end_date' => '2027-06-30',
            'is_current' => true,
        ]);
        $module = Module::create([
            'name' => 'Module IFAM',
            'code' => 'MOD-IFAM',
        ]);

        return Course::create([
            'formation_id' => $formation->id,
            'academic_session_id' => $session->id,
            'module_id' => $module->id,
            'enseignant_id' => $teacherProfile->id,
            'teacher_profile_id' => $teacherProfile->id,
        ]);
    }
}
