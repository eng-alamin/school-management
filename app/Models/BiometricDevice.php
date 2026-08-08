<?php

namespace App\Models;

use App\Traits\BelongsToInstitution;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class BiometricDevice extends Model
{
    use BelongsToInstitution, SoftDeletes;

    protected $fillable = [
        'institution_id',
        'device_serial',
        'device_name',
        'device_type',
        'ip_address',
        'location',
        'last_seen_at',
        'is_active',
    ];

    protected $casts = [
        'last_seen_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function userMappings(): HasMany
    {
        return $this->hasMany(BiometricDeviceUserMapping::class);
    }

    public function attendanceLogs(): HasMany
    {
        return $this->hasMany(BiometricAttendanceLog::class);
    }
}
