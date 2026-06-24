<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\BelongsToInstitution;

class Notice extends Model
{
    use BelongsToInstitution;
    
    protected $guarded = [];

    protected $casts = [
        'published_at' => 'date',
        'expires_at'   => 'date',
    ];

    /* ------------------------------------------------------------------ */
    /*  Relationships                                                        */
    /* ------------------------------------------------------------------ */

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /* ------------------------------------------------------------------ */
    /*  Scopes                                                              */
    /* ------------------------------------------------------------------ */

    public function scopeActive($query)
    {
        return $query->where('status', 'active')
                     ->where('published_at', '<=', today())
                     ->where(fn ($q) =>
                         $q->whereNull('expires_at')
                           ->orWhere('expires_at', '>=', today())
                     );
    }

    public function scopeForAudience($query, string $role)
    {
        return $query->where(fn ($q) =>
            $q->where('audience', 'all')
              ->orWhere('audience', $role)
        );
    }

    /* ------------------------------------------------------------------ */
    /*  Helpers                                                             */
    /* ------------------------------------------------------------------ */

    public function getPriorityColorAttribute(): string
    {
        return match($this->priority) {
            'urgent' => 'danger',
            'high'   => 'warning',
            'medium' => 'primary',
            'low'    => 'secondary',
            default  => 'primary',
        };
    }

    public function getPriorityIconAttribute(): string
    {
        return match($this->priority) {
            'urgent' => 'fas fa-exclamation-circle',
            'high'   => 'fas fa-arrow-up',
            'medium' => 'fas fa-minus',
            'low'    => 'fas fa-arrow-down',
            default  => 'fas fa-minus',
        };
    }

    public function getIsExpiredAttribute(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function getExcerptAttribute(): string
    {
        return \Str::limit(strip_tags($this->description), 100);
    }
}
