<?php

namespace App\Traits;

use App\Models\Branch;
use App\Models\Scopes\BranchScope;
use App\Support\Feature;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToBranch
{
    protected static function bootBelongsToBranch(): void
    {
        static::addGlobalScope(new BranchScope());

        static::creating(function ($model) {
            if (!auth()->check()) {
                return;
            }

            $user = auth()->user();

            if (!feature_enabled(Feature::BRANCH_MODULE)) {
                $model->branch_id ??= Branch::withoutGlobalScopes()
                    ->where('institution_id', $user->institution_id)
                    ->where('is_main', true)
                    ->value('id');

                return;
            }

            $model->branch_id ??= $user->branch_id;
        });
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
}