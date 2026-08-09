<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Grievance extends Model
{
    public const TYPE_STUDENT = 'student';
    public const TYPE_GUARDIAN = 'guardian';
    public const TYPE_TEACHER = 'teacher';

    public const TYPES = [
        self::TYPE_STUDENT,
        self::TYPE_GUARDIAN,
        self::TYPE_TEACHER,
    ];

    public const TYPE_LABELS = [
        self::TYPE_STUDENT => 'Student',
        self::TYPE_GUARDIAN => 'Guardian',
        self::TYPE_TEACHER => 'Teacher',
    ];

    public const STATUS_SUBMITTED = 'submitted';
    public const STATUS_UNDER_REVIEW = 'under_review';
    public const STATUS_RESOLVED = 'resolved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_ESCALATED = 'escalated';

    public const STATUSES = [
        self::STATUS_SUBMITTED,
        self::STATUS_UNDER_REVIEW,
        self::STATUS_RESOLVED,
        self::STATUS_REJECTED,
        self::STATUS_ESCALATED,
    ];

    public const STATUS_LABELS = [
        self::STATUS_SUBMITTED => 'Submitted',
        self::STATUS_UNDER_REVIEW => 'Under Review',
        self::STATUS_RESOLVED => 'Resolved',
        self::STATUS_REJECTED => 'Rejected',
        self::STATUS_ESCALATED => 'Escalated',
    ];

    protected $fillable = [
        'institution_id',
        'student_id',
        'complainant_type',
        'complainant_id',
        'is_anonymous',
        'category',
        'subject',
        'description',
        'status',
        'assigned_to',
        'resolution_note',
        'resolved_at',
        'violation_id',
    ];

    protected $casts = [
        'is_anonymous' => 'boolean',
        'resolved_at' => 'datetime',
    ];

    // Note: no BelongsToInstitution trait here (deliberately, matching the
    // Inspection/ComplianceViolation models) — Ministry needs cross-tenant
    // access, so scoping is enforced per-query in the components, not globally.

    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function complainant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'complainant_id');
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function violation(): BelongsTo
    {
        return $this->belongsTo(ComplianceViolation::class, 'violation_id');
    }

    public function statusLabel(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }

    public function complainantTypeLabel(): string
    {
        return self::TYPE_LABELS[$this->complainant_type] ?? $this->complainant_type;
    }

    public function isClosed(): bool
    {
        return in_array($this->status, [self::STATUS_RESOLVED, self::STATUS_REJECTED], true);
    }

    // Display-safe name — hides identity in the review UI for anonymous
    // submissions, per the privacy design decision (identity still stored in DB).
    public function displayComplainantName(): string
    {
        if ($this->is_anonymous) {
            return 'Anonymous ' . $this->complainantTypeLabel();
        }

        return $this->complainant->name ?? 'Unknown';
    }
}