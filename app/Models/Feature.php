<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToInstitution;
use App\Traits\BelongsToBranch;

class Feature extends Model
{
    use BelongsToInstitution;
    // use BelongsToBranch;

    protected $fillable = ['institution_id', 'feature_key', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }
}
