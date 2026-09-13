<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('materials', function (Blueprint $table) {
            if (! Schema::hasColumn('materials', 'category')) {
                $table->string('category')->nullable()->after('material_name');
            }

            if (! Schema::hasColumn('materials', 'unit')) {
                $table->string('unit')->nullable()->after('category');
            }

            if (! Schema::hasColumn('materials', 'price_per_unit')) {
                $table->decimal('price_per_unit', 12, 3)->default(0)->after('minimum_stock');
            }

            if (! Schema::hasColumn('materials', 'selling_price')) {
                $table->decimal('selling_price', 12, 3)->default(0)->after('price_per_unit');
            }
        });

        Schema::table('products', function (Blueprint $table) {
            if (! Schema::hasColumn('products', 'category')) {
                $table->string('category')->nullable()->after('product_name');
            }

            if (! Schema::hasColumn('products', 'unit')) {
                $table->string('unit')->nullable()->after('category');
            }

            if (! Schema::hasColumn('products', 'minimum_stock')) {
                $table->decimal('minimum_stock', 12, 3)->default(0)->after('worker_fee');
            }

            if (! Schema::hasColumn('products', 'warning_stock')) {
                $table->decimal('warning_stock', 12, 3)->default(20)->after('minimum_stock');
            }

            if (! Schema::hasColumn('products', 'price_per_unit')) {
                $table->decimal('price_per_unit', 12, 3)->default(0)->after('warning_stock');
            }

            if (! Schema::hasColumn('products', 'selling_price')) {
                $table->decimal('selling_price', 12, 3)->default(0)->after('price_per_unit');
            }
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE materials MODIFY stock DECIMAL(12,3) NOT NULL DEFAULT 0');
            DB::statement('ALTER TABLE materials MODIFY minimum_stock DECIMAL(12,3) NOT NULL DEFAULT 10');
            DB::statement('ALTER TABLE products MODIFY stock DECIMAL(12,3) NOT NULL DEFAULT 0');
        }
    }

    public function down(): void
    {
        Schema::table('materials', function (Blueprint $table) {
            foreach (['category', 'unit', 'price_per_unit', 'selling_price'] as $column) {
                if (Schema::hasColumn('materials', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('products', function (Blueprint $table) {
            foreach (['category', 'unit', 'minimum_stock', 'warning_stock', 'price_per_unit', 'selling_price'] as $column) {
                if (Schema::hasColumn('products', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE materials MODIFY stock INT NOT NULL DEFAULT 0');
            DB::statement('ALTER TABLE materials MODIFY minimum_stock INT NOT NULL DEFAULT 10');
            DB::statement('ALTER TABLE products MODIFY stock INT NOT NULL DEFAULT 0');
        }
    }
};
