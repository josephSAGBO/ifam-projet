<?php

namespace Database\Seeders;

use App\Models\ServiceProfile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    public const GENERAL_ROLES = [
        'administrateur',
        'direction',
        'responsable_site',
        'enseignant',
        'etudiant',
        'parent',
        'alumni',
    ];

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

    public const ROLES = self::GENERAL_ROLES;

    /**
     * Les rôles et permissions de l'application IFAM Connect.
     */
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Ensure obsolete role is permanently removed
        Role::query()
            ->where('name', 'responsable_session')
            ->where('guard_name', 'web')
            ->delete();

        // 1. Create General Roles
        foreach (self::GENERAL_ROLES as $role) {
            Role::firstOrCreate([
                'name' => $role,
                'guard_name' => 'web',
            ]);
        }

        // 2. Administrateur Permissions (Technical management only)
        $adminPerms = [
            'manage users',
            'manage roles',
            'manage permissions',
            'manage system settings',
        ];
        foreach ($adminPerms as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }
        Role::findByName('administrateur', 'web')->givePermissionTo($adminPerms);

        // 3. Direction Générale Permissions
        $directionPerms = [
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
        foreach ($directionPerms as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }
        Role::findByName('direction', 'web')->givePermissionTo($directionPerms);

        // 4. Responsable de site Permissions
        $siteManagerPerms = [
            'view assigned sites',
            'view site dashboard',
            'view site enrollments',
            'view site sessions',
            'view site attendances',
            'view site rooms',
        ];
        foreach ($siteManagerPerms as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }
        Role::findByName('responsable_site', 'web')->givePermissionTo($siteManagerPerms);

        // 5. 14 Services & Roles (Agent & Responsable)
        foreach (self::SERVICES as $code => $config) {
            if (Schema::hasTable('service_profiles')) {
                ServiceProfile::firstOrCreate(
                    ['code' => $code],
                    [
                        'name' => $config['name'],
                        'is_active' => true,
                    ]
                );
            }

            $allServicePerms = array_merge($config['agent_perms'], $config['responsable_perms']);
            foreach ($allServicePerms as $perm) {
                Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
            }

            $agentRole = Role::firstOrCreate(['name' => "agent_{$code}", 'guard_name' => 'web']);
            $agentRole->syncPermissions($config['agent_perms']);

            $responsableRole = Role::firstOrCreate(['name' => "responsable_{$code}", 'guard_name' => 'web']);
            $responsableRole->syncPermissions($allServicePerms);
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        if (isset($this->command)) {
            $this->command->info('✅ Rôles et permissions IFAM Connect initialisés avec succès.');
        }
    }
}
