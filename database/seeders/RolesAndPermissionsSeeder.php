<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

/**
 * Seeder IFAM Connect — Rôles et permissions.
 *
 * Rôles du système définis dans RoleSeeder.
 *
 * + Création d'un compte admin par défaut (à changer en production).
 */
class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // ── Rôles ────────────────────────────────────────────────────
        $this->call(RoleSeeder::class);
        $roles = RoleSeeder::ROLES;

        // ── Admin par défaut ─────────────────────────────────────────
        $admin = User::firstOrCreate(
            ['email' => 'admin@ifam.bj'],
            [
                'first_name' => 'Super',
                'last_name' => 'Admin',
                'password' => bcrypt('Admin@1234!'),
                'is_active' => true,
                'must_change_password' => true,
            ]
        );

        $admin->assignRole('administrateur');

        $this->command->info('✓ Rôles créés : '.implode(', ', $roles));
        $this->command->info('✓ Admin par défaut : admin@ifam.bj / Admin@1234!');
    }
}
