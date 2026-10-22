<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\ServiceProfile;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public const DEPARTMENTS = [
        [
            'code' => 'affaires_academiques',
            'name' => 'Département des Affaires Académiques',
            'description' => 'Coordination pédagogique, évaluations, suivi académique, insertion professionnelle et réseau alumni.',
            'services' => ['pedagogie_etudes', 'insertion_professionnelle', 'innovation_accompagnement'],
        ],
        [
            'code' => 'administration_finances',
            'name' => 'Département Administratif & Financier',
            'description' => 'Gestion administrative, comptabilité, scolarité et finances.',
            'services' => ['administratif', 'financier'],
        ],
        [
            'code' => 'developpement_communication',
            'name' => 'Département Développement & Communication',
            'description' => 'Commercialisation, marketing, communication digitale et organisation d\'événements.',
            'services' => ['commercial', 'marketing', 'communication', 'evenementiel', 'apres_vente'],
        ],
        [
            'code' => 'logistique_systemes',
            'name' => 'Département Logistique & Systèmes d\'Information',
            'description' => 'Gestion des stocks, maintenance des infrastructures, logistique et administration systèmes.',
            'services' => ['gestion_stocks', 'maintenance_logistique', 'administration_systemes'],
        ],
        [
            'code' => 'relations_exterieures',
            'name' => 'Département Relations Extérieures',
            'description' => 'Partenariats institutionnels et relations extérieures transversaux.',
            'services' => ['relations_exterieures'],
        ],
    ];

    public function run(): void
    {
        foreach (self::DEPARTMENTS as $deptData) {
            $department = Department::firstOrCreate(
                ['code' => $deptData['code']],
                [
                    'name' => $deptData['name'],
                    'description' => $deptData['description'],
                    'is_active' => true,
                ]
            );

            foreach ($deptData['services'] as $serviceCode) {
                $service = ServiceProfile::where('code', $serviceCode)->first();
                if ($service) {
                    $service->update([
                        'department_id' => $department->id,
                        'department_name' => $department->name,
                    ]);
                }
            }
        }
    }
}
