<?php

namespace Tests\Feature\Admin;

use App\Models\Alumni;
use App\Models\Formation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AlumniTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->admin = User::role('administrateur')->first();
    }

    public function test_admin_can_access_alumnis_index(): void
    {
        Alumni::create([
            'nom' => 'Konan',
            'prenom' => 'Adjoa',
            'statut_actuel' => 'En emploi',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.alumnis.index'));

        $response->assertStatus(200);
        $response->assertSee('Konan');
    }

    public function test_admin_can_create_alumni(): void
    {
        Storage::fake('public');
        $formation = Formation::first();

        $response = $this->actingAs($this->admin)->post(route('admin.alumnis.store'), [
            'nom' => 'Kouakou',
            'prenom' => 'Eric',
            'email' => 'eric@example.com',
            'telephone' => '0700000001',
            'formation_id' => $formation?->id,
            'date_diplome' => '2024-06-15',
            'lieu_residence' => 'Abidjan',
            'statut_actuel' => 'En emploi',
            'entreprise_actuelle' => 'MTN CI',
            'poste_actuel' => 'Ingénieur Réseau',
            'rapports_donnees' => 'Très bonne insertion.',
            'photo' => UploadedFile::fake()->image('photo.jpg'),
        ]);

        $response->assertRedirect(route('admin.alumnis.index'));
        $this->assertDatabaseHas('alumnis', [
            'nom' => 'Kouakou',
            'prenom' => 'Eric',
            'statut_actuel' => 'En emploi',
            'is_public' => false,
        ]);
    }

    public function test_admin_can_update_alumni(): void
    {
        $alumni = Alumni::create([
            'nom' => 'Bamba',
            'prenom' => 'Mamadou',
            'statut_actuel' => 'En recherche',
        ]);

        $response = $this->actingAs($this->admin)->put(route('admin.alumnis.update', $alumni), [
            'nom' => 'Bamba',
            'prenom' => 'Mamadou',
            'statut_actuel' => 'Entrepreneur',
            'entreprise_actuelle' => 'Ma Startup',
            'poste_actuel' => 'Fondateur',
            'is_public' => true,
        ]);

        $response->assertRedirect(route('admin.alumnis.index'));
        $this->assertDatabaseHas('alumnis', [
            'id' => $alumni->id,
            'statut_actuel' => 'Entrepreneur',
            'is_public' => true,
        ]);
    }

    public function test_admin_can_delete_alumni(): void
    {
        $alumni = Alumni::create([
            'nom' => 'ToDelete',
            'prenom' => 'Alumni',
            'statut_actuel' => 'Autre',
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.alumnis.destroy', $alumni));

        $response->assertRedirect(route('admin.alumnis.index'));
        $this->assertDatabaseMissing('alumnis', ['id' => $alumni->id]);
    }
}
