<?php

namespace Tests\Feature;

use App\Models\AcademicSession;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AcademicSessionTest extends TestCase
{
    use RefreshDatabase;

    public function test_creating_a_current_session_keeps_other_current_sessions_active(): void
    {
        $admin = $this->createAdmin();
        $previousSession = $this->createSession('Session précédente', true);

        $response = $this->actingAs($admin)->post(route('admin.sessions.store'), [
            'name' => 'Nouvelle session',
            'start_date' => '2027-09-01',
            'end_date' => '2028-06-30',
            'status' => 'en_cours',
            'is_current' => '1',
        ]);

        $response->assertRedirect(route('admin.sessions.index'));
        $this->assertDatabaseHas('academic_sessions', [
            'id' => $previousSession->id,
            'is_current' => true,
        ]);
        $this->assertDatabaseHas('academic_sessions', [
            'name' => 'Nouvelle session',
            'is_current' => true,
        ]);
        $this->assertSame(2, AcademicSession::where('is_current', true)->count());
    }

    public function test_activating_a_session_keeps_other_current_sessions_active(): void
    {
        $admin = $this->createAdmin();
        $currentSession = $this->createSession('Session actuelle', true);
        $nextSession = $this->createSession('Session suivante', false);

        $response = $this->actingAs($admin)->put(route('admin.sessions.update', $nextSession), [
            'name' => 'Session suivante',
            'start_date' => '2027-09-01',
            'end_date' => '2028-06-30',
            'status' => 'en_cours',
            'is_current' => '1',
        ]);

        $response->assertRedirect(route('admin.sessions.index'));
        $this->assertDatabaseHas('academic_sessions', [
            'id' => $currentSession->id,
            'is_current' => true,
        ]);
        $this->assertDatabaseHas('academic_sessions', [
            'id' => $nextSession->id,
            'is_current' => true,
        ]);
        $this->assertSame(2, AcademicSession::where('is_current', true)->count());
    }

    public function test_session_names_are_trimmed_and_cannot_duplicate_an_existing_name(): void
    {
        $admin = $this->createAdmin();
        $this->createSession('2026-2027', false);

        $duplicateResponse = $this->actingAs($admin)->post(route('admin.sessions.store'), [
            'name' => ' 2026-2027 ',
            'start_date' => '2027-09-01',
            'end_date' => '2028-06-30',
            'status' => 'en_cours',
        ]);

        $duplicateResponse->assertSessionHasErrors([
            'name' => 'Une session académique portant ce nom existe déjà.',
        ]);
        $this->assertSame(1, AcademicSession::count());

        $createResponse = $this->actingAs($admin)->post(route('admin.sessions.store'), [
            'name' => '  Nouvelle   session  ',
            'start_date' => '2027-09-01',
            'end_date' => '2028-06-30',
            'status' => 'en_cours',
        ]);

        $createResponse->assertRedirect(route('admin.sessions.index'));
        $this->assertDatabaseHas('academic_sessions', ['name' => 'Nouvelle session']);
    }

    public function test_creating_a_session_name_is_case_insensitive(): void
    {
        $admin = $this->createAdmin();
        $this->createSession('Session Automne', false);

        $response = $this->actingAs($admin)->post(route('admin.sessions.store'), [
            'name' => 'session automne',
            'start_date' => '2027-09-01',
            'end_date' => '2028-06-30',
            'status' => 'en_cours',
        ]);

        $response->assertSessionHasErrors('name');
        $this->assertSame(1, AcademicSession::count());
    }

    public function test_database_rejects_duplicate_normalized_session_names(): void
    {
        $this->createSession('Session Automne', false);

        $this->expectException(QueryException::class);

        AcademicSession::create([
            'name' => ' session   automne ',
            'start_date' => '2027-09-01',
            'end_date' => '2028-06-30',
        ]);
    }

    public function test_updating_a_session_rejects_another_sessions_normalized_name(): void
    {
        $admin = $this->createAdmin();
        $existingSession = $this->createSession('Session Automne', false);
        $sessionToUpdate = $this->createSession('Session Printemps', false);

        $response = $this->actingAs($admin)->put(route('admin.sessions.update', $sessionToUpdate), [
            'name' => '  SESSION   AUTOMNE ',
            'start_date' => '2027-09-01',
            'end_date' => '2028-06-30',
            'status' => 'en_cours',
        ]);

        $response->assertSessionHasErrors('name');
        $this->assertDatabaseHas('academic_sessions', [
            'id' => $existingSession->id,
            'name' => 'Session Automne',
        ]);
        $this->assertDatabaseHas('academic_sessions', [
            'id' => $sessionToUpdate->id,
            'name' => 'Session Printemps',
        ]);
    }

    public function test_admin_can_set_a_session_status_when_creating_and_updating_a_session(): void
    {
        $admin = $this->createAdmin();
        $session = $this->createSession('Session en cours', true);

        $createResponse = $this->actingAs($admin)->post(route('admin.sessions.store'), [
            'name' => 'Session terminée',
            'start_date' => '2025-09-01',
            'end_date' => '2026-06-30',
            'status' => 'terminee',
        ]);

        $createResponse->assertRedirect(route('admin.sessions.index'));
        $this->assertDatabaseHas('academic_sessions', [
            'name' => 'Session terminée',
            'status' => 'terminee',
        ]);

        $updateResponse = $this->actingAs($admin)->put(route('admin.sessions.update', $session), [
            'name' => 'Session en cours',
            'start_date' => '2026-09-01',
            'end_date' => '2027-06-30',
            'status' => 'en_cours',
        ]);

        $updateResponse->assertRedirect(route('admin.sessions.index'));
        $this->assertDatabaseHas('academic_sessions', [
            'id' => $session->id,
            'status' => 'en_cours',
        ]);

        $indexResponse = $this->actingAs($admin)->get(route('admin.sessions.index'));

        $indexResponse->assertSee('En cours');
        $indexResponse->assertSee('Terminée');
    }

    public function test_session_status_rejects_values_outside_the_available_choices(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->post(route('admin.sessions.store'), [
            'name' => 'Session invalide',
            'start_date' => '2026-09-01',
            'end_date' => '2027-06-30',
            'status' => 'suspendue',
        ]);

        $response->assertSessionHasErrors('status');
        $this->assertDatabaseMissing('academic_sessions', ['name' => 'Session invalide']);
    }

    private function createAdmin(): User
    {
        $role = Role::create(['name' => 'administrateur', 'guard_name' => 'web']);
        $admin = User::create([
            'first_name' => 'Admin',
            'last_name' => 'IFAM',
            'email' => 'admin@ifam.test',
            'password' => Hash::make('Secret123!'),
            'is_active' => true,
        ]);
        $admin->assignRole($role);

        return $admin;
    }

    private function createSession(string $name, bool $isCurrent): AcademicSession
    {
        return AcademicSession::create([
            'name' => $name,
            'start_date' => '2026-09-01',
            'end_date' => '2027-06-30',
            'status' => 'en_cours',
            'is_current' => $isCurrent,
        ]);
    }
}
