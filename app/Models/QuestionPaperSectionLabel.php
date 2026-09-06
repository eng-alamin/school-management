<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuestionPaperSectionLabel extends Model
{
    protected $fillable = ['question_paper_id', 'section_key', 'label_bn', 'sort_order'];

    public function paper(): BelongsTo
    {
        return $this->belongsTo(QuestionPaper::class, 'question_paper_id');
    }
}
