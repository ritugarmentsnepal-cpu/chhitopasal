<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * PHASE-2.4: every AI image generation attempt is recorded — result file,
     * model, estimated cost, who ran it, and (once saved) which template or
     * mockup it became. Unconfirmed attempts are prunable after 30 days.
     */
    public function up(): void
    {
        Schema::create('ai_generations', function (Blueprint $table) {
            $table->id();
            $table->string('type', 20)->index(); // template | mockup
            $table->string('model')->nullable();
            $table->string('image_path');
            $table->foreignId('template_id')->nullable()->constrained('mockup_templates')->nullOnDelete();
            $table->foreignId('mockup_id')->nullable()->constrained('mockups')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('cost_estimate', 8, 4)->default(0); // USD
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_generations');
    }
};
