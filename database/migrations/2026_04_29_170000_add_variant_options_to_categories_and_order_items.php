<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->boolean('has_color_variants')->default(false)->after('slug');
            $table->boolean('has_size_variants')->default(false)->after('has_color_variants');
            $table->json('color_options')->nullable()->after('has_size_variants');
            $table->json('size_options')->nullable()->after('color_options');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->string('color')->nullable()->after('cost_at_purchase');
            $table->string('size')->nullable()->after('color');
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn(['has_color_variants', 'has_size_variants', 'color_options', 'size_options']);
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn(['color', 'size']);
        });
    }
};
