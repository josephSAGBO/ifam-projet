<?php

namespace Tests\Feature;

use App\Models\AcademicSession;
use App\Models\Evaluation;
use App\Models\Formation;
use App\Models\Module;
use App\Models\TeacherProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminEvaluationIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_evaluation_index_loads_with_pagination_views(): void
    {
        Role::create(['name' => 'administrateur', 'guard_name' => 'web']);

        $admin = User::create([
            'first_name' => 'Admin',
            'last_name' => 'Eval',
            'email' => 'admin-eval@ifam.test',
            'password' => Hash::make('Secret123!'),
            'is_active' => true,
        ]);
        $admin->assignRole('administrateur');

        $teacherUser = User::create([
            'first_name' => 'Prof',
            'last_name' => 'Eval',
            'email' => 'teacher-eval@ifam.test',
            'password' => Hash::make('Secret123!'),
            'is_active' => true,
        ]);
        $teacher = TeacherProfile::create(['user_id' => $teacherUser->id]);

        $session = AcademicSession::create([
            'name' => '2026-2027',
            'start_date' => '2026-09-01',
            'end_date' => '2027-06-30',
        ]);

        $formation = Formation::create(['name' => 'Formation test']);
        $module = Module::create(['name' => 'Module test', 'code' => 'MOD1']);
        $formation->modules()->attach($module->id);
        $module->teachers()->attach($teacher->id);

        foreach (range(1, 16) as $index) {
            Evaluation::create([
                'type' => 'devoir',
                'created_by' => $admin->id,
                'assigned_teacher_profile_id' => $teacher->id,
                'formation_id' => $formation->id,
                'module_id' => $module->id,
                'session_id' => $session->id,
                'titre' => 'Devoir '.$index,
                'date' => now()->subDays($index),
                'statut' => 'ouverte',
            ]);
        }

        $response = $this->actingAs($admin)->get(route('admin.evaluations.index'));

        $response->assertOk();
        $response->assertSee('Devoir 1');
    }
}
