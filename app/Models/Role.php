<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\BelongsToBranch;
use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    use BelongsToBranch;

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
}