<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * AI mockup generation: templates carry their generation settings so they
     * can be regenerated, and mockups keep the customer logo used so it can be
     * downloaded for printing once the order is confirmed.
     */
    public function up(): void
    {
        Schema::table('mockup_templates', function (Blueprint $table) {
            $table->string('size')->nullable()->after('product_type');            // aspect/size preset e.g. "square", "portrait"
            $table->string('theme')->nullable()->after('size');                   // e.g. "studio", "lifestyle", "flat_lay"
            $table->string('color_scheme')->nullable()->after('theme');           // free text, e.g. "white product, soft beige backdrop"
            $table->string('placements')->nullable()->after('color_scheme');      // e.g. "front chest pocket logo + large back print"
            $table->text('style_notes')->nullable()->after('placements');         // free-form extra styling instructions
            $table->string('source_image_path')->nullable()->after('image_path'); // uploaded product reference photo
            $table->boolean('is_ai_generated')->default(false)->after('source_image_path');
        });

        Schema::table('mockups', function (Blueprint $table) {
            $table->string('logo_path')->nullable()->after('image_path'); // customer branding used for this mockup
        });
    }

    public function down(): void
    {
        Schema::table('mockup_templates', function (Blueprint $table) {
            $table->dropColumn([
                'size', 'theme', 'color_scheme', 'placements',
                'style_notes', 'source_image_path', 'is_ai_generated',
            ]);
        });

        Schema::table('mockups', function (Blueprint $table) {
            $table->dropColumn('logo_path');
        });
    }
};
