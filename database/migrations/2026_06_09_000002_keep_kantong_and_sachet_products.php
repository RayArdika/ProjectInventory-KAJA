<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('products')
            ->whereRaw('LOWER(product_name) NOT LIKE ?', ['kantong %'])
            ->whereRaw('LOWER(product_name) NOT LIKE ?', ['sachet %'])
            ->delete();

        $products = DB::table('products')
            ->orderByRaw(
                "CASE WHEN LOWER(product_name) LIKE 'kantong %' THEN 0 ELSE 1 END"
            )
            ->orderBy('id')
            ->get();

        foreach ($products as $index => $product) {
            DB::table('products')
                ->where('id', $product->id)
                ->update([
                    'sku' => str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT),
                ]);
        }

        Schema::table('products', function (Blueprint $table) {
            $table->unique('sku', 'products_sku_unique');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropUnique('products_sku_unique');
        });
    }
};
