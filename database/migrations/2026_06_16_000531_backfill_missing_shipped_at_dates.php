<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Backfill shipped_at for orders that are shipped, delivered, or return_delivered
        // and have a missing shipped_at date. We fall back to pathao_status_updated_at or created_at
        DB::statement("
            UPDATE orders 
            SET shipped_at = COALESCE(pathao_status_updated_at, created_at)
            WHERE status IN ('shipped', 'delivered', 'return_delivered') 
            AND shipped_at IS NULL
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Cannot cleanly reverse this, as we won't know which were originally NULL
    }
};
