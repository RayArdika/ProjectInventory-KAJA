<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('productions', function (Blueprint $table) {

            $table->id();

            $table->date('production_date');

            $table->string('worker_name');

            $table->string('product_name');

            $table->integer('qty_pass');

            $table->integer('qty_reject');

            $table->string('status')
                ->default('Pending');

            $table->timestamps();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('productions');
    }
};