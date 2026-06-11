<?php

use App\Models\School;
use Illuminate\Support\Facades\Cache;

if (! function_exists('school')) {
    function school(): School
    {
        static $school = null;

        if ($school === null) {
            $schoolId = auth()->user()->school_id;

            $school = Cache::rememberForever(
                "school_settings_{$schoolId}",
                fn() => School::withoutGlobalScope(\App\Models\Scopes\SchoolScope::class)
                    ->findOrFail($schoolId)
            );
        }

        return $school;
    }
}