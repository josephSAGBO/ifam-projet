<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_flow(): void
    {
        Role::create(['name' => 'administrateur', 'guard_name' => 'web']);

        $user = User::create([
            'first_name' => 'Admin',
            'last_name' => 'IFAM',
            'email' => 'admin@ifam.bj',
            'password' => Hash::make('Admin@1234!'),
            'is_active' => true,
            'must_change_password' => false,
        ]);
        $user->assignRole('administrateur');

        $response = $this->post('/login', [
            'email' => 'admin@ifam.bj',
            'password' => 'Admin@1234!',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($user);
    }
}
