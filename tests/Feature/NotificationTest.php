<?php

namespace Tests\Feature;

use App\Models\Notification;
use App\Models\ServiceProfile;
use App\Models\Site;
use App\Models\User;
use App\Models\UserAssignment;
use App\Services\NotificationService;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $direction;

    protected User $etudiant;

    protected User $agent;

    protected Site $site;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);

        $this->site = Site::create([
            'nom' => 'Cotonou Test',
            'ville' => 'Cotonou',
            'est_actif' => true,
        ]);

        $this->admin = User::create([
            'first_name' => 'Admin',
            'last_name' => 'Test',
            'email' => 'admin.notif@ifam.test',
            'password' => Hash::make('Password123!'),
            'is_active' => true,
        ]);
        $this->admin->assignRole('administrateur');

        $this->direction = User::create([
            'first_name' => 'Direction',
            'last_name' => 'Test',
            'email' => 'direction.notif@ifam.test',
            'password' => Hash::make('Password123!'),
            'is_active' => true,
        ]);
        $this->direction->assignRole('direction');

        $this->agent = User::create([
            'first_name' => 'Agent',
            'last_name' => 'Test',
            'email' => 'agent.notif@ifam.test',
            'password' => Hash::make('Password123!'),
            'is_active' => true,
        ]);
        $this->agent->assignRole('agent_administratif');

        $this->etudiant = User::create([
            'first_name' => 'Etudiant',
            'last_name' => 'Test',
            'email' => 'etudiant.notif@ifam.test',
            'password' => Hash::make('Password123!'),
            'is_active' => true,
        ]);
        $this->etudiant->assignRole('etudiant');
    }

    /** @test */
    public function test_notifier_evenement_envoie_par_role(): void
    {
        $service = app(NotificationService::class);

        // Agit comme admin (auteur exclu)
        $this->actingAs($this->admin);

        $service->notifierEvenement('publication_alumni_soumise', [
            'auteur_id' => $this->admin->id,
            'placeholders' => [
                'alumni' => 'Jean Dupont',
                'titre' => 'Mon article',
            ],
        ]);

        // Direction reçoit la notification
        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->direction->id,
            'type' => 'alumni',
        ]);

        // L'auteur (admin) n'est PAS notifié
        $this->assertDatabaseMissing('notifications', [
            'user_id' => $this->admin->id,
            'type' => 'alumni',
        ]);
    }

    /** @test */
    public function test_notifier_evenement_envoie_par_service(): void
    {
        $service = app(NotificationService::class);

        $serviceProfile = ServiceProfile::create([
            'code' => 'NOTIF-SVC-TEST',
            'name' => 'Service Test',
            'is_active' => true,
        ]);

        $responsable = User::create([
            'first_name' => 'Resp',
            'last_name' => 'Service',
            'email' => 'resp.service.notif@ifam.test',
            'password' => Hash::make('Password123!'),
            'is_active' => true,
        ]);
        $responsable->assignRole('administrateur');

        UserAssignment::create([
            'user_id' => $responsable->id,
            'service_profile_id' => $serviceProfile->id,
            'site_id' => $this->site->id,
            'level' => 'responsable',
        ]);

        $this->actingAs($this->direction);

        $service->notifierEvenement('rappel_echeance_rapport', [
            'auteur_id' => $this->direction->id,
            'service_code' => 'NOTIF-SVC-TEST',
            'site_id' => $this->site->id,
            'placeholders' => [
                'service' => 'Service Test',
                'site' => ' (Cotonou Test)',
                'type_rapport' => 'mensuel',
                'echeance' => '31/10/2026',
            ],
        ]);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $responsable->id,
            'type' => 'report',
        ]);

        // L'auteur (direction) n'est pas notifié
        $this->assertDatabaseMissing('notifications', [
            'user_id' => $this->direction->id,
            'type' => 'report',
        ]);
    }

    /** @test */
    public function test_auteur_exclu_des_destinataires(): void
    {
        $service = app(NotificationService::class);

        // Admin est également administrateur → serait dans la liste 'administrateur' role
        // On vérifie qu'il est exclu quand auteur_id == son id
        $this->actingAs($this->admin);

        $service->notifierEvenement('publication_alumni_soumise', [
            'auteur_id' => $this->admin->id,
            'placeholders' => ['alumni' => 'A', 'titre' => 'T'],
        ]);

        $count = Notification::where('user_id', $this->admin->id)->count();
        $this->assertEquals(0, $count, 'L\'auteur ne doit pas se notifier lui-même.');
    }

    /** @test */
    public function test_pas_de_doublon_si_meme_utilisateur_dans_deux_roles(): void
    {
        // Ajouter direction_daf à $direction pour qu'elle soit dans 2 rôles
        $this->direction->assignRole('direction');

        $service = app(NotificationService::class);
        $this->actingAs($this->admin);

        $service->notifierEvenement('rapport_envoye', [
            'auteur_id' => $this->admin->id,
            'placeholders' => [
                'service' => 'Service',
                'type_rapport' => 'mensuel',
                'debut' => '01/10/2026',
                'fin' => '31/10/2026',
                'auteur' => 'Admin Test',
            ],
        ]);

        // direction reçoit exactement 1 notification
        $count = Notification::where('user_id', $this->direction->id)->count();
        $this->assertEquals(1, $count, 'Pas de doublon pour un même destinataire.');
    }

    /** @test */
    public function test_marquer_comme_lu(): void
    {
        $notif = Notification::create([
            'user_id' => $this->agent->id,
            'title' => 'Test',
            'message' => 'Msg',
            'type' => 'info',
            'icon' => 'info',
        ]);

        $this->assertNull($notif->read_at);

        $this->actingAs($this->agent)
            ->patch(route('admin.notifications.markRead', $notif));

        $this->assertNotNull($notif->fresh()->read_at);
    }

    /** @test */
    public function test_utilisateur_ne_voit_que_ses_propres_notifications(): void
    {
        Notification::create([
            'user_id' => $this->agent->id,
            'title' => 'Pour agent',
            'message' => 'Msg',
            'type' => 'info',
            'icon' => 'info',
        ]);

        Notification::create([
            'user_id' => $this->admin->id,
            'title' => 'Pour admin',
            'message' => 'Msg',
            'type' => 'info',
            'icon' => 'info',
        ]);

        $response = $this->actingAs($this->agent)
            ->get(route('admin.notifications.index'));

        $response->assertOk();
        $response->assertSee('Pour agent');
        $response->assertDontSee('Pour admin');
    }

    /** @test */
    public function test_marquer_toutes_comme_lues(): void
    {
        Notification::insert([
            ['user_id' => $this->agent->id, 'title' => 'N1', 'message' => 'M', 'type' => 'info', 'icon' => 'info', 'read_at' => null, 'created_at' => now(), 'updated_at' => now()],
            ['user_id' => $this->agent->id, 'title' => 'N2', 'message' => 'M', 'type' => 'info', 'icon' => 'info', 'read_at' => null, 'created_at' => now(), 'updated_at' => now()],
        ]);

        $this->actingAs($this->agent)
            ->patch(route('admin.notifications.markAllRead'));

        $unread = Notification::where('user_id', $this->agent->id)
            ->whereNull('read_at')
            ->count();

        $this->assertEquals(0, $unread);
    }

    /** @test */
    public function test_open_marque_comme_lu_et_redirige(): void
    {
        $notif = Notification::create([
            'user_id' => $this->agent->id,
            'title' => 'Test open',
            'message' => 'Msg',
            'type' => 'info',
            'icon' => 'info',
            'link' => route('admin.notifications.index'),
        ]);

        $this->actingAs($this->agent)
            ->get(route('admin.notifications.open', $notif))
            ->assertRedirect($notif->link);

        $this->assertNotNull($notif->fresh()->read_at);
    }

    /** @test */
    public function test_autre_utilisateur_ne_peut_pas_marquer_notification_dautrui(): void
    {
        $notif = Notification::create([
            'user_id' => $this->admin->id,
            'title' => 'Pour admin',
            'message' => 'Msg',
            'type' => 'info',
            'icon' => 'info',
        ]);

        $this->actingAs($this->agent)
            ->patch(route('admin.notifications.markRead', $notif))
            ->assertForbidden();

        $this->assertNull($notif->fresh()->read_at);
    }

    /** @test */
    public function test_commande_prune_supprime_vieilles_notifications_lues(): void
    {
        // Ancienne notification lue (91 jours)
        $old = Notification::create([
            'user_id' => $this->etudiant->id,
            'title' => 'Vieille lue',
            'message' => 'Msg',
            'type' => 'info',
            'icon' => 'info',
            'read_at' => now()->subDays(91),
        ]);

        // Récente notification lue
        $recent = Notification::create([
            'user_id' => $this->etudiant->id,
            'title' => 'Récente lue',
            'message' => 'Msg',
            'type' => 'info',
            'icon' => 'info',
            'read_at' => now()->subDays(1),
        ]);

        $this->artisan('notifications:prune')->assertExitCode(0);

        $this->assertDatabaseMissing('notifications', ['id' => $old->id]);
        $this->assertDatabaseHas('notifications', ['id' => $recent->id]);
    }
}
