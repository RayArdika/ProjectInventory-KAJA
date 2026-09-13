<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('products')->update(['unit' => 'pcs']);
        DB::table('work_activities')->update(['unit' => 'pcs']);

        DB::table('materials')
            ->select('id', 'stock', 'minimum_stock')
            ->orderBy('id')
            ->each(function ($material) {
                DB::table('materials')
                    ->where('id', $material->id)
                    ->update([
                        'unit' => 'pcs',
                        'stock' => floor((float) $material->stock),
                        'minimum_stock' => floor((float) $material->minimum_stock),
                    ]);
            });

        DB::table('bill_of_materials')->delete();

        $activeMaterialIds = DB::table('work_activity_materials')
            ->pluck('material_id')
            ->unique()
            ->values();

        DB::table('materials')
            ->whereNotIn('id', $activeMaterialIds)
            ->delete();

        DB::table('inventory_logs')->update(['unit' => 'pcs']);
        DB::table('payrolls')->update(['unit' => 'pcs']);
    }

    public function down(): void
    {
        // Original gram data can be restored from the database backup.
    }
};
