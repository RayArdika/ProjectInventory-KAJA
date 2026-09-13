<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\Product;
use App\Models\ActivityLog;
use App\Models\InventoryLog;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class InventoryController extends Controller
{
    public function index()
    {
        $products = Product::orderBy('sku')->get();
        $nextProductSku = Product::nextSku();

        $materials = Material::orderBy('category')
            ->orderBy('material_name')
            ->get();

        $inventoryLogs = InventoryLog::latest()->limit(20)->get();

        return view('inventory', compact('products', 'materials', 'inventoryLogs', 'nextProductSku'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => ['required', Rule::in(['product', 'material'])],
            'inventory_date' => ['required', 'date'],
            'name' => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:120'],
            'unit' => ['required', Rule::in(['pcs'])],
            'stock' => ['nullable', 'integer', 'min:0'],
            'minimum_stock' => ['nullable', 'integer', 'min:0'],
            'warning_stock' => ['nullable', 'integer', 'min:0'],
            'price_per_unit' => ['nullable', 'numeric', 'min:0'],
            'selling_price' => ['nullable', 'numeric', 'min:0'],
            'worker_fee' => ['nullable', 'numeric', 'min:0'],
            'note' => ['nullable', 'string'],
        ]);

        if ($validated['type'] === 'product') {
            $request->validate([
                'name' => ['required', 'regex:/^(Kantong|Sachet|Hampers)(?:\s|$)/i'],
                'unit' => ['required', Rule::in(['pcs'])],
                'stock' => ['nullable', 'integer', 'min:0'],
                'minimum_stock' => ['nullable', 'integer', 'min:0'],
                'warning_stock' => ['nullable', 'integer', 'min:0'],
            ], [
                'name.regex' => 'Finished good name must start with Kantong, Sachet, or Hampers.',
                'unit.in' => 'Inventory unit must be pcs.',
                'stock.integer' => 'Finished good stock must be a whole number.',
            ]);

            $item = Product::create([
                'sku' => Product::nextSku(),
                'product_name' => $validated['name'],
                'category' => $validated['category'] ?? 'STOCK JADI',
                'unit' => $validated['unit'],
                'stock' => $validated['stock'] ?? 0,
                'minimum_stock' => $validated['minimum_stock'] ?? 0,
                'warning_stock' => $validated['warning_stock'] ?? 20,
                'price_per_unit' => $validated['price_per_unit'] ?? 0,
                'selling_price' => $validated['selling_price'] ?? 0,
                'worker_fee' => $validated['worker_fee'] ?? 0,
            ]);
        } else {
            $item = Material::create([
                'material_name' => $validated['name'],
                'category' => $validated['category'] ?? 'STOCK MATERIAL',
                'unit' => $validated['unit'],
                'stock' => $validated['stock'] ?? 0,
                'minimum_stock' => $validated['minimum_stock'] ?? 0,
                'price_per_unit' => $validated['price_per_unit'] ?? 0,
                'selling_price' => $validated['selling_price'] ?? 0,
            ]);
        }

        $this->recordInventoryLog('Created inventory item', $validated, $item);

        return redirect('/inventory');
    }

    public function update(Request $request, string $type, int $id)
    {
        abort_unless(in_array($type, ['product', 'material'], true), 404);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:120'],
            'unit' => ['required', Rule::in(['pcs'])],
            'stock' => ['nullable', 'integer', 'min:0'],
            'minimum_stock' => ['nullable', 'integer', 'min:0'],
            'warning_stock' => ['nullable', 'integer', 'min:0'],
            'price_per_unit' => ['nullable', 'numeric', 'min:0'],
            'selling_price' => ['nullable', 'numeric', 'min:0'],
            'worker_fee' => ['nullable', 'numeric', 'min:0'],
        ]);

        if ($type === 'product') {
            $product = Product::findOrFail($id);

            $request->validate([
                'name' => ['required', 'regex:/^(Kantong|Sachet|Hampers)(?:\s|$)/i'],
                'unit' => ['required', Rule::in(['pcs'])],
                'stock' => ['nullable', 'integer', 'min:0'],
                'minimum_stock' => ['nullable', 'integer', 'min:0'],
                'warning_stock' => ['nullable', 'integer', 'min:0'],
            ], [
                'name.regex' => 'Finished good name must start with Kantong, Sachet, or Hampers.',
                'unit.in' => 'Inventory unit must be pcs.',
                'stock.integer' => 'Finished good stock must be a whole number.',
            ]);

            $product->update([
                'product_name' => $validated['name'],
                'category' => $validated['category'] ?? $product->category,
                'unit' => $validated['unit'],
                'stock' => $validated['stock'] ?? 0,
                'minimum_stock' => $validated['minimum_stock'] ?? 0,
                'warning_stock' => $validated['warning_stock'] ?? 20,
                'price_per_unit' => $validated['price_per_unit'] ?? 0,
                'selling_price' => $validated['selling_price'] ?? 0,
                'worker_fee' => $validated['worker_fee'] ?? 0,
            ]);

            $this->recordInventoryLog('Updated inventory item', [
                ...$validated,
                'type' => 'product',
                'inventory_date' => now()->toDateString(),
            ], $product);
        } else {
            $material = Material::findOrFail($id);

            $material->update([
                'material_name' => $validated['name'],
                'category' => $validated['category'] ?? $material->category,
                'unit' => $validated['unit'],
                'stock' => $validated['stock'] ?? 0,
                'minimum_stock' => $validated['minimum_stock'] ?? 0,
                'price_per_unit' => $validated['price_per_unit'] ?? 0,
                'selling_price' => $validated['selling_price'] ?? 0,
            ]);

            $this->recordInventoryLog('Updated inventory item', [
                ...$validated,
                'type' => 'material',
                'inventory_date' => now()->toDateString(),
            ], $material);
        }

        return redirect('/inventory');
    }

    public function destroy(string $type, int $id)
    {
        abort_unless(in_array($type, ['product', 'material'], true), 404);

        if ($type === 'product') {
            $item = Product::findOrFail($id);
            $this->recordInventoryLog('Deleted inventory item', [
                'type' => 'product',
                'inventory_date' => now()->toDateString(),
                'name' => $item->product_name,
                'sku' => $item->sku,
                'category' => $item->category,
                'unit' => $item->unit,
                'stock' => $item->stock,
            ], $item);
            $item->delete();
        } else {
            $item = Material::findOrFail($id);
            $this->recordInventoryLog('Deleted inventory item', [
                'type' => 'material',
                'inventory_date' => now()->toDateString(),
                'name' => $item->material_name,
                'category' => $item->category,
                'unit' => $item->unit,
                'stock' => $item->stock,
            ], $item);
            $item->delete();
        }

        return redirect('/inventory');
    }

    private function recordInventoryLog(string $action, array $data, Product|Material $item): void
    {
        $itemName = $item instanceof Product ? $item->product_name : $item->material_name;
        $sku = $item instanceof Product ? $item->sku : null;

        InventoryLog::create([
            'inventory_date' => $data['inventory_date'],
            'user_name' => auth()->user()->name,
            'action' => $action,
            'item_type' => $data['type'],
            'sku' => $sku,
            'item_name' => $itemName,
            'category' => $data['category'] ?? $item->category,
            'unit' => $data['unit'] ?? $item->unit,
            'stock' => $data['stock'] ?? $item->stock,
            'note' => $data['note'] ?? null,
        ]);

        ActivityLog::create([
            'user_name' => auth()->user()->name,
            'activity' => $action . ': ' . $itemName,
        ]);
    }
}
