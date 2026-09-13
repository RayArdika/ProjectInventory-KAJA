<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('work_activities', function (Blueprint $table) {
            $table->dropColumn('adds_finished_stock');
        });
    }

    public function down(): void
    {
        Schema::table('work_activities', function (Blueprint $table) {
            $table->boolean('adds_finished_stock')->default(true)->after('fee_per_unit');
        });
    }
};
