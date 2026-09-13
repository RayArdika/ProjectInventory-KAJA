<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('materials', function (Blueprint $table) {

            $table->id();

            $table->string('material_name');

            $table->integer('stock')->default(0);

            $table->integer('minimum_stock')->default(10);

            $table->timestamps();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('materials');
    }
};