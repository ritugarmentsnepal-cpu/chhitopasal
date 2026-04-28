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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('customer_name');
            $table->string('customer_phone');
            $table->string('address');
            $table->string('city')->nullable(); // Original text city
            $table->integer('pathao_city_id')->nullable();
            $table->integer('pathao_zone_id')->nullable();
            $table->integer('pathao_area_id')->nullable();
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->enum('status', ['pending', 'confirmed', 'shipped', 'delivered', 'failed', 'rejected'])->default('pending');
            $table->string('source'); // web, manual, csv
            $table->string('pathao_consignment_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
