<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_conversation_logs', function (Blueprint $table) {
            $table->id();
            $table->string('page_id', 50)->index();
            $table->string('thread_id', 100);
            $table->string('sender_id', 50);
            $table->string('sender_name')->nullable();
            $table->boolean('is_page_reply')->default(false);
            $table->text('message')->nullable();
            $table->string('facebook_message_id', 100)->unique();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->index(['page_id', 'thread_id']);
            $table->index('sent_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_conversation_logs');
    }
};
