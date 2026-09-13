<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $this->get('/register')
            ->assertOk()
            ->assertSee('registered as Worker')
            ->assertDontSee('Admin / Owner');
    }

    public function test_public_registration_creates_an_active_worker(): void
    {
        $response = $this->post('/register', [
            'name' => 'New Worker',
            'email' => 'new.worker@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'admin',
        ]);

        $response
            ->assertRedirect('/login')
            ->assertSessionHas('success');

        $worker = User::where('email', 'new.worker@example.com')->firstOrFail();
        $this->assertSame('worker', $worker->role);
        $this->assertTrue($worker->is_active);
    }

    public function test_password_confirmation_must_match(): void
    {
        $this->from('/register')->post('/register', [
            'name' => 'New Worker',
            'email' => 'worker@example.com',
            'password' => 'password123',
            'password_confirmation' => 'different-password',
        ])
            ->assertRedirect('/register')
            ->assertSessionHasErrors('password');
    }

    public function test_password_recovery_routes_are_not_available(): void
    {
        $this->get('/forgot-password')->assertNotFound();
        $this->get('/reset-password/example-token')->assertNotFound();
    }
}
