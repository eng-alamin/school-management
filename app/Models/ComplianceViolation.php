<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComplianceViolation extends Model
{
    public const SEVERITY_MINOR = 'minor';
    public const SEVERITY_MAJOR = 'major';
    public const SEVERITY_CRITICAL = 'critical';

    public const SEVERITIES = [
        self::SEVERITY_MINOR,
        self::SEVERITY_MAJOR,
        self::SEVERITY_CRITICAL,
    ];

    public const SEVERITY_LABELS = [
        self::SEVERITY_MINOR => 'Minor',
        self::SEVERITY_MAJOR => 'Major',
        self::SEVERITY_CRITICAL => 'Critical',
    ];

    public const STATUS_OPEN = 'open';
    public const STATUS_RESOLVED = 'resolved';
    public const STATUS_ESCALATED = 'escalated';

    public const STATUSES = [
        self::STATUS_OPEN,
        self::STATUS_RESOLVED,
        self::STATUS_ESCALATED,
    ];

    public const STATUS_LABELS = [
        self::STATUS_OPEN => 'Open',
        self::STATUS_RESOLVED => 'Resolved',
        self::STATUS_ESCALATED => 'Escalated',
    ];

    protected $fillable = [
        'institution_id',
        'inspection_id',
        'severity',
        'description',
        'status',
        'resolved_at',
        'resolution_note',
        'reported_by',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    public function inspection(): BelongsTo
    {
        return $this->belongsTo(Inspection::class);
    }

    public function reportedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function severityLabel(): string
    {
        return self::SEVERITY_LABELS[$this->severity] ?? $this->severity;
    }

    public function statusLabel(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }

    public function isOpen(): bool
    {
        return $this->status === self::STATUS_OPEN;
    }
}