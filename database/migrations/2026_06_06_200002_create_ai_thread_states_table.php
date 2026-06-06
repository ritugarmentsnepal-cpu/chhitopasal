<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_thread_states', function (Blueprint $table) {
            $table->id();
            $table->string('page_id', 50);
            $table->string('thread_id', 100);
            $table->boolean('ai_enabled')->default(true);
            $table->boolean('human_takeover')->default(false);
            $table->timestamp('human_takeover_at')->nullable();
            $table->string('customer_phone', 20)->nullable();
            $table->string('customer_name')->nullable();
            $table->string('conversation_stage')->default('greeting'); // greeting, product_inquiry, collecting_info, order_created, complaint, resolved
            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->unsignedBigInteger('ticket_id')->nullable();
            $table->timestamps();

            $table->unique(['page_id', 'thread_id']);
            $table->foreign('ticket_id')->references('id')->on('support_tickets')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_thread_states');
    }
};
