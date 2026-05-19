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
        Schema::create('visitor_events', function (Blueprint $table) {
            $table->id();
            $table->string('session_id');
            $table->enum('event_type', ['page_view', 'view_product', 'add_to_cart', 'initiate_checkout', 'purchase']);
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->text('url')->nullable();
            $table->timestamp('created_at')->nullable();
            
            $table->foreign('session_id')->references('session_id')->on('visitor_sessions')->cascadeOnDelete();
            
            $table->index('event_type');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visitor_events');
    }
};
