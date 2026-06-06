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
        Schema::table('ai_thread_states', function (Blueprint $table) {
            $table->integer('followup_count')->default(0)->after('human_takeover_at');
            $table->timestamp('last_interaction_at')->nullable()->after('followup_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ai_thread_states', function (Blueprint $table) {
            $table->dropColumn(['followup_count', 'last_interaction_at']);
        });
    }
};
