<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuestionPaperQuestionPart extends Model
{
    protected $fillable = ['question_paper_question_id', 'part_label', 'part_text', 'marks', 'sort_order'];
    protected $casts = ['marks' => 'decimal:2'];

    public function question(): BelongsTo
    {
        return $this->belongsTo(QuestionPaperQuestion::class, 'question_paper_question_id');
    }
}
