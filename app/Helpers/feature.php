<?php

use App\Models\Feature;

if (!function_exists('feature_enabled')) {
    function feature_enabled(string $featureKey): bool
    {
        static $cache = null;

        $institutionId = auth()->user()->institution_id ?? null;

        if (!$institutionId) {
            return false;
        }

        if ($cache === null || ($cache['institution_id'] ?? null) !== $institutionId) {
            $cache = [
                'institution_id' => $institutionId,
                'data' => Feature::where('institution_id', $institutionId)
                    ->pluck('is_active', 'feature_key')
                    ->toArray(),
            ];
        }

        return $cache['data'][$featureKey] ?? true; // default active jodi row na thake
    }
}