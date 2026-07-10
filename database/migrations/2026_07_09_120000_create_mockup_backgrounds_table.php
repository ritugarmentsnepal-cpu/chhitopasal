<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Background-first template generation: backgrounds are generated once,
     * kept as a reusable library, and templates record which background they
     * were staged on.
     */
    public function up(): void
    {
        Schema::create('mockup_backgrounds', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('image_path');
            $table->string('theme')->nullable();
            $table->string('lighting')->nullable();
            $table->string('color_scheme')->nullable();
            $table->string('size')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::table('mockup_templates', function (Blueprint $table) {
            $table->foreignId('background_id')->nullable()->after('options')
                ->constrained('mockup_backgrounds')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('mockup_templates', function (Blueprint $table) {
            $table->dropConstrainedForeignId('background_id');
        });

        Schema::dropIfExists('mockup_backgrounds');
    }
};
