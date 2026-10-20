<?php

use App\Models\ServiceProfile;
use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public const SERVICES = [
        'pedagogie_etudes' => [
            'name' => 'Pédagogie / Études',
            'agent_perms' => ['view pedagogie dashboard', 'view pedagogie records'],
            'responsable_perms' => ['supervise pedagogie service'],
        ],
        'insertion_professionnelle' => [
            'name' => 'Insertion professionnelle',
            'agent_perms' => ['view insertion dashboard', 'view insertion candidates', 'manage insertion followups'],
            'responsable_perms' => ['supervise insertion service', 'validate insertion partnerships'],
        ],
        'administratif' => [
            'name' => 'Administratif',
            'agent_perms' => ['view administratif dashboard', 'view administratif records'],
            'responsable_perms' => ['supervise administratif service'],
        ],
        'financier' => [
            'name' => 'Financier',
            'agent_perms' => ['view financier dashboard', 'view financier records'],
            'responsable_perms' => ['supervise financier service'],
        ],
        'commercial' => [
            'name' => 'Commercial',
            'agent_perms' => ['view commercial dashboard', 'view commercial records'],
            'responsable_perms' => ['supervise commercial service'],
        ],
        'apres_vente' => [
            'name' => 'Après-vente',
            'agent_perms' => ['view apres_vente dashboard', 'view apres_vente records'],
            'responsable_perms' => ['supervise apres_vente service'],
        ],
        'gestion_stocks' => [
            'name' => 'Gestion des stocks',
            'agent_perms' => ['view stock dashboard', 'view stock items', 'record stock movements'],
            'responsable_perms' => ['supervise stock service', 'validate stock audits'],
        ],
        'maintenance_logistique' => [
            'name' => 'Maintenance et logistique',
            'agent_perms' => ['view maintenance dashboard', 'view maintenance records'],
            'responsable_perms' => ['supervise maintenance service'],
        ],
        'marketing' => [
            'name' => 'Marketing',
            'agent_perms' => ['view marketing dashboard', 'view marketing records'],
            'responsable_perms' => ['supervise marketing service'],
        ],
        'communication' => [
            'name' => 'Communication',
            'agent_perms' => ['view communication dashboard', 'view communication records'],
            'responsable_perms' => ['supervise communication service'],
        ],
        'evenementiel' => [
            'name' => 'Événementiel',
            'agent_perms' => ['view evenementiel dashboard', 'view evenementiel records'],
            'responsable_perms' => ['supervise evenementiel service'],
        ],
        'relations_exterieures' => [
            'name' => 'Relations extérieures',
            'agent_perms' => ['view relations_exterieures dashboard', 'view relations_exterieures records'],
            'responsable_perms' => ['supervise relations_exterieures service'],
        ],
        'administration_systemes' => [
            'name' => 'Administration des systèmes',
            'agent_perms' => ['view administration_systemes dashboard', 'view administration_systemes records'],
            'responsable_perms' => ['supervise administration_systemes service'],
        ],
        'innovation_accompagnement' => [
            'name' => 'Innovation et accompagnement',
            'agent_perms' => ['view innovation dashboard', 'view innovation records'],
            'responsable_perms' => ['supervise innovation service'],
        ],
    ];

    public function up(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Purge obsolete responsable_session role if present
        Role::query()
            ->where('name', 'responsable_session')
            ->where('guard_name', 'web')
            ->delete();

        // 2. Ensure new Responsable de site role exists
        Role::firstOrCreate(['name' => 'responsable_site', 'guard_name' => 'web']);

        // 3. System Administrator Permissions (Technical ONLY - no automatic grades or payments)
        $adminPermissions = [
            'manage users',
            'manage roles',
            'manage permissions',
            'manage system settings',
        ];

        foreach ($adminPermissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        $adminRole = Role::where(['name' => 'administrateur', 'guard_name' => 'web'])->first();
        $adminRole?->givePermissionTo($adminPermissions);

        // 4. Direction Générale (DG) Permissions (Steering & Global Oversight)
        $directionPermissions = [
            'view global dashboard',
            'view all sites',
            'view enrollments',
            'view formations',
            'view academic sessions',
            'view results',
            'view attendances',
            'view finances',
            'view indicators',
        ];

        foreach ($directionPermissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        $directionRole = Role::where(['name' => 'direction', 'guard_name' => 'web'])->first();
        $directionRole?->givePermissionTo($directionPermissions);

        // 5. Responsable de site Permissions
        $siteManagerPermissions = [
            'view assigned sites',
            'view site dashboard',
            'view site enrollments',
            'view site sessions',
            'view site attendances',
            'view site rooms',
        ];

        foreach ($siteManagerPermissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        $siteManagerRole = Role::where(['name' => 'responsable_site', 'guard_name' => 'web'])->first();
        $siteManagerRole?->givePermissionTo($siteManagerPermissions);

        // 6. Service Profiles and Functional Level Roles (Agent & Responsable)
        foreach (self::SERVICES as $code => $config) {
            // Seed service_profile table record
            ServiceProfile::firstOrCreate(
                ['code' => $code],
                [
                    'name' => $config['name'],
                    'is_active' => true,
                ]
            );

            // Create permissions
            $allServicePerms = array_merge($config['agent_perms'], $config['responsable_perms']);
            foreach ($allServicePerms as $perm) {
                Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
            }

            // Create Agent Role
            $agentRole = Role::firstOrCreate(['name' => "agent_{$code}", 'guard_name' => 'web']);
            $agentRole->syncPermissions($config['agent_perms']);

            // Create Responsable Role (Agent perms + Responsable supervision/validation perms)
            $responsableRole = Role::firstOrCreate(['name' => "responsable_{$code}", 'guard_name' => 'web']);
            $responsableRole->syncPermissions($allServicePerms);
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }

    public function down(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        foreach (self::SERVICES as $code => $config) {
            $allServicePerms = array_merge($config['agent_perms'], $config['responsable_perms']);
            foreach ($allServicePerms as $perm) {
                Permission::where(['name' => $perm, 'guard_name' => 'web'])->delete();
            }

            Role::where(['name' => "agent_{$code}", 'guard_name' => 'web'])->delete();
            Role::where(['name' => "responsable_{$code}", 'guard_name' => 'web'])->delete();

            ServiceProfile::where('code', $code)->delete();
        }

        $siteManagerPermissions = [
            'view assigned sites',
            'view site dashboard',
            'view site enrollments',
            'view site sessions',
            'view site attendances',
            'view site rooms',
        ];
        foreach ($siteManagerPermissions as $perm) {
            Permission::where(['name' => $perm, 'guard_name' => 'web'])->delete();
        }
        Role::where(['name' => 'responsable_site', 'guard_name' => 'web'])->delete();

        $directionPermissions = [
            'view global dashboard',
            'view all sites',
            'view enrollments',
            'view formations',
            'view academic sessions',
            'view results',
            'view attendances',
            'view finances',
            'view indicators',
        ];
        foreach ($directionPermissions as $perm) {
            Permission::where(['name' => $perm, 'guard_name' => 'web'])->delete();
        }

        $adminPermissions = [
            'manage users',
            'manage roles',
            'manage permissions',
            'manage system settings',
        ];
        foreach ($adminPermissions as $perm) {
            Permission::where(['name' => $perm, 'guard_name' => 'web'])->delete();
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
};
