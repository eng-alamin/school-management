<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Circular extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'published_at' => 'date',
        'expires_at'   => 'date',
    ];

    public const AUDIENCE_ALL         = 'all';
    public const AUDIENCE_DIVISION    = 'division';
    public const AUDIENCE_DISTRICT    = 'district';
    public const AUDIENCE_INSTITUTION = 'institution';

    public const AUDIENCES = [
        self::AUDIENCE_ALL         => 'All Institutions',
        self::AUDIENCE_DIVISION    => 'Specific Division',
        self::AUDIENCE_DISTRICT    => 'Specific District',
        self::AUDIENCE_INSTITUTION => 'Specific Institution',
    ];

    /* ------------------------------------------------------------------ */
    /*  Relationships                                                     */
    /* ------------------------------------------------------------------ */

    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function reads(): HasMany
    {
        return $this->hasMany(CircularRead::class);
    }

    /* ------------------------------------------------------------------ */
    /*  Scopes                                                             */
    /* ------------------------------------------------------------------ */

    public function scopeActive($query)
    {
        return $query->where('status', 'active')
            ->where('published_at', '<=', today())
            ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>=', today()));
    }

    /* ------------------------------------------------------------------ */
    /*  Helpers                                                            */
    /* ------------------------------------------------------------------ */

    public function getIsExpiredAttribute(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function getExcerptAttribute(): string
    {
        return \Str::limit(strip_tags($this->description), 100);
    }

    public function targetInstitutionsQuery()
    {
        return match ($this->audience) {
            self::AUDIENCE_DIVISION    => Institution::where('division', $this->division),
            self::AUDIENCE_DISTRICT    => Institution::where('district', $this->district),
            self::AUDIENCE_INSTITUTION => Institution::where('id', $this->institution_id),
            default                    => Institution::query(),
        };
    }
}