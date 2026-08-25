<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToInstitution;
use App\Traits\BelongsToBranch;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BiometricDevice extends Model
{
    use BelongsToInstitution;
    use BelongsToBranch;
    // use SoftDeletes;

    protected $fillable = [
        'institution_id',
        'branch_id',
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
