<?php

namespace Database\Seeders;

use App\Models\Site;
use Illuminate\Database\Seeder;

class SiteSeeder extends Seeder
{
    public function run(): void
    {
        Site::firstOrCreate(
            ['nom' => 'Cotonou'],
            [
                'ville' => 'Cotonou',
                'adresse' => 'Haie Vive, Cotonou, Bénin',
                'telephone' => '+229 21 30 00 01',
                'email' => 'cotonou@ifam.bj',
                'est_actif' => true,
            ]
        );

        Site::firstOrCreate(
            ['nom' => 'Porto-Novo'],
            [
                'ville' => 'Porto-Novo',
                'adresse' => 'Quartier Avakpa, Porto-Novo, Bénin',
                'telephone' => '+229 20 21 00 02',
                'email' => 'portonovo@ifam.bj',
                'est_actif' => true,
            ]
        );

        Site::firstOrCreate(
            ['nom' => 'Parakou'],
            [
                'ville' => 'Parakou',
                'adresse' => 'Quartier Albarika, Parakou, Bénin',
                'telephone' => '+229 23 61 00 03',
                'email' => 'parakou@ifam.bj',
                'est_actif' => true,
            ]
        );
    }
}
