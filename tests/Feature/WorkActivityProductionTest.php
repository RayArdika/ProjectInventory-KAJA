<?php

namespace Tests\Feature;

use App\Models\Material;
use App\Models\InventoryLog;
use App\Models\Product;
use App\Models\Production;
use App\Models\User;
use App\Models\WorkActivity;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkActivityProductionTest extends TestCase
{
    use RefreshDatabase;

    public function test_work_activity_fee_unit_only_accepts_pcs(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $product = Product::create([
            'sku' => '001',
            'product_name' => 'Kantong Unit Test',
            'category' => 'STOCK JADI',
            'unit' => 'pcs',
            'stock' => 0,
            'worker_fee' => 0,
            'minimum_stock' => 0,
            'warning_stock' => 20,
            'price_per_unit' => 0,
            'selling_price' => 0,
        ]);

        $this->actingAs($admin)
            ->post('/work-activities', [
                'product_id' => $product->id,
                'activity_name' => 'Packing Test',
                'unit' => 'gram',
                'fee_per_unit' => 100,
                'is_active' => 1,
            ])
            ->assertSessionHasErrors('unit');

        $this->assertDatabaseMissing('work_activities', [
            'activity_name' => 'Packing Test',
        ]);
    }

    public function test_approved_packing_activity_updates_stock_payroll_and_log(): void
    {
        $admin = User::factory()->create([
            'name' => 'Owner',
            'role' => 'admin',
        ]);

        $worker = User::factory()->create([
            'name' => 'Siti Aminah',
            'role' => 'worker',
        ]);

        $product = Product::create([
            'sku' => 'KJ-042',
            'product_name' => 'Kantong Yellow Helow',
            'category' => 'Finished Goods',
            'unit' => 'pcs',
            'stock' => 10,
            'worker_fee' => 0,
            'minimum_stock' => 0,
            'warning_stock' => 20,
            'price_per_unit' => 0,
            'selling_price' => 0,
        ]);

        $materials = collect([
            ['Kantong Teh Polos Personal', 'pcs'],
            ['Sticker Kantong Yellow', 'pcs'],
            ['Yellow Helow Bubuk', 'pcs'],
        ])->map(fn (array $item) => Material::create([
            'material_name' => $item[0],
            'category' => 'Material',
            'unit' => $item[1],
            'stock' => 100,
            'minimum_stock' => 10,
            'price_per_unit' => 0,
            'selling_price' => 0,
        ]));

        $activity = WorkActivity::create([
            'product_id' => $product->id,
            'activity_name' => 'Packing Kantong',
            'unit' => 'pcs',
            'fee_per_unit' => 120,
            'is_active' => true,
        ]);

        foreach ($materials as $material) {
            $activity->materialRequirements()->create([
                'material_id' => $material->id,
                'qty_needed' => 1,
            ]);
        }

        $this->actingAs($worker)
            ->get('/produksi')
            ->assertOk()
            ->assertSee('Activity')
            ->assertSee('SKU / Item')
            ->assertDontSee('Activity and SKU')
            ->assertDontSee('Work Activities');

        $this->actingAs($worker)->post('/produksi', [
            'production_date' => '2026-06-09',
            'activity_name' => 'Packing Kantong',
            'product_id' => $product->id,
            'qty_pass' => 50,
            'qty_reject' => 0,
        ])->assertSessionHasNoErrors();

        $production = Production::firstOrFail();

        $this->assertSame('Siti Aminah', $production->worker_name);
        $this->assertSame('Packing Kantong', $production->activity_name);
        $this->assertSame('Pending', $production->status);

        $this->actingAs($admin)
            ->post("/produksi/{$production->id}/approve")
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('productions', [
            'id' => $production->id,
            'status' => 'Approved',
        ]);
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock' => 60,
        ]);

        foreach ($materials as $material) {
            $this->assertDatabaseHas('materials', [
                'id' => $material->id,
                'stock' => 50,
            ]);
        }

        $this->assertDatabaseHas('payrolls', [
            'production_id' => $production->id,
            'worker_name' => 'Siti Aminah',
            'activity_name' => 'Packing Kantong',
            'product_name' => 'Kantong Yellow Helow',
            'qty' => 50,
            'unit' => 'pcs',
            'fee' => 120,
            'total_salary' => 6000,
        ]);
        $this->assertDatabaseHas('activity_logs', [
            'user_name' => 'Owner',
        ]);
    }

    public function test_approval_consumes_completed_and_rejected_but_pays_completed_only(): void
    {
        $admin = User::factory()->create([
            'name' => 'Owner',
            'role' => 'admin',
        ]);

        $worker = User::factory()->create([
            'name' => 'Siti Aminah',
            'role' => 'worker',
        ]);

        $product = Product::create([
            'sku' => '001',
            'product_name' => 'Kantong Yellow Helow',
            'category' => 'STOCK JADI',
            'unit' => 'pcs',
            'stock' => 5,
            'worker_fee' => 0,
            'minimum_stock' => 0,
            'warning_stock' => 20,
            'price_per_unit' => 0,
            'selling_price' => 0,
        ]);

        $material = Material::create([
            'material_name' => 'Yellow Helow Bubuk',
            'category' => 'STOCK BUBUK',
            'unit' => 'pcs',
            'stock' => 30,
            'minimum_stock' => 0,
            'price_per_unit' => 0,
            'selling_price' => 0,
        ]);

        $activity = WorkActivity::create([
            'product_id' => $product->id,
            'activity_name' => 'Packing Kantong',
            'unit' => 'pcs',
            'fee_per_unit' => 120,
            'is_active' => true,
        ]);

        $activity->materialRequirements()->create([
            'material_id' => $material->id,
            'qty_needed' => 1,
        ]);

        $this->actingAs($worker)
            ->post('/produksi', [
                'production_date' => '2026-06-20',
                'activity_name' => 'Packing Kantong',
                'product_id' => $product->id,
                'qty_pass' => 10,
                'qty_reject' => 14,
                'note' => 'Sticker position was incorrect',
            ])
            ->assertSessionHasNoErrors();

        $production = Production::firstOrFail();

        $this->actingAs($admin)
            ->post("/produksi/{$production->id}/approve")
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('materials', [
            'id' => $material->id,
            'stock' => 6,
        ]);
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock' => 15,
        ]);
        $this->assertDatabaseHas('payrolls', [
            'production_id' => $production->id,
            'qty' => 10,
            'total_salary' => 1200,
        ]);
        $this->assertDatabaseHas('inventory_logs', [
            'item_name' => 'Yellow Helow Bubuk',
            'stock' => 6,
        ]);
        $this->assertDatabaseHas('inventory_logs', [
            'item_name' => 'Kantong Yellow Helow',
            'stock' => 15,
        ]);
    }

    public function test_returned_entry_can_be_corrected_and_resubmitted(): void
    {
        $admin = User::factory()->create([
            'name' => 'Owner',
            'role' => 'admin',
        ]);

        $worker = User::factory()->create([
            'name' => 'Siti Aminah',
            'role' => 'worker',
        ]);

        $product = Product::create([
            'sku' => '001',
            'product_name' => 'Kantong Yellow Helow',
            'category' => 'STOCK JADI',
            'unit' => 'pcs',
            'stock' => 0,
            'worker_fee' => 0,
            'minimum_stock' => 0,
            'warning_stock' => 20,
            'price_per_unit' => 0,
            'selling_price' => 0,
        ]);

        $activity = WorkActivity::create([
            'product_id' => $product->id,
            'activity_name' => 'Packing Kantong',
            'unit' => 'pcs',
            'fee_per_unit' => 120,
            'is_active' => true,
        ]);

        $production = Production::create([
            'production_date' => '2026-06-20',
            'worker_name' => 'Siti Aminah',
            'activity_name' => 'Packing Kantong',
            'work_activity_id' => $activity->id,
            'category' => 'STOCK JADI',
            'product_name' => 'Kantong Yellow Helow',
            'qty_pass' => 10,
            'qty_reject' => 0,
            'status' => 'Pending',
        ]);

        $this->actingAs($admin)
            ->post("/produksi/{$production->id}/reject", [
                'return_note' => 'Correct the completed quantity',
            ])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('productions', [
            'id' => $production->id,
            'status' => 'Returned',
        ]);

        $this->actingAs($worker)
            ->put("/produksi/{$production->id}", [
                'production_date' => '2026-06-20',
                'activity_name' => 'Packing Kantong',
                'product_id' => $product->id,
                'qty_pass' => 12,
                'qty_reject' => 0,
                'note' => 'Quantity corrected',
            ])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('productions', [
            'id' => $production->id,
            'qty_pass' => 12,
            'status' => 'Pending',
        ]);
    }

    public function test_rejected_quantity_requires_note_and_completed_must_be_positive(): void
    {
        $worker = User::factory()->create([
            'name' => 'Siti Aminah',
            'role' => 'worker',
        ]);

        $product = Product::create([
            'sku' => '001',
            'product_name' => 'Kantong Yellow Helow',
            'category' => 'STOCK JADI',
            'unit' => 'pcs',
            'stock' => 0,
            'worker_fee' => 0,
            'minimum_stock' => 0,
            'warning_stock' => 20,
            'price_per_unit' => 0,
            'selling_price' => 0,
        ]);

        WorkActivity::create([
            'product_id' => $product->id,
            'activity_name' => 'Packing Kantong',
            'unit' => 'pcs',
            'fee_per_unit' => 120,
            'is_active' => true,
        ]);

        $this->actingAs($worker)
            ->post('/produksi', [
                'production_date' => '2026-06-20',
                'activity_name' => 'Packing Kantong',
                'product_id' => $product->id,
                'qty_pass' => 0,
                'qty_reject' => 2,
            ])
            ->assertSessionHasErrors(['qty_pass', 'note']);
    }

    public function test_production_date_must_be_in_current_month_and_not_future(): void
    {
        $worker = User::factory()->create([
            'name' => 'Siti Aminah',
            'role' => 'worker',
        ]);

        $product = Product::create([
            'sku' => '001',
            'product_name' => 'Kantong Yellow Helow',
            'category' => 'STOCK JADI',
            'unit' => 'pcs',
            'stock' => 0,
            'worker_fee' => 0,
            'minimum_stock' => 0,
            'warning_stock' => 20,
            'price_per_unit' => 0,
            'selling_price' => 0,
        ]);

        WorkActivity::create([
            'product_id' => $product->id,
            'activity_name' => 'Packing Kantong',
            'unit' => 'pcs',
            'fee_per_unit' => 120,
            'is_active' => true,
        ]);

        $baseData = [
            'activity_name' => 'Packing Kantong',
            'product_id' => $product->id,
            'qty_pass' => 1,
            'qty_reject' => 0,
        ];

        $this->actingAs($worker)
            ->post('/produksi', [
                ...$baseData,
                'production_date' => now()->subMonth()->endOfMonth()->toDateString(),
            ])
            ->assertSessionHasErrors('production_date');

        $this->actingAs($worker)
            ->post('/produksi', [
                ...$baseData,
                'production_date' => now()->addDay()->toDateString(),
            ])
            ->assertSessionHasErrors('production_date');
    }
}
