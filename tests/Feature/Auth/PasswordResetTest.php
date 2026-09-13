<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_password_recovery_is_not_available(): void
    {
        $this->get('/forgot-password')->assertNotFound();
        $this->post('/forgot-password', ['email' => 'worker@example.com'])->assertNotFound();
        $this->get('/reset-password/example-token')->assertNotFound();
        $this->post('/reset-password')->assertNotFound();
    }
}
