<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('products', 'cost_price')) {
            Schema::table('products', function (Blueprint $table) {
                $table->decimal('cost_price', 10, 2)->default(0)->after('price');
            });
        }

        if (!Schema::hasColumn('order_items', 'cost_at_purchase')) {
            Schema::table('order_items', function (Blueprint $table) {
                $table->decimal('cost_at_purchase', 10, 2)->default(0)->after('price_at_purchase');
            });
        }

        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->string('supplier_name');
            $table->string('reference_no')->nullable();
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->date('date');
            $table->string('status')->default('completed');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('purchase_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->integer('quantity');
            $table->decimal('unit_cost', 10, 2);
            $table->timestamps();
        });

        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->string('category');
            $table->decimal('amount', 12, 2);
            $table->date('date');
            $table->string('description')->nullable();
            $table->string('reference_no')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenses');
        Schema::dropIfExists('purchase_items');
        Schema::dropIfExists('purchases');

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn('cost_at_purchase');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('cost_price');
        });
    }
};
