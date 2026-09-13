<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('activity_name');
            $table->string('unit')->default('pcs');
            $table->decimal('fee_per_unit', 12, 2)->default(0);
            $table->boolean('adds_finished_stock')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['product_id', 'activity_name']);
        });

        Schema::create('work_activity_materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_activity_id')->constrained()->cascadeOnDelete();
            $table->foreignId('material_id')->constrained()->cascadeOnDelete();
            $table->decimal('qty_needed', 12, 3);
            $table->timestamps();

            $table->unique(['work_activity_id', 'material_id']);
        });

        Schema::table('productions', function (Blueprint $table) {
            $table->foreignId('work_activity_id')
                ->nullable()
                ->after('activity_name')
                ->constrained()
                ->nullOnDelete();
        });

        Schema::table('payrolls', function (Blueprint $table) {
            $table->foreignId('production_id')
                ->nullable()
                ->after('id')
                ->constrained()
                ->nullOnDelete();
            $table->string('activity_name')->nullable()->after('product_name');
            $table->string('unit')->nullable()->after('qty');
        });
    }

    public function down(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            $table->dropConstrainedForeignId('production_id');
            $table->dropColumn(['activity_name', 'unit']);
        });

        Schema::table('productions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('work_activity_id');
        });

        Schema::dropIfExists('work_activity_materials');
        Schema::dropIfExists('work_activities');
    }
};
