<?php

namespace Tests\Feature;

use App\Mail\ActivationMail;
use App\Mail\PasswordResetMail;
use App\Models\Site;
use App\Models\User;
use Database\Seeders\SiteSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AccountActivationFlowTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected Site $site;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(SiteSeeder::class);
        $this->site = Site::where('nom', 'Cotonou')->firstOrFail();

        // Créer les rôles nécessaires
        Role::create(['name' => 'administrateur', 'guard_name' => 'web']);
        Role::create(['name' => 'direction', 'guard_name' => 'web']);
        Role::create(['name' => 'enseignant', 'guard_name' => 'web']);
        Role::create(['name' => 'etudiant', 'guard_name' => 'web']);
        Role::create(['name' => 'parent', 'guard_name' => 'web']);

        // Créer l'administrateur
        $this->admin = User::create([
            'first_name' => 'Admin',
            'last_name' => 'IFAM',
            'email' => 'admin@ifam.bj',
            'password' => Hash::make('Admin@1234!'),
            'is_active' => true,
            'must_change_password' => false,
        ]);
        $this->admin->assignRole('administrateur');
    }

    public function test_admin_can_create_user_without_password_and_sends_activation_email(): void
    {
        Mail::fake();

        $response = $this->actingAs($this->admin)->post(route('admin.users.store'), [
            'first_name' => 'Alice',
            'last_name' => 'Koffi',
            'email' => 'alice.koffi@example.com',
            'role' => 'etudiant',
            'phone' => '+229 01 23 45 67',
            'site_id' => $this->site->id,
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('success');

        $user = User::where('email', 'alice.koffi@example.com')->first();
        $this->assertNotNull($user);
        $this->assertNull($user->password);
        $this->assertNotNull($user->activation_token);
        $this->assertNotNull($user->activation_expires_at);
        $this->assertTrue($user->hasRole('etudiant'));
        $this->assertNotNull($user->matricule);
        $this->assertTrue($user->sites->contains('id', $this->site->id));

        Mail::assertSent(ActivationMail::class, function ($mail) use ($user) {
            return $mail->hasTo('alice.koffi@example.com') && $mail->token === $user->activation_token;
        });
    }

    public function test_cannot_create_administrator_or_with_invalid_email_format(): void
    {
        Mail::fake();

        // 1. Essai avec rôle administrateur interdit
        $resRole = $this->actingAs($this->admin)->post(route('admin.users.store'), [
            'first_name' => 'Bob',
            'last_name' => 'Admin',
            'email' => 'bob.admin@example.com',
            'role' => 'administrateur',
            'site_id' => $this->site->id,
        ]);
        $resRole->assertSessionHasErrors(['role']);

        // 2. Essai avec email au format invalide
        $resEmail = $this->actingAs($this->admin)->post(route('admin.users.store'), [
            'first_name' => 'Bob',
            'last_name' => 'Test',
            'email' => 'email-non-valide',
            'role' => 'etudiant',
            'site_id' => $this->site->id,
        ]);
        $resEmail->assertSessionHasErrors(['email']);

        Mail::assertNothingSent();
    }

    public function test_user_can_access_activation_page_and_define_password(): void
    {
        $token = Str::random(64);
        $user = User::create([
            'first_name' => 'Marc',
            'last_name' => 'Tossou',
            'email' => 'marc.tossou@example.com',
            'password' => null,
            'activation_token' => $token,
            'activation_expires_at' => now()->addHours(48),
            'is_active' => true,
        ]);
        $user->assignRole('enseignant');

        // Accès à la page
        $resShow = $this->get(route('activation.show', $token));
        $resShow->assertStatus(200);
        $resShow->assertSee('Définissez votre mot de passe');
        $resShow->assertSee('marc.tossou@example.com');

        // Soumission du mot de passe
        $resActivate = $this->post(route('activation.activate', $token), [
            'password' => 'SecretPass123!',
            'password_confirmation' => 'SecretPass123!',
        ]);

        $resActivate->assertStatus(200);
        $resActivate->assertSee('Mot de passe enregistré');
        $resActivate->assertSee(route('login'));

        $user->refresh();
        $this->assertNull($user->activation_token);
        $this->assertNull($user->activation_expires_at);
        $this->assertTrue(Hash::check('SecretPass123!', $user->password));
    }

    public function test_expired_or_used_activation_token_shows_error_page(): void
    {
        // 1. Token inexistant / déjà utilisé
        $resInvalide = $this->get(route('activation.show', 'token-invalide'));
        $resInvalide->assertStatus(404);
        $resInvalide->assertSee('Lien déjà utilisé ou invalide');
        $resInvalide->assertSee(route('login'));

        // 2. Token expiré (> 48h)
        $tokenExpire = Str::random(64);
        User::create([
            'first_name' => 'Claire',
            'last_name' => 'Dossou',
            'email' => 'claire.dossou@example.com',
            'password' => null,
            'activation_token' => $tokenExpire,
            'activation_expires_at' => now()->subHours(1),
            'is_active' => true,
        ]);

        $resExpire = $this->get(route('activation.show', $tokenExpire));
        $resExpire->assertStatus(410);
        $resExpire->assertSee('Lien expiré');
        $resExpire->assertSee("Veuillez contacter l'administration", false);
    }

    public function test_forgot_password_sends_email_with_neutral_response(): void
    {
        Mail::fake();

        $user = User::create([
            'first_name' => 'David',
            'last_name' => 'Hounou',
            'email' => 'david.hounou@example.com',
            'password' => Hash::make('OldPassword123!'),
            'is_active' => true,
            'must_change_password' => false,
        ]);
        $user->assignRole('parent');

        // Demande pour utilisateur existant
        $response = $this->post(route('password.email'), [
            'email' => 'david.hounou@example.com',
        ]);

        $response->assertSessionHas('status', 'Si un compte existe avec cet email, un lien de réinitialisation a été envoyé.');

        $user->refresh();
        $this->assertNotNull($user->activation_token);

        Mail::assertSent(PasswordResetMail::class, function ($mail) {
            return $mail->hasTo('david.hounou@example.com');
        });

        // Demande pour un email inconnu (réponse neutre identique)
        $responseUnknown = $this->post(route('password.email'), [
            'email' => 'inconnu@example.com',
        ]);
        $responseUnknown->assertSessionHas('status', 'Si un compte existe avec cet email, un lien de réinitialisation a été envoyé.');
    }

    public function test_admin_can_resend_activation_email(): void
    {
        Mail::fake();

        $user = User::create([
            'first_name' => 'Emma',
            'last_name' => 'Boco',
            'email' => 'emma.boco@example.com',
            'password' => null,
            'activation_token' => Str::random(64),
            'activation_expires_at' => now()->addHours(10),
            'is_active' => true,
        ]);
        $user->assignRole('direction');

        $response = $this->actingAs($this->admin)->post(route('admin.users.resendActivation', $user));
        $response->assertRedirect();
        $response->assertSessionHas('success');

        Mail::assertSent(ActivationMail::class, function ($mail) {
            return $mail->hasTo('emma.boco@example.com');
        });
    }
}
