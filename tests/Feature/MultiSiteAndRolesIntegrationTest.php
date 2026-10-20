<?php

namespace Tests\Feature;

use App\Models\AcademicSession;
use App\Models\Alumni;
use App\Models\Enrollment;
use App\Models\Formation;
use App\Models\ServiceProfile;
use App\Models\Site;
use App\Models\StudentProfile;
use App\Models\User;
use App\Models\UserAssignment;
use Database\Seeders\RoleSeeder;
use Database\Seeders\SiteSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class MultiSiteAndRolesIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(SiteSeeder::class);
        $this->seed(RoleSeeder::class);
    }

    public function test_cotonou_site_seeding_is_idempotent(): void
    {
        $initialCount = Site::where('nom', 'Cotonou')->count();
        $this->assertSame(1, $initialCount);

        // Run seeder multiple times
        $this->seed(SiteSeeder::class);
        $this->seed(SiteSeeder::class);

        $this->assertSame(1, Site::where('nom', 'Cotonou')->count());
        $cotonou = Site::where('nom', 'Cotonou')->first();
        $this->assertSame('Cotonou', $cotonou->ville);
        $this->assertTrue($cotonou->est_actif);
    }

    public function test_site_inheritance_from_session_to_enrollment_to_alumni(): void
    {
        $site = Site::create([
            'nom' => 'Parakou',
            'ville' => 'Parakou',
            'est_actif' => true,
        ]);

        $session = AcademicSession::create([
            'name' => 'Session Parakou 2026',
            'normalized_name' => 'session parakou 2026',
            'site_id' => $site->id,
            'start_date' => '2026-09-01',
            'end_date' => '2027-06-30',
            'status' => 'en_cours',
        ]);

        $formation = Formation::create([
            'name' => 'Couture Pro',
            'prix' => 100000,
            'is_active' => true,
        ]);

        $user = User::create([
            'first_name' => 'Bio',
            'last_name' => 'Guerin',
            'email' => 'bio.guerin@ifam.test',
            'password' => Hash::make('Secret123!'),
            'is_active' => true,
        ]);
        $studentProfile = StudentProfile::create(['user_id' => $user->id]);

        $admin = User::create([
            'first_name' => 'Admin',
            'last_name' => 'Test',
            'email' => 'admin-inherit@ifam.test',
            'password' => Hash::make('Secret123!'),
            'is_active' => true,
        ]);
        $admin->assignRole('administrateur');

        // Test Enrollment creation inherits session site
        $this->actingAs($admin)->post(route('admin.enrollments.store'), [
            'student_profile_id' => $studentProfile->id,
            'formation_id' => $formation->id,
            'academic_session_id' => $session->id,
            'status' => 'completed',
            'enrollment_date' => '2026-09-01',
        ]);

        $enrollment = Enrollment::where('student_profile_id', $studentProfile->id)->firstOrFail();
        $this->assertSame($site->id, $enrollment->site_id);

        // Test Alumni conversion inherits enrollment's site
        $this->actingAs($admin)->post(route('admin.alumnis.convert', $studentProfile));
        $alumni = Alumni::where('student_profile_id', $studentProfile->id)->firstOrFail();
        $this->assertSame($site->id, $alumni->site_id);
    }

    public function test_user_site_relationships_and_assignments(): void
    {
        $cotonou = Site::where('nom', 'Cotonou')->firstOrFail();
        $portoNovo = Site::create(['nom' => 'Porto-Novo', 'ville' => 'Porto-Novo', 'est_actif' => true]);

        $user = User::create([
            'first_name' => 'Kofi',
            'last_name' => 'Manager',
            'email' => 'kofi.manager@ifam.test',
            'password' => Hash::make('Secret123!'),
            'is_active' => true,
        ]);

        // Attach multiple sites
        $user->sites()->attach([$cotonou->id, $portoNovo->id]);
        $user->refresh();

        $this->assertCount(2, $user->sites);
        $this->assertTrue($user->sites->contains('id', $cotonou->id));
        $this->assertTrue($user->sites->contains('id', $portoNovo->id));

        // Create assignment to a service profile
        $insertionProfile = ServiceProfile::where('code', 'insertion_professionnelle')->firstOrFail();
        $assignment = UserAssignment::create([
            'user_id' => $user->id,
            'service_profile_id' => $insertionProfile->id,
            'site_id' => $cotonou->id,
            'level' => 'responsable',
            'is_department_head' => true,
            'department_name' => 'Insertion & Carrières',
        ]);

        $this->assertTrue($user->hasServiceAssignment('insertion_professionnelle', 'responsable', $cotonou->id));
        $this->assertTrue($user->isDepartmentHead());
        $this->assertTrue($user->isDepartmentHead('Insertion & Carrières'));
        $this->assertFalse($user->hasServiceAssignment('gestion_stocks'));
    }

    public function test_all_14_service_profiles_exist_with_agent_and_responsable_roles(): void
    {
        $expectedProfiles = [
            'pedagogie_etudes' => 'Pédagogie / Études',
            'insertion_professionnelle' => 'Insertion professionnelle',
            'administratif' => 'Administratif',
            'financier' => 'Financier',
            'commercial' => 'Commercial',
            'apres_vente' => 'Après-vente',
            'gestion_stocks' => 'Gestion des stocks',
            'maintenance_logistique' => 'Maintenance et logistique',
            'marketing' => 'Marketing',
            'communication' => 'Communication',
            'evenementiel' => 'Événementiel',
            'relations_exterieures' => 'Relations extérieures',
            'administration_systemes' => 'Administration des systèmes',
            'innovation_accompagnement' => 'Innovation et accompagnement',
        ];

        $this->assertSame(14, ServiceProfile::count());

        foreach ($expectedProfiles as $code => $name) {
            $profile = ServiceProfile::where('code', $code)->first();
            $this->assertNotNull($profile, "Service profile {$code} must exist");
            $this->assertSame($name, $profile->name);

            // Check roles exist for both Agent and Responsable
            $this->assertTrue(Role::where('name', "agent_{$code}")->exists(), "Role agent_{$code} must exist");
            $this->assertTrue(Role::where('name', "responsable_{$code}")->exists(), "Role responsable_{$code} must exist");
        }
    }

    public function test_responsable_session_role_is_absent_and_never_recreated(): void
    {
        $this->assertFalse(
            Role::where('name', 'responsable_session')->exists(),
            'Role responsable_session must not exist in target configuration'
        );

        // Re-seeding must never recreate it
        $this->seed(RoleSeeder::class);
        $this->assertFalse(
            Role::where('name', 'responsable_session')->exists(),
            'Role responsable_session must never be recreated'
        );
    }

    public function test_sidebar_does_not_contain_responsables_de_session(): void
    {
        $admin = User::create([
            'first_name' => 'Admin',
            'last_name' => 'Sidebar',
            'email' => 'admin-sidebar@ifam.test',
            'password' => Hash::make('Secret123!'),
            'is_active' => true,
        ]);
        $admin->assignRole('administrateur');

        $response = $this->actingAs($admin)->get(route('admin.users.index'));
        $response->assertOk();
        $response->assertDontSee('Responsables de session');
    }

    public function test_no_duplicate_roles_and_permissions(): void
    {
        $roles = Role::pluck('name');
        $this->assertSame($roles->count(), $roles->unique()->count(), 'There must be no duplicate role names');

        $permissions = Permission::pluck('name');
        $this->assertSame($permissions->count(), $permissions->unique()->count(), 'There must be no duplicate permission names');
    }

    public function test_distinction_between_admin_dg_and_responsable_site(): void
    {
        $adminRole = Role::findByName('administrateur', 'web');
        $directionRole = Role::findByName('direction', 'web');
        $siteManagerRole = Role::findByName('responsable_site', 'web');

        // Admin: technical system settings, no automatic grades or payments
        $this->assertTrue($adminRole->hasPermissionTo('manage users'));
        $this->assertTrue($adminRole->hasPermissionTo('manage system settings'));
        $this->assertFalse($adminRole->hasPermissionTo('view global dashboard'));
        $this->assertFalse($adminRole->hasPermissionTo('view assigned sites'));

        // Direction: global overview and steering, no system technical settings
        $this->assertTrue($directionRole->hasPermissionTo('view global dashboard'));
        $this->assertTrue($directionRole->hasPermissionTo('view all sites'));
        $this->assertTrue($directionRole->hasPermissionTo('view finances'));
        $this->assertFalse($directionRole->hasPermissionTo('manage system settings'));
        $this->assertFalse($directionRole->hasPermissionTo('manage roles'));

        // Responsable de site: assigned sites only, no system admin or global permissions
        $this->assertTrue($siteManagerRole->hasPermissionTo('view assigned sites'));
        $this->assertTrue($siteManagerRole->hasPermissionTo('view site dashboard'));
        $this->assertFalse($siteManagerRole->hasPermissionTo('manage system settings'));
        $this->assertFalse($siteManagerRole->hasPermissionTo('view all sites'));
    }

    public function test_priority_profiles_insertion_and_stock_have_functional_permissions(): void
    {
        $agentInsertion = Role::findByName('agent_insertion_professionnelle', 'web');
        $respInsertion = Role::findByName('responsable_insertion_professionnelle', 'web');

        $this->assertTrue($agentInsertion->hasPermissionTo('view insertion dashboard'));
        $this->assertTrue($agentInsertion->hasPermissionTo('manage insertion followups'));
        $this->assertFalse($agentInsertion->hasPermissionTo('supervise insertion service'));

        $this->assertTrue($respInsertion->hasPermissionTo('manage insertion followups'));
        $this->assertTrue($respInsertion->hasPermissionTo('supervise insertion service'));
        $this->assertTrue($respInsertion->hasPermissionTo('validate insertion partnerships'));

        $agentStock = Role::findByName('agent_gestion_stocks', 'web');
        $respStock = Role::findByName('responsable_gestion_stocks', 'web');

        $this->assertTrue($agentStock->hasPermissionTo('view stock dashboard'));
        $this->assertTrue($agentStock->hasPermissionTo('record stock movements'));
        $this->assertFalse($agentStock->hasPermissionTo('supervise stock service'));

        $this->assertTrue($respStock->hasPermissionTo('record stock movements'));
        $this->assertTrue($respStock->hasPermissionTo('supervise stock service'));
        $this->assertTrue($respStock->hasPermissionTo('validate stock audits'));

        // Non-priority profiles only have consultation / supervision
        $agentMarketing = Role::findByName('agent_marketing', 'web');
        $this->assertTrue($agentMarketing->hasPermissionTo('view marketing dashboard'));
        $this->assertTrue($agentMarketing->hasPermissionTo('view marketing records'));
        $this->assertFalse($agentMarketing->hasPermissionTo('supervise marketing service'));
    }

    public function test_cumulative_roles_and_permissions(): void
    {
        $user = User::create([
            'first_name' => 'Amina',
            'last_name' => 'Cumul',
            'email' => 'amina.cumul@ifam.test',
            'password' => Hash::make('Secret123!'),
            'is_active' => true,
        ]);

        // Assign multiple roles: enseignant + responsable_pedagogie_etudes + responsable_site
        $user->assignRole(['enseignant', 'responsable_pedagogie_etudes', 'responsable_site']);

        $this->assertTrue($user->hasRole('enseignant'));
        $this->assertTrue($user->hasRole('responsable_pedagogie_etudes'));
        $this->assertTrue($user->hasRole('responsable_site'));

        // Permissions combined
        $this->assertTrue($user->hasPermissionTo('view assigned sites'));
        $this->assertTrue($user->hasPermissionTo('supervise pedagogie service'));
        $this->assertFalse($user->hasPermissionTo('manage system settings'));
    }

    public function test_perimeter_site_access_rules(): void
    {
        $cotonou = Site::where('nom', 'Cotonou')->firstOrFail();
        $portoNovo = Site::create(['nom' => 'Porto-Novo', 'ville' => 'Porto-Novo', 'est_actif' => true]);

        // 1. DG has access to any site
        $dg = User::create([
            'first_name' => 'DG',
            'last_name' => 'Global',
            'email' => 'dg-global@ifam.test',
            'password' => Hash::make('Secret123!'),
            'is_active' => true,
        ]);
        $dg->assignRole('direction');
        $this->assertTrue($dg->hasSiteAccess($cotonou));
        $this->assertTrue($dg->hasSiteAccess($portoNovo));

        // 2. Site Manager assigned only to Cotonou
        $siteManager = User::create([
            'first_name' => 'Manager',
            'last_name' => 'Cotonou',
            'email' => 'manager.cotonou@ifam.test',
            'password' => Hash::make('Secret123!'),
            'is_active' => true,
        ]);
        $siteManager->assignRole('responsable_site');
        $siteManager->sites()->attach($cotonou->id);

        $this->assertTrue($siteManager->hasSiteAccess($cotonou));
        $this->assertFalse($siteManager->hasSiteAccess($portoNovo));
        $this->assertTrue($siteManager->isSiteManager($cotonou->id));
        $this->assertFalse($siteManager->isSiteManager($portoNovo->id));
    }
}
