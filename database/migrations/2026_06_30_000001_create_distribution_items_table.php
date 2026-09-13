<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('distributions', function (Blueprint $table) {
            $table->string('invoice_number')->nullable()->unique()->after('id');
            $table->string('client_phone')->nullable()->after('destination');
            $table->string('payment_method')->default('Transfer')->after('payment_status');
        });

        Schema::create('distribution_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('distribution_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('product_name');
            $table->string('unit')->default('pcs');
            $table->unsignedInteger('qty_out');
            $table->decimal('price', 12, 2)->default(0);
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->timestamps();
        });

        DB::table('distributions')->orderBy('id')->get()->each(function ($distribution) {
            $product = DB::table('products')
                ->where('product_name', $distribution->product_name)
                ->first();
            $date = str_replace('-', '', (string) $distribution->distribution_date);

            DB::table('distributions')
                ->where('id', $distribution->id)
                ->update([
                    'invoice_number' => sprintf('INV-%s-%04d', $date, $distribution->id),
                ]);

            DB::table('distribution_items')->insert([
                'distribution_id' => $distribution->id,
                'product_id' => $product?->id,
                'product_name' => $distribution->product_name,
                'unit' => $product?->unit ?? 'pcs',
                'qty_out' => $distribution->qty_out,
                'price' => $distribution->price,
                'subtotal' => $distribution->qty_out * $distribution->price,
                'created_at' => $distribution->created_at,
                'updated_at' => $distribution->updated_at,
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('distribution_items');

        Schema::table('distributions', function (Blueprint $table) {
            $table->dropUnique(['invoice_number']);
            $table->dropColumn(['invoice_number', 'client_phone', 'payment_method']);
        });
    }
};
