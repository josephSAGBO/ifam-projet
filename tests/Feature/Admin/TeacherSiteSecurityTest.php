<?php

namespace Tests\Feature\Admin;

use App\Models\Site;
use App\Models\TeacherProfile;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Database\Seeders\SiteSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TeacherSiteSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected Site $cotonou;

    protected Site $bohicon;

    protected User $superAdmin;

    protected User $cotonouAdmin;

    protected User $bohiconAdmin;

    protected User $cotonouTeacher;

    protected User $bohiconTeacher;

    protected User $unassignedTeacher;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(SiteSeeder::class);
        $this->seed(RoleSeeder::class);

        $this->cotonou = Site::where('nom', 'Cotonou')->firstOrFail();
        $this->bohicon = Site::create(['nom' => 'Bohicon', 'ville' => 'Bohicon', 'est_actif' => true]);

        $this->superAdmin = User::create(['first_name' => 'Super', 'last_name' => 'Admin', 'email' => 'superadmin.teacher@ifam.test', 'password' => Hash::make('Password123!'), 'is_active' => true]);
        $this->superAdmin->assignRole('administrateur');

        $this->cotonouAdmin = User::create(['first_name' => 'Admin', 'last_name' => 'Cotonou', 'email' => 'admin.cotonou.teacher@ifam.test', 'password' => Hash::make('Password123!'), 'is_active' => true]);
        $this->cotonouAdmin->assignRole('responsable_site');
        $this->cotonouAdmin->sites()->attach($this->cotonou->id);

        $this->bohiconAdmin = User::create(['first_name' => 'Admin', 'last_name' => 'Bohicon', 'email' => 'admin.bohicon.teacher@ifam.test', 'password' => Hash::make('Password123!'), 'is_active' => true]);
        $this->bohiconAdmin->assignRole('responsable_site');
        $this->bohiconAdmin->sites()->attach($this->bohicon->id);

        $this->cotonouTeacher = User::create(['first_name' => 'Koffi', 'last_name' => 'Cotonou', 'email' => 'koffi.cotonou@ifam.test', 'password' => Hash::make('Password123!'), 'is_active' => true]);
        $this->cotonouTeacher->assignRole('enseignant');
        $this->cotonouTeacher->sites()->attach($this->cotonou->id);
        TeacherProfile::create(['user_id' => $this->cotonouTeacher->id]);

        $this->bohiconTeacher = User::create(['first_name' => 'Adjoua', 'last_name' => 'Bohicon', 'email' => 'adjoua.bohicon@ifam.test', 'password' => Hash::make('Password123!'), 'is_active' => true]);
        $this->bohiconTeacher->assignRole('enseignant');
        $this->bohiconTeacher->sites()->attach($this->bohicon->id);
        TeacherProfile::create(['user_id' => $this->bohiconTeacher->id]);

        $this->unassignedTeacher = User::create(['first_name' => 'Orphelin', 'last_name' => 'SansSite', 'email' => 'orphelin.teacher@ifam.test', 'password' => Hash::make('Password123!'), 'is_active' => true]);
        $this->unassignedTeacher->assignRole('enseignant');
        TeacherProfile::create(['user_id' => $this->unassignedTeacher->id]);
    }

    /**
     * Le responsable de site ne voit dans l'index que les enseignants de son site.
     */
    public function test_responsable_site_sees_only_teachers_from_managed_site_in_index(): void
    {
        $response = $this->actingAs($this->cotonouAdmin)->get(route('admin.teachers.index'));

        $response->assertOk();
        $response->assertSee('Koffi');

        $teachers = $response->viewData('teachers');
        $this->assertSame(1, $teachers->total());
        $this->assertTrue($teachers->contains($this->cotonouTeacher));
        $this->assertFalse($teachers->contains($this->bohiconTeacher));
        $this->assertFalse($teachers->contains($this->unassignedTeacher));
    }

    /**
     * La recherche est strictement cloisonnee au site gere : aucun resultat hors perimetre.
     */
    public function test_search_is_strictly_scoped_to_managed_site(): void
    {
        // Recherche du nom d'un enseignant de l'autre site : zero resultats
        $r = $this->actingAs($this->cotonouAdmin)
            ->get(route('admin.teachers.index', ['search' => 'adjoua.bohicon@ifam.test']));
        $r->assertOk();
        $this->assertSame(0, $r->viewData('teachers')->total());

        // Recherche de l'email de son propre enseignant : un resultat
        $r = $this->actingAs($this->cotonouAdmin)
            ->get(route('admin.teachers.index', ['search' => 'koffi.cotonou@ifam.test']));
        $r->assertOk();
        $this->assertSame(1, $r->viewData('teachers')->total());
        $this->assertTrue($r->viewData('teachers')->contains($this->cotonouTeacher));
    }

    /**
     * La consultation directe (show) d'un enseignant d'un autre site est refusee avec HTTP 403.
     */
    public function test_responsable_site_cannot_view_teacher_from_another_site(): void
    {
        $this->actingAs($this->cotonouAdmin)
            ->get(route('admin.teachers.show', $this->bohiconTeacher))
            ->assertForbidden();
    }

    /**
     * L'acces au formulaire d'edition (edit) d'un enseignant d'un autre site est refuse avec HTTP 403.
     */
    public function test_responsable_site_cannot_edit_teacher_from_another_site(): void
    {
        $this->actingAs($this->cotonouAdmin)
            ->get(route('admin.teachers.edit', $this->bohiconTeacher))
            ->assertForbidden();
    }

    /**
     * La mise a jour (update) d'un enseignant d'un autre site est refusee sans alteration de donnees.
     */
    public function test_responsable_site_cannot_update_teacher_from_another_site(): void
    {
        $profile = $this->bohiconTeacher->teacherProfile;
        $original = $profile->qualification;

        $this->actingAs($this->cotonouAdmin)
            ->put(route('admin.teachers.update', $this->bohiconTeacher), ['qualification' => 'Pirate'])
            ->assertForbidden();

        $profile->refresh();
        $this->assertSame($original, $profile->qualification);
    }

    /**
     * Un responsable de site peut consulter et modifier un enseignant de son propre site.
     */
    public function test_responsable_site_can_view_and_update_teacher_of_own_site(): void
    {
        $this->actingAs($this->cotonouAdmin)
            ->get(route('admin.teachers.show', $this->cotonouTeacher))
            ->assertOk()
            ->assertSee('Koffi');

        $this->actingAs($this->cotonouAdmin)
            ->get(route('admin.teachers.edit', $this->cotonouTeacher))
            ->assertOk();

        $this->actingAs($this->cotonouAdmin)
            ->put(route('admin.teachers.update', $this->cotonouTeacher), [
                'qualification' => 'Docteur en Sciences',
                'teacher_type' => 'permanent',
            ])
            ->assertRedirect(route('admin.teachers.show', $this->cotonouTeacher));

        $this->cotonouTeacher->teacherProfile->refresh();
        $this->assertSame('Docteur en Sciences', $this->cotonouTeacher->teacherProfile->qualification);
    }

    /**
     * Un enseignant sans site est strictement inaccessible au responsable local.
     */
    public function test_teacher_without_site_is_strictly_forbidden_to_local_admin(): void
    {
        $indexResponse = $this->actingAs($this->cotonouAdmin)->get(route('admin.teachers.index'));
        $indexResponse->assertOk();
        $this->assertFalse($indexResponse->viewData('teachers')->contains($this->unassignedTeacher));

        $this->actingAs($this->cotonouAdmin)
            ->get(route('admin.teachers.show', $this->unassignedTeacher))
            ->assertForbidden();

        $this->actingAs($this->cotonouAdmin)
            ->get(route('admin.teachers.edit', $this->unassignedTeacher))
            ->assertForbidden();

        $this->actingAs($this->cotonouAdmin)
            ->put(route('admin.teachers.update', $this->unassignedTeacher), ['qualification' => 'X'])
            ->assertForbidden();
    }

    /**
     * L'administrateur general conserve un acces global a tous les enseignants.
     */
    public function test_superadmin_has_global_access_to_all_teachers(): void
    {
        $r = $this->actingAs($this->superAdmin)->get(route('admin.teachers.index'));
        $r->assertOk();
        $this->assertSame(3, $r->viewData('teachers')->total());

        $this->actingAs($this->superAdmin)->get(route('admin.teachers.show', $this->cotonouTeacher))->assertOk();
        $this->actingAs($this->superAdmin)->get(route('admin.teachers.show', $this->bohiconTeacher))->assertOk();
        $this->actingAs($this->superAdmin)->get(route('admin.teachers.show', $this->unassignedTeacher))->assertOk();
    }

    /**
     * L'administrateur general peut filtrer la liste par site.
     */
    public function test_superadmin_can_filter_teachers_by_site(): void
    {
        $r = $this->actingAs($this->superAdmin)
            ->get(route('admin.teachers.index', ['site_id' => $this->cotonou->id]));
        $r->assertOk();
        $this->assertSame(1, $r->viewData('teachers')->total());
        $this->assertTrue($r->viewData('teachers')->contains($this->cotonouTeacher));
        $this->assertFalse($r->viewData('teachers')->contains($this->bohiconTeacher));
    }
}
