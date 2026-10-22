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

        // 6. Rôles et Permissions du Département Administratif et Financier (DAF)
        $dafPermissions = [
            'validate financial operations',
            'create payments',
            'view payments',
            'print receipts',
            'view fees',
            'view discounts',
            'manage discounts',
            'view expenses',
            'create expenses',
            'view financial reports',
            'view administrative dashboard',
            'view financial dashboard',
            'view enrollments',
            'manage enrollments',
            'view student dossiers',
            'edit student administrative',
            'view teacher dossiers',
            'edit teacher administrative',
            'view parent dossiers',
            'view staff dossiers',
            'view administrative documents',
            'manage administrative documents',
            'view archives',
            'view correspondances',
        ];

        foreach ($dafPermissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        // Direction Générale du DAF : vue transversale DAF et pouvoir de validation
        $roleDgDaf = Role::firstOrCreate(['name' => 'direction_daf', 'guard_name' => 'web']);
        $roleDgDaf->syncPermissions([
            'validate financial operations',
            'view payments',
            'create payments',
            'print receipts',
            'view fees',
            'view discounts',
            'manage discounts',
            'view expenses',
            'create expenses',
            'view financial reports',
            'view administrative dashboard',
            'view financial dashboard',
            'view enrollments',
            'manage enrollments',
            'view student dossiers',
            'edit student administrative',
            'view teacher dossiers',
            'edit teacher administrative',
            'view parent dossiers',
            'view staff dossiers',
            'view administrative documents',
            'manage administrative documents',
            'view archives',
            'view correspondances',
        ]);

        // Responsable Administratif et Financier (RAF) : supervise les 2 services sur son site
        $roleRaf = Role::firstOrCreate(['name' => 'responsable_administratif_financier', 'guard_name' => 'web']);
        $roleRaf->syncPermissions([
            'view payments',
            'create payments',
            'print receipts',
            'view fees',
            'view discounts',
            'manage discounts',
            'view expenses',
            'create expenses',
            'view financial reports',
            'view administrative dashboard',
            'view financial dashboard',
            'view enrollments',
            'manage enrollments',
            'view student dossiers',
            'edit student administrative',
            'view teacher dossiers',
            'edit teacher administrative',
            'view parent dossiers',
            'view staff dossiers',
            'view administrative documents',
            'manage administrative documents',
            'view archives',
            'view correspondances',
        ]);

        // Service Administratif
        $roleRespAdmin = Role::firstOrCreate(['name' => 'responsable_administratif', 'guard_name' => 'web']);
        $roleRespAdmin->syncPermissions([
            'view administrative dashboard',
            'view enrollments',
            'manage enrollments',
            'view student dossiers',
            'edit student administrative',
            'view teacher dossiers',
            'edit teacher administrative',
            'view parent dossiers',
            'view staff dossiers',
            'view administrative documents',
            'manage administrative documents',
            'view archives',
            'view correspondances',
            'view fees',
        ]);

        $roleSecrAdmin = Role::firstOrCreate(['name' => 'secretaire_administratif', 'guard_name' => 'web']);
        $roleSecrAdmin->syncPermissions([
            'view administrative dashboard',
            'view enrollments',
            'manage enrollments',
            'view student dossiers',
            'edit student administrative',
            'view teacher dossiers',
            'view parent dossiers',
            'view administrative documents',
            'manage administrative documents',
            'view archives',
            'view correspondances',
        ]);

        $roleChargeScol = Role::firstOrCreate(['name' => 'charge_scolarite', 'guard_name' => 'web']);
        $roleChargeScol->syncPermissions([
            'view administrative dashboard',
            'view enrollments',
            'manage enrollments',
            'view student dossiers',
            'edit student administrative',
            'view administrative documents',
            'manage administrative documents',
            'view archives',
        ]);

        $roleAgentAdmin = Role::firstOrCreate(['name' => 'agent_administratif', 'guard_name' => 'web']);
        $roleAgentAdmin->syncPermissions([
            'view administrative dashboard',
            'view enrollments',
            'view student dossiers',
            'view administrative documents',
            'view archives',
            'view correspondances',
        ]);

        // Service Financier
        $roleRespFin = Role::firstOrCreate(['name' => 'responsable_financier', 'guard_name' => 'web']);
        $roleRespFin->syncPermissions([
            'view financial dashboard',
            'view fees',
            'view payments',
            'create payments',
            'print receipts',
            'view discounts',
            'manage discounts',
            'view expenses',
            'create expenses',
            'view financial reports',
            'view enrollments',
        ]);

        $roleComptable = Role::firstOrCreate(['name' => 'comptable', 'guard_name' => 'web']);
        $roleComptable->syncPermissions([
            'view financial dashboard',
            'view fees',
            'view payments',
            'create payments',
            'print receipts',
            'view discounts',
            'manage discounts',
            'view expenses',
            'create expenses',
            'view financial reports',
            'view enrollments',
        ]);

        $roleCaissier = Role::firstOrCreate(['name' => 'caissier', 'guard_name' => 'web']);
        $roleCaissier->syncPermissions([
            'view financial dashboard',
            'view fees',
            'view payments',
            'create payments',
            'print receipts',
            'view expenses',
            'view enrollments',
        ]);

        $roleRecouvrement = Role::firstOrCreate(['name' => 'agent_recouvrement', 'guard_name' => 'web']);
        $roleRecouvrement->syncPermissions([
            'view financial dashboard',
            'view fees',
            'view payments',
            'print receipts',
            'view discounts',
            'view enrollments',
        ]);

        // 7. Rôles et Permissions du Département des Affaires Académiques (DAA)
        $daaPermissions = [
            // Catalogue partagé (lecture + gestion pour la DG)
            'view formations',
            'manage formations',
            'view modules',
            'manage modules',
            // Pédagogie / Études
            'view academic sessions',
            'manage academic sessions',
            'view courses',
            'manage courses',
            'view evaluations',
            'manage evaluations',
            'view grades',
            'manage grades',
            'view attendances',
            'manage attendances',
            'view results',
            'view bulletins',
            'publish bulletins',
            'view academic dashboard',
            // Insertion professionnelle
            'view insertion dashboard',
            'view alumni',
            'manage alumni',
            'view partnerships',
            'manage partnerships',
            'view insertion candidates',
            'manage insertion followups',
            'validate insertion partnerships',
        ];

        foreach ($daaPermissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        // Direction Générale du DAA : accès transversal lecture + validation catalogue
        $roleDgDaa = Role::firstOrCreate(['name' => 'direction_daa', 'guard_name' => 'web']);
        $roleDgDaa->syncPermissions([
            'view formations',
            'manage formations',
            'view modules',
            'manage modules',
            'view academic sessions',
            'manage academic sessions',
            'view courses',
            'manage courses',
            'view evaluations',
            'manage evaluations',
            'view grades',
            'manage grades',
            'view attendances',
            'manage attendances',
            'view results',
            'view bulletins',
            'publish bulletins',
            'view academic dashboard',
            'view insertion dashboard',
            'view alumni',
            'manage alumni',
            'view partnerships',
            'manage partnerships',
            'view insertion candidates',
            'manage insertion followups',
            'validate insertion partnerships',
        ]);

        // Responsable du Département des Affaires Académiques :
        // supervise les 2 services sur son périmètre de site
        $roleRespDaa = Role::firstOrCreate(['name' => 'responsable_daa', 'guard_name' => 'web']);
        $roleRespDaa->syncPermissions([
            'view formations',
            'view modules',
            'view academic sessions',
            'manage academic sessions',
            'view courses',
            'manage courses',
            'view evaluations',
            'manage evaluations',
            'view grades',
            'manage grades',
            'view attendances',
            'manage attendances',
            'view results',
            'view bulletins',
            'publish bulletins',
            'view academic dashboard',
            'view insertion dashboard',
            'view alumni',
            'manage alumni',
            'view partnerships',
            'manage partnerships',
            'view insertion candidates',
            'manage insertion followups',
        ]);

        // Responsable du Service Pédagogie / Études :
        // gère les cours, évaluations, notes, présences, bulletins sur son site
        $roleRespEtudes = Role::firstOrCreate(['name' => 'responsable_etudes', 'guard_name' => 'web']);
        $roleRespEtudes->syncPermissions([
            'view formations',
            'view modules',
            'view academic sessions',
            'view courses',
            'manage courses',
            'view evaluations',
            'manage evaluations',
            'view grades',
            'manage grades',
            'view attendances',
            'manage attendances',
            'view results',
            'view bulletins',
            'publish bulletins',
            'view academic dashboard',
        ]);

        // Responsable du Service Insertion Professionnelle :
        // gère les alumni, partenariats et suivis d'insertion sur son site
        $roleRespInsertion = Role::firstOrCreate(['name' => 'responsable_insertion', 'guard_name' => 'web']);
        $roleRespInsertion->syncPermissions([
            'view formations',
            'view modules',
            'view insertion dashboard',
            'view alumni',
            'manage alumni',
            'view partnerships',
            'manage partnerships',
            'view insertion candidates',
            'manage insertion followups',
        ]);

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        if (isset($this->command)) {
            $this->command->info('✅ Rôles et permissions IFAM Connect initialisés avec succès.');
        }
    }
}
