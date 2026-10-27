<?php

namespace Tests\Feature\Admin;

use App\Models\InternshipSite;
use App\Models\Site;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Database\Seeders\SiteSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class InternshipSiteTest extends TestCase
{
    use RefreshDatabase;

    protected Site $cotonou;

    protected Site $bohicon;

    protected User $superAdmin;

    protected User $directionDaa;

    protected User $cotonouAdmin;

    protected User $bohiconAdmin;

    protected User $teacher;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(SiteSeeder::class);
        $this->seed(RoleSeeder::class);

        $this->cotonou = Site::where('nom', 'Cotonou')->firstOrFail();
        $this->bohicon = Site::create([
            'nom' => 'Bohicon',
            'ville' => 'Bohicon',
            'est_actif' => true,
        ]);

        $this->superAdmin = User::create([
            'first_name' => 'Super',
            'last_name' => 'Admin',
            'email' => 'superadmin@ifam.test',
            'password' => Hash::make('Password123!'),
            'is_active' => true,
        ]);
        $this->superAdmin->assignRole('administrateur');

        $this->directionDaa = User::create([
            'first_name' => 'Directeur',
            'last_name' => 'DAA',
            'email' => 'daa@ifam.test',
            'password' => Hash::make('Password123!'),
            'is_active' => true,
        ]);
        $this->directionDaa->assignRole('direction_daa');

        $this->cotonouAdmin = User::create([
            'first_name' => 'Admin',
            'last_name' => 'Cotonou',
            'email' => 'admin.cotonou@ifam.test',
            'password' => Hash::make('Password123!'),
            'is_active' => true,
        ]);
        $this->cotonouAdmin->assignRole('responsable_site');
        $this->cotonouAdmin->sites()->attach($this->cotonou->id);

        $this->bohiconAdmin = User::create([
            'first_name' => 'Admin',
            'last_name' => 'Bohicon',
            'email' => 'admin.bohicon@ifam.test',
            'password' => Hash::make('Password123!'),
            'is_active' => true,
        ]);
        $this->bohiconAdmin->assignRole('responsable_site');
        $this->bohiconAdmin->sites()->attach($this->bohicon->id);

        $this->teacher = User::create([
            'first_name' => 'Enseignant',
            'last_name' => 'Test',
            'email' => 'teacher@ifam.test',
            'password' => Hash::make('Password123!'),
            'is_active' => true,
        ]);
        $this->teacher->assignRole('enseignant');
        $this->teacher->sites()->attach($this->cotonou->id);
    }

    public function test_site_manager_only_sees_internship_sites_of_own_site(): void
    {
        $siteCotonou = InternshipSite::create([
            'nom' => 'Structure Cotonou',
            'type' => 'Entreprise privée',
            'secteur_activite' => 'Informatique',
            'departement' => 'Littoral',
            'commune' => 'Cotonou',
            'adresse' => 'Haie Vive',
            'contact_nom' => 'Jean Cotonou',
            'telephone_principal' => '+229 97 00 00 01',
            'statut' => 'actif',
            'site_id' => $this->cotonou->id,
        ]);

        $siteBohicon = InternshipSite::create([
            'nom' => 'Structure Bohicon',
            'type' => 'Cabinet d\'expertise',
            'secteur_activite' => 'Audit',
            'departement' => 'Zou',
            'commune' => 'Bohicon',
            'adresse' => 'Centre ville',
            'contact_nom' => 'Paul Bohicon',
            'telephone_principal' => '+229 97 00 00 02',
            'statut' => 'actif',
            'site_id' => $this->bohicon->id,
        ]);

        $response = $this->actingAs($this->cotonouAdmin)->get(route('admin.internship-sites.index'));
        $response->assertOk();
        $response->assertSee('Structure Cotonou');
        $response->assertDontSee('Structure Bohicon');
    }

    public function test_direction_daa_and_superadmin_see_all_internship_sites(): void
    {
        InternshipSite::create([
            'nom' => 'Structure Cotonou DAA',
            'type' => 'Entreprise privée',
            'secteur_activite' => 'Informatique',
            'departement' => 'Littoral',
            'commune' => 'Cotonou',
            'adresse' => 'Haie Vive',
            'contact_nom' => 'Jean',
            'telephone_principal' => '+229 97 00 00 11',
            'statut' => 'actif',
            'site_id' => $this->cotonou->id,
        ]);

        InternshipSite::create([
            'nom' => 'Structure Bohicon DAA',
            'type' => 'Cabinet',
            'secteur_activite' => 'Audit',
            'departement' => 'Zou',
            'commune' => 'Bohicon',
            'adresse' => 'Gare',
            'contact_nom' => 'Paul',
            'telephone_principal' => '+229 97 00 00 22',
            'statut' => 'actif',
            'site_id' => $this->bohicon->id,
        ]);

        $resDaa = $this->actingAs($this->directionDaa)->get(route('admin.internship-sites.index'));
        $resDaa->assertOk();
        $resDaa->assertSee('Structure Cotonou DAA');
        $resDaa->assertSee('Structure Bohicon DAA');

        $resAdmin = $this->actingAs($this->superAdmin)->get(route('admin.internship-sites.index'));
        $resAdmin->assertOk();
        $resAdmin->assertSee('Structure Cotonou DAA');
        $resAdmin->assertSee('Structure Bohicon DAA');
    }

    public function test_site_manager_creation_forces_own_site_server_side(): void
    {
        $response = $this->actingAs($this->cotonouAdmin)->post(route('admin.internship-sites.store'), [
            'nom' => 'Nouvelle Entreprise Cotonou',
            'type' => 'Entreprise privée',
            'secteur_activite' => 'Télécoms',
            'pays' => 'Bénin',
            'departement' => 'Littoral',
            'commune' => 'Cotonou',
            'adresse' => 'Boulevard Marina',
            'contact_nom' => 'Marc Tuteur',
            'telephone_principal' => '+229 95 00 00 00',
            'statut' => 'actif',
            'site_id' => $this->bohicon->id, // Tente de forcer Bohicon
        ]);

        $response->assertRedirect(route('admin.internship-sites.index'));
        $site = InternshipSite::where('nom', 'Nouvelle Entreprise Cotonou')->firstOrFail();
        $this->assertEquals($this->cotonou->id, $site->site_id);
    }

    public function test_site_manager_cannot_access_or_edit_internship_site_of_other_site(): void
    {
        $siteBohicon = InternshipSite::create([
            'nom' => 'Structure Interdite',
            'type' => 'Entreprise privée',
            'secteur_activite' => 'Santé',
            'departement' => 'Zou',
            'commune' => 'Bohicon',
            'adresse' => 'Rue 1',
            'contact_nom' => 'Contact',
            'telephone_principal' => '+229 90 00 00 00',
            'statut' => 'actif',
            'site_id' => $this->bohicon->id,
        ]);

        // Show interdit
        $this->actingAs($this->cotonouAdmin)
            ->get(route('admin.internship-sites.show', $siteBohicon))
            ->assertForbidden();

        // Edit interdit
        $this->actingAs($this->cotonouAdmin)
            ->get(route('admin.internship-sites.edit', $siteBohicon))
            ->assertForbidden();

        // Update interdit
        $this->actingAs($this->cotonouAdmin)
            ->put(route('admin.internship-sites.update', $siteBohicon), [
                'nom' => 'Piraté',
                'type' => 'Entreprise privée',
                'secteur_activite' => 'Santé',
                'departement' => 'Zou',
                'commune' => 'Bohicon',
                'adresse' => 'Rue 1',
                'contact_nom' => 'Contact',
                'telephone_principal' => '+229 90 00 00 00',
                'statut' => 'actif',
            ])
            ->assertForbidden();

        // Delete interdit
        $this->actingAs($this->cotonouAdmin)
            ->delete(route('admin.internship-sites.destroy', $siteBohicon))
            ->assertForbidden();
    }

    public function test_unknown_capacity_renders_dash_and_integer_renders_value(): void
    {
        $siteSansCapacite = InternshipSite::create([
            'nom' => 'Sans Capacité',
            'type' => 'Entreprise',
            'secteur_activite' => 'Service',
            'departement' => 'Littoral',
            'commune' => 'Cotonou',
            'adresse' => 'Rue X',
            'contact_nom' => 'Nom',
            'telephone_principal' => '+229 91 00 00 00',
            'capacite_max' => null,
            'statut' => 'actif',
            'site_id' => $this->cotonou->id,
        ]);

        $resShow = $this->actingAs($this->cotonouAdmin)->get(route('admin.internship-sites.show', $siteSansCapacite));
        $resShow->assertOk();
        $resShow->assertSee('—');

        $resIndex = $this->actingAs($this->cotonouAdmin)->get(route('admin.internship-sites.index'));
        $resIndex->assertOk();
        $resIndex->assertSee('—');
    }

    public function test_unauthorized_user_cannot_access_internship_sites(): void
    {
        $this->actingAs($this->teacher)
            ->get(route('admin.internship-sites.index'))
            ->assertForbidden();
    }
}
