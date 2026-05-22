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
        Schema::table('order_items', function (Blueprint $table) {
            $table->unsignedInteger('returned_good_qty')->default(0)->after('size');
            $table->unsignedInteger('returned_damaged_qty')->default(0)->after('returned_good_qty');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->text('return_notes')->nullable()->after('return_verified_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn(['returned_good_qty', 'returned_damaged_qty']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('return_notes');
        });
    }
};
