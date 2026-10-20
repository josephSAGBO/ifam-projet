<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Création du rôle Spatie alumni
        $alumniRole = Role::firstOrCreate(['name' => 'alumni', 'guard_name' => 'web']);

        // Permissions spécifiques au module alumni
        $permissions = [
            'view alumni space',
            'manage own alumni profile',
            'create alumni posts',
            'manage own alumni posts',
            'moderate alumni posts',
            'manage alumni directory',
        ];

        foreach ($permissions as $permissionName) {
            Permission::firstOrCreate(['name' => $permissionName, 'guard_name' => 'web']);
        }

        // Attribution des permissions aux rôles correspondants
        $alumniRole->givePermissionTo([
            'view alumni space',
            'manage own alumni profile',
            'create alumni posts',
            'manage own alumni posts',
        ]);

        $adminRole = Role::where(['name' => 'administrateur', 'guard_name' => 'web'])->first();
        if ($adminRole) {
            $adminRole->givePermissionTo([
                'moderate alumni posts',
                'manage alumni directory',
            ]);
        }

        $directionRole = Role::where(['name' => 'direction', 'guard_name' => 'web'])->first();
        if ($directionRole) {
            $directionRole->givePermissionTo([
                'moderate alumni posts',
                'manage alumni directory',
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $permissions = [
            'view alumni space',
            'manage own alumni profile',
            'create alumni posts',
            'manage own alumni posts',
            'moderate alumni posts',
            'manage alumni directory',
        ];

        foreach ($permissions as $permissionName) {
            $perm = Permission::where('name', $permissionName)->where('guard_name', 'web')->first();
            $perm?->delete();
        }

        $alumniRole = Role::where('name', 'alumni')->where('guard_name', 'web')->first();
        $alumniRole?->delete();
    }
};
