<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Setting;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $setting = Setting::where('key', 'openrouter_model')->first();
        if ($setting && $setting->value === 'anthropic/claude-3.5-sonnet') {
            $setting->update(['value' => 'anthropic/claude-sonnet-latest']);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $setting = Setting::where('key', 'openrouter_model')->first();
        if ($setting && $setting->value === 'anthropic/claude-sonnet-latest') {
            $setting->update(['value' => 'anthropic/claude-3.5-sonnet']);
        }
    }
};
