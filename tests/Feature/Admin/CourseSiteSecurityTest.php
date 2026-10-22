<?php

namespace Tests\Feature\Admin;

use App\Models\AcademicSession;
use App\Models\Course;
use App\Models\Formation;
use App\Models\Module;
use App\Models\Site;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Database\Seeders\SiteSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CourseSiteSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected Site $cotonou;

    protected Site $bohicon;

    protected User $superAdmin;

    protected User $cotonouAdmin;

    protected User $bohiconAdmin;

    protected AcademicSession $cotonouSession;

    protected AcademicSession $bohiconSession;

    protected Formation $formation;

    protected Module $module;

    protected Course $cotonouCourse;

    protected Course $bohiconCourse;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(SiteSeeder::class);
        $this->seed(RoleSeeder::class);

        $this->cotonou = Site::where('nom', 'Cotonou')->firstOrFail();
        $this->bohicon = Site::create(['nom' => 'Bohicon', 'ville' => 'Bohicon', 'est_actif' => true]);

        $this->superAdmin = User::create(['first_name' => 'Super', 'last_name' => 'Admin', 'email' => 'superadmin.course@ifam.test', 'password' => Hash::make('Password123!'), 'is_active' => true]);
        $this->superAdmin->assignRole('administrateur');

        $this->cotonouAdmin = User::create(['first_name' => 'Admin', 'last_name' => 'Cotonou', 'email' => 'admin.cotonou.course@ifam.test', 'password' => Hash::make('Password123!'), 'is_active' => true]);
        $this->cotonouAdmin->assignRole('responsable_site');
        $this->cotonouAdmin->sites()->attach($this->cotonou->id);

        $this->bohiconAdmin = User::create(['first_name' => 'Admin', 'last_name' => 'Bohicon', 'email' => 'admin.bohicon.course@ifam.test', 'password' => Hash::make('Password123!'), 'is_active' => true]);
        $this->bohiconAdmin->assignRole('responsable_site');
        $this->bohiconAdmin->sites()->attach($this->bohicon->id);

        $this->cotonouSession = AcademicSession::create(['name' => 'Session Cotonou 2026', 'site_id' => $this->cotonou->id, 'start_date' => '2026-09-01', 'end_date' => '2027-06-30', 'status' => 'en_cours']);
        $this->bohiconSession = AcademicSession::create(['name' => 'Session Bohicon 2026', 'site_id' => $this->bohicon->id, 'start_date' => '2026-09-01', 'end_date' => '2027-06-30', 'status' => 'en_cours']);

        $this->formation = Formation::create(['name' => 'Gestion de Projet', 'is_active' => true]);
        $this->module = Module::create(['name' => 'Introduction GP', 'code' => 'GP101']);
        $this->formation->modules()->attach($this->module->id);

        $this->cotonouCourse = Course::create(['formation_id' => $this->formation->id, 'academic_session_id' => $this->cotonouSession->id, 'module_id' => $this->module->id, 'statut' => 'en_cours']);
        $this->bohiconCourse = Course::create(['formation_id' => $this->formation->id, 'academic_session_id' => $this->bohiconSession->id, 'module_id' => $this->module->id, 'statut' => 'en_cours']);
    }

    /**
     * Le responsable de site ne voit dans l'index que les cours de son site.
     */
    public function test_responsable_site_sees_only_courses_from_managed_site_in_index(): void
    {
        $response = $this->actingAs($this->cotonouAdmin)->get(route('admin.courses.index'));
        $response->assertOk();

        $courses = $response->viewData('courses');
        $this->assertSame(1, $courses->total());
        $this->assertTrue($courses->contains($this->cotonouCourse));
        $this->assertFalse($courses->contains($this->bohiconCourse));
    }

    public function test_responsable_daa_can_filter_course_index_by_an_assigned_site(): void
    {
        $responsableDaa = User::create([
            'first_name' => 'Responsable',
            'last_name' => 'DAA',
            'email' => 'responsable.daa.course@ifam.test',
            'password' => Hash::make('Password123!'),
            'is_active' => true,
        ]);
        $responsableDaa->assignRole('responsable_daa');
        $responsableDaa->sites()->attach([$this->cotonou->id, $this->bohicon->id]);

        $response = $this->actingAs($responsableDaa)
            ->get(route('admin.courses.index', ['site_id' => $this->bohicon->id]));

        $response->assertOk()
            ->assertSee('Filtrer les cours par site')
            ->assertSee('Session Bohicon 2026')
            ->assertDontSee('Session Cotonou 2026');
        $this->assertSame(1, $response->viewData('courses')->total());
        $this->assertSame($this->bohicon->id, $response->viewData('selectedSiteId'));

        $thirdSite = Site::create(['nom' => 'Parakou', 'ville' => 'Parakou', 'est_actif' => true]);
        $this->actingAs($responsableDaa)
            ->get(route('admin.courses.index', ['site_id' => $thirdSite->id]))
            ->assertForbidden();
    }

    public function test_responsable_etudes_can_filter_courses_by_formation_and_module_on_the_index_page(): void
    {
        $secondFormation = Formation::create(['name' => 'Comptabilité', 'is_active' => true]);
        $secondModule = Module::create(['name' => 'Comptabilité générale', 'code' => 'CG101']);
        $secondFormation->modules()->attach($secondModule->id);
        $secondCourse = Course::create([
            'formation_id' => $secondFormation->id,
            'academic_session_id' => $this->cotonouSession->id,
            'module_id' => $secondModule->id,
            'statut' => 'en_cours',
        ]);

        $responsableEtudes = User::create([
            'first_name' => 'Responsable',
            'last_name' => 'Études',
            'email' => 'responsable.etudes.course@ifam.test',
            'password' => Hash::make('Password123!'),
            'is_active' => true,
        ]);
        $responsableEtudes->assignRole('responsable_etudes');
        $responsableEtudes->sites()->attach($this->cotonou->id);

        $formationResponse = $this->actingAs($responsableEtudes)
            ->get(route('admin.courses.index', ['formation_id' => $secondFormation->id]));

        $formationResponse->assertOk()
            ->assertSee('Toutes les formations')
            ->assertSee('Tous les modules')
            ->assertDontSee('Filtrer les cours par site');
        $this->assertSame(1, $formationResponse->viewData('courses')->total());
        $this->assertTrue($formationResponse->viewData('courses')->contains($secondCourse));

        $moduleResponse = $this->get(route('admin.courses.index', ['module_id' => $this->module->id]));
        $this->assertSame(1, $moduleResponse->viewData('courses')->total());
        $this->assertTrue($moduleResponse->viewData('courses')->contains($this->cotonouCourse));

        $combinedResponse = $this->get(route('admin.courses.index', [
            'formation_id' => $secondFormation->id,
            'module_id' => $this->module->id,
        ]));
        $this->assertSame(0, $combinedResponse->viewData('courses')->total());
    }

    /**
     * La consultation directe (show) d'un cours d'un autre site est refusee avec HTTP 403.
     */
    public function test_responsable_site_cannot_view_course_from_another_site(): void
    {
        $this->actingAs($this->cotonouAdmin)
            ->get(route('admin.courses.show', $this->bohiconCourse))
            ->assertForbidden();
    }

    /**
     * L'acces au formulaire d'edition (edit) d'un cours d'un autre site est refuse avec HTTP 403.
     */
    public function test_responsable_site_cannot_edit_course_from_another_site(): void
    {
        $this->actingAs($this->cotonouAdmin)
            ->get(route('admin.courses.edit', $this->bohiconCourse))
            ->assertForbidden();
    }

    /**
     * La mise a jour (update) d'un cours d'un autre site est refusee avec HTTP 403 sans alteration.
     */
    public function test_responsable_site_cannot_update_course_from_another_site(): void
    {
        $original = $this->bohiconCourse->statut;

        $this->actingAs($this->cotonouAdmin)
            ->put(route('admin.courses.update', $this->bohiconCourse), [
                'formation_id' => $this->formation->id,
                'academic_session_id' => $this->bohiconSession->id,
                'module_id' => $this->module->id,
                'statut' => 'termine',
            ])
            ->assertForbidden();

        $this->bohiconCourse->refresh();
        $this->assertSame($original, $this->bohiconCourse->statut);
    }

    /**
     * La mise a jour de statut (updateStatus) d'un cours d'un autre site est refusee avec HTTP 403.
     */
    public function test_responsable_site_cannot_update_status_of_course_from_another_site(): void
    {
        $this->actingAs($this->cotonouAdmin)
            ->patch(route('admin.courses.status', $this->bohiconCourse), ['statut' => 'termine'])
            ->assertForbidden();

        $this->bohiconCourse->refresh();
        $this->assertSame('en_cours', $this->bohiconCourse->statut);
    }

    /**
     * La suppression (destroy) d'un cours d'un autre site est refusee avec HTTP 403.
     */
    public function test_responsable_site_cannot_delete_course_from_another_site(): void
    {
        $this->actingAs($this->cotonouAdmin)
            ->delete(route('admin.courses.destroy', $this->bohiconCourse))
            ->assertForbidden();

        $this->assertDatabaseHas('courses', ['id' => $this->bohiconCourse->id]);
    }

    /**
     * Un responsable de site peut consulter et modifier un cours de son propre site.
     */
    public function test_responsable_site_can_view_and_update_course_of_own_site(): void
    {
        $this->actingAs($this->cotonouAdmin)
            ->get(route('admin.courses.show', $this->cotonouCourse))
            ->assertOk();

        $this->actingAs($this->cotonouAdmin)
            ->get(route('admin.courses.edit', $this->cotonouCourse))
            ->assertOk();

        $this->actingAs($this->cotonouAdmin)
            ->put(route('admin.courses.update', $this->cotonouCourse), [
                'formation_id' => $this->formation->id,
                'academic_session_id' => $this->cotonouSession->id,
                'module_id' => $this->module->id,
                'statut' => 'termine',
            ])
            ->assertRedirect(route('admin.courses.index'));

        $this->cotonouCourse->refresh();
        $this->assertSame('termine', $this->cotonouCourse->statut);
    }

    /**
     * Un responsable ne peut pas changer la session d'un cours vers un site hors de son perimetre.
     */
    public function test_responsable_site_cannot_reassign_course_to_session_of_another_site(): void
    {
        $this->actingAs($this->cotonouAdmin)
            ->put(route('admin.courses.update', $this->cotonouCourse), [
                'formation_id' => $this->formation->id,
                'academic_session_id' => $this->bohiconSession->id,
                'module_id' => $this->module->id,
                'statut' => 'en_cours',
            ])
            ->assertForbidden();

        $this->cotonouCourse->refresh();
        $this->assertSame($this->cotonouSession->id, $this->cotonouCourse->academic_session_id);
    }

    /**
     * Un responsable ne peut pas creer un cours avec une session hors de son perimetre.
     */
    public function test_responsable_site_cannot_store_course_with_session_from_another_site(): void
    {
        $this->actingAs($this->cotonouAdmin)
            ->post(route('admin.courses.store'), [
                'formation_id' => $this->formation->id,
                'academic_session_id' => $this->bohiconSession->id,
                'module_id' => $this->module->id,
                'statut' => 'en_cours',
            ])
            ->assertForbidden();

        $this->assertSame(1, Course::where('academic_session_id', $this->bohiconSession->id)->count());
    }

    /**
     * L'administrateur general a acces global a tous les cours.
     */
    public function test_superadmin_has_global_access_to_all_courses(): void
    {
        $r = $this->actingAs($this->superAdmin)->get(route('admin.courses.index'));
        $r->assertOk();
        $this->assertSame(2, $r->viewData('courses')->total());

        $this->actingAs($this->superAdmin)->get(route('admin.courses.show', $this->cotonouCourse))->assertOk();
        $this->actingAs($this->superAdmin)->get(route('admin.courses.show', $this->bohiconCourse))->assertOk();
    }
}
