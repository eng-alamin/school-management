<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToInstitution;
use App\Traits\BelongsToBranch;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class BiometricDeviceUserMapping extends Model
{
    use BelongsToInstitution;
    use BelongsToBranch;
    // use SoftDeletes;

    protected $fillable = [
        'institution_id',
        'branch_id',
        'biometric_device_id',
        'device_user_id',
        'card_number',
        'attendable_type',
        'attendable_id',
    ];

    public function biometricDevice()
    {
        return $this->belongsTo(\App\Models\BiometricDevice::class);
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(BiometricDevice::class, 'biometric_device_id');
    }

    public function attendable(): MorphTo
    {
        return $this->morphTo();
    }
}
