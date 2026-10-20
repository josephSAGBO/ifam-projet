<?php

namespace Tests\Feature;

use App\Models\AcademicSession;
use App\Models\Enrollment;
use App\Models\Evaluation;
use App\Models\Formation;
use App\Models\Grade;
use App\Models\Module;
use App\Models\Parcours;
use App\Models\StudentProfile;
use App\Models\User;
use App\Services\BulletinService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class BulletinServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_parcours_average_uses_completed_and_current_formation_enrollments(): void
    {
        $studentUser = User::create([
            'first_name' => 'Awa',
            'last_name' => 'Etudiante',
            'email' => 'awa@ifam.test',
            'password' => Hash::make('Secret123!'),
            'is_active' => true,
        ]);
        $student = StudentProfile::create(['user_id' => $studentUser->id]);
        $parcours = Parcours::create(['nom' => 'Stylisme-Modélisme']);
        $formationOne = Formation::create([
            'name' => 'Stylisme année 1',
            'parcours_id' => $parcours->id,
            'ordre' => 1,
            'is_active' => true,
        ]);
        $formationTwo = Formation::create([
            'name' => 'Stylisme année 2',
            'parcours_id' => $parcours->id,
            'ordre' => 2,
            'is_active' => true,
        ]);
        $moduleOne = Module::create(['name' => 'Coupe niveau 1', 'code' => 'COUPE-1']);
        $moduleTwo = Module::create(['name' => 'Coupe niveau 2', 'code' => 'COUPE-2']);
        $formationOne->modules()->attach($moduleOne->id);
        $formationTwo->modules()->attach($moduleTwo->id);
        $sessionOne = AcademicSession::create([
            'name' => '2024-2025',
            'start_date' => '2024-09-01',
            'end_date' => '2025-06-30',
            'is_current' => false,
        ]);
        $sessionTwo = AcademicSession::create([
            'name' => '2025-2026',
            'start_date' => '2025-09-01',
            'end_date' => '2026-06-30',
            'is_current' => true,
        ]);

        Enrollment::create([
            'student_profile_id' => $student->id,
            'formation_id' => $formationOne->id,
            'academic_session_id' => $sessionOne->id,
            'status' => 'completed',
        ]);
        Enrollment::create([
            'student_profile_id' => $student->id,
            'formation_id' => $formationTwo->id,
            'academic_session_id' => $sessionTwo->id,
            'status' => 'active',
        ]);

        $evaluationOne = Evaluation::create([
            'module_id' => $moduleOne->id,
            'session_id' => $sessionOne->id,
            'titre' => 'Devoir année 1',
            'type' => 'devoir',
            'statut' => 'cloturee',
        ]);
        $evaluationTwo = Evaluation::create([
            'module_id' => $moduleTwo->id,
            'session_id' => $sessionTwo->id,
            'titre' => 'Devoir année 2',
            'type' => 'devoir',
            'statut' => 'cloturee',
        ]);
        Grade::create(['evaluation_id' => $evaluationOne->id, 'student_profile_id' => $student->id, 'score' => 16]);
        Grade::create(['evaluation_id' => $evaluationTwo->id, 'student_profile_id' => $student->id, 'score' => 12]);

        $average = app(BulletinService::class)->moyenneFinaleParcours($student, $parcours);

        $this->assertSame(14.0, $average);
    }
}
