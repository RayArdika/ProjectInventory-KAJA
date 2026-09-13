<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('products')
            ->whereNotIn('unit', ['pcs', 'pack'])
            ->update(['unit' => 'pcs']);

        DB::table('work_activities')
            ->whereNotIn('unit', ['pcs', 'pack'])
            ->update(['unit' => 'pcs']);

        DB::table('materials')
            ->where('unit', 'pck')
            ->update(['unit' => 'pack']);

        $usedMaterialIds = DB::table('work_activity_materials')
            ->pluck('material_id')
            ->merge(DB::table('bill_of_materials')->pluck('material_id'))
            ->unique()
            ->values();

        if ($usedMaterialIds->isEmpty()) {
            return;
        }

        DB::table('materials')
            ->whereNotIn('id', $usedMaterialIds)
            ->delete();
    }

    public function down(): void
    {
        // Removed legacy inventory is restored from the database backup if needed.
    }
};
