<?php

use App\Models\Institution;
use Illuminate\Support\Facades\Cache;

if (! function_exists('institution')) {
    function institution(): Institution
    {
        static $institution = null;

        if ($institution === null) {
            $institutionId = auth()->user()->institution_id;

            $institution = Cache::rememberForever(
                "institution_settings_{$institutionId}",
                fn() => Institution::withoutGlobalScope(\App\Models\Scopes\InstitutionScope::class)
                    ->findOrFail($institutionId)
            );
        }

        return $institution;
    }
}