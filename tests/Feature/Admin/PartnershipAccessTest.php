<?php

namespace Tests\Feature\Admin;

use App\Models\Partnership;
use App\Models\Site;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Database\Seeders\SiteSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PartnershipAccessTest extends TestCase
{
    use RefreshDatabase;

    protected Site $cotonou;

    protected User $superAdmin;

    protected User $directionDaa;

    protected User $directionDaf;

    protected User $siteManager;

    protected User $commercial;

    protected User $teacher;

    protected Partnership $partnership;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(SiteSeeder::class);
        $this->seed(RoleSeeder::class);

        $this->cotonou = Site::where('nom', 'Cotonou')->firstOrFail();

        $this->superAdmin = User::create([
            'first_name' => 'Super',
            'last_name' => 'Admin',
            'email' => 'superadmin@ifam.test',
            'password' => Hash::make('Password123!'),
            'is_active' => true,
        ]);
        $this->superAdmin->assignRole('administrateur');

        $this->directionDaa = User::create([
            'first_name' => 'Dir',
            'last_name' => 'DAA',
            'email' => 'daa@ifam.test',
            'password' => Hash::make('Password123!'),
            'is_active' => true,
        ]);
        $this->directionDaa->assignRole('direction_daa');

        $this->directionDaf = User::create([
            'first_name' => 'Dir',
            'last_name' => 'DAF',
            'email' => 'daf@ifam.test',
            'password' => Hash::make('Password123!'),
            'is_active' => true,
        ]);
        $this->directionDaf->assignRole('direction_daf');

        $this->siteManager = User::create([
            'first_name' => 'Resp',
            'last_name' => 'Site',
            'email' => 'respsite@ifam.test',
            'password' => Hash::make('Password123!'),
            'is_active' => true,
        ]);
        $this->siteManager->assignRole('responsable_site');
        $this->siteManager->sites()->attach($this->cotonou->id);

        $this->commercial = User::create([
            'first_name' => 'Resp',
            'last_name' => 'Commercial',
            'email' => 'commercial@ifam.test',
            'password' => Hash::make('Password123!'),
            'is_active' => true,
        ]);
        $this->commercial->assignRole('responsable_commercial');

        $this->teacher = User::create([
            'first_name' => 'Prof',
            'last_name' => 'Test',
            'email' => 'teacher@ifam.test',
            'password' => Hash::make('Password123!'),
            'is_active' => true,
        ]);
        $this->teacher->assignRole('enseignant');

        $this->partnership = Partnership::create([
            'nom' => 'Partenaire Test',
            'prenom' => 'Contact Partenaire',
            'type_partenariat' => 'Stage',
            'statut' => 'Actif',
        ]);
    }

    public function test_direction_daa_and_daf_and_admin_can_access_partnerships(): void
    {
        $this->actingAs($this->directionDaa)->get(route('admin.partnerships.index'))->assertOk();
        $this->actingAs($this->directionDaf)->get(route('admin.partnerships.index'))->assertOk();
        $this->actingAs($this->superAdmin)->get(route('admin.partnerships.index'))->assertOk();
    }

    public function test_responsable_site_cannot_access_partnerships(): void
    {
        $this->actingAs($this->siteManager)->get(route('admin.partnerships.index'))->assertForbidden();
        $this->actingAs($this->siteManager)->get(route('admin.partnerships.create'))->assertForbidden();
        $this->actingAs($this->siteManager)->get(route('admin.partnerships.show', $this->partnership))->assertForbidden();
        $this->actingAs($this->siteManager)->get(route('admin.partnerships.edit', $this->partnership))->assertForbidden();
        $this->actingAs($this->siteManager)->delete(route('admin.partnerships.destroy', $this->partnership))->assertForbidden();
    }

    public function test_responsable_commercial_cannot_access_partnerships(): void
    {
        $this->actingAs($this->commercial)->get(route('admin.partnerships.index'))->assertForbidden();
    }

    public function test_other_roles_cannot_access_partnerships(): void
    {
        $this->actingAs($this->teacher)->get(route('admin.partnerships.index'))->assertForbidden();
    }
}
