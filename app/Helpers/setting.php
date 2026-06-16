<?php

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

function setting($key, $default = null)
{
    $value = Cache::remember("setting_{$key}", 3600, function () use ($key) {
        return Setting::where('key', $key)->value('value');
    });

    return $value ?? $default;
}