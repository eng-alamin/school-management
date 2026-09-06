<?php

namespace App\Models;

use App\Traits\BelongsToInstitution;
use App\Traits\BelongsToBranch;
use App\Models\AcademicSubject;
use App\Models\ExamSetup;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuestionPaperPrintAuthorization extends Model
{
    use BelongsToInstitution, BelongsToBranch;

    protected $fillable = [
        'institution_id', 'branch_id', 'user_id', 'exam_id', 'subject_id',
        'valid_from', 'valid_until', 'is_revoked',
    ];

    protected $casts = [
        'valid_from' => 'datetime',
        'valid_until' => 'datetime',
        'is_revoked' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** exam_id points at exam_setups — one Exam+Class combination. */
    public function examSetup(): BelongsTo
    {
        return $this->belongsTo(ExamSetup::class, 'exam_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(AcademicSubject::class, 'subject_id');
    }

    public function isCurrentlyOpen(): bool
    {
        return !$this->is_revoked && now()->between($this->valid_from, $this->valid_until);
    }
}