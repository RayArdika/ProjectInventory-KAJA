<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_login_assets_use_public_https_host_behind_reverse_proxy(): void
    {
        $response = $this
            ->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
            ->withHeaders([
                'X-Forwarded-Host' => 'example.serveousercontent.com',
                'X-Forwarded-Proto' => 'https',
                'X-Forwarded-Port' => '443',
            ])
            ->get('/login');

        $response
            ->assertOk()
            ->assertSee(
                'https://example.serveousercontent.com/build/assets/',
                false
            )
            ->assertDontSee('127.0.0.1:8000/build/assets/', false);
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect('/produksi');
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $response = $this->from('/login')->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
        $response->assertRedirect('/login');

        $this->get('/login')
            ->assertOk()
            ->assertSee('The email or password you entered is incorrect.');
    }

    public function test_inactive_worker_can_not_authenticate(): void
    {
        $user = User::factory()->create([
            'role' => 'worker',
            'is_active' => false,
        ]);

        $response = $this->from('/login')->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertGuest();
        $response
            ->assertRedirect('/login')
            ->assertSessionHasErrors('email');
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }
}
