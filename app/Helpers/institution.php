<?php

use App\Models\Institution;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

if (! function_exists('institution')) {
    function institution(): ?Institution
    {
        static $institution = null;

        if ($institution !== null) {
            return $institution;
        }

        $user = Auth::user();

        if (!$user || !$user->institution_id) {
            return null;
        }

        $institutionId = $user->institution_id;

        $institution = Cache::rememberForever(
            "institution_settings_{$institutionId}",
            fn() => Institution::withoutGlobalScope(\App\Models\Scopes\InstitutionScope::class)
                ->find($institutionId)
        );

        return $institution;
    }
}