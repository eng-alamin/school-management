<?php

namespace App\Models;

use App\Traits\BelongsToInstitution;
use App\Traits\BelongsToBranch;
use App\Models\AcademicSubject;
use App\Models\ExamSetup;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuestionPaper extends Model
{
    use BelongsToInstitution, BelongsToBranch;

    protected $fillable = [
        'institution_id', 'branch_id', 'exam_id', 'subject_id', 'academic_class_id',
        'institute_name', 'exam_name', 'class_label', 'subject_label',
        'full_marks', 'time_label', 'language',
        'is_locked', 'locked_at', 'locked_by', 'created_by',
    ];

    protected $casts = [
        'full_marks' => 'decimal:2',
        'is_locked' => 'boolean',
        'locked_at' => 'datetime',
    ];

    public function exam(): BelongsTo
    {
        return $this->belongsTo(ExamSetup::class, 'exam_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(AcademicSubject::class, 'subject_id'); // rename if your model class differs
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(QuestionPaperQuestion::class)->orderBy('sort_order');
    }

    public function sectionLabels(): HasMany
    {
        return $this->hasMany(QuestionPaperSectionLabel::class)->orderBy('sort_order');
    }

    /**
     * Freeze the paper so its content can never change again once it has
     * entered the print/publish pipeline. Any leaked-copy forensic tracing
     * (watermark or question-order) depends on the paper content being
     * immutable from this point onward — editing a "live" print source
     * would make older print logs untraceable against new content.
     */
    public function lock(int $userId): void
    {
        abort_if($this->is_locked, 409, 'This question paper is already locked.');

        $this->update([
            'is_locked' => true,
            'locked_at' => now(),
            'locked_by' => $userId,
        ]);

        activity()
            ->tap(fn ($a) => $a->institution_id = $this->institution_id)
            ->causedBy($userId)
            ->performedOn($this)
            ->withProperties(['icon' => 'lock', 'type' => 'question_paper_locked'])
            ->log('Question paper locked for publishing');
    }

    public function totalMarks(): float
    {
        return $this->questions->sum(function (QuestionPaperQuestion $q) {
            return $q->family === 'stimulus_parts'
                ? $q->parts->sum('marks')
                : $q->marks;
        });
    }
}