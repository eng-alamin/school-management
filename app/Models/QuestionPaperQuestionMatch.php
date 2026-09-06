<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuestionPaperQuestionMatch extends Model
{
    protected $table = 'question_paper_question_matches';
    protected $fillable = ['question_paper_question_id', 'left_text', 'right_text', 'sort_order'];

    public function question(): BelongsTo
    {
        return $this->belongsTo(QuestionPaperQuestion::class, 'question_paper_question_id');
    }
}
