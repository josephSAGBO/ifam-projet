<?php

namespace Tests\Feature;

use App\Models\AcademicSession;
use App\Models\Enrollment;
use App\Models\Formation;
use App\Models\Site;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class EnrollmentSiteTest extends TestCase
{
    use RefreshDatabase;

    public function test_enrollment_uses_the_site_of_its_academic_session(): void
    {
        $adminRole = Role::create(['name' => 'administrateur', 'guard_name' => 'web']);
        $admin = User::create([
            'first_name' => 'Admin',
            'last_name' => 'IFAM',
            'email' => 'admin-site@ifam.test',
            'password' => Hash::make('Secret123!'),
            'is_active' => true,
        ]);
        $admin->assignRole($adminRole);

        $student = User::create([
            'first_name' => 'Awa',
            'last_name' => 'Etudiante',
            'email' => 'awa-site@ifam.test',
            'password' => Hash::make('Secret123!'),
            'is_active' => true,
        ]);
        $studentProfile = StudentProfile::create(['user_id' => $student->id]);

        $site = Site::create([
            'nom' => 'Porto-Novo',
            'ville' => 'Porto-Novo',
            'est_actif' => true,
        ]);
        $session = AcademicSession::create([
            'name' => 'Session Porto-Novo',
            'site_id' => $site->id,
            'start_date' => '2026-09-01',
            'end_date' => '2027-06-30',
            'status' => 'en_cours',
        ]);
        $formation = Formation::create([
            'name' => 'Formation test',
            'prix' => 1000,
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.enrollments.store'), [
            'student_profile_id' => $studentProfile->id,
            'formation_id' => $formation->id,
            'academic_session_id' => $session->id,
            'status' => 'active',
            'enrollment_date' => '2026-09-01',
        ]);

        $response->assertRedirect(route('admin.enrollments.index'));
        $this->assertSame($site->id, Enrollment::query()->firstOrFail()->site_id);
    }
}
