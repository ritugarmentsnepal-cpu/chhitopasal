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
        Schema::table('orders', function (Blueprint $table) {
            $table->timestamp('shipped_at')->nullable()->after('status');
        });

        // Backfill: set shipped_at = updated_at for existing shipped/delivered/failed/rejected/return_delivered orders
        DB::table('orders')
            ->whereIn('status', ['shipped', 'delivered', 'failed', 'rejected', 'return_delivered'])
            ->whereNull('shipped_at')
            ->update(['shipped_at' => DB::raw('updated_at')]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('shipped_at');
        });
    }
};
