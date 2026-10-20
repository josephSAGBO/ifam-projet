<?php

namespace Tests\Feature\Admin;

use App\Models\Partnership;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PartnershipTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->admin = User::role('administrateur')->first();
    }

    public function test_admin_can_access_partnerships_index(): void
    {
        Partnership::create([
            'nom' => 'Entreprise Test',
            'prenom' => 'Jean Contact',
            'type_partenariat' => 'Stage',
            'statut' => 'Actif',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.partnerships.index'));

        $response->assertStatus(200);
        $response->assertSee('Entreprise Test');
    }

    public function test_admin_can_create_partnership(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.partnerships.store'), [
            'nom' => 'Orange CI',
            'prenom' => 'Koffi Paul',
            'telephone' => '0700000000',
            'email' => 'partner@orange.ci',
            'type_partenariat' => 'Stage',
            'duree' => '1 an',
            'secteur_intervention' => 'Télécoms',
            'ce_qu_ils_offrent' => 'Stages de fin d\'études',
            'ce_qu_on_offre' => 'Visibilité',
            'personne_ressource_interne' => 'Dr. Kouamé',
            'suivi' => 'Premier rendez-vous fixé',
            'date_dernier_contact' => '2026-10-01',
            'statut' => 'Actif',
        ]);

        $response->assertRedirect(route('admin.partnerships.index'));
        $this->assertDatabaseHas('partnerships', [
            'nom' => 'Orange CI',
            'prenom' => 'Koffi Paul',
        ]);
    }

    public function test_admin_can_update_partnership(): void
    {
        $partnership = Partnership::create([
            'nom' => 'Ancienne Structure',
            'prenom' => 'Contact Initial',
            'type_partenariat' => 'Emploi',
            'statut' => 'En discussion',
        ]);

        $response = $this->actingAs($this->admin)->put(route('admin.partnerships.update', $partnership), [
            'nom' => 'Nouveau Nom Structure',
            'prenom' => 'Contact Modifié',
            'type_partenariat' => 'Formations',
            'statut' => 'Actif',
        ]);

        $response->assertRedirect(route('admin.partnerships.index'));
        $this->assertDatabaseHas('partnerships', [
            'id' => $partnership->id,
            'nom' => 'Nouveau Nom Structure',
            'statut' => 'Actif',
        ]);
    }

    public function test_admin_can_delete_partnership(): void
    {
        $partnership = Partnership::create([
            'nom' => 'Structure A Supprimer',
            'prenom' => 'Contact A Supprimer',
            'type_partenariat' => 'Autre',
            'statut' => 'Inactif',
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.partnerships.destroy', $partnership));

        $response->assertRedirect(route('admin.partnerships.index'));
        $this->assertDatabaseMissing('partnerships', [
            'id' => $partnership->id,
        ]);
    }
}
