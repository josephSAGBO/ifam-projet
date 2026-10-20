<?php

namespace Tests\Feature;

use App\Models\Formation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminFormationDescriptionTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_update_a_formation_description(): void
    {
        Role::create(['name' => 'administrateur', 'guard_name' => 'web']);
        $admin = User::create([
            'first_name' => 'Admin',
            'last_name' => 'IFAM',
            'email' => 'admin-formation-description@ifam.test',
            'password' => Hash::make('Secret123!'),
            'is_active' => true,
        ]);
        $admin->assignRole('administrateur');
        $formation = Formation::create([
            'name' => 'Formation à modifier',
            'prix' => 0,
            'ordre' => 0,
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->put(route('admin.formations.update', $formation), [
            'name' => 'Formation à modifier',
            'description' => 'Description corrigée',
            'prix' => 0,
            'ordre' => 0,
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('admin.formations.index'));
        $this->assertDatabaseHas('formations', [
            'id' => $formation->id,
            'description' => 'Description corrigée',
        ]);
    }
}
