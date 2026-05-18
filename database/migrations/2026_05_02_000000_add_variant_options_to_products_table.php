<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'color_options')) {
                $table->json('color_options')->nullable()->after('bundles');
            }
            if (!Schema::hasColumn('products', 'size_options')) {
                $table->json('size_options')->nullable()->after('color_options');
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['color_options', 'size_options']);
        });
    }
};
