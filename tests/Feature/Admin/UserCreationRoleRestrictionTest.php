<?php

namespace Tests\Feature\Admin;

use App\Models\Site;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Database\Seeders\SiteSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserCreationRoleRestrictionTest extends TestCase
{
    use RefreshDatabase;

    protected Site $cotonou;

    protected Site $bohicon;

    protected User $superAdmin;

    protected User $cotonouAdmin;

    protected User $commercial;

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

        // SuperAdmin
        $this->superAdmin = User::create([
            'first_name' => 'Super',
            'last_name' => 'Admin',
            'email' => 'superadmin@ifam.test',
            'password' => Hash::make('Password123!'),
            'is_active' => true,
        ]);
        $this->superAdmin->assignRole('administrateur');

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

        // Responsable Commercial
        $this->commercial = User::create([
            'first_name' => 'Resp',
            'last_name' => 'Commercial',
            'email' => 'commercial@ifam.test',
            'password' => Hash::make('Password123!'),
            'is_active' => true,
            'must_change_password' => false,
        ]);
        $this->commercial->assignRole('responsable_commercial');
        $this->commercial->sites()->attach($this->cotonou->id);
    }

    public function test_superadmin_can_create_user_with_privileged_role(): void
    {
        $response = $this->actingAs($this->superAdmin)->post(route('admin.users.store'), [
            'first_name' => 'Directeur',
            'last_name' => 'DAA',
            'email' => 'dir.daa@ifam.test',
            'role' => 'direction_daa',
            'site_id' => $this->cotonou->id,
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $user = User::where('email', 'dir.daa@ifam.test')->firstOrFail();
        $this->assertTrue($user->hasRole('direction_daa'));
    }

    public function test_site_manager_can_create_student_teacher_and_parent(): void
    {
        // Étudiant
        $res1 = $this->actingAs($this->cotonouAdmin)->post(route('admin.users.store'), [
            'first_name' => 'Etudiant',
            'last_name' => 'Test',
            'email' => 'etudiant.local@ifam.test',
            'role' => 'etudiant',
        ]);
        $res1->assertRedirect(route('admin.users.index'));
        $student = User::where('email', 'etudiant.local@ifam.test')->firstOrFail();
        $this->assertTrue($student->hasRole('etudiant'));
        $this->assertTrue($student->sites->contains($this->cotonou->id));

        // Enseignant
        $res2 = $this->actingAs($this->cotonouAdmin)->post(route('admin.users.store'), [
            'first_name' => 'Enseignant',
            'last_name' => 'Test',
            'email' => 'prof.local@ifam.test',
            'role' => 'enseignant',
        ]);
        $res2->assertRedirect(route('admin.users.index'));
        $teacher = User::where('email', 'prof.local@ifam.test')->firstOrFail();
        $this->assertTrue($teacher->hasRole('enseignant'));

        // Parent
        $res3 = $this->actingAs($this->cotonouAdmin)->post(route('admin.users.store'), [
            'first_name' => 'Parent',
            'last_name' => 'Test',
            'email' => 'parent.local@ifam.test',
            'role' => 'parent',
        ]);
        $res3->assertRedirect(route('admin.users.index'));
        $parent = User::where('email', 'parent.local@ifam.test')->firstOrFail();
        $this->assertTrue($parent->hasRole('parent'));
    }

    public function test_site_manager_cannot_create_administrateur(): void
    {
        $response = $this->actingAs($this->cotonouAdmin)->post(route('admin.users.store'), [
            'first_name' => 'Faux',
            'last_name' => 'Admin',
            'email' => 'fake.admin@ifam.test',
            'role' => 'administrateur',
        ]);

        $response->assertSessionHasErrors('role');
        $this->assertDatabaseMissing('users', ['email' => 'fake.admin@ifam.test']);
    }

    public function test_site_manager_cannot_create_direction_role(): void
    {
        $response = $this->actingAs($this->cotonouAdmin)->post(route('admin.users.store'), [
            'first_name' => 'Fausse',
            'last_name' => 'Direction',
            'email' => 'fake.dir@ifam.test',
            'role' => 'direction_daa',
        ]);

        $response->assertSessionHasErrors('role');
        $this->assertDatabaseMissing('users', ['email' => 'fake.dir@ifam.test']);
    }

    public function test_site_manager_cannot_create_responsable_commercial(): void
    {
        $response = $this->actingAs($this->cotonouAdmin)->post(route('admin.users.store'), [
            'first_name' => 'Faux',
            'last_name' => 'Commercial',
            'email' => 'fake.commercial@ifam.test',
            'role' => 'responsable_commercial',
        ]);

        $response->assertSessionHasErrors('role');
        $this->assertDatabaseMissing('users', ['email' => 'fake.commercial@ifam.test']);
    }

    public function test_site_manager_creation_enforces_own_site_server_side_even_if_tampered(): void
    {
        $response = $this->actingAs($this->cotonouAdmin)->post(route('admin.users.store'), [
            'first_name' => 'Piratage',
            'last_name' => 'Site',
            'email' => 'pirate@ifam.test',
            'role' => 'etudiant',
            'site_id' => $this->bohicon->id, // Tentative de créer sur Bohicon
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $created = User::where('email', 'pirate@ifam.test')->firstOrFail();
        $this->assertTrue($created->sites->contains($this->cotonou->id));
        $this->assertFalse($created->sites->contains($this->bohicon->id));
    }

    public function test_responsable_commercial_can_only_create_etudiant(): void
    {
        // Création étudiant autorisée
        $response = $this->actingAs($this->commercial)->post(route('admin.users.store'), [
            'first_name' => 'Etudiant',
            'last_name' => 'Inscrit',
            'email' => 'etudiant.commercial@ifam.test',
            'role' => 'etudiant',
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $user = User::where('email', 'etudiant.commercial@ifam.test')->firstOrFail();
        $this->assertTrue($user->hasRole('etudiant'));
    }

    public function test_responsable_commercial_cannot_create_enseignant_or_parent(): void
    {
        // Tentative enseignant
        $res1 = $this->actingAs($this->commercial)->post(route('admin.users.store'), [
            'first_name' => 'Enseignant',
            'last_name' => 'Refuse',
            'email' => 'prof.refuse@ifam.test',
            'role' => 'enseignant',
        ]);
        $res1->assertSessionHasErrors('role');
        $this->assertDatabaseMissing('users', ['email' => 'prof.refuse@ifam.test']);

        // Tentative parent
        $res2 = $this->actingAs($this->commercial)->post(route('admin.users.store'), [
            'first_name' => 'Parent',
            'last_name' => 'Refuse',
            'email' => 'parent.refuse@ifam.test',
            'role' => 'parent',
        ]);
        $res2->assertSessionHasErrors('role');
        $this->assertDatabaseMissing('users', ['email' => 'parent.refuse@ifam.test']);
    }

    public function test_responsable_commercial_cannot_create_administrateur(): void
    {
        $response = $this->actingAs($this->commercial)->post(route('admin.users.store'), [
            'first_name' => 'Admin',
            'last_name' => 'Refuse',
            'email' => 'admin.refuse@ifam.test',
            'role' => 'administrateur',
        ]);

        $response->assertSessionHasErrors('role');
        $this->assertDatabaseMissing('users', ['email' => 'admin.refuse@ifam.test']);
    }

    public function test_create_form_only_displays_allowed_roles_in_dropdown(): void
    {
        // Commercial voit seulement 'etudiant'
        $resCommercial = $this->actingAs($this->commercial)->get(route('admin.users.create'));
        $resCommercial->assertOk();
        $resCommercial->assertSee('value="etudiant"', false);
        $resCommercial->assertDontSee('value="administrateur"', false);
        $resCommercial->assertDontSee('value="enseignant"', false);

        // Responsable de site voit etudiant, enseignant, parent mais pas administrateur ni direction
        $resSiteAdmin = $this->actingAs($this->cotonouAdmin)->get(route('admin.users.create'));
        $resSiteAdmin->assertOk();
        $resSiteAdmin->assertSee('value="etudiant"', false);
        $resSiteAdmin->assertSee('value="enseignant"', false);
        $resSiteAdmin->assertSee('value="parent"', false);
        $resSiteAdmin->assertDontSee('value="administrateur"', false);
        $resSiteAdmin->assertDontSee('value="direction_daa"', false);
    }

    public function test_responsable_commercial_login_redirects_to_dashboard(): void
    {
        $response = $this->post(route('login'), [
            'email' => $this->commercial->email,
            'password' => 'Password123!',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($this->commercial);
    }
}
