<?php

namespace Tests\Feature\Admin;

use App\Models\Department;
use App\Models\Room;
use App\Models\ServiceProfile;
use App\Models\Site;
use App\Models\User;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\SiteSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SuperAdminAndSiteAdminConfigTest extends TestCase
{
    use RefreshDatabase;

    protected Site $cotonou;

    protected Site $bohicon;

    protected User $superAdmin;

    protected User $siteAdminCotonou;

    protected User $siteAdminBohicon;

    protected User $studentCotonou;

    protected User $studentBohicon;

    protected Department $deptPedagogie;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(SiteSeeder::class);
        $this->seed(RoleSeeder::class);
        $this->seed(DepartmentSeeder::class);

        $this->cotonou = Site::where('nom', 'Cotonou')->firstOrFail();
        $this->bohicon = Site::firstOrCreate(
            ['nom' => 'Bohicon'],
            ['ville' => 'Bohicon', 'est_actif' => true]
        );

        $this->deptPedagogie = Department::where('code', 'affaires_academiques')->firstOrFail();

        // 1. SuperAdmin
        $this->superAdmin = User::create([
            'first_name' => 'Super',
            'last_name' => 'Admin',
            'email' => 'superadmin@ifam.test',
            'password' => Hash::make('Password123!'),
            'is_active' => true,
        ]);
        $this->superAdmin->assignRole('administrateur');

        // 2. Administrateur de site Cotonou
        $this->siteAdminCotonou = User::create([
            'first_name' => 'Admin',
            'last_name' => 'Cotonou',
            'email' => 'admin.cotonou@ifam.test',
            'password' => Hash::make('Password123!'),
            'is_active' => true,
        ]);
        $this->siteAdminCotonou->assignRole('responsable_site');
        $this->siteAdminCotonou->sites()->attach($this->cotonou->id);

        // 3. Administrateur de site Bohicon
        $this->siteAdminBohicon = User::create([
            'first_name' => 'Admin',
            'last_name' => 'Bohicon',
            'email' => 'admin.bohicon@ifam.test',
            'password' => Hash::make('Password123!'),
            'is_active' => true,
        ]);
        $this->siteAdminBohicon->assignRole('responsable_site');
        $this->siteAdminBohicon->sites()->attach($this->bohicon->id);

        // 4. Étudiant Cotonou
        $this->studentCotonou = User::create([
            'first_name' => 'Aïcha',
            'last_name' => 'Cotonou',
            'email' => 'aicha.cotonou@ifam.test',
            'password' => Hash::make('Password123!'),
            'is_active' => true,
        ]);
        $this->studentCotonou->assignRole('etudiant');
        $this->studentCotonou->sites()->attach($this->cotonou->id);

        // 5. Étudiant Bohicon
        $this->studentBohicon = User::create([
            'first_name' => 'Koffi',
            'last_name' => 'Bohicon',
            'email' => 'koffi.bohicon@ifam.test',
            'password' => Hash::make('Password123!'),
            'is_active' => true,
        ]);
        $this->studentBohicon->assignRole('etudiant');
        $this->studentBohicon->sites()->attach($this->bohicon->id);
    }

    // ── 1. Scénarios Utilisateurs & Sites ─────────────────────────────────────

    public function test_superadmin_sees_users_from_all_sites(): void
    {
        $response = $this->actingAs($this->superAdmin)->get(route('admin.users.index'));

        $response->assertOk();
        $response->assertSee($this->studentCotonou->full_name);
        $response->assertSee($this->studentBohicon->full_name);
    }

    public function test_superadmin_can_create_user_with_chosen_site(): void
    {
        $response = $this->actingAs($this->superAdmin)->post(route('admin.users.store'), [
            'first_name' => 'Nouvel',
            'last_name' => 'Inscrit',
            'email' => 'nouveau.bohicon@ifam.test',
            'role' => 'etudiant',
            'site_id' => $this->bohicon->id,
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $createdUser = User::where('email', 'nouveau.bohicon@ifam.test')->firstOrFail();

        $this->assertTrue($createdUser->sites->contains($this->bohicon->id));
        $this->assertFalse($createdUser->sites->contains($this->cotonou->id));
    }

    public function test_site_admin_only_sees_users_of_their_site(): void
    {
        $response = $this->actingAs($this->siteAdminCotonou)->get(route('admin.users.index'));

        $response->assertOk();
        $response->assertSee($this->studentCotonou->full_name);
        $response->assertDontSee($this->studentBohicon->full_name);
    }

    public function test_site_admin_cannot_view_or_edit_user_of_another_site(): void
    {
        $this->actingAs($this->siteAdminCotonou)
            ->get(route('admin.users.show', $this->studentBohicon))
            ->assertForbidden();

        $this->actingAs($this->siteAdminCotonou)
            ->get(route('admin.users.edit', $this->studentBohicon))
            ->assertForbidden();
    }

    public function test_site_admin_can_only_create_user_for_their_site_even_with_manipulated_payload(): void
    {
        $response = $this->actingAs($this->siteAdminCotonou)->post(route('admin.users.store'), [
            'first_name' => 'Tentative',
            'last_name' => 'Détournement',
            'email' => 'tentative@ifam.test',
            'role' => 'etudiant',
            'site_id' => $this->bohicon->id, // payload falsifié
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $createdUser = User::where('email', 'tentative@ifam.test')->firstOrFail();

        // Le serveur force le site de l'administrateur de site (Cotonou)
        $this->assertTrue($createdUser->sites->contains($this->cotonou->id));
        $this->assertFalse($createdUser->sites->contains($this->bohicon->id));
    }

    public function test_site_admin_cannot_change_site_of_user(): void
    {
        $this->actingAs($this->siteAdminCotonou)->put(route('admin.users.update', $this->studentCotonou), [
            'first_name' => 'Aïcha',
            'last_name' => 'Modifiée',
            'site_id' => $this->bohicon->id,
        ]);

        $this->studentCotonou->refresh();
        $this->assertEquals('Modifiée', $this->studentCotonou->last_name);
        $this->assertTrue($this->studentCotonou->sites->contains($this->cotonou->id));
        $this->assertFalse($this->studentCotonou->sites->contains($this->bohicon->id));
    }

    // ── 2. Rôles et Permissions ──────────────────────────────────────────────

    public function test_superadmin_can_list_and_create_roles(): void
    {
        $response = $this->actingAs($this->superAdmin)->get(route('admin.roles.index'));
        $response->assertOk();
        $response->assertSee('administrateur');

        $searchResponse = $this->actingAs($this->superAdmin)->get(route('admin.roles.index', ['search' => 'responsable_site']));
        $searchResponse->assertOk();
        $searchResponse->assertSee('responsable_site');

        $createResponse = $this->actingAs($this->superAdmin)->post(route('admin.roles.store'), [
            'name' => 'superviseur_stage',
            'permissions' => ['view insertion dashboard'],
        ]);

        $createResponse->assertRedirect(route('admin.roles.index'));
        $this->assertTrue(Role::where('name', 'superviseur_stage')->exists());
    }

    public function test_site_admin_cannot_access_roles_and_permissions(): void
    {
        $this->actingAs($this->siteAdminCotonou)
            ->get(route('admin.roles.index'))
            ->assertForbidden();

        $this->actingAs($this->siteAdminCotonou)
            ->post(route('admin.roles.store'), ['name' => 'hacker_role'])
            ->assertForbidden();
    }

    // ── 3. Départements ──────────────────────────────────────────────────────

    public function test_superadmin_can_manage_departments(): void
    {
        $response = $this->actingAs($this->superAdmin)->get(route('admin.departments.index'));
        $response->assertOk();
        $response->assertSee('Département des Affaires Académiques');

        $createResponse = $this->actingAs($this->superAdmin)->post(route('admin.departments.store'), [
            'name' => 'Département Recherche & Innovation',
            'code' => 'recherche_innovation',
            'description' => 'Coordination des projets doctoraux et publications.',
            'is_active' => true,
        ]);

        $createResponse->assertRedirect(route('admin.departments.index'));
        $this->assertTrue(Department::where('code', 'recherche_innovation')->exists());
    }

    public function test_site_admin_cannot_access_departments(): void
    {
        $this->actingAs($this->siteAdminCotonou)
            ->get(route('admin.departments.index'))
            ->assertForbidden();

        $this->actingAs($this->siteAdminCotonou)
            ->post(route('admin.departments.store'), ['name' => 'Dept Interdit'])
            ->assertForbidden();
    }

    // ── 4. Services ──────────────────────────────────────────────────────────

    public function test_superadmin_can_manage_services_with_mandatory_department(): void
    {
        $response = $this->actingAs($this->superAdmin)->get(route('admin.services.index'));
        $response->assertOk();
        $response->assertSee('Pédagogie / Études');

        $createResponse = $this->actingAs($this->superAdmin)->post(route('admin.services.store'), [
            'name' => 'Service E-learning & MOOC',
            'code' => 'elearning_mooc',
            'department_id' => $this->deptPedagogie->id,
            'description' => 'Gestion de la plateforme en ligne',
            'is_active' => true,
        ]);

        $createResponse->assertRedirect(route('admin.services.index'));
        $newService = ServiceProfile::where('code', 'elearning_mooc')->firstOrFail();
        $this->assertEquals($this->deptPedagogie->id, $newService->department_id);
    }

    public function test_service_creation_fails_without_valid_department(): void
    {
        $response = $this->actingAs($this->superAdmin)->post(route('admin.services.store'), [
            'name' => 'Service Orphelin',
            'code' => 'orphelin',
            'department_id' => 99999, // Inexistant
        ]);

        $response->assertSessionHasErrors('department_id');
    }

    public function test_site_admin_cannot_access_services(): void
    {
        $this->actingAs($this->siteAdminCotonou)
            ->get(route('admin.services.index'))
            ->assertForbidden();
    }

    // ── 5. Sites IFAM ────────────────────────────────────────────────────────

    public function test_site_admin_cannot_access_sites_management(): void
    {
        $this->actingAs($this->siteAdminCotonou)
            ->get(route('admin.sites.index'))
            ->assertForbidden();

        $this->actingAs($this->siteAdminCotonou)
            ->get(route('admin.sites.create'))
            ->assertForbidden();
    }

    // ── 6. Salles ────────────────────────────────────────────────────────────

    public function test_rooms_remain_scoped_by_site(): void
    {
        $cotonouRoom = Room::create([
            'name' => 'Salle Cotonou Amphi',
            'capacity' => 100,
            'is_active' => true,
            'site_id' => $this->cotonou->id,
        ]);

        $bohiconRoom = Room::create([
            'name' => 'Salle Bohicon Lab',
            'capacity' => 30,
            'is_active' => true,
            'site_id' => $this->bohicon->id,
        ]);

        $responseCotonou = $this->actingAs($this->siteAdminCotonou)->get(route('admin.rooms.index'));
        $responseCotonou->assertOk();
        $responseCotonou->assertSee('Salle Cotonou Amphi');
        $responseCotonou->assertDontSee('Salle Bohicon Lab');

        // Accès direct refusé
        $this->actingAs($this->siteAdminCotonou)
            ->get(route('admin.rooms.edit', $bohiconRoom))
            ->assertForbidden();
    }

    // ── 7. Sidebar différenciée ──────────────────────────────────────────────

    public function test_sidebar_displays_restricted_menu_for_site_admin(): void
    {
        $response = $this->actingAs($this->siteAdminCotonou)->get(route('admin.rooms.index'));

        $response->assertOk();
        // Visible pour site admin
        $response->assertSee('Utilisateurs de mon site');
        $response->assertSee('Salles du site');
        $response->assertSee('Périmètre Local (Cotonou)');

        // Masqué pour site admin
        $response->assertDontSee('Sites IFAM');
        $response->assertDontSee('Rôles &amp; Permissions', false);
        $response->assertDontSee('Départements');
        $response->assertDontSee('Frais de scolarité');
    }

    public function test_sidebar_displays_full_administration_menu_for_superadmin(): void
    {
        $response = $this->actingAs($this->superAdmin)->get(route('admin.departments.index'));

        $response->assertOk();
        $response->assertSee('Administration Globale');
        $response->assertSee('Sites IFAM');
        $response->assertSee('Rôles &amp; Permissions', false);
        $response->assertSee('Départements');
        $response->assertSee('Services');
        $response->assertSee('Salles');
    }
}
