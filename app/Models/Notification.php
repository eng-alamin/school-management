<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToSchool;
use Illuminate\Database\Eloquent\Builder;

class Notification extends Model
{
    use BelongsToSchool;
    
    protected $guarded = [];

    protected $casts = [
        'data'    => 'array',
        'read_at' => 'datetime',
    ];

    // ─── Relationships ────────────────────────────────────────────────────────

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function notifiable(): MorphTo
    {
        return $this->morphTo();
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopeUnread(Builder $query): Builder
    {
        return $query->whereNull('read_at');
    }

    public function scopeRead(Builder $query): Builder
    {
        return $query->whereNotNull('read_at');
    }

    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    public function scopeHighPriority(Builder $query): Builder
    {
        return $query->where('priority', 'high');
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    public function isUnread(): bool
    {
        return is_null($this->read_at);
    }

    public function markAsRead(): bool
    {
        if ($this->isUnread()) {
            return $this->update(['read_at' => now()]);
        }
        return false;
    }

    public function getIconAttribute(): string
    {
        return $this->data['icon'] ?? $this->defaultIcon();
    }

    public function getUrlAttribute(): ?string
    {
        return $this->data['url'] ?? null;
    }

    protected function defaultIcon(): string
    {
        return match ($this->type) {
            'admission'     => 'person_add',
            'fee_due'       => 'payments',
            'fee_paid'      => 'paid',
            'attendance'    => 'event_available',
            'exam_result'   => 'grade',
            'leave_request' => 'event_busy',
            'low_stock'     => 'inventory_2',
            'announcement'  => 'campaign',
            default         => 'notifications',
        };
    }

    public static function typeLabels(): array
    {
        return [
            'admission'     => 'New Admission',
            'fee_due'       => 'Fee Overdue',
            'fee_paid'      => 'Payment Received',
            'attendance'    => 'Attendance Alert',
            'exam_result'   => 'Exam Result',
            'leave_request' => 'Leave Request',
            'low_stock'     => 'Low Stock',
            'announcement'  => 'Announcement',
        ];
    }
}

