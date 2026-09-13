<?php

namespace Tests\Feature;

use App\Models\Material;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryProductNumberingTest extends TestCase
{
    use RefreshDatabase;

    public function test_finished_goods_receive_sequential_sku_and_allow_kantong_sachet_or_hampers(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $baseData = [
            'type' => 'product',
            'inventory_date' => '2026-06-09',
            'category' => 'STOCK JADI',
            'unit' => 'pcs',
            'stock' => 0,
            'minimum_stock' => 0,
            'warning_stock' => 20,
            'price_per_unit' => 0,
            'selling_price' => 0,
            'worker_fee' => 0,
        ];

        $this->actingAs($admin)
            ->post('/inventory/items', [
                ...$baseData,
                'name' => 'Kantong Test',
            ])
            ->assertSessionHasNoErrors();

        $this->actingAs($admin)
            ->post('/inventory/items', [
                ...$baseData,
                'name' => 'Sachet Test',
            ])
            ->assertSessionHasNoErrors();

        $this->actingAs($admin)
            ->post('/inventory/items', [
                ...$baseData,
                'name' => 'Hampers',
            ])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('products', [
            'product_name' => 'Kantong Test',
            'sku' => '001',
        ]);
        $this->assertDatabaseHas('products', [
            'product_name' => 'Sachet Test',
            'sku' => '002',
        ]);
        $this->assertDatabaseHas('products', [
            'product_name' => 'Hampers',
            'sku' => '003',
        ]);

        $sachet = Product::where('product_name', 'Sachet Test')->firstOrFail();

        $this->actingAs($admin)
            ->delete("/inventory/items/product/{$sachet->id}")
            ->assertSessionHasNoErrors();

        $this->actingAs($admin)
            ->post('/inventory/items', [
                ...$baseData,
                'name' => 'Sachet Next',
            ])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('products', [
            'product_name' => 'Sachet Next',
            'sku' => '004',
        ]);

        $this->actingAs($admin)
            ->post('/inventory/items', [
                ...$baseData,
                'name' => 'Pouch Test',
            ])
            ->assertSessionHasErrors('name');

        $this->assertDatabaseMissing('products', [
            'product_name' => 'Pouch Test',
        ]);

        $this->actingAs($admin)
            ->post('/inventory/items', [
                ...$baseData,
                'name' => 'Kantong Invalid Unit',
                'unit' => 'gram',
            ])
            ->assertSessionHasErrors('unit');

        $this->assertDatabaseMissing('products', [
            'product_name' => 'Kantong Invalid Unit',
        ]);

        $this->actingAs($admin)
            ->post('/inventory/items', [
                ...$baseData,
                'name' => 'Kantong Pack Test',
                'unit' => 'pack',
            ])
            ->assertSessionHasErrors('unit');
    }

    public function test_material_inventory_is_preserved(): void
    {
        Material::create([
            'material_name' => 'Sticker Kantong Yellow',
            'category' => 'STOCK STICKER',
            'unit' => 'pcs',
            'stock' => 100,
            'minimum_stock' => 0,
            'price_per_unit' => 0,
            'selling_price' => 0,
        ]);

        $this->assertDatabaseHas('materials', [
            'material_name' => 'Sticker Kantong Yellow',
            'stock' => 100,
        ]);
        $this->assertDatabaseCount('products', 0);
    }

    public function test_inventory_counts_zero_stock_products_and_materials_as_critical(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        Product::create([
            'sku' => '001',
            'product_name' => 'Hampers Test',
            'category' => 'STOCK JADI',
            'unit' => 'pcs',
            'stock' => 0,
            'worker_fee' => 0,
            'minimum_stock' => 30,
            'warning_stock' => 20,
            'price_per_unit' => 0,
            'selling_price' => 0,
        ]);

        Material::create([
            'material_name' => 'Box Hampers',
            'category' => 'PACKAGING',
            'unit' => 'pcs',
            'stock' => 0,
            'minimum_stock' => 0,
            'price_per_unit' => 0,
            'selling_price' => 0,
        ]);

        $this->actingAs($admin)
            ->get('/inventory')
            ->assertOk()
            ->assertSeeInOrder(['Critical Stock', '2']);
    }

    public function test_dashboard_formats_stock_as_whole_pieces_and_includes_zero_stock(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        Product::create([
            'sku' => '001',
            'product_name' => 'Hampers Test',
            'category' => 'STOCK JADI',
            'unit' => 'pcs',
            'stock' => 2,
            'worker_fee' => 0,
            'minimum_stock' => 30,
            'warning_stock' => 20,
            'price_per_unit' => 0,
            'selling_price' => 0,
        ]);

        Product::create([
            'sku' => '002',
            'product_name' => 'Kantong Empty',
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
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('Stock: 2 pcs')
            ->assertDontSee('Stock: 2.000 pcs')
            ->assertSee('Kantong Empty')
            ->assertSee('Stock: 0 pcs');
    }
}
