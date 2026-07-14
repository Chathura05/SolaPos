<?php

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

if (! function_exists('setting')) {
    /**
     * Get / set the specified setting value.
     *
     * If an array is passed as the key, we will assume you want to set an array of values.
     *
     * @param  array|string|null  $key
     * @param  mixed  $default
     * @return mixed
     */
    function setting($key = null, $default = null)
    {
        if (is_null($key)) {
            return app(Setting::class);
        }

        if (is_array($key)) {
            foreach ($key as $k => $v) {
                Setting::updateOrCreate(['key' => $k], ['value' => $v]);
            }
            Cache::forget('app_settings');
            return true;
        }

        // Cache settings to avoid repeated DB queries
        $settings = Cache::rememberForever('app_settings', function () {
            return Setting::pluck('value', 'key')->toArray();
        });

        return $settings[$key] ?? $default;
    }
}
