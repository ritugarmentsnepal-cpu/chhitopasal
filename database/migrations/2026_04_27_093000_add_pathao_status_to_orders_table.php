<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('pathao_status')->nullable()->after('pathao_consignment_id');
            $table->timestamp('pathao_status_updated_at')->nullable()->after('pathao_status');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['pathao_status', 'pathao_status_updated_at']);
        });
    }
};
