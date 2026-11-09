<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_renders_login_and_register_pages_for_guests(): void
    {
        $loginResponse = $this->get(route('login'));
        $registerResponse = $this->get(route('register'));

        $loginResponse->assertOk()->assertSee('Masuk ke Smart-PJU');
        $registerResponse->assertOk()->assertSee('Registrasi Pengguna');
    }

    public function test_registers_new_pengelola_user_and_redirects_to_dashboard(): void
    {
        $response = $this->post(route('register.submit'), [
            'name' => 'Pengelola Baru',
            'email' => 'baru@smartpju.id',
            'role' => 'pengelola',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => 'baru@smartpju.id',
            'role' => UserRole::Pengelola->value,
        ]);
    }

    public function test_authenticates_valid_credentials_and_logs_out_cleanly(): void
    {
        $user = User::factory()->pengelola()->create([
            'email' => 'habibie@smartpju.id',
        ]);

        $loginResponse = $this->post(route('login.submit'), [
            'email' => 'habibie@smartpju.id',
            'password' => 'password',
        ]);

        $loginResponse->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);

        $logoutResponse = $this->post(route('logout'));

        $logoutResponse->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_rejects_invalid_login_credentials_with_error_message(): void
    {
        User::factory()->create([
            'email' => 'habibie@smartpju.id',
        ]);

        $response = $this->from(route('login'))->post(route('login.submit'), [
            'email' => 'habibie@smartpju.id',
            'password' => 'wrong-password',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }
}
