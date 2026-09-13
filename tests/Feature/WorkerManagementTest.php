<?php

namespace Tests\Feature;

use App\Models\Payroll;
use App\Models\Production;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class WorkerManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_admin_can_open_worker_management(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $worker = User::factory()->create(['role' => 'worker']);

        $this->actingAs($admin)
            ->get('/workers')
            ->assertOk()
            ->assertSee('Manage Workers')
            ->assertSee('Edit')
            ->assertSee('Reset')
            ->assertSee('Deactivate')
            ->assertSee('Delete');

        $this->actingAs($worker)
            ->get('/workers')
            ->assertRedirect('/produksi');
    }

    public function test_admin_can_create_and_edit_a_worker(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post('/workers', [
            'name' => 'Siti Aminah',
            'email' => 'siti@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect();

        $worker = User::where('email', 'siti@example.com')->firstOrFail();
        $this->assertSame('worker', $worker->role);
        $this->assertTrue($worker->is_active);

        $this->actingAs($admin)->put("/workers/{$worker->id}", [
            'name' => 'Siti Aminah Updated',
            'email' => 'siti.updated@example.com',
        ])->assertRedirect();

        $this->assertDatabaseHas('users', [
            'id' => $worker->id,
            'name' => 'Siti Aminah Updated',
            'email' => 'siti.updated@example.com',
        ]);
    }

    public function test_renaming_worker_keeps_production_and_payroll_history_connected(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $worker = User::factory()->create(['role' => 'worker', 'name' => 'Old Name']);
        $production = Production::create([
            'production_date' => now()->toDateString(),
            'worker_name' => 'Old Name',
            'product_name' => 'Kantong Yellow Helow',
            'qty_pass' => 10,
            'qty_reject' => 0,
            'status' => 'Approved',
        ]);
        Payroll::create([
            'production_id' => $production->id,
            'worker_name' => 'Old Name',
            'product_name' => 'Kantong Yellow Helow',
            'activity_name' => 'Packing',
            'qty' => 10,
            'unit' => 'pcs',
            'fee' => 100,
            'total_salary' => 1000,
        ]);

        $this->actingAs($admin)->put("/workers/{$worker->id}", [
            'name' => 'New Name',
            'email' => $worker->email,
        ])->assertRedirect();

        $this->assertDatabaseHas('productions', ['id' => $production->id, 'worker_name' => 'New Name']);
        $this->assertDatabaseHas('payrolls', ['production_id' => $production->id, 'worker_name' => 'New Name']);
    }

    public function test_admin_can_reset_password_and_deactivate_worker(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $worker = User::factory()->create(['role' => 'worker']);

        $this->actingAs($admin)->patch("/workers/{$worker->id}/reset-password", [
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ])->assertRedirect();

        $this->assertTrue(Hash::check('new-password-123', $worker->fresh()->password));

        $this->actingAs($admin)
            ->patch("/workers/{$worker->id}/status")
            ->assertRedirect();

        $this->assertFalse($worker->fresh()->is_active);

        $this->post('/logout');
        $this->post('/login', [
            'email' => $worker->email,
            'password' => 'new-password-123',
        ]);
        $this->assertGuest();
    }

    public function test_worker_with_history_is_deactivated_instead_of_deleted(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $worker = User::factory()->create(['role' => 'worker', 'name' => 'Worker With History']);
        Production::create([
            'production_date' => now()->toDateString(),
            'worker_name' => $worker->name,
            'product_name' => 'Kantong Yellow Helow',
            'qty_pass' => 1,
            'qty_reject' => 0,
            'status' => 'Pending',
        ]);

        $this->actingAs($admin)
            ->delete("/workers/{$worker->id}")
            ->assertSessionHasErrors('worker');

        $this->assertDatabaseHas('users', ['id' => $worker->id]);
    }

    public function test_worker_without_history_can_be_deleted(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $worker = User::factory()->create(['role' => 'worker']);

        $this->actingAs($admin)
            ->delete("/workers/{$worker->id}")
            ->assertRedirect();

        $this->assertDatabaseMissing('users', ['id' => $worker->id]);
    }
}
