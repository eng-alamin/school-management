<?php

namespace App\Models;

use App\Traits\BelongsToInstitution;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BiometricDeviceCommand extends Model
{
    use BelongsToInstitution;

    protected $fillable = [
        'institution_id',
        'biometric_device_id',
        'command_id',
        'command_text',
        'card_number',
        'status',
        'response',
        'sent_at',
        'confirmed_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'confirmed_at' => 'datetime',
    ];

    public function device(): BelongsTo
    {
        return $this->belongsTo(BiometricDevice::class, 'biometric_device_id');
    }

    public static function generateCommandId(): string
    {
        return (string) now()->timestamp . random_int(100, 999);
    }
}