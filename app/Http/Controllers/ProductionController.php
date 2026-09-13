<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Production;
use App\Models\Product;
use App\Models\Payroll;
use App\Models\Material;
use App\Models\ActivityLog;
use App\Models\InventoryLog;
use App\Models\WorkActivity;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ProductionController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | INDEX
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        $productionQuery = Production::query();

        if (auth()->user()->role !== 'admin') {
            $productionQuery->where('worker_name', auth()->user()->name);
        }

        $productionQuery
            ->when($request->filled('date'), fn ($query) =>
                $query->whereDate('production_date', $request->date)
            )
            ->when($request->filled('status'), fn ($query) =>
                $query->where('status', $request->status)
            )
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim($request->search);

                $query->where(function ($query) use ($search) {
                    $query->where('worker_name', 'like', "%{$search}%")
                        ->orWhere('activity_name', 'like', "%{$search}%")
                        ->orWhere('product_name', 'like', "%{$search}%")
                        ->orWhere('note', 'like', "%{$search}%");
                });
            });

        $productions = $productionQuery
            ->latest('production_date')
            ->latest('created_at')
            ->paginate(10)
            ->withQueryString();

        /*
        |--------------------------------------------------------------------------
        | PRODUCTS
        |--------------------------------------------------------------------------
        */

        $availableWork = WorkActivity::with('product')
            ->where('is_active', true)
            ->orderBy('activity_name')
            ->get();

        $jobOptions = $availableWork
            ->pluck('activity_name')
            ->unique()
            ->values();

        $products = Product::whereIn('id', $availableWork->pluck('product_id'))
            ->with(['workActivities' => fn ($query) => $query->where('is_active', true)])
            ->orderBy('sku')
            ->get();

        $workers = User::where('role', 'worker')
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view(
            'produksi',
            compact('productions', 'jobOptions', 'products', 'workers')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | STORE
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        $workerName = auth()->user()->role == 'admin'
            ? $request->worker_name
            : auth()->user()->name;

        $validated = $request->validate([
            'production_date' => [
                'required',
                'date',
                'after_or_equal:' . now()->startOfMonth()->toDateString(),
                'before_or_equal:' . now()->toDateString(),
            ],
            'worker_name' => auth()->user()->role === 'admin'
                ? [
                    'required',
                    Rule::exists('users', 'name')->where(fn ($query) =>
                        $query->where('role', 'worker')->where('is_active', true)
                    ),
                ]
                : ['nullable'],
            'activity_name' => ['required', 'string', 'max:255'],
            'product_id' => ['required', 'exists:products,id'],
            'qty_pass' => ['required', 'integer', 'min:1'],
            'qty_reject' => ['required', 'integer', 'min:0'],
            'note' => [
                Rule::requiredIf(fn () => (int) $request->qty_reject > 0),
                'nullable',
                'string',
            ],
        ]);

        $workActivity = WorkActivity::with('product')
            ->where('is_active', true)
            ->where('activity_name', $validated['activity_name'])
            ->where('product_id', $validated['product_id'])
            ->first();

        if (! $workActivity) {
            throw ValidationException::withMessages([
                'product_id' => 'The selected job is not available for this product.',
            ]);
        }

        $production = Production::create([

            'production_date' => $validated['production_date'],

            'worker_name' => $workerName,

            'activity_name' => $workActivity->activity_name,

            'work_activity_id' => $workActivity->id,

            'category' => $workActivity->product->category,

            'product_name' => $workActivity->product->product_name,

            'qty_pass' => $validated['qty_pass'],

            'qty_reject' => $validated['qty_reject'],

            'status' => 'Pending',

            'note' => $validated['note'] ?? null,

        ]);

        /*
        |--------------------------------------------------------------------------
        | ACTIVITY LOG
        |--------------------------------------------------------------------------
        */

        ActivityLog::create([

            'user_name' => auth()->user()->name,

            'activity' => sprintf(
                '%s recorded %s %s for %s (%s)',
                $workerName,
                $validated['qty_pass'],
                $workActivity->unit,
                $workActivity->activity_name,
                $workActivity->product->product_name
            ),

        ]);

        return redirect()->back()->with(
            'success',
            'Work result saved as Pending: ' .
            $production->activity_name . ' - ' .
            $production->product_name
        );
    }

    /*
    |--------------------------------------------------------------------------
    | EDIT
    |--------------------------------------------------------------------------
    */

    public function edit($id)
    {
        $production = Production::findOrFail($id);

        abort_if(
            auth()->user()->role != 'admin' &&
            $production->worker_name != auth()->user()->name,
            403
        );
        abort_unless(in_array($production->status, ['Pending', 'Returned'], true), 403);

        $availableWork = WorkActivity::with('product')
            ->where('is_active', true)
            ->orderBy('activity_name')
            ->get();

        $jobOptions = $availableWork
            ->pluck('activity_name')
            ->unique()
            ->values();

        $products = Product::whereIn('id', $availableWork->pluck('product_id'))
            ->with(['workActivities' => fn ($query) => $query->where('is_active', true)])
            ->orderBy('sku')
            ->get();

        $workers = User::where('role', 'worker')
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view(
            'edit-produksi',
            compact('production', 'jobOptions', 'products', 'workers')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */

    public function update(Request $request, $id)
    {
        $production = Production::findOrFail($id);

        abort_if(
            auth()->user()->role != 'admin' &&
            $production->worker_name != auth()->user()->name,
            403
        );
        abort_unless(in_array($production->status, ['Pending', 'Returned'], true), 403);

        $workerName = auth()->user()->role == 'admin'
            ? $request->worker_name
            : auth()->user()->name;

        $validated = $request->validate([
            'production_date' => [
                'required',
                'date',
                'after_or_equal:' . now()->startOfMonth()->toDateString(),
                'before_or_equal:' . now()->toDateString(),
            ],
            'worker_name' => auth()->user()->role === 'admin'
                ? [
                    'required',
                    Rule::exists('users', 'name')->where(fn ($query) =>
                        $query->where('role', 'worker')->where('is_active', true)
                    ),
                ]
                : ['nullable'],
            'activity_name' => ['required', 'string', 'max:255'],
            'product_id' => ['required', 'exists:products,id'],
            'qty_pass' => ['required', 'integer', 'min:1'],
            'qty_reject' => ['required', 'integer', 'min:0'],
            'note' => [
                Rule::requiredIf(fn () => (int) $request->qty_reject > 0),
                'nullable',
                'string',
            ],
        ]);

        $workActivity = WorkActivity::with('product')
            ->where('is_active', true)
            ->where('activity_name', $validated['activity_name'])
            ->where('product_id', $validated['product_id'])
            ->first();

        if (! $workActivity) {
            throw ValidationException::withMessages([
                'product_id' => 'The selected job is not available for this product.',
            ]);
        }

        $production->update([

            'production_date' => $validated['production_date'],

            'worker_name' => $workerName,

            'activity_name' => $workActivity->activity_name,

            'work_activity_id' => $workActivity->id,

            'category' => $workActivity->product->category,

            'product_name' => $workActivity->product->product_name,

            'qty_pass' => $validated['qty_pass'],

            'qty_reject' => $validated['qty_reject'],

            'note' => $validated['note'] ?? null,

            'status' => 'Pending',

        ]);

        /*
        |--------------------------------------------------------------------------
        | ACTIVITY LOG
        |--------------------------------------------------------------------------
        */

        ActivityLog::create([

            'user_name' => auth()->user()->name,

            'activity' => 'Edit produksi',

        ]);

        return redirect('/produksi');
    }

    /*
    |--------------------------------------------------------------------------
    | DELETE
    |--------------------------------------------------------------------------
    */

    public function destroy($id)
    {
        $production = Production::findOrFail($id);

        abort_if(
            auth()->user()->role != 'admin' &&
            $production->worker_name != auth()->user()->name,
            403
        );
        abort_unless(in_array($production->status, ['Pending', 'Returned'], true), 403);

        /*
        |--------------------------------------------------------------------------
        | ACTIVITY LOG
        |--------------------------------------------------------------------------
        */

        ActivityLog::create([

            'user_name' => auth()->user()->name,

            'activity' => 'Hapus produksi',

        ]);

        $production->delete();

        return redirect()->back();
    }

    /*
    |--------------------------------------------------------------------------
    | QC PAGE
    |--------------------------------------------------------------------------
    */

    public function qc()
    {
        $productions = Production::where('status', 'Pending')
            ->latest()
            ->paginate(10);

        return view('qc', compact('productions'));
    }

    /*
    |--------------------------------------------------------------------------
    | APPROVE
    |--------------------------------------------------------------------------
    */

    public function approve($id)
    {
        abort_unless(auth()->user()->role == 'admin', 403);

        /*
        |--------------------------------------------------------------------------
        | FIND PRODUCTION
        |--------------------------------------------------------------------------
        */

        $production = Production::findOrFail($id);

        /*
        |--------------------------------------------------------------------------
        | CEK AGAR TIDAK APPROVE DOBEL / DATA SUDAH DIPROSES
        |--------------------------------------------------------------------------
        */

        if ($production->status != 'Pending') {

            return redirect()->back();

        }

        /*
        |--------------------------------------------------------------------------
        | CARI PRODUK
        |--------------------------------------------------------------------------
        */

        $workActivity = $production->workActivity()
            ->with(['product', 'materialRequirements.material'])
            ->first();

        $product = $workActivity?->product;

        /*
        |--------------------------------------------------------------------------
        | VALIDASI PRODUK DAN STOK BAHAN BAKU
        |--------------------------------------------------------------------------
        */

        if (!$product) {

            return redirect()
                ->back()
                ->withErrors(['product' => 'Work activity and product configuration were not found.']);

        }

        $requirements = $workActivity->materialRequirements;

        $insufficientMaterials = [];
        $totalQuantity = $production->qty_pass + $production->qty_reject;

        foreach ($requirements as $requirement) {

            $material = $requirement->material;

            if (!$material) {

                continue;

            }

            $neededStock =
                $requirement->qty_needed *
                $totalQuantity;

            if ($material->stock < $neededStock) {

                $insufficientMaterials[] = sprintf(
                    '%s: required %s pcs, available %s pcs',
                    $material->material_name,
                    number_format($neededStock, 0, '.', ','),
                    number_format($material->stock, 0, '.', ',')
                );

            }
        }

        if (!empty($insufficientMaterials)) {

            return redirect()
                ->back()
                ->withErrors([
                    'materials' =>
                        "Approval failed.\nInsufficient stock:\n- " .
                        implode("\n- ", $insufficientMaterials),
                ]);

        }

        /*
        |--------------------------------------------------------------------------
        | UPDATE DATA APPROVE
        |--------------------------------------------------------------------------
        */

        DB::transaction(function () use ($production, $product, $requirements, $workActivity, $totalQuantity) {
            $lockedProduct = Product::whereKey($product->id)
                ->lockForUpdate()
                ->firstOrFail();

            $lockedMaterials = [];

            foreach ($requirements as $requirement) {
                $material = Material::whereKey($requirement->material_id)
                    ->lockForUpdate()
                    ->first();

                if (! $material) {
                    continue;
                }

                $usedStock = $requirement->qty_needed * $totalQuantity;

                if ($material->stock < $usedStock) {
                    throw ValidationException::withMessages([
                        'materials' => sprintf(
                            'Approval failed. %s requires %s pcs, but only %s pcs is available.',
                            $material->material_name,
                            number_format($usedStock, 0, '.', ','),
                            number_format($material->stock, 0, '.', ',')
                        ),
                    ]);
                }

                $lockedMaterials[$requirement->material_id] = $material;
            }

            /*
            |--------------------------------------------------------------------------
            | UPDATE STATUS
            |--------------------------------------------------------------------------
            */

            $production->status = 'Approved';

            $production->save();

            /*
            |--------------------------------------------------------------------------
            | ACTIVITY LOG
            |--------------------------------------------------------------------------
            */

            ActivityLog::create([

                'user_name' => auth()->user()->name,

                'activity' => sprintf(
                    'Approved %s: %s completed %s %s of %s',
                    $production->production_code ?: '#' . $production->id,
                    $production->worker_name,
                    $production->qty_pass,
                    $workActivity?->unit ?? 'unit',
                    $production->activity_name . ' - ' . $production->product_name
                ),

            ]);

            /*
            |--------------------------------------------------------------------------
            | UPDATE STOCK PRODUK JADI
            |--------------------------------------------------------------------------
            */

            $lockedProduct->stock =
                $lockedProduct->stock + $production->qty_pass;

            $lockedProduct->save();

            InventoryLog::create([
                'inventory_date' => now()->toDateString(),
                'user_name' => auth()->user()->name,
                'action' => 'Production approved - finished stock added',
                'item_type' => 'product',
                'sku' => $lockedProduct->sku,
                'item_name' => $lockedProduct->product_name,
                'category' => $lockedProduct->category,
                'unit' => $lockedProduct->unit,
                'stock' => $lockedProduct->stock,
                'note' => sprintf(
                    '%s: +%s completed pcs',
                    $production->production_code ?: '#' . $production->id,
                    $production->qty_pass
                ),
            ]);

            /*
            |--------------------------------------------------------------------------
            | BOM SYSTEM
            |--------------------------------------------------------------------------
            | POTONG STOCK BAHAN BAKU
            |--------------------------------------------------------------------------
            */

            foreach ($requirements as $requirement) {

                $material = $lockedMaterials[$requirement->material_id] ?? null;

                if ($material) {

                    /*
                    |--------------------------------------------------------------------------
                    | HITUNG PEMAKAIAN BAHAN
                    |--------------------------------------------------------------------------
                    */

                    $usedStock =
                        $requirement->qty_needed *
                        $totalQuantity;

                    /*
                    |--------------------------------------------------------------------------
                    | KURANGI STOCK
                    |--------------------------------------------------------------------------
                    */

                    $material->stock =
                        $material->stock - $usedStock;

                    $material->save();

                    InventoryLog::create([
                        'inventory_date' => now()->toDateString(),
                        'user_name' => auth()->user()->name,
                        'action' => 'Production approved - material consumed',
                        'item_type' => 'material',
                        'sku' => null,
                        'item_name' => $material->material_name,
                        'category' => $material->category,
                        'unit' => $material->unit,
                        'stock' => $material->stock,
                        'note' => sprintf(
                            '%s: -%s pcs for %s completed and %s rejected',
                            $production->production_code ?: '#' . $production->id,
                            $usedStock,
                            $production->qty_pass,
                            $production->qty_reject
                        ),
                    ]);
                }
            }

            /*
            |--------------------------------------------------------------------------
            | CEK PAYROLL AGAR TIDAK DUPLICATE
            |--------------------------------------------------------------------------
            */

            $payrollExists = Payroll::where('production_id', $production->id)->exists();

            /*
            |--------------------------------------------------------------------------
            | CREATE PAYROLL
            |--------------------------------------------------------------------------
            */

            if (!$payrollExists) {

                Payroll::create([

                    'production_id' =>
                        $production->id,

                    'worker_name' =>
                        $production->worker_name,

                    'product_name' =>
                        $production->product_name,

                    'activity_name' =>
                        $production->activity_name,

                    'qty' =>
                        $production->qty_pass,

                    'unit' =>
                        $workActivity?->unit ?? $product->unit ?? 'unit',

                    'fee' =>
                        $workActivity?->fee_per_unit ?? $product->worker_fee,

                    'total_salary' =>
                        $production->qty_pass *
                        ($workActivity?->fee_per_unit ?? $product->worker_fee),

                ]);
            }

        });

        /*
        |--------------------------------------------------------------------------
        | REDIRECT
        |--------------------------------------------------------------------------
        */

        return redirect()->back();
    }

    /*
    |--------------------------------------------------------------------------
    | REJECT
    |--------------------------------------------------------------------------
    */

    public function reject(Request $request, $id)
    {
        abort_unless(auth()->user()->role == 'admin', 403);

        $production = Production::findOrFail($id);

        if ($production->status !== 'Pending') {
            return redirect()->back();
        }

        $validated = $request->validate([
            'return_note' => ['required', 'string', 'max:1000'],
        ]);

        $production->update([
            'status' => 'Returned',
            'note' => trim(
                ($production->note ? $production->note . PHP_EOL : '') .
                'Correction requested: ' . $validated['return_note']
            ),
        ]);

        /*
        |--------------------------------------------------------------------------
        | ACTIVITY LOG
        |--------------------------------------------------------------------------
        */

        ActivityLog::create([

            'user_name' => auth()->user()->name,

            'activity' => sprintf(
                'Returned production %s for correction: %s',
                $production->production_code ?: '#' . $production->id,
                $validated['return_note']
            ),

        ]);

        return redirect()->back();
    }
}
