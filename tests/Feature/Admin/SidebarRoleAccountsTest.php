<?php

namespace Tests\Feature\Admin;

use App\Models\Site;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Database\Seeders\SiteSeeder;
use Database\Seeders\TestAccountsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SidebarRoleAccountsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(SiteSeeder::class);
        $this->seed(RoleSeeder::class);
        $this->seed(TestAccountsSeeder::class);
    }

    /**
     * Vérifie que tous les comptes de test peuvent se connecter avec le mot de passe unifié.
     */
    public function test_all_test_accounts_can_authenticate_with_default_password(): void
    {
        $emails = [
            'superadmin@ifam.bj',
            'admin.cotonou@ifam.bj',
            'admin.portonovo@ifam.bj',
            'admin.bohicon@ifam.bj',
            'dg.daf@ifam.bj',
            'raf.cotonou@ifam.bj',
            'resp.admin.cotonou@ifam.bj',
            'resp.finance.cotonou@ifam.bj',
            'comptable.cotonou@ifam.bj',
            'caissier.cotonou@ifam.bj',
            'dg.daa@ifam.bj',
            'resp.daa.cotonou@ifam.bj',
            'resp.etudes.cotonou@ifam.bj',
            'resp.insertion.cotonou@ifam.bj',
        ];

        foreach ($emails as $email) {
            $user = User::where('email', $email)->first();
            $this->assertNotNull($user, "L'utilisateur $email doit exister.");

            $response = $this->post(route('login.post'), [
                'email' => $email,
                'password' => TestAccountsSeeder::DEFAULT_PASSWORD,
            ]);

            $response->assertRedirect(route('admin.dashboard'));
            $this->assertAuthenticatedAs($user);

            auth()->logout();
        }
    }

    /**
     * Vérifie le badge et la sidebar affichée pour chaque type de profil.
     */
    public function test_sidebar_badges_and_sections_per_role(): void
    {
        // 1. SuperAdmin
        $superAdmin = User::where('email', 'superadmin@ifam.bj')->firstOrFail();
        $response = $this->actingAs($superAdmin)->get(route('admin.dashboard'));
        $response->assertOk();
        $response->assertSee('SuperAdmin');
        $response->assertSee('Administration Globale');
        $response->assertSee('Sites IFAM');

        // 2. Admin Site (Cotonou)
        $adminCotonou = User::where('email', 'admin.cotonou@ifam.bj')->firstOrFail();
        $response = $this->actingAs($adminCotonou)->get(route('admin.dashboard'));
        $response->assertOk();
        $response->assertSee('Admin Site');
        $response->assertSee('Périmètre Local (Cotonou)');

        // 3. DG DAF
        $dgDaf = User::where('email', 'dg.daf@ifam.bj')->firstOrFail();
        $response = $this->actingAs($dgDaf)->get(route('admin.dashboard'));
        $response->assertOk();
        $response->assertSee('DG DAF');
        $response->assertSee('Supervision DAF Globale');

        // 4. RAF Cotonou
        $raf = User::where('email', 'raf.cotonou@ifam.bj')->firstOrFail();
        $response = $this->actingAs($raf)->get(route('admin.dashboard'));
        $response->assertOk();
        $response->assertSee('RAF');
        $response->assertSee('DAF Site (Cotonou)');

        // 5. Service Administratif
        $respAdmin = User::where('email', 'resp.admin.cotonou@ifam.bj')->firstOrFail();
        $response = $this->actingAs($respAdmin)->get(route('admin.dashboard'));
        $response->assertOk();
        $response->assertSee('Svc Admin');
        $response->assertSee('Admin Scolarité (Cotonou)');
        $response->assertSee('Dossiers étudiants');

        // 6. Service Financier
        $respFin = User::where('email', 'resp.finance.cotonou@ifam.bj')->firstOrFail();
        $response = $this->actingAs($respFin)->get(route('admin.dashboard'));
        $response->assertOk();
        $response->assertSee('Svc Finance');
        $response->assertSee('Finance & Caisse (Cotonou)');
        $response->assertSee('Paiements & Encaissements');

        // 7. DG DAA
        $dgDaa = User::where('email', 'dg.daa@ifam.bj')->firstOrFail();
        $response = $this->actingAs($dgDaa)->get(route('admin.dashboard'));
        $response->assertOk();
        $response->assertSee('DG DAA');
        $response->assertSee('Affaires Académiques — Global');
        $response->assertSee('Catalogue Commun');

        // 8. Responsable DAA Cotonou
        $respDaa = User::where('email', 'resp.daa.cotonou@ifam.bj')->firstOrFail();
        $response = $this->actingAs($respDaa)->get(route('admin.dashboard'));
        $response->assertOk();
        $response->assertSee('Resp. DAA');
        $response->assertSee('DAA (Cotonou)');
        $response->assertSee('Pédagogie du site');
        $response->assertSee('Insertion du site');

        // 9. Responsable Études Cotonou
        $respEtudes = User::where('email', 'resp.etudes.cotonou@ifam.bj')->firstOrFail();
        $response = $this->actingAs($respEtudes)->get(route('admin.dashboard'));
        $response->assertOk();
        $response->assertSee('Svc Études');
        $response->assertSee('Pédagogie (Cotonou)');
        $response->assertSee('Gestion pédagogique');
        $response->assertDontSee('Insertion du site');

        // 10. Responsable Insertion Cotonou
        $respInsertion = User::where('email', 'resp.insertion.cotonou@ifam.bj')->firstOrFail();
        $response = $this->actingAs($respInsertion)->get(route('admin.dashboard'));
        $response->assertOk();
        $response->assertSee('Svc Insertion');
        $response->assertSee('Insertion Professionnelle');
        $response->assertSee('Partenariats');
        $response->assertDontSee('Gestion pédagogique');
    }
}
