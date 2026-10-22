<?php

namespace Tests\Feature;

use App\Models\AcademicSession;
use App\Models\Evaluation;
use App\Models\Formation;
use App\Models\Module;
use App\Models\Site;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Database\Seeders\SiteSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminEvaluationFiltersTest extends TestCase
{
    use RefreshDatabase;

    public function test_responsable_etudes_can_filter_evaluations_by_formation_and_module_without_site_filter(): void
    {
        $this->seed(SiteSeeder::class);
        $this->seed(RoleSeeder::class);

        $cotonou = Site::where('nom', 'Cotonou')->firstOrFail();
        $bohicon = Site::create(['nom' => 'Bohicon', 'ville' => 'Bohicon', 'est_actif' => true]);
        $responsable = User::create([
            'first_name' => 'Responsable',
            'last_name' => 'Études',
            'email' => 'responsable.etudes.evaluations@ifam.test',
            'password' => Hash::make('Password123!'),
            'is_active' => true,
        ]);
        $responsable->assignRole('responsable_etudes');
        $responsable->sites()->attach($cotonou->id);

        $cotonouSession = AcademicSession::create([
            'name' => 'Session Cotonou 2026',
            'site_id' => $cotonou->id,
            'start_date' => '2026-09-01',
            'end_date' => '2027-06-30',
        ]);
        $bohiconSession = AcademicSession::create([
            'name' => 'Session Bohicon 2026',
            'site_id' => $bohicon->id,
            'start_date' => '2026-09-01',
            'end_date' => '2027-06-30',
        ]);

        $formation = Formation::create(['name' => 'Gestion', 'is_active' => true]);
        $otherFormation = Formation::create(['name' => 'Comptabilité', 'is_active' => true]);
        $module = Module::create(['name' => 'Introduction', 'code' => 'GES101']);
        $otherModule = Module::create(['name' => 'Comptabilité générale', 'code' => 'COM101']);
        $formation->modules()->attach($module->id);
        $otherFormation->modules()->attach($otherModule->id);

        $evaluation = Evaluation::create([
            'type' => 'devoir',
            'created_by' => $responsable->id,
            'formation_id' => $formation->id,
            'module_id' => $module->id,
            'session_id' => $cotonouSession->id,
            'titre' => 'Devoir Gestion',
            'statut' => 'ouverte',
        ]);
        Evaluation::create([
            'type' => 'devoir',
            'created_by' => $responsable->id,
            'formation_id' => $otherFormation->id,
            'module_id' => $otherModule->id,
            'session_id' => $cotonouSession->id,
            'titre' => 'Devoir Comptabilité',
            'statut' => 'ouverte',
        ]);
        Evaluation::create([
            'type' => 'devoir',
            'created_by' => $responsable->id,
            'formation_id' => $otherFormation->id,
            'module_id' => $otherModule->id,
            'session_id' => $bohiconSession->id,
            'titre' => 'Devoir autre site',
            'statut' => 'ouverte',
        ]);

        $response = $this->actingAs($responsable)->get(route('admin.evaluations.index', [
            'formation_id' => $formation->id,
            'module_id' => $module->id,
        ]));

        $response->assertOk()
            ->assertSee('Toutes les formations')
            ->assertSee('Tous les modules')
            ->assertDontSee('Tous les sites accessibles')
            ->assertSee('Devoir Gestion')
            ->assertDontSee('Devoir Comptabilité')
            ->assertDontSee('Devoir autre site');
        $this->assertSame([$evaluation->id], $response->viewData('evaluations')->getCollection()->modelKeys());
    }
}
