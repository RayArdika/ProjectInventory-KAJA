<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bill_of_materials', function (Blueprint $table) {

            $table->id();

            $table->foreignId('product_id')
                ->constrained()
                ->onDelete('cascade');

            $table->foreignId('material_id')
                ->constrained()
                ->onDelete('cascade');

            $table->integer('qty_needed');

            $table->timestamps();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bill_of_materials');
    }
};