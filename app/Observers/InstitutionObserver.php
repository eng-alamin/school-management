<?php

namespace App\Observers;

use App\Models\Institution;
use App\Services\InstitutionDefaultsService;

class InstitutionObserver
{
    /**
     * Handle the Institution "created" event.
     */
    public function created(Institution $institution): void
    {
        InstitutionDefaultsService::create($institution);
    }
}