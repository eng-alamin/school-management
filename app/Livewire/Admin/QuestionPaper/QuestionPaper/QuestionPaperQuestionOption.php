<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuestionPaperQuestionOption extends Model
{
    protected $fillable = ['question_paper_question_id', 'option_text', 'is_correct', 'sort_order'];
    protected $casts = ['is_correct' => 'boolean'];

    public function question(): BelongsTo
    {
        return $this->belongsTo(QuestionPaperQuestion::class, 'question_paper_question_id');
    }
}
