<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Production;
use App\Models\User;
use App\Models\WorkActivity;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ProductionHistoryFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_sees_worker_dropdown_and_can_filter_production_history(): void
    {
        $admin = User::factory()->create([
            'name' => 'Owner',
            'role' => 'admin',
        ]);

        User::factory()->create([
            'name' => 'Siti Aminah',
            'role' => 'worker',
        ]);

        User::factory()->create([
            'name' => 'Another Admin',
            'role' => 'admin',
        ]);

        Production::create([
            'production_date' => '2026-06-10',
            'worker_name' => 'Siti Aminah',
            'activity_name' => 'Packing Kantong',
            'product_name' => 'Kantong Yellow',
            'qty_pass' => 50,
            'qty_reject' => 0,
            'status' => 'Returned',
            'note' => 'Sticker position is incorrect',
        ]);

        Production::create([
            'production_date' => '2026-06-11',
            'worker_name' => 'Siti Aminah',
            'activity_name' => 'Packing Sachet',
            'product_name' => 'Sachet Green',
            'qty_pass' => 20,
            'qty_reject' => 0,
            'status' => 'Approved',
        ]);

        $this->actingAs($admin)
            ->get('/produksi?date=2026-06-10&status=Returned')
            ->assertOk()
            ->assertSee('Activity Master')
            ->assertDontSee('Item Master')
            ->assertSee('Siti Aminah')
            ->assertDontSee('Another Admin')
            ->assertSee('Kantong Yellow')
            ->assertSee('Sticker position is incorrect')
            ->assertDontSee('Sachet Green');
    }

    public function test_worker_name_always_uses_authenticated_worker(): void
    {
        $worker = User::factory()->create([
            'name' => 'Siti Aminah',
            'role' => 'worker',
        ]);

        $product = Product::create([
            'sku' => '001',
            'product_name' => 'Kantong Yellow',
            'category' => 'Kantong',
            'unit' => 'pcs',
            'stock' => 0,
            'worker_fee' => 0,
            'minimum_stock' => 0,
            'warning_stock' => 0,
            'price_per_unit' => 0,
            'selling_price' => 0,
        ]);

        WorkActivity::create([
            'product_id' => $product->id,
            'activity_name' => 'Packing Kantong',
            'unit' => 'pcs',
            'fee_per_unit' => 100,
            'is_active' => true,
        ]);

        $this->actingAs($worker)
            ->get('/produksi')
            ->assertOk()
            ->assertDontSee('Activity Master')
            ->assertDontSee('Item Master');

        $this->actingAs($worker)
            ->post('/produksi', [
                'production_date' => '2026-06-12',
                'worker_name' => 'Different Worker',
                'activity_name' => 'Packing Kantong',
                'product_id' => $product->id,
                'qty_pass' => 10,
                'qty_reject' => 0,
                'note' => 'Morning shift',
            ])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('productions', [
            'worker_name' => 'Siti Aminah',
            'note' => 'Morning shift',
        ]);
    }

    public function test_activity_log_can_be_filtered_by_activity_and_date(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        DB::table('activity_logs')->insert([
            [
                'user_name' => 'Owner',
                'activity' => 'Approved production',
                'created_at' => '2026-06-10 08:00:00',
                'updated_at' => '2026-06-10 08:00:00',
            ],
            [
                'user_name' => 'Owner',
                'activity' => 'Deleted distribution',
                'created_at' => '2026-06-11 08:00:00',
                'updated_at' => '2026-06-11 08:00:00',
            ],
        ]);

        $this->actingAs($admin)
            ->get('/activity-log?activity=Approved&date=2026-06-10')
            ->assertOk()
            ->assertSee('Approved production')
            ->assertDontSee('Deleted distribution');
    }

    public function test_qc_only_displays_pending_entries(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        Production::create([
            'production_date' => now()->toDateString(),
            'worker_name' => 'Pending Worker',
            'activity_name' => 'Packing Kantong',
            'product_name' => 'Kantong Pending',
            'qty_pass' => 10,
            'qty_reject' => 0,
            'status' => 'Pending',
        ]);

        Production::create([
            'production_date' => now()->toDateString(),
            'worker_name' => 'Approved Worker',
            'activity_name' => 'Packing Kantong',
            'product_name' => 'Kantong Approved',
            'qty_pass' => 10,
            'qty_reject' => 0,
            'status' => 'Approved',
        ]);

        $this->actingAs($admin)
            ->get('/qc')
            ->assertOk()
            ->assertSee('Kantong Pending')
            ->assertDontSee('Kantong Approved');
    }

    public function test_dashboard_total_production_only_counts_approved_entries(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        foreach ([
            ['Approved', 10],
            ['Pending', 25],
            ['Returned', 40],
        ] as [$status, $quantity]) {
            Production::create([
                'production_date' => now()->toDateString(),
                'worker_name' => 'Siti Aminah',
                'activity_name' => 'Packing Kantong',
                'product_name' => 'Kantong Yellow',
                'qty_pass' => $quantity,
                'qty_reject' => 0,
                'status' => $status,
            ]);
        }

        $this->actingAs($admin)
            ->get('/dashboard')
            ->assertOk()
            ->assertSeeInOrder(['Total Production', '10']);
    }
}
