<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * SEC-HIGH-03: Add soft delete columns to critical financial and operational tables.
 * This ensures deleted records are preserved for audit trail and recovery.
 */
return new class extends Migration
{
    public function up(): void
    {
        $tables = ['orders', 'products', 'transactions', 'expenses', 'purchases'];

        foreach ($tables as $table) {
            if (Schema::hasTable($table) && !Schema::hasColumn($table, 'deleted_at')) {
                Schema::table($table, function (Blueprint $table) {
                    $table->softDeletes();
                });
            }
        }
    }

    public function down(): void
    {
        $tables = ['orders', 'products', 'transactions', 'expenses', 'purchases'];

        foreach ($tables as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'deleted_at')) {
                Schema::table($table, function (Blueprint $table) {
                    $table->dropSoftDeletes();
                });
            }
        }
    }
};
