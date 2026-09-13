<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('productions')) {
            Schema::table('productions', function (Blueprint $table) {
                if (! Schema::hasColumn('productions', 'activity_name')) {
                    $table->string('activity_name')->nullable()->after('worker_name');
                }

                if (! Schema::hasColumn('productions', 'category')) {
                    $table->string('category')->nullable()->after('activity_name');
                }

                if (! Schema::hasColumn('productions', 'note')) {
                    $table->text('note')->nullable()->after('status');
                }
            });
        }

        if (! Schema::hasTable('distributions')) {
            Schema::create('distributions', function (Blueprint $table) {
                $table->id();
                $table->date('distribution_date');
                $table->string('product_name');
                $table->integer('qty_out');
                $table->string('destination');
                $table->timestamps();
            });
        }

        Schema::table('distributions', function (Blueprint $table) {
            if (! Schema::hasColumn('distributions', 'price')) {
                $table->decimal('price', 12, 2)->default(0)->after('qty_out');
            }

            if (! Schema::hasColumn('distributions', 'shipping_cost')) {
                $table->decimal('shipping_cost', 12, 2)->default(0)->after('price');
            }

            if (! Schema::hasColumn('distributions', 'tax')) {
                $table->decimal('tax', 12, 2)->default(0)->after('shipping_cost');
            }

            if (! Schema::hasColumn('distributions', 'total_amount')) {
                $table->decimal('total_amount', 12, 2)->default(0)->after('tax');
            }

            if (! Schema::hasColumn('distributions', 'payment_status')) {
                $table->string('payment_status')->default('Unpaid')->after('total_amount');
            }

            if (! Schema::hasColumn('distributions', 'paid_amount')) {
                $table->decimal('paid_amount', 12, 2)->default(0)->after('payment_status');
            }

            if (! Schema::hasColumn('distributions', 'note')) {
                $table->text('note')->nullable()->after('destination');
            }
        });

        if (! Schema::hasTable('inventory_logs')) {
            Schema::create('inventory_logs', function (Blueprint $table) {
                $table->id();
                $table->date('inventory_date');
                $table->string('user_name');
                $table->string('action');
                $table->string('item_type');
                $table->string('sku')->nullable();
                $table->string('item_name');
                $table->string('category')->nullable();
                $table->string('unit')->nullable();
                $table->decimal('stock', 12, 3)->default(0);
                $table->text('note')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_logs');

        if (Schema::hasTable('distributions')) {
            Schema::table('distributions', function (Blueprint $table) {
                foreach (['price', 'shipping_cost', 'tax', 'total_amount', 'payment_status', 'paid_amount', 'note'] as $column) {
                    if (Schema::hasColumn('distributions', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }

        if (Schema::hasTable('productions')) {
            Schema::table('productions', function (Blueprint $table) {
                foreach (['activity_name', 'category', 'note'] as $column) {
                    if (Schema::hasColumn('productions', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
