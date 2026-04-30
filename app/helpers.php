<?php

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

if (!function_exists('setting')) {
    /**
     * Get a setting value by key.
     * Uses application-level cache to avoid repeated DB queries across requests.
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

        return $settings[$key] ?? $default;
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
