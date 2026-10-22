<?php

namespace Tests\Feature\Admin;

use App\Models\Room;
use App\Models\Site;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Database\Seeders\SiteSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserAndRoomSiteSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected Site $cotonou;

    protected Site $bohicon;

    protected User $superAdmin;

    protected User $cotonouAdmin;

    protected User $bohiconAdmin;

    protected User $dgUser;

    protected User $cotonouStudent;

    protected User $bohiconStudent;

    protected Room $cotonouRoom;

    protected Room $bohiconRoom;

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

        // 1. SuperAdmin
        $this->superAdmin = User::create([
            'first_name' => 'Super',
            'last_name' => 'Admin',
            'email' => 'superadmin@ifam.test',
            'password' => Hash::make('Password123!'),
            'is_active' => true,
        ]);
        $this->superAdmin->assignRole('administrateur');

        // 2. Administrateur Cotonou
        $this->cotonouAdmin = User::create([
            'first_name' => 'Admin',
            'last_name' => 'Cotonou',
            'email' => 'admin.cotonou@ifam.test',
            'password' => Hash::make('Password123!'),
            'is_active' => true,
        ]);
        $this->cotonouAdmin->assignRole('responsable_site');
        $this->cotonouAdmin->sites()->attach($this->cotonou->id);

        // 3. Administrateur Bohicon
        $this->bohiconAdmin = User::create([
            'first_name' => 'Admin',
            'last_name' => 'Bohicon',
            'email' => 'admin.bohicon@ifam.test',
            'password' => Hash::make('Password123!'),
            'is_active' => true,
        ]);
        $this->bohiconAdmin->assignRole('responsable_site');
        $this->bohiconAdmin->sites()->attach($this->bohicon->id);

        // 4. Direction Générale (compte transversal)
        $this->dgUser = User::create([
            'first_name' => 'Directeur',
            'last_name' => 'Général',
            'email' => 'dg@ifam.test',
            'password' => Hash::make('Password123!'),
            'is_active' => true,
        ]);
        $this->dgUser->assignRole('direction');

        // 5. Étudiant Cotonou
        $this->cotonouStudent = User::create([
            'first_name' => 'Etudiant',
            'last_name' => 'Cotonou',
            'email' => 'student.cotonou@ifam.test',
            'password' => Hash::make('Password123!'),
            'is_active' => true,
        ]);
        $this->cotonouStudent->assignRole('etudiant');
        $this->cotonouStudent->sites()->attach($this->cotonou->id);

        // 6. Étudiant Bohicon
        $this->bohiconStudent = User::create([
            'first_name' => 'Etudiant',
            'last_name' => 'Bohicon',
            'email' => 'student.bohicon@ifam.test',
            'password' => Hash::make('Password123!'),
            'is_active' => true,
        ]);
        $this->bohiconStudent->assignRole('etudiant');
        $this->bohiconStudent->sites()->attach($this->bohicon->id);

        // 7. Salles
        $this->cotonouRoom = Room::create([
            'name' => 'Salle Cotonou 1',
            'capacity' => 40,
            'is_active' => true,
            'site_id' => $this->cotonou->id,
        ]);

        $this->bohiconRoom = Room::create([
            'name' => 'Salle Bohicon 1',
            'capacity' => 35,
            'is_active' => true,
            'site_id' => $this->bohicon->id,
        ]);
    }

    // ── 1. SuperAdmin tests ──────────────────────────────────────────────────

    public function test_superadmin_sees_users_from_all_sites(): void
    {
        $response = $this->actingAs($this->superAdmin)->get(route('admin.users.index'));

        $response->assertOk();
        $response->assertSee($this->cotonouStudent->full_name);
        $response->assertSee($this->bohiconStudent->full_name);
    }

    public function test_superadmin_can_create_user_with_explicit_site_selection(): void
    {
        $response = $this->actingAs($this->superAdmin)->post(route('admin.users.store'), [
            'first_name' => 'Nouveau',
            'last_name' => 'Bohiconnais',
            'email' => 'new.bohicon@ifam.test',
            'role' => 'etudiant',
            'site_id' => $this->bohicon->id,
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $newUser = User::where('email', 'new.bohicon@ifam.test')->firstOrFail();
        $this->assertTrue($newUser->sites->contains($this->bohicon->id));
        $this->assertFalse($newUser->sites->contains($this->cotonou->id));
    }

    // ── 2. Site Manager user scoping tests ───────────────────────────────────

    public function test_cotonou_admin_only_sees_cotonou_users_not_bohicon(): void
    {
        $response = $this->actingAs($this->cotonouAdmin)->get(route('admin.users.index'));

        $response->assertOk();
        $response->assertSee($this->cotonouStudent->full_name);
        $response->assertDontSee($this->bohiconStudent->full_name);
    }

    public function test_cotonou_admin_cannot_create_user_at_bohicon_even_with_manipulated_request(): void
    {
        // Falsifying request: sending site_id of Bohicon
        $response = $this->actingAs($this->cotonouAdmin)->post(route('admin.users.store'), [
            'first_name' => 'Piratage',
            'last_name' => 'Site',
            'email' => 'falsified@ifam.test',
            'role' => 'etudiant',
            'site_id' => $this->bohicon->id,
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $createdUser = User::where('email', 'falsified@ifam.test')->firstOrFail();

        // Must be automatically and strictly assigned to Cotonou
        $this->assertTrue($createdUser->sites->contains($this->cotonou->id));
        $this->assertFalse($createdUser->sites->contains($this->bohicon->id));
    }

    public function test_cotonou_admin_cannot_show_or_edit_bohicon_user(): void
    {
        // Direct URL access to Bohicon student
        $responseShow = $this->actingAs($this->cotonouAdmin)->get(route('admin.users.show', $this->bohiconStudent));
        $responseShow->assertForbidden();

        $responseEdit = $this->actingAs($this->cotonouAdmin)->get(route('admin.users.edit', $this->bohiconStudent));
        $responseEdit->assertForbidden();

        $responseUpdate = $this->actingAs($this->cotonouAdmin)->put(route('admin.users.update', $this->bohiconStudent), [
            'first_name' => 'Tentative',
            'last_name' => 'Modif',
        ]);
        $responseUpdate->assertForbidden();
    }

    public function test_cotonou_admin_cannot_deactivate_bohicon_user(): void
    {
        $response = $this->actingAs($this->cotonouAdmin)->delete(route('admin.users.destroy', $this->bohiconStudent));
        $response->assertForbidden();
        $this->assertTrue($this->bohiconStudent->fresh()->is_active);
    }

    // ── 3. Transversal Direction accounts tests ──────────────────────────────

    public function test_transversal_direction_accounts_not_blocked_or_tampered_by_site_admin(): void
    {
        // Cotonou admin cannot modify DG transversal account
        $response = $this->actingAs($this->cotonouAdmin)->get(route('admin.users.edit', $this->dgUser));
        $response->assertForbidden();

        // DG has global access via hasSiteAccess
        $this->assertTrue($this->dgUser->hasSiteAccess($this->cotonou));
        $this->assertTrue($this->dgUser->hasSiteAccess($this->bohicon));
    }

    // ── 4. Rooms security tests ──────────────────────────────────────────────

    public function test_superadmin_sees_rooms_from_all_sites(): void
    {
        $response = $this->actingAs($this->superAdmin)->get(route('admin.rooms.index'));

        $response->assertOk();
        $response->assertSee($this->cotonouRoom->name);
        $response->assertSee($this->bohiconRoom->name);
    }

    public function test_cotonou_admin_only_sees_cotonou_rooms(): void
    {
        $response = $this->actingAs($this->cotonouAdmin)->get(route('admin.rooms.index'));

        $response->assertOk();
        $response->assertSee($this->cotonouRoom->name);
        $response->assertDontSee($this->bohiconRoom->name);
    }

    public function test_cotonou_admin_cannot_create_room_at_bohicon_even_with_manipulated_request(): void
    {
        $response = $this->actingAs($this->cotonouAdmin)->post(route('admin.rooms.store'), [
            'name' => 'Salle Forcée',
            'capacity' => 25,
            'site_id' => $this->bohicon->id,
        ]);

        $response->assertRedirect(route('admin.rooms.index'));
        $createdRoom = Room::where('name', 'Salle Forcée')->firstOrFail();

        $this->assertSame($this->cotonou->id, $createdRoom->site_id);
    }

    public function test_cotonou_admin_cannot_edit_or_delete_bohicon_room(): void
    {
        $responseEdit = $this->actingAs($this->cotonouAdmin)->get(route('admin.rooms.edit', $this->bohiconRoom));
        $responseEdit->assertForbidden();

        $responseUpdate = $this->actingAs($this->cotonouAdmin)->put(route('admin.rooms.update', $this->bohiconRoom), [
            'name' => 'Piratage Salle',
            'capacity' => 50,
        ]);
        $responseUpdate->assertForbidden();

        $responseDelete = $this->actingAs($this->cotonouAdmin)->delete(route('admin.rooms.destroy', $this->bohiconRoom));
        $responseDelete->assertForbidden();
        $this->assertDatabaseHas('rooms', ['id' => $this->bohiconRoom->id]);
    }

    public function test_admin_can_create_room_with_type_and_capacity_persisted_in_database(): void
    {
        $response = $this->actingAs($this->superAdmin)->post(route('admin.rooms.store'), [
            'name' => 'Labo Info 1',
            'site_id' => $this->cotonou->id,
            'capacity' => 45,
            'type' => 'Informatique',
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('admin.rooms.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('rooms', [
            'name' => 'Labo Info 1',
            'site_id' => $this->cotonou->id,
            'capacity' => 45,
            'type' => 'Informatique',
        ]);

        // Vérifier l'affichage dans la liste
        $listResponse = $this->actingAs($this->superAdmin)->get(route('admin.rooms.index'));
        $listResponse->assertOk();
        $listResponse->assertSee('Labo Info 1');
        $listResponse->assertSee('Informatique');
        $listResponse->assertSee('45 places');
    }

    public function test_admin_can_update_room_type_and_capacity(): void
    {
        $response = $this->actingAs($this->superAdmin)->put(route('admin.rooms.update', $this->cotonouRoom), [
            'name' => 'Salle Cotonou Modifiée',
            'site_id' => $this->cotonou->id,
            'capacity' => 60,
            'type' => 'Atelier',
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('admin.rooms.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('rooms', [
            'id' => $this->cotonouRoom->id,
            'name' => 'Salle Cotonou Modifiée',
            'capacity' => 60,
            'type' => 'Atelier',
        ]);

        // Vérifier l'affichage dans la vue de modification
        $editResponse = $this->actingAs($this->superAdmin)->get(route('admin.rooms.edit', $this->cotonouRoom));
        $editResponse->assertOk();
        $editResponse->assertSee('value="60"', false);
        $editResponse->assertSee('value="Atelier" selected', false);
    }

    public function test_historical_room_with_unknown_capacity_shows_dash_and_not_default_30(): void
    {
        $historicalRoom = Room::create([
            'name' => 'Salle Historique Inconnue',
            'site_id' => $this->cotonou->id,
            'capacity' => null,
            'type' => null,
            'is_active' => true,
        ]);

        $this->assertNull($historicalRoom->capacity);

        $response = $this->actingAs($this->superAdmin)->get(route('admin.rooms.index'));
        $response->assertOk();
        $response->assertSee('Salle Historique Inconnue');
        $response->assertSee('—');
    }
}
