<?php

namespace Tests\Feature;

use App\Models\Distribution;
use App\Models\Product;
use App\Models\Production;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DistributionManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_update_payment_and_delete_distribution_with_stock_adjustment(): void
    {
        $admin = User::factory()->create([
            'name' => 'Owner',
            'role' => 'admin',
        ]);

        $product = Product::create([
            'sku' => 'KJ-042',
            'product_name' => 'Kantong Yellow Helow',
            'category' => 'STOCK JADI',
            'unit' => 'pcs',
            'stock' => 100,
            'worker_fee' => 0,
            'minimum_stock' => 0,
            'warning_stock' => 20,
            'price_per_unit' => 0,
            'selling_price' => 0,
        ]);

        Production::create([
            'production_date' => '2026-06-09',
            'worker_name' => 'Siti Aminah',
            'activity_name' => 'Packing Kantong',
            'category' => 'STOCK JADI',
            'product_name' => $product->product_name,
            'qty_pass' => 100,
            'qty_reject' => 0,
            'status' => 'Approved',
        ]);

        $this->actingAs($admin)->post('/distribution', [
            'distribution_date' => '2026-06-09',
            'product_name' => $product->product_name,
            'qty_out' => 20,
            'price' => 10000,
            'shipping_cost' => 10000,
            'tax' => 5000,
            'destination' => 'Client A',
            'client_phone' => '081234567890',
            'payment_status' => 'Unpaid',
            'paid_amount' => 99999,
        ])->assertSessionHasNoErrors();

        $distribution = Distribution::firstOrFail();

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock' => 80,
        ]);
        $this->assertDatabaseHas('distributions', [
            'id' => $distribution->id,
            'total_amount' => 215000,
            'payment_status' => 'Unpaid',
            'paid_amount' => 0,
        ]);

        $this->actingAs($admin)
            ->get('/distribution')
            ->assertOk()
            ->assertSee("/distribution/{$distribution->id}/edit", false)
            ->assertSee('Delete');

        $this->actingAs($admin)
            ->get("/distribution/{$distribution->id}/edit")
            ->assertOk()
            ->assertSee('Edit Distribution')
            ->assertSee('Payment Status');

        $this->actingAs($admin)->put("/distribution/{$distribution->id}", [
            'distribution_date' => '2026-06-10',
            'product_name' => $product->product_name,
            'qty_out' => 30,
            'price' => 10000,
            'shipping_cost' => 10000,
            'tax' => 5000,
            'destination' => 'Client A',
            'client_phone' => '081234567890',
            'payment_status' => 'Paid',
            'paid_amount' => 1,
            'note' => 'Paid in full',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock' => 70,
        ]);
        $this->assertDatabaseHas('distributions', [
            'id' => $distribution->id,
            'qty_out' => 30,
            'total_amount' => 315000,
            'payment_status' => 'Paid',
            'paid_amount' => 315000,
        ]);

        $this->actingAs($admin)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('Rp315.000')
            ->assertSee('Rp0');

        $this->actingAs($admin)
            ->delete("/distribution/{$distribution->id}")
            ->assertSessionHasNoErrors();

        $this->assertDatabaseMissing('distributions', [
            'id' => $distribution->id,
        ]);
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock' => 100,
        ]);
        $this->assertDatabaseHas('activity_logs', [
            'user_name' => 'Owner',
        ]);
    }

    public function test_partial_payment_must_be_lower_than_total(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $product = Product::create([
            'sku' => 'KJ-043',
            'product_name' => 'Kantong Reddish Wish',
            'category' => 'STOCK JADI',
            'unit' => 'pcs',
            'stock' => 10,
            'worker_fee' => 0,
            'minimum_stock' => 0,
            'warning_stock' => 20,
            'price_per_unit' => 0,
            'selling_price' => 0,
        ]);

        $this->actingAs($admin)->post('/distribution', [
            'distribution_date' => '2026-06-09',
            'product_name' => $product->product_name,
            'qty_out' => 2,
            'price' => 10000,
            'shipping_cost' => 0,
            'tax' => 0,
            'destination' => 'Client B',
            'client_phone' => '081234567890',
            'payment_status' => 'Partial',
            'paid_amount' => 20000,
        ])->assertSessionHasErrors('paid_amount');

        $this->assertDatabaseCount('distributions', 0);
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock' => 10,
        ]);
    }

    public function test_admin_can_filter_and_export_distribution_pdf_per_client(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        Distribution::create([
            'distribution_date' => '2026-06-10',
            'product_name' => 'Kantong Yellow Helow',
            'qty_out' => 10,
            'price' => 10000,
            'shipping_cost' => 5000,
            'tax' => 0,
            'total_amount' => 105000,
            'payment_status' => 'Partial',
            'paid_amount' => 50000,
            'destination' => 'Hotel Pullman',
            'note' => 'First order',
        ]);

        Distribution::create([
            'distribution_date' => '2026-06-11',
            'product_name' => 'Sachet Yellow Helow',
            'qty_out' => 5,
            'price' => 8000,
            'shipping_cost' => 0,
            'tax' => 0,
            'total_amount' => 40000,
            'payment_status' => 'Paid',
            'paid_amount' => 40000,
            'destination' => 'Client B',
            'note' => null,
        ]);

        $this->actingAs($admin)
            ->get('/distribution?client=Hotel%20Pullman')
            ->assertOk()
            ->assertSee('Hotel Pullman')
            ->assertSee('First order')
            ->assertDontSee('Sachet Yellow Helow');

        $this->actingAs($admin)
            ->get('/distribution/pdf?client=Hotel%20Pullman&date_from=2026-06-01&date_to=2026-06-30')
            ->assertOk()
            ->assertDownload('distribution-hotel-pullman.pdf');
    }

    public function test_multi_item_distribution_updates_all_stock_and_exports_invoice(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $firstProduct = Product::create([
            'sku' => '001',
            'product_name' => 'Kantong Yellow Helow',
            'category' => 'STOCK JADI',
            'unit' => 'pcs',
            'stock' => 100,
            'worker_fee' => 0,
            'minimum_stock' => 0,
            'warning_stock' => 20,
            'price_per_unit' => 0,
            'selling_price' => 4000,
        ]);
        $secondProduct = Product::create([
            'sku' => '002',
            'product_name' => 'Kantong Reddish Wish',
            'category' => 'STOCK JADI',
            'unit' => 'pcs',
            'stock' => 80,
            'worker_fee' => 0,
            'minimum_stock' => 0,
            'warning_stock' => 20,
            'price_per_unit' => 0,
            'selling_price' => 5000,
        ]);

        foreach ([$firstProduct, $secondProduct] as $product) {
            Production::create([
                'production_date' => '2026-06-30',
                'worker_name' => 'Siti Aminah',
                'activity_name' => 'Packing',
                'category' => 'STOCK JADI',
                'product_name' => $product->product_name,
                'qty_pass' => 10,
                'qty_reject' => 0,
                'status' => 'Approved',
            ]);
        }

        $this->actingAs($admin)->post('/distribution', [
            'distribution_date' => '2026-06-30',
            'destination' => 'Client Multi',
            'client_phone' => '08123456789',
            'items' => [
                ['product_id' => $firstProduct->id, 'qty_out' => 10, 'price' => 4000],
                ['product_id' => $secondProduct->id, 'qty_out' => 5, 'price' => 5000],
            ],
            'shipping_cost' => 5000,
            'tax' => 1000,
            'payment_status' => 'Partial',
            'payment_method' => 'Transfer',
            'paid_amount' => 20000,
            'note' => 'Two products',
        ])->assertSessionHasNoErrors();

        $distribution = Distribution::with('items')->firstOrFail();
        $this->assertCount(2, $distribution->items);
        $this->assertSame(15, $distribution->qty_out);
        $this->assertEquals(71000, $distribution->total_amount);
        $this->assertDatabaseHas('products', ['id' => $firstProduct->id, 'stock' => 90]);
        $this->assertDatabaseHas('products', ['id' => $secondProduct->id, 'stock' => 75]);

        $this->actingAs($admin)
            ->get("/distribution/{$distribution->id}/invoice")
            ->assertOk()
            ->assertDownload($distribution->invoice_number . '.pdf');

        $this->actingAs($admin)->put("/distribution/{$distribution->id}", [
            'distribution_date' => '2026-06-30',
            'destination' => 'Client Multi',
            'client_phone' => '08123456789',
            'items' => [
                ['product_id' => $firstProduct->id, 'qty_out' => 4, 'price' => 4000],
                ['product_id' => $secondProduct->id, 'qty_out' => 6, 'price' => 5000],
            ],
            'shipping_cost' => 0,
            'tax' => 0,
            'payment_status' => 'Paid',
            'payment_method' => 'Cash',
            'paid_amount' => 0,
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('products', ['id' => $firstProduct->id, 'stock' => 96]);
        $this->assertDatabaseHas('products', ['id' => $secondProduct->id, 'stock' => 74]);
        $this->assertDatabaseHas('distributions', [
            'id' => $distribution->id,
            'qty_out' => 10,
            'total_amount' => 46000,
            'paid_amount' => 46000,
        ]);

        $this->actingAs($admin)
            ->delete("/distribution/{$distribution->id}")
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('products', ['id' => $firstProduct->id, 'stock' => 100]);
        $this->assertDatabaseHas('products', ['id' => $secondProduct->id, 'stock' => 80]);
    }

    public function test_client_phone_must_contain_10_to_13_digits(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $product = Product::create([
            'sku' => '003',
            'product_name' => 'Sachet Yellow Helow',
            'category' => 'STOCK JADI',
            'unit' => 'pcs',
            'stock' => 20,
            'worker_fee' => 0,
            'minimum_stock' => 0,
            'warning_stock' => 20,
            'price_per_unit' => 0,
            'selling_price' => 5000,
        ]);

        foreach (['08123', '08123456ABCD', '08123456789012'] as $invalidPhone) {
            $this->actingAs($admin)->post('/distribution', [
                'distribution_date' => '2026-06-30',
                'destination' => 'Client Phone Test',
                'client_phone' => $invalidPhone,
                'items' => [[
                    'product_id' => $product->id,
                    'qty_out' => 1,
                    'price' => 5000,
                ]],
                'shipping_cost' => 0,
                'tax' => 0,
                'payment_status' => 'Unpaid',
                'payment_method' => 'Transfer',
                'paid_amount' => 0,
            ])->assertSessionHasErrors('client_phone');
        }

        $this->assertDatabaseCount('distributions', 0);
    }
}
