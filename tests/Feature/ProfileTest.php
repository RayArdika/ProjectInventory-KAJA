<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_management_is_not_exposed(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/profile')->assertNotFound();
        $this->actingAs($user)->patch('/profile')->assertNotFound();
        $this->actingAs($user)->delete('/profile')->assertNotFound();
    }
}
