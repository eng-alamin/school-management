<?php

namespace App\Models;

use App\Traits\BelongsToInstitution;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToBranch;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class BiometricAttendanceLog extends Model
{
    use BelongsToInstitution;
    use BelongsToBranch;
    // use SoftDeletes;

    public const VERIFY_MODE_PASSWORD = 0;
    public const VERIFY_MODE_FINGERPRINT = 1;
    public const VERIFY_MODE_CARD = 2;
    public const VERIFY_MODE_FACE = 15;

    protected $fillable = [
        'institution_id',
        'branch_id',
        'biometric_device_id',
        'device_user_id',
        'attendable_type',
        'attendable_id',
        'punch_time',
        'verify_mode',
        'in_out_mode',
        'work_code',
        'raw_payload',
        'processed',
        'processed_at',
    ];

    protected $casts = [
        'punch_time' => 'datetime',
        'processed' => 'boolean',
        'processed_at' => 'datetime',
    ];

    public function device(): BelongsTo
    {
        return $this->belongsTo(BiometricDevice::class, 'biometric_device_id');
    }

    public function attendable(): MorphTo
    {
        return $this->morphTo();
    }

    public static function verifyModeLabel(?int $mode): string
    {
        return match ($mode) {
            self::VERIFY_MODE_PASSWORD => 'Password',
            self::VERIFY_MODE_FINGERPRINT => 'Fingerprint',
            self::VERIFY_MODE_CARD => 'Card',
            self::VERIFY_MODE_FACE => 'Face',
            default => 'Unknown',
        };
    }
}
