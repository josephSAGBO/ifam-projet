<?php

namespace Tests\Feature\Admin;

use App\Models\AcademicSession;
use App\Models\Enrollment;
use App\Models\Formation;
use App\Models\Payment;
use App\Models\Site;
use App\Models\StudentProfile;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Database\Seeders\SiteSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class EnrollmentRoleAccessTest extends TestCase
{
    use RefreshDatabase;

    protected Site $cotonou;

    protected Site $bohicon;

    protected User $commercial;

    protected User $cotonouAdmin;

    protected User $studentCotonou;

    protected User $studentBohicon;

    protected Enrollment $enrollmentCotonou;

    protected Enrollment $enrollmentBohicon;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(SiteSeeder::class);
        $this->seed(RoleSeeder::class);

        $this->cotonou = Site::where('nom', 'Cotonou')->firstOrFail();
        $this->bohicon = Site::create([
            'nom' => 'Bohicon',
            'ville' => 'Bohicon',
            'est_actif' => true,
        ]);

        // Responsable Commercial
        $this->commercial = User::create([
            'first_name' => 'Resp',
            'last_name' => 'Commercial',
            'email' => 'commercial@ifam.test',
            'password' => Hash::make('Password123!'),
            'is_active' => true,
        ]);
        $this->commercial->assignRole('responsable_commercial');

        // Responsable de site Cotonou
        $this->cotonouAdmin = User::create([
            'first_name' => 'Admin',
            'last_name' => 'Cotonou',
            'email' => 'admin.cotonou@ifam.test',
            'password' => Hash::make('Password123!'),
            'is_active' => true,
        ]);
        $this->cotonouAdmin->assignRole('responsable_site');
        $this->cotonouAdmin->sites()->attach($this->cotonou->id);

        // Étudiant Cotonou
        $this->studentCotonou = User::create([
            'first_name' => 'Awa',
            'last_name' => 'KPONON',
            'email' => 'student.cotonou@ifam.test',
            'password' => Hash::make('Password123!'),
            'is_active' => true,
        ]);
        $this->studentCotonou->assignRole('etudiant');
        $this->studentCotonou->sites()->attach($this->cotonou->id);
        $profileCotonou = StudentProfile::create(['user_id' => $this->studentCotonou->id]);

        // Étudiant Bohicon
        $this->studentBohicon = User::create([
            'first_name' => 'Benoit',
            'last_name' => 'BIOKOU',
            'email' => 'student.bohicon@ifam.test',
            'password' => Hash::make('Password123!'),
            'is_active' => true,
        ]);
        $this->studentBohicon->assignRole('etudiant');
        $this->studentBohicon->sites()->attach($this->bohicon->id);
        $profileBohicon = StudentProfile::create(['user_id' => $this->studentBohicon->id]);

        $formation = Formation::create([
            'name' => 'Licence Informatique',
            'prix' => 500000,
            'is_active' => true,
        ]);

        $sessionCotonou = AcademicSession::create([
            'name' => 'Session 2026 Cotonou',
            'site_id' => $this->cotonou->id,
            'start_date' => '2026-10-01',
            'end_date' => '2027-06-30',
            'status' => 'en_cours',
        ]);

        $sessionBohicon = AcademicSession::create([
            'name' => 'Session 2026 Bohicon',
            'site_id' => $this->bohicon->id,
            'start_date' => '2026-10-01',
            'end_date' => '2027-06-30',
            'status' => 'en_cours',
        ]);

        $this->enrollmentCotonou = Enrollment::create([
            'student_profile_id' => $profileCotonou->id,
            'formation_id' => $formation->id,
            'academic_session_id' => $sessionCotonou->id,
            'site_id' => $this->cotonou->id,
            'enrollment_date' => '2026-10-05',
            'prix_formation' => 500000,
            'montant_du' => 500000,
            'status' => 'active',
        ]);

        $this->enrollmentBohicon = Enrollment::create([
            'student_profile_id' => $profileBohicon->id,
            'formation_id' => $formation->id,
            'academic_session_id' => $sessionBohicon->id,
            'site_id' => $this->bohicon->id,
            'enrollment_date' => '2026-10-06',
            'prix_formation' => 500000,
            'montant_du' => 500000,
            'status' => 'active',
        ]);
    }

    public function test_responsable_commercial_can_view_enrollments_from_multiple_sites(): void
    {
        $response = $this->actingAs($this->commercial)->get(route('admin.enrollments.index'));
        $response->assertOk();
        $response->assertSee('Cotonou');
        $response->assertSee('Bohicon');
    }

    public function test_responsable_commercial_dashboard_summarizes_enrollments_across_sites(): void
    {
        $this->travelTo(now()->setDate(2026, 10, 21));

        $response = $this->actingAs($this->commercial)->get(route('admin.dashboard'));

        $response->assertOk()
            ->assertSee('Tableau de bord — Inscriptions commerciales')
            ->assertSee('Inscriptions par site')
            ->assertSee('Inscriptions récentes')
            ->assertSee('Cotonou')
            ->assertSee('Bohicon')
            ->assertSee($this->studentCotonou->last_name)
            ->assertSee($this->studentBohicon->last_name)
            ->assertViewHas('commercialSupervision.totalEnrollments', 2)
            ->assertViewHas('commercialSupervision.enrollmentsThisMonth', 2)
            ->assertViewHas('commercialSupervision.activeEnrollments', 2)
            ->assertViewHas('commercialSupervision.uniqueStudents', 2);
    }

    public function test_responsable_commercial_can_filter_enrollments_by_site(): void
    {
        $resCotonou = $this->actingAs($this->commercial)->get(route('admin.enrollments.index', ['site_id' => $this->cotonou->id]));
        $resCotonou->assertOk();
        $resCotonou->assertSee($this->studentCotonou->last_name);
        $resCotonou->assertDontSee($this->studentBohicon->last_name);

        $resBohicon = $this->actingAs($this->commercial)->get(route('admin.enrollments.index', ['site_id' => $this->bohicon->id]));
        $resBohicon->assertOk();
        $resBohicon->assertSee($this->studentBohicon->last_name);
        $resBohicon->assertDontSee($this->studentCotonou->last_name);
    }

    public function test_responsable_site_only_sees_enrollments_of_own_site(): void
    {
        $response = $this->actingAs($this->cotonouAdmin)->get(route('admin.enrollments.index'));
        $response->assertOk();
        $response->assertSee($this->studentCotonou->last_name);
        $response->assertDontSee($this->studentBohicon->last_name);
    }

    public function test_responsable_site_cannot_access_enrollment_of_other_site(): void
    {
        $this->actingAs($this->cotonouAdmin)
            ->get(route('admin.enrollments.show', $this->enrollmentBohicon))
            ->assertForbidden();

        $this->actingAs($this->cotonouAdmin)
            ->get(route('admin.enrollments.edit', $this->enrollmentBohicon))
            ->assertForbidden();

        $this->actingAs($this->cotonouAdmin)
            ->delete(route('admin.enrollments.destroy', $this->enrollmentBohicon))
            ->assertForbidden();
    }

    public function test_responsable_commercial_can_view_enrollment_from_any_site(): void
    {
        $this->actingAs($this->commercial)
            ->get(route('admin.enrollments.show', $this->enrollmentCotonou))
            ->assertOk();

        $this->actingAs($this->commercial)
            ->get(route('admin.enrollments.show', $this->enrollmentBohicon))
            ->assertOk();
    }

    public function test_responsable_commercial_can_view_receipt_of_payment(): void
    {
        $payment = Payment::create([
            'inscription_id' => $this->enrollmentCotonou->id,
            'student_profile_id' => $this->enrollmentCotonou->student_profile_id,
            'montant' => 100000,
            'date_paiement' => '2026-10-10',
            'mode' => 'especes',
            'reference' => 'IFAM-REC-2026-000099',
            'status' => 'valide',
        ]);

        $this->actingAs($this->commercial)
            ->get(route('admin.payments.receipt', $payment))
            ->assertOk()
            ->assertSee('IFAM-REC-2026-000099');
    }
}
