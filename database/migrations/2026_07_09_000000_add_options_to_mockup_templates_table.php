<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Richer template generation: presentation style, camera angle, lighting
     * and view layout are stored as JSON so a template's exact recipe can be
     * inspected and regenerated later.
     */
    public function up(): void
    {
        Schema::table('mockup_templates', function (Blueprint $table) {
            $table->json('options')->nullable()->after('style_notes');
        });
    }

    public function down(): void
    {
        Schema::table('mockup_templates', function (Blueprint $table) {
            $table->dropColumn('options');
        });
    }
};
