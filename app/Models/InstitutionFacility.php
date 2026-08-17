<?php

namespace App\Models;

use App\Traits\BelongsToInstitution; // ⚠️ verify actual namespace in your project
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InstitutionFacility extends Model
{
    use HasFactory, BelongsToInstitution;

    public const STATUS_ACTIVE   = 'active';
    public const STATUS_INACTIVE = 'inactive';

    /**
     * Mass assignable attributes.
     */
    protected $fillable = [
        'institution_id',
        'name',
        'status',
    ];

    // ── Scopes ───────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }
}