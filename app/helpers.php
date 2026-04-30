<?php

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;

/**
 * SEC-PHASE-04: Keys that are stored encrypted in the database.
 */
define('ENCRYPTED_SETTING_KEYS', [
    'pathao_client_secret',
    'pathao_password',
]);

if (!function_exists('setting')) {
    /**
     * Get a setting value by key.
     * Uses application-level cache to avoid repeated DB queries across requests.
     * SEC-PHASE-04: Automatically decrypts sensitive settings.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    function setting($key, $default = null)
    {
        // Use static cache for the current request to avoid repeated cache lookups
        static $settings = null;

        if ($settings === null) {
            $settings = Cache::remember('app_settings', 300, function () {
                return Setting::pluck('value', 'key')->toArray();
            });
        }

        $value = $settings[$key] ?? $default;

        // SEC-PHASE-04: Auto-decrypt sensitive settings
        if ($value && $value !== $default && in_array($key, ENCRYPTED_SETTING_KEYS)) {
            try {
                $value = Crypt::decryptString($value);
            } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
                // Value may not be encrypted yet (legacy plain-text), return as-is
            }
        }

        return $value;
    }
}

if (!function_exists('clear_settings_cache')) {
    /**
     * Clear the settings cache. Call this after updating settings.
     */
    function clear_settings_cache()
    {
        Cache::forget('app_settings');
    }
}

if (!function_exists('encrypt_setting_value')) {
    /**
     * SEC-PHASE-04: Encrypt a setting value if the key is in the sensitive list.
     *
     * @param string $key
     * @param string $value
     * @return string
     */
    function encrypt_setting_value(string $key, string $value): string
    {
        if (in_array($key, ENCRYPTED_SETTING_KEYS) && !empty($value)) {
            return Crypt::encryptString($value);
        }
        return $value;
    }
}

