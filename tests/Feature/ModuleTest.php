<?php

namespace Tests\Feature;

use App\Models\Module;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_module_calculates_duree_en_heures_and_minutes_correctly()
    {
        // 2 Mois, 3 Semaines, 23 Heures, 45 Minutes
        $module = new Module([
            'name' => 'Design & Modélisme',
            'code' => 'MOD101',
            'duree_mois' => 2,
            'duree_semaines' => 3,
            'duree_heures' => 23,
            'duree_minutes' => 45,
        ]);

        // Minutes : (2 * 160 * 60) + (3 * 40 * 60) + (23 * 60) + 45 = 19200 + 7200 + 1380 + 45 = 27825
        $this->assertEquals(27825, $module->dureeEnMinutes());
        $this->assertEquals(27825 / 60, $module->dureeEnHeures());
        $this->assertEquals('2 Mois, 3 Semaine(s), 23 Heure(s), 45 min', $module->duree_affichage);
    }
}
