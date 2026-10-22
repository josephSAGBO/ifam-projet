<?php

namespace Tests\Feature\Admin;

use App\Models\AcademicSession;
use App\Models\Site;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Database\Seeders\SiteSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SiteControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $student;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
        $this->seed(SiteSeeder::class);

        $this->admin = User::create([
            'first_name' => 'Admin',
            'last_name' => 'Test',
            'email' => 'admin.sites@ifam.test',
            'password' => Hash::make('Password123!'),
            'is_active' => true,
        ]);
        $this->admin->assignRole('administrateur');

        $this->student = User::create([
            'first_name' => 'Etudiant',
            'last_name' => 'Test',
            'email' => 'etudiant.sites@ifam.test',
            'password' => Hash::make('Password123!'),
            'is_active' => true,
        ]);
        $this->student->assignRole('etudiant');
    }

    public function test_guests_and_non_admins_cannot_access_sites_management(): void
    {
        $response = $this->get(route('admin.sites.index'));
        $response->assertRedirect(route('login'));

        $response = $this->actingAs($this->student)->get(route('admin.sites.index'));
        $response->assertForbidden();
    }

    public function test_admin_can_view_sites_list(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.sites.index'));

        $response->assertOk();
        $response->assertViewIs('admin.sites.index');
        $response->assertSee('Cotonou');
    }

    public function test_admin_can_create_a_new_site(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.sites.store'), [
            'nom' => 'Bohicon',
            'ville' => 'Bohicon',
            'adresse' => 'Quartier Passagon',
            'telephone' => '+229 97 00 00 02',
            'email' => 'bohicon@ifam.bj',
            'est_actif' => '1',
        ]);

        $response->assertRedirect(route('admin.sites.index'));
        $this->assertDatabaseHas('sites', [
            'nom' => 'Bohicon',
            'ville' => 'Bohicon',
            'est_actif' => true,
        ]);
    }

    public function test_admin_can_toggle_site_active_status(): void
    {
        $site = Site::where('nom', 'Cotonou')->firstOrFail();
        $this->assertTrue($site->est_actif);

        $response = $this->actingAs($this->admin)->patch(route('admin.sites.toggle-active', $site));

        $response->assertRedirect(route('admin.sites.index'));
        $this->assertFalse($site->fresh()->est_actif);

        // Toggle back to active
        $this->actingAs($this->admin)->patch(route('admin.sites.toggle-active', $site));
        $this->assertTrue($site->fresh()->est_actif);
    }

    public function test_admin_can_delete_site_without_data(): void
    {
        $site = Site::create([
            'nom' => 'Site Vide',
            'ville' => 'Ouidah',
            'est_actif' => true,
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.sites.destroy', $site));

        $response->assertRedirect(route('admin.sites.index'));
        $this->assertDatabaseMissing('sites', ['id' => $site->id]);
    }

    public function test_admin_cannot_delete_site_with_associated_data(): void
    {
        $site = Site::where('nom', 'Cotonou')->firstOrFail();

        AcademicSession::create([
            'name' => 'Session 2026-2027',
            'site_id' => $site->id,
            'start_date' => '2026-10-01',
            'end_date' => '2027-06-30',
            'status' => 'en_cours',
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.sites.destroy', $site));

        $response->assertRedirect(route('admin.sites.index'));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('sites', ['id' => $site->id]);
    }

    public function test_site_manager_cannot_access_sites_management_route_and_gets_403(): void
    {
        $siteManager = User::create([
            'first_name' => 'Manager',
            'last_name' => 'Site',
            'email' => 'manager.sites@ifam.test',
            'password' => Hash::make('Password123!'),
            'is_active' => true,
        ]);
        $siteManager->assignRole('responsable_site');

        $response = $this->actingAs($siteManager)->get(route('admin.sites.index'));
        $response->assertForbidden();
    }

    public function test_sidebar_displays_sites_link_only_for_admin(): void
    {
        $site = Site::where('nom', 'Cotonou')->firstOrFail();

        $siteManager = User::create([
            'first_name' => 'Manager',
            'last_name' => 'Site',
            'email' => 'manager.sidebar@ifam.test',
            'password' => Hash::make('Password123!'),
            'is_active' => true,
        ]);
        $siteManager->assignRole('responsable_site');
        $siteManager->sites()->attach($site->id);

        // 1. Administrateur voit le lien Sites IFAM et Mon profil
        $adminResponse = $this->actingAs($this->admin)->get(route('admin.users.index'));
        $adminResponse->assertOk();
        $adminResponse->assertSee('Sites IFAM');
        $adminResponse->assertSee(route('admin.sites.index'));
        $adminResponse->assertSee('Mon profil');
        $adminResponse->assertSee(route('admin.settings.index'));

        // 2. Responsable de site ne voit pas le lien Sites IFAM mais voit Mon profil
        $managerResponse = $this->actingAs($siteManager)->get(route('admin.users.index'));
        $managerResponse->assertOk();
        $managerResponse->assertDontSee('Sites IFAM');
        $managerResponse->assertDontSee(route('admin.sites.index'));
        $managerResponse->assertSee('Mon profil');
        $managerResponse->assertSee(route('admin.settings.index'));
    }
}
