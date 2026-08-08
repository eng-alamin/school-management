<?php

namespace App\Models;

use App\Traits\BelongsToInstitution;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class BiometricDeviceUserMapping extends Model
{
    use BelongsToInstitution, SoftDeletes;

    protected $fillable = [
        'institution_id',
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
