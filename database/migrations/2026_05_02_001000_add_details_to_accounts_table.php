<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            if (!Schema::hasColumn('accounts', 'account_number')) {
                $table->string('account_number')->nullable()->after('type');
            }
            if (!Schema::hasColumn('accounts', 'bank_name')) {
                $table->string('bank_name')->nullable()->after('account_number');
            }
            if (!Schema::hasColumn('accounts', 'branch')) {
                $table->string('branch')->nullable()->after('bank_name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->dropColumn(['account_number', 'bank_name', 'branch']);
        });
    }
};
