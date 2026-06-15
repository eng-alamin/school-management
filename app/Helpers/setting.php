<?php

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

function setting($key, $default = null)
{
    return Cache::remember("setting_{$key}", 3600, function () use ($key, $default) {
        $value = Setting::where('key', $key)->value('value');
        return $value ?? $default;
    });
}