<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * SEC (Phase 0): encrypt credentials that were stored in plaintext.
     *  - facebook_pages.access_token (model now uses the 'encrypted' cast)
     *  - settings: openrouter_api_key (added to ENCRYPTED_SETTING_KEYS)
     *
     * Uses raw DB access (not Eloquent) because the model cast would try to
     * decrypt the still-plaintext values and throw.
     */
    public function up(): void
    {
        // Encrypted payloads are ~3-4x longer than plaintext tokens
        Schema::table('facebook_pages', function (Blueprint $table) {
            $table->text('access_token')->change();
        });

        foreach (DB::table('facebook_pages')->get() as $page) {
            if ($this->isEncrypted($page->access_token)) {
                continue;
            }
            DB::table('facebook_pages')
                ->where('id', $page->id)
                ->update(['access_token' => Crypt::encryptString($page->access_token)]);
        }

        foreach (['openrouter_api_key'] as $key) {
            $row = DB::table('settings')->where('key', $key)->first();
            if ($row && !empty($row->value) && !$this->isEncrypted($row->value)) {
                DB::table('settings')
                    ->where('key', $key)
                    ->update(['value' => Crypt::encryptString($row->value)]);
            }
        }

        // The settings cache still holds plaintext values — flush it
        if (function_exists('clear_settings_cache')) {
            clear_settings_cache();
        }
    }

    public function down(): void
    {
        // Intentionally left non-reversible: decrypting credentials back to
        // plaintext on rollback would be a security regression.
    }

    protected function isEncrypted(?string $value): bool
    {
        if (empty($value)) {
            return true; // nothing to encrypt
        }
        try {
            Crypt::decryptString($value);
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }
};
