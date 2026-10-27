<?php

namespace Tests\Feature;

use App\Models\Notification;
use App\Models\ServiceProfile;
use App\Models\ServiceReport;
use App\Models\Site;
use App\Models\User;
use App\Models\UserAssignment;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\SiteSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ServiceReportTest extends TestCase
{
    use RefreshDatabase;

    protected User $directionUser;

    protected User $responsableUser;

    protected User $agentUser;

    protected User $otherServiceUser;

    protected ServiceProfile $servicePedagogie;

    protected ServiceProfile $serviceFinances;

    protected Site $siteCotonou;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(SiteSeeder::class);
        $this->seed(RoleSeeder::class);
        $this->seed(DepartmentSeeder::class);

        $this->siteCotonou = Site::where('nom', 'Cotonou')->firstOrFail();

        $this->servicePedagogie = ServiceProfile::where('code', 'pedagogie_etudes')->firstOrFail();
        $this->serviceFinances = ServiceProfile::where('code', 'financier')->firstOrFail();

        // 1. Utilisateur Direction
        $this->directionUser = User::create([
            'first_name' => 'Directeur',
            'last_name' => 'Général',
            'email' => 'directeur@ifam.bj',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);
        $this->directionUser->assignRole('direction');

        // 2. Responsable de Service (Pédagogie)
        $this->responsableUser = User::create([
            'first_name' => 'Jean',
            'last_name' => 'Responsable',
            'email' => 'resp.pedagogie@ifam.bj',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);
        UserAssignment::create([
            'user_id' => $this->responsableUser->id,
            'service_profile_id' => $this->servicePedagogie->id,
            'site_id' => $this->siteCotonou->id,
            'level' => 'responsable',
        ]);

        // 3. Agent de Service (Pédagogie)
        $this->agentUser = User::create([
            'first_name' => 'Marc',
            'last_name' => 'Agent',
            'email' => 'agent.pedagogie@ifam.bj',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);
        UserAssignment::create([
            'user_id' => $this->agentUser->id,
            'service_profile_id' => $this->servicePedagogie->id,
            'site_id' => $this->siteCotonou->id,
            'level' => 'agent',
        ]);

        // 4. Utilisateur d'un autre service (Finances)
        $this->otherServiceUser = User::create([
            'first_name' => 'Paul',
            'last_name' => 'Finances',
            'email' => 'agent.finances@ifam.bj',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);
        UserAssignment::create([
            'user_id' => $this->otherServiceUser->id,
            'service_profile_id' => $this->serviceFinances->id,
            'site_id' => $this->siteCotonou->id,
            'level' => 'agent',
        ]);
    }

    /**
     * Test 1 : Un agent peut créer et modifier ses brouillons avec indicateurs et pièces jointes.
     */
    public function test_agent_can_create_and_update_draft_report_with_attachments(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->create('releve.pdf', 500, 'application/pdf');

        $response = $this->actingAs($this->agentUser)->post(route('service.reports.store'), [
            'service_profile_id' => $this->servicePedagogie->id,
            'site_id' => $this->siteCotonou->id,
            'type' => 'mensuel',
            'periode_debut' => '2026-10-01',
            'periode_fin' => '2026-10-31',
            'titre' => 'Rapport mensuel Pédagogie Octobre 2026',
            'resume' => 'Résumé synthétique du mois.',
            'activites' => 'Examens et évaluations semestrielles.',
            'difficultes' => 'Retard dans les saisies.',
            'besoins' => 'Appui en vacataires.',
            'indicateurs' => [
                ['libelle' => 'Taux d’évaluation', 'valeur' => '98%'],
                ['libelle' => 'Présences', 'valeur' => '92%'],
            ],
            'attachments' => [$file],
            'action' => 'draft',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('service_reports', [
            'titre' => 'Rapport mensuel Pédagogie Octobre 2026',
            'statut' => ServiceReport::STATUT_BROUILLON,
            'auteur_id' => $this->agentUser->id,
        ]);

        $report = ServiceReport::where('titre', 'Rapport mensuel Pédagogie Octobre 2026')->firstOrFail();
        $this->assertEquals(1, $report->documents()->count());

        // Modification par l'agent
        $updateResponse = $this->actingAs($this->agentUser)->put(route('service.reports.update', $report), [
            'site_id' => $this->siteCotonou->id,
            'type' => 'mensuel',
            'periode_debut' => '2026-10-01',
            'periode_fin' => '2026-10-31',
            'titre' => 'Rapport mensuel Pédagogie Octobre 2026 V2',
            'resume' => 'Résumé mis à jour.',
            'activites' => 'Examens terminés avec succès.',
            'action' => 'draft',
        ]);

        $updateResponse->assertRedirect();
        $this->assertDatabaseHas('service_reports', [
            'id' => $report->id,
            'titre' => 'Rapport mensuel Pédagogie Octobre 2026 V2',
        ]);
    }

    /**
     * Test 2 : Seul le niveau responsable peut envoyer le rapport à la Direction.
     */
    public function test_only_responsable_can_send_report(): void
    {
        $report = ServiceReport::create([
            'service_profile_id' => $this->servicePedagogie->id,
            'site_id' => $this->siteCotonou->id,
            'auteur_id' => $this->agentUser->id,
            'type' => 'mensuel',
            'periode_debut' => '2026-10-01',
            'periode_fin' => '2026-10-31',
            'titre' => 'Rapport à envoyer',
            'resume' => 'Résumé',
            'activites' => 'Activités',
            'statut' => ServiceReport::STATUT_BROUILLON,
        ]);

        // L'agent tente d'envoyer -> 403 interdit
        $agentSend = $this->actingAs($this->agentUser)->post(route('service.reports.send', $report));
        $agentSend->assertForbidden();
        $this->assertEquals(ServiceReport::STATUT_BROUILLON, $report->fresh()->statut);

        // Le responsable envoie -> succès
        $respSend = $this->actingAs($this->responsableUser)->post(route('service.reports.send', $report));
        $respSend->assertRedirect(route('service.reports.index'));

        $report->refresh();
        $this->assertEquals(ServiceReport::STATUT_ENVOYE, $report->statut);
        $this->assertNotNull($report->envoye_le);

        // Vérifier notification vers la Direction
        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->directionUser->id,
            'type' => 'report',
        ]);
    }

    /**
     * Test 3 : Plus de modification possible après envoi, sauf si retourné.
     */
    public function test_report_cannot_be_modified_after_sending_unless_returned(): void
    {
        $report = ServiceReport::create([
            'service_profile_id' => $this->servicePedagogie->id,
            'site_id' => $this->siteCotonou->id,
            'auteur_id' => $this->agentUser->id,
            'type' => 'mensuel',
            'periode_debut' => '2026-10-01',
            'periode_fin' => '2026-10-31',
            'titre' => 'Rapport envoyé figé',
            'resume' => 'Résumé',
            'activites' => 'Activités',
            'statut' => ServiceReport::STATUT_ENVOYE,
            'envoye_le' => now(),
        ]);

        // Tentative de modification par le responsable
        $updateResponse = $this->actingAs($this->responsableUser)->put(route('service.reports.update', $report), [
            'site_id' => $this->siteCotonou->id,
            'type' => 'mensuel',
            'periode_debut' => '2026-10-01',
            'periode_fin' => '2026-10-31',
            'titre' => 'Tentative modif',
            'resume' => 'Nouveau résumé',
            'activites' => 'Nouvelles activités',
        ]);
        $updateResponse->assertForbidden();
    }

    /**
     * Test 4 : La Direction ne voit jamais les brouillons mais voit tous les rapports envoyés et les marque comme lus à l'ouverture.
     */
    public function test_direction_cannot_see_drafts_and_marks_sent_reports_as_read(): void
    {
        $draft = ServiceReport::create([
            'service_profile_id' => $this->servicePedagogie->id,
            'site_id' => $this->siteCotonou->id,
            'auteur_id' => $this->agentUser->id,
            'type' => 'mensuel',
            'periode_debut' => '2026-10-01',
            'periode_fin' => '2026-10-31',
            'titre' => 'Brouillon confidentiel',
            'resume' => 'Résumé',
            'activites' => 'Activités',
            'statut' => ServiceReport::STATUT_BROUILLON,
        ]);

        $sent = ServiceReport::create([
            'service_profile_id' => $this->servicePedagogie->id,
            'site_id' => $this->siteCotonou->id,
            'auteur_id' => $this->responsableUser->id,
            'type' => 'mensuel',
            'periode_debut' => '2026-09-01',
            'periode_fin' => '2026-09-30',
            'titre' => 'Rapport officiel transmis',
            'resume' => 'Résumé',
            'activites' => 'Activités',
            'statut' => ServiceReport::STATUT_ENVOYE,
            'envoye_le' => now(),
        ]);

        // Direction ne peut pas voir le brouillon
        $this->actingAs($this->directionUser)->get(route('direction.reports.show', $draft))
            ->assertForbidden();

        // Direction consulte le rapport envoyé -> passe en 'lu'
        $response = $this->actingAs($this->directionUser)->get(route('direction.reports.show', $sent));
        $response->assertOk();
        $response->assertSee('Rapport officiel transmis');

        $sent->refresh();
        $this->assertEquals(ServiceReport::STATUT_LU, $sent->statut);
        $this->assertNotNull($sent->lu_le);
        $this->assertEquals($this->directionUser->id, $sent->lu_par);
    }

    /**
     * Test 5 : Un service ne voit que ses propres rapports.
     */
    public function test_service_only_sees_its_own_reports(): void
    {
        $report = ServiceReport::create([
            'service_profile_id' => $this->servicePedagogie->id,
            'site_id' => $this->siteCotonou->id,
            'auteur_id' => $this->responsableUser->id,
            'type' => 'mensuel',
            'periode_debut' => '2026-10-01',
            'periode_fin' => '2026-10-31',
            'titre' => 'Rapport Pédagogie',
            'resume' => 'Résumé',
            'activites' => 'Activités',
            'statut' => ServiceReport::STATUT_ENVOYE,
            'envoye_le' => now(),
        ]);

        // Un utilisateur du service finances tente de le consulter -> 403
        $this->actingAs($this->otherServiceUser)
            ->get(route('service.reports.show', $report))
            ->assertForbidden();
    }

    /**
     * Test 6 : Retour pour correction par la Direction avec commentaire, notification et réouverture à l'édition.
     */
    public function test_direction_can_return_report_for_correction_and_notifies_responsible(): void
    {
        $report = ServiceReport::create([
            'service_profile_id' => $this->servicePedagogie->id,
            'site_id' => $this->siteCotonou->id,
            'auteur_id' => $this->agentUser->id,
            'type' => 'mensuel',
            'periode_debut' => '2026-10-01',
            'periode_fin' => '2026-10-31',
            'titre' => 'Rapport incomplet',
            'resume' => 'Résumé succinct',
            'activites' => 'Activités',
            'statut' => ServiceReport::STATUT_ENVOYE,
            'envoye_le' => now(),
        ]);

        $returnResponse = $this->actingAs($this->directionUser)->post(route('direction.reports.return', $report), [
            'commentaire' => 'Merci de détailler les indicateurs de présence par classe.',
        ]);

        $returnResponse->assertRedirect();
        $report->refresh();

        $this->assertEquals(ServiceReport::STATUT_RETOURNE, $report->statut);
        $this->assertEquals('Merci de détailler les indicateurs de présence par classe.', $report->commentaire_direction);

        // Notification envoyée au responsable
        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->responsableUser->id,
            'type' => 'warning',
        ]);

        // Le rapport est de nouveau modifiable
        $this->assertTrue($report->isEditable());

        $editResponse = $this->actingAs($this->responsableUser)->put(route('service.reports.update', $report), [
            'site_id' => $this->siteCotonou->id,
            'type' => 'mensuel',
            'periode_debut' => '2026-10-01',
            'periode_fin' => '2026-10-31',
            'titre' => 'Rapport corrigé et enrichi',
            'resume' => 'Résumé corrigé',
            'activites' => 'Activités précisées avec succès',
        ]);

        $editResponse->assertRedirect();
        $this->assertEquals('Rapport corrigé et enrichi', $report->fresh()->titre);
    }

    /**
     * Test 7 : Unicité des rapports périodiques (service_profile_id, site_id, type, periode_debut).
     */
    public function test_periodic_report_uniqueness_is_enforced(): void
    {
        ServiceReport::create([
            'service_profile_id' => $this->servicePedagogie->id,
            'site_id' => $this->siteCotonou->id,
            'auteur_id' => $this->responsableUser->id,
            'type' => 'mensuel',
            'periode_debut' => '2026-10-01',
            'periode_fin' => '2026-10-31',
            'titre' => 'Premier rapport',
            'resume' => 'Résumé',
            'activites' => 'Activités',
            'statut' => ServiceReport::STATUT_BROUILLON,
        ]);

        // Tentative de créer un second rapport pour le même service, site, type et date de début
        $response = $this->actingAs($this->responsableUser)->post(route('service.reports.store'), [
            'service_profile_id' => $this->servicePedagogie->id,
            'site_id' => $this->siteCotonou->id,
            'type' => 'mensuel',
            'periode_debut' => '2026-10-01',
            'periode_fin' => '2026-10-31',
            'titre' => 'Doublon interdit',
            'resume' => 'Résumé',
            'activites' => 'Activités',
        ]);

        $response->assertSessionHasErrors('periode_debut');
    }

    /**
     * Test 8 : Commande planifiée quotidienne : rappel à J-2 et alertes de retard à la Direction.
     */
    public function test_deadlines_command_sends_reminders_and_alerts(): void
    {
        // 1. Test à J-2 de l'échéance du 5 (soit le 3 du mois) -> Rappel au responsable
        $this->artisan('reports:check-deadlines', ['--date' => '2026-11-03'])
            ->assertSuccessful();

        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->responsableUser->id,
            'type' => 'report',
        ]);

        // 2. Test après l'échéance du 5 (soit le 6 du mois) -> Alerte de retard à la Direction
        $this->artisan('reports:check-deadlines', ['--date' => '2026-11-06'])
            ->assertSuccessful();

        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->directionUser->id,
            'type' => 'warning',
        ]);
    }

    /**
     * Test 9 : Page Direction des rapports manquants.
     */
    public function test_direction_missing_reports_page(): void
    {
        $response = $this->actingAs($this->directionUser)
            ->get(route('direction.reports.missing', ['month' => '2026-09']));

        $response->assertOk();
        $response->assertSee('Services en défaut de rapport');
        $response->assertSee('pedagogie_etudes');
    }

    /**
     * Test 10 : Export PDF d'un rapport avec DomPDF.
     */
    public function test_direction_can_export_report_pdf(): void
    {
        $report = ServiceReport::create([
            'service_profile_id' => $this->servicePedagogie->id,
            'site_id' => $this->siteCotonou->id,
            'auteur_id' => $this->responsableUser->id,
            'type' => 'mensuel',
            'periode_debut' => '2026-10-01',
            'periode_fin' => '2026-10-31',
            'titre' => 'Rapport PDF test',
            'resume' => 'Résumé du rapport',
            'activites' => 'Activités détaillées',
            'statut' => ServiceReport::STATUT_ENVOYE,
            'envoye_le' => now(),
        ]);

        $response = $this->actingAs($this->directionUser)->get(route('direction.reports.pdf', $report));
        $response->assertOk();
        $this->assertTrue(str_contains($response->headers->get('content-type') ?? '', 'pdf') || str_contains($response->headers->get('content-disposition') ?? '', '.pdf'));
    }

    /**
     * Test 11 : Le responsable commercial peut rédiger et envoyer un rapport d'activité à la Direction.
     */
    public function test_responsable_commercial_can_create_and_send_report_to_direction(): void
    {
        $commercial = User::create([
            'first_name' => 'Commercial',
            'last_name' => 'IFAM',
            'email' => 'commercial.report@ifam.test',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);
        $commercial->assignRole('responsable_commercial');

        $commercialService = ServiceProfile::where('code', 'commercial')->firstOrFail();

        // 1. Accès à la liste des rapports et au formulaire
        $responseIndex = $this->actingAs($commercial)->get(route('service.reports.index'));
        $responseIndex->assertOk();

        $responseCreate = $this->actingAs($commercial)->get(route('service.reports.create'));
        $responseCreate->assertOk();
        $responseCreate->assertSee('Commercial');

        // 2. Création et transmission directe à la Direction
        $responseStore = $this->actingAs($commercial)->post(route('service.reports.store'), [
            'service_profile_id' => $commercialService->id,
            'site_id' => $this->siteCotonou->id,
            'type' => 'mensuel',
            'periode_debut' => '2026-10-01',
            'periode_fin' => '2026-10-31',
            'titre' => 'Rapport commercial mensuel des inscriptions',
            'resume' => 'Bilan des inscriptions et objectifs atteints',
            'activites' => 'Prospection, salons et validation des inscriptions transversales',
            'difficultes' => 'Aucune difficulté majeure',
            'besoins' => 'Supports de communication supplémentaires',
            'action' => 'send',
        ]);

        $responseStore->assertRedirect(route('service.reports.index'));
        $responseStore->assertSessionHas('success');

        // 3. Vérification en base de données
        $this->assertDatabaseHas('service_reports', [
            'service_profile_id' => $commercialService->id,
            'titre' => 'Rapport commercial mensuel des inscriptions',
            'statut' => ServiceReport::STATUT_ENVOYE,
        ]);

        // 4. Notification transmise à la Direction
        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->directionUser->id,
            'type' => 'report',
        ]);
    }
}
