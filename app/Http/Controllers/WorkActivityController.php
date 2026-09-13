<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Material;
use App\Models\Product;
use App\Models\WorkActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class WorkActivityController extends Controller
{
    public function index()
    {
        $activities = WorkActivity::with(['product', 'materialRequirements.material'])
            ->orderBy('activity_name')
            ->orderBy('product_id')
            ->get();

        $products = Product::orderBy('sku', 'asc')->get();
        $materials = Material::orderBy('material_name')->get();

        return view('work-activities', compact('activities', 'products', 'materials'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateActivity($request);

        DB::transaction(function () use ($validated) {
            $activity = WorkActivity::create([
                'product_id' => $validated['product_id'],
                'activity_name' => $validated['activity_name'],
                'unit' => $validated['unit'],
                'fee_per_unit' => $validated['fee_per_unit'],
                'is_active' => $validated['is_active'] ?? false,
            ]);

            $this->syncMaterials($activity, $validated);

            ActivityLog::create([
                'user_name' => auth()->user()->name,
                'activity' => 'Created work activity: ' . $activity->activity_name,
            ]);
        });

        return redirect('/work-activities')
            ->with('success', 'Work activity created.')
            ->withInput($request->except('_token'));
    }

    public function update(Request $request, WorkActivity $workActivity)
    {
        $validated = $this->validateActivity($request, $workActivity->id);

        DB::transaction(function () use ($validated, $workActivity) {
            $workActivity->update([
                'product_id' => $validated['product_id'],
                'activity_name' => $validated['activity_name'],
                'unit' => $validated['unit'],
                'fee_per_unit' => $validated['fee_per_unit'],
                'is_active' => $validated['is_active'] ?? false,
            ]);

            $this->syncMaterials($workActivity, $validated);

            ActivityLog::create([
                'user_name' => auth()->user()->name,
                'activity' => 'Updated work activity: ' . $workActivity->activity_name,
            ]);
        });

        return redirect('/work-activities#activity-' . $workActivity->id)
            ->with('success', 'Work activity updated.')
            ->withInput($request->except('_token', '_method'));
    }

    public function destroy(WorkActivity $workActivity)
    {
        $name = $workActivity->activity_name;
        $workActivity->delete();

        ActivityLog::create([
            'user_name' => auth()->user()->name,
            'activity' => 'Deleted work activity: ' . $name,
        ]);

        return redirect('/work-activities')->with('success', 'Work activity deleted.');
    }

    private function validateActivity(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'activity_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('work_activities')
                    ->where(fn ($query) => $query->where('product_id', $request->product_id))
                    ->ignore($ignoreId),
            ],
            'unit' => ['required', Rule::in(['pcs'])],
            'fee_per_unit' => ['required', 'numeric', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'material_id' => ['nullable', 'array'],
            'material_id.*' => ['nullable', 'exists:materials,id'],
            'qty_needed' => ['nullable', 'array'],
            'qty_needed.*' => ['nullable', 'numeric', 'min:0.001'],
        ]);
    }

    private function syncMaterials(WorkActivity $activity, array $validated): void
    {
        $requirements = collect($validated['material_id'] ?? [])
            ->map(function ($materialId, $index) use ($validated) {
                $qty = $validated['qty_needed'][$index] ?? null;

                if (! $materialId || ! $qty) {
                    return null;
                }

                return [
                    'material_id' => $materialId,
                    'qty_needed' => $qty,
                ];
            })
            ->filter()
            ->unique('material_id')
            ->values();

        $activity->materialRequirements()->delete();
        $activity->materialRequirements()->createMany($requirements->all());
    }
}
