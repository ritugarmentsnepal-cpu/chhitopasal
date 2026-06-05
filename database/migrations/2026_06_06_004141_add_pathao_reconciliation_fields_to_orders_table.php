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
            $table->timestamp('pathao_settled_at')->nullable();
            $table->boolean('pathao_disputed')->default(false);
            $table->decimal('pathao_actual_delivery_charge', 10, 2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['pathao_settled_at', 'pathao_disputed', 'pathao_actual_delivery_charge']);
        });
    }
};
