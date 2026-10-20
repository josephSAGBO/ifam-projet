<?php

namespace Tests\Feature;

use App\Models\AcademicSession;
use App\Models\Enrollment;
use App\Models\Evaluation;
use App\Models\Formation;
use App\Models\Grade;
use App\Models\Module;
use App\Models\StudentProfile;
use App\Models\TeacherProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class BulletinGenerationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_generate_bulletin_view_for_a_student(): void
    {
        Role::create(['name' => 'administrateur', 'guard_name' => 'web']);
        $admin = User::create([
            'first_name' => 'Admin',
            'last_name' => 'Bulletin',
            'email' => 'adminbulletin@ifam.test',
            'password' => Hash::make('Secret123!'),
            'is_active' => true,
        ]);
        $admin->assignRole('administrateur');

        $teacherUser = User::create([
            'first_name' => 'Prof',
            'last_name' => 'Bulletin',
            'email' => 'teacherbulletin@ifam.test',
            'password' => Hash::make('Secret123!'),
            'is_active' => true,
        ]);
        $teacher = TeacherProfile::create(['user_id' => $teacherUser->id]);

        $session = AcademicSession::create([
            'name' => '2026-2027',
            'start_date' => '2026-09-01',
            'end_date' => '2027-06-30',
        ]);
        $formation = Formation::create(['name' => 'Formation bulletin']);
        $module = Module::create(['name' => 'Module bulletin', 'code' => 'BUL']);
        $formation->modules()->attach($module->id);

        $student = StudentProfile::create(['user_id' => User::create([
            'first_name' => 'Élève',
            'last_name' => 'Bulletin',
            'email' => 'elevebulletin@ifam.test',
            'password' => Hash::make('Secret123!'),
            'is_active' => true,
        ])->id]);

        Enrollment::create([
            'student_profile_id' => $student->id,
            'formation_id' => $formation->id,
            'academic_session_id' => $session->id,
            'status' => 'active',
        ]);

        $devoir = Evaluation::create([
            'type' => 'devoir',
            'session_id' => $session->id,
            'formation_id' => $formation->id,
            'module_id' => $module->id,
            'assigned_teacher_profile_id' => $teacher->id,
            'created_by' => $admin->id,
            'titre' => 'Devoir bulletin',
            'date' => '2026-10-07',
            'statut' => 'ouverte',
        ]);
        $cc = Evaluation::create([
            'type' => 'controle_continu',
            'session_id' => $session->id,
            'formation_id' => $formation->id,
            'module_id' => $module->id,
            'assigned_teacher_profile_id' => $teacher->id,
            'created_by' => $admin->id,
            'titre' => 'Contrôle continu bulletin',
            'date' => '2026-10-08',
            'statut' => 'ouverte',
        ]);

        Grade::create([
            'evaluation_id' => $devoir->id,
            'student_profile_id' => $student->id,
            'score' => 15,
            'comments' => 'Très bon',
            'is_absent' => false,
        ]);
        Grade::create([
            'evaluation_id' => $cc->id,
            'student_profile_id' => $student->id,
            'score' => 12,
            'comments' => 'Correct',
            'is_absent' => false,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.bulletins.index', [
            'session_id' => $session->id,
            'formation_id' => $formation->id,
        ]));

        $response->assertOk();
        $response->assertSee('Bulletins');
        $response->assertSee('Élève');

        $detailResponse = $this->actingAs($admin)->get(route('admin.bulletins.show', [
            'student' => $student->id,
            'session_id' => $session->id,
            'formation_id' => $formation->id,
        ]));

        $detailResponse->assertOk();
        $detailResponse->assertSee('MOY.INT');
        $detailResponse->assertSee('COMP');
        $detailResponse->assertSee('MOY. COEF');
        $detailResponse->assertSee('Module bulletin');
        $detailResponse->assertSee('15');
        $detailResponse->assertSee('12,00');
        $detailResponse->assertSee(asset('img/watermark_ifam.png'));
        $detailResponse->assertSee(asset('img/cachet_dg_transparent.png'));
        $detailResponse->assertSee(asset('img/signature_dg_transparent.png'));
        $detailResponse->assertSee('IFAM BENIN');
        $detailResponse->assertSee('M. Thomas Koffi TETEH');
        $detailResponse->assertDontSee('Contrôles continus');
    }

    public function test_admin_can_publish_a_student_bulletin(): void
    {
        Role::create(['name' => 'administrateur', 'guard_name' => 'web']);
        $admin = User::create([
            'first_name' => 'Admin',
            'last_name' => 'Publish',
            'email' => 'adminpublish@ifam.test',
            'password' => Hash::make('Secret123!'),
            'is_active' => true,
        ]);
        $admin->assignRole('administrateur');

        $teacherUser = User::create([
            'first_name' => 'Prof',
            'last_name' => 'Publish',
            'email' => 'teacherpublish@ifam.test',
            'password' => Hash::make('Secret123!'),
            'is_active' => true,
        ]);
        TeacherProfile::create(['user_id' => $teacherUser->id]);

        $session = AcademicSession::create([
            'name' => '2026-2027',
            'start_date' => '2026-09-01',
            'end_date' => '2027-06-30',
        ]);
        $formation = Formation::create(['name' => 'Formation publication']);
        $module = Module::create(['name' => 'Module publication', 'code' => 'PUB']);
        $formation->modules()->attach($module->id);

        $student = StudentProfile::create(['user_id' => User::create([
            'first_name' => 'Étudiant',
            'last_name' => 'Publish',
            'email' => 'studentpublish@ifam.test',
            'password' => Hash::make('Secret123!'),
            'is_active' => true,
        ])->id]);

        Enrollment::create([
            'student_profile_id' => $student->id,
            'formation_id' => $formation->id,
            'academic_session_id' => $session->id,
            'status' => 'active',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.bulletins.publish', [
            'student' => $student->id,
            'session_id' => $session->id,
            'formation_id' => $formation->id,
        ]));

        $response->assertRedirect();
        $this->assertDatabaseHas('bulletin_publications', [
            'student_profile_id' => $student->id,
            'formation_id' => $formation->id,
            'academic_session_id' => $session->id,
            'is_published' => true,
        ]);

        $this->actingAs($admin)->post(route('admin.bulletins.publish', [
            'student' => $student->id,
            'session_id' => $session->id,
            'formation_id' => $formation->id,
        ]));

        $this->assertDatabaseHas('bulletin_publications', [
            'student_profile_id' => $student->id,
            'formation_id' => $formation->id,
            'academic_session_id' => $session->id,
            'is_published' => false,
        ]);

        $this->actingAs($admin)->post(route('admin.bulletins.publish', [
            'student' => $student->id,
            'session_id' => $session->id,
            'formation_id' => $formation->id,
        ]));

        $this->assertDatabaseHas('bulletin_publications', [
            'student_profile_id' => $student->id,
            'formation_id' => $formation->id,
            'academic_session_id' => $session->id,
            'is_published' => true,
        ]);
    }
}
