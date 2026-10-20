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

class ResultAverageTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_see_average_per_student_for_a_formation_session(): void
    {
        Role::create(['name' => 'administrateur', 'guard_name' => 'web']);
        $admin = User::create([
            'first_name' => 'Admin',
            'last_name' => 'Results',
            'email' => 'adminresults@ifam.test',
            'password' => Hash::make('Secret123!'),
            'is_active' => true,
        ]);
        $admin->assignRole('administrateur');

        $teacherUser = User::create([
            'first_name' => 'Prof',
            'last_name' => 'Moyenne',
            'email' => 'moyenne@ifam.test',
            'password' => Hash::make('Secret123!'),
            'is_active' => true,
        ]);
        $teacher = TeacherProfile::create(['user_id' => $teacherUser->id]);

        $session = AcademicSession::create([
            'name' => '2026-2027',
            'start_date' => '2026-09-01',
            'end_date' => '2027-06-30',
        ]);
        $formation = Formation::create(['name' => 'Formation moyenne']);
        $module = Module::create(['name' => 'Module moyenne', 'code' => 'MOY']);

        $formation->modules()->attach($module->id);

        $student = StudentProfile::create(['user_id' => User::create([
            'first_name' => 'Étudiant',
            'last_name' => 'Moyenne',
            'email' => 'studentmoyenne@ifam.test',
            'password' => Hash::make('Secret123!'),
            'is_active' => true,
        ])->id]);

        Enrollment::create([
            'student_profile_id' => $student->id,
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
            'titre' => 'Devoir 1',
            'date' => '2026-10-07',
            'statut' => 'ouverte',
        ]);

        Grade::create([
            'evaluation_id' => $evaluation->id,
            'student_profile_id' => $student->id,
            'score' => 12,
            'comments' => 'Bien',
            'is_absent' => false,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.results.index', [
            'session_id' => $session->id,
            'formation_id' => $formation->id,
        ]));

        $response->assertOk();
        $response->assertSee('Étudiant');
        $response->assertSee('12,00');
    }
}
