<?php

namespace Database\Seeders;

use App\Models\Site;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\PermissionRegistrar;

class TestAccountsSeeder extends Seeder
{
    /**
     * Mot de passe unifié pour tous les comptes de test.
     */
    public const DEFAULT_PASSWORD = 'Password123!';

    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Récupération des sites
        $cotonou = Site::where('nom', 'Cotonou')->first();
        $portoNovo = Site::where('nom', 'Porto-Novo')->first();
        $bohicon = Site::where('nom', 'Bohicon')->first();

        $hashedPassword = Hash::make(self::DEFAULT_PASSWORD);

        // 2. Définition des comptes de test par rôle
        $accounts = [
            // ── A. SuperAdmin & Direction ────────────────────────────
            [
                'email' => 'superadmin@ifam.bj',
                'first_name' => 'Super',
                'last_name' => 'Administrateur',
                'matricule' => 'SUP-001',
                'role' => 'administrateur',
                'site_id' => null, // Global
            ],
            [
                'email' => 'admin@ifam.bj',
                'first_name' => 'Admin',
                'last_name' => 'Principal',
                'matricule' => 'ADM-001',
                'role' => 'administrateur',
                'site_id' => null, // Global
            ],
            [
                'email' => 'direction@ifam.bj',
                'first_name' => 'Directrice',
                'last_name' => 'AKPOVO',
                'matricule' => 'DIR-001',
                'role' => 'direction',
                'site_id' => null, // Global
            ],

            // ── B. Responsables de Sites ─────────────────────────────
            [
                'email' => 'admin.cotonou@ifam.bj',
                'first_name' => 'Admin Site',
                'last_name' => 'Cotonou',
                'matricule' => 'SIT-COT-01',
                'role' => 'responsable_site',
                'site_id' => $cotonou?->id,
            ],
            [
                'email' => 'admin.portonovo@ifam.bj',
                'first_name' => 'Admin Site',
                'last_name' => 'Porto-Novo',
                'matricule' => 'SIT-PN-01',
                'role' => 'responsable_site',
                'site_id' => $portoNovo?->id,
            ],
            [
                'email' => 'admin.bohicon@ifam.bj',
                'first_name' => 'Admin Site',
                'last_name' => 'Bohicon',
                'matricule' => 'SIT-BOH-01',
                'role' => 'responsable_site',
                'site_id' => $bohicon?->id,
            ],

            // ── C. Département Administratif et Financier (DAF) ──────
            [
                'email' => 'dg.daf@ifam.bj',
                'first_name' => 'Directeur',
                'last_name' => 'Général DAF',
                'matricule' => 'DAF-DIR-01',
                'role' => 'direction_daf',
                'site_id' => null, // Global
            ],
            [
                'email' => 'raf.cotonou@ifam.bj',
                'first_name' => 'Responsable Admin & Fin.',
                'last_name' => 'Cotonou',
                'matricule' => 'DAF-RAF-01',
                'role' => 'responsable_administratif_financier',
                'site_id' => $cotonou?->id,
            ],
            [
                'email' => 'resp.admin.cotonou@ifam.bj',
                'first_name' => 'Resp. Service',
                'last_name' => 'Administratif Cotonou',
                'matricule' => 'DAF-ADM-01',
                'role' => 'responsable_administratif',
                'site_id' => $cotonou?->id,
            ],
            [
                'email' => 'secretaire.cotonou@ifam.bj',
                'first_name' => 'Secrétaire',
                'last_name' => 'Admin Cotonou',
                'matricule' => 'DAF-SEC-01',
                'role' => 'secretaire_administratif',
                'site_id' => $cotonou?->id,
            ],
            [
                'email' => 'scolarite.cotonou@ifam.bj',
                'first_name' => 'Chargé',
                'last_name' => 'Scolarité Cotonou',
                'matricule' => 'DAF-SCO-01',
                'role' => 'charge_scolarite',
                'site_id' => $cotonou?->id,
            ],
            [
                'email' => 'resp.finance.cotonou@ifam.bj',
                'first_name' => 'Resp. Service',
                'last_name' => 'Financier Cotonou',
                'matricule' => 'DAF-FIN-01',
                'role' => 'responsable_financier',
                'site_id' => $cotonou?->id,
            ],
            [
                'email' => 'comptable.cotonou@ifam.bj',
                'first_name' => 'Comptable',
                'last_name' => 'Cotonou',
                'matricule' => 'DAF-CPT-01',
                'role' => 'comptable',
                'site_id' => $cotonou?->id,
            ],
            [
                'email' => 'caissier.cotonou@ifam.bj',
                'first_name' => 'Caissier',
                'last_name' => 'Cotonou',
                'matricule' => 'DAF-CAI-01',
                'role' => 'caissier',
                'site_id' => $cotonou?->id,
            ],
            [
                'email' => 'recouvrement.cotonou@ifam.bj',
                'first_name' => 'Agent',
                'last_name' => 'Recouvrement Cotonou',
                'matricule' => 'DAF-REC-01',
                'role' => 'agent_recouvrement',
                'site_id' => $cotonou?->id,
            ],

            // ── D. Département des Affaires Académiques (DAA) ────────
            [
                'email' => 'dg.daa@ifam.bj',
                'first_name' => 'Directeur',
                'last_name' => 'Général DAA',
                'matricule' => 'DAA-DIR-01',
                'role' => 'direction_daa',
                'site_id' => null, // Global
            ],
            [
                'email' => 'resp.daa.cotonou@ifam.bj',
                'first_name' => 'Responsable DAA',
                'last_name' => 'Cotonou',
                'matricule' => 'DAA-RESP-01',
                'role' => 'responsable_daa',
                'site_id' => $cotonou?->id,
            ],
            [
                'email' => 'resp.etudes.cotonou@ifam.bj',
                'first_name' => 'Resp. Service',
                'last_name' => 'Études Cotonou',
                'matricule' => 'DAA-ETU-01',
                'role' => 'responsable_etudes',
                'site_id' => $cotonou?->id,
            ],
            [
                'email' => 'resp.insertion.cotonou@ifam.bj',
                'first_name' => 'Resp. Service',
                'last_name' => 'Insertion Cotonou',
                'matricule' => 'DAA-INS-01',
                'role' => 'responsable_insertion',
                'site_id' => $cotonou?->id,
            ],
            [
                'email' => 'resp.daa.portonovo@ifam.bj',
                'first_name' => 'Responsable DAA',
                'last_name' => 'Porto-Novo',
                'matricule' => 'DAA-RESP-02',
                'role' => 'responsable_daa',
                'site_id' => $portoNovo?->id,
            ],
            [
                'email' => 'resp.etudes.bohicon@ifam.bj',
                'first_name' => 'Resp. Service',
                'last_name' => 'Études Bohicon',
                'matricule' => 'DAA-ETU-02',
                'role' => 'responsable_etudes',
                'site_id' => $bohicon?->id,
            ],
        ];

        // 3. Création ou mise à jour des utilisateurs
        foreach ($accounts as $data) {
            $user = User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'first_name' => $data['first_name'],
                    'last_name' => $data['last_name'],
                    'matricule' => $data['matricule'],
                    'password' => $hashedPassword,
                    'is_active' => true,
                    'must_change_password' => false,
                ]
            );

            // Synchronisation du rôle
            $user->syncRoles([$data['role']]);

            // Rattachement au site le cas échéant
            if ($data['site_id']) {
                $user->sites()->sync([$data['site_id']]);
            } else {
                $user->sites()->detach();
            }
        }

        if (isset($this->command)) {
            $this->command->info('✅ Comptes de test initialisés avec succès.');
        }
    }
}
