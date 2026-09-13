<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE bill_of_materials MODIFY qty_needed DECIMAL(12,3) NOT NULL DEFAULT 0');
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE bill_of_materials MODIFY qty_needed INT NOT NULL DEFAULT 0');
        }
    }
};
