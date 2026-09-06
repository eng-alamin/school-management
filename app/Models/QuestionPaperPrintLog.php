<?php

namespace App\Models;

use App\Traits\BelongsToInstitution;
use App\Traits\BelongsToBranch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuestionPaperPrintLog extends Model
{
    use BelongsToInstitution, BelongsToBranch;

    protected $fillable = [
        'institution_id', 'branch_id', 'exam_id', 'subject_id',
        'printed_by', 'print_authorization_id',
        'watermark_code', 'question_order', 'shuffle_seed',
        'copy_count', 'ip_address', 'device_fingerprint', 'printed_at',
    ];

    protected $casts = [
        'question_order' => 'array',
        'printed_at' => 'datetime',
    ];

    public function printedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'printed_by');
    }

    public function authorization(): BelongsTo
    {
        return $this->belongsTo(QuestionPaperPrintAuthorization::class, 'print_authorization_id');
    }

    public function exam(): BelongsTo
    {
        return $this->belongsTo(ExamSetup::class, 'exam_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(AcademicSubject::class, 'subject_id');
    }
}
