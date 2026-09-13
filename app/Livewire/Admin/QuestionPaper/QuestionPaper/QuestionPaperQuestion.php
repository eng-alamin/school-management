<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuestionPaperQuestion extends Model
{
    /**
     * Mirrors QUESTION_TYPES in the builder: value => [section, family, ...]
     * Kept server-side too so validation/rendering don't trust client input
     * for which family a type belongs to.
     */
    public const TYPE_DEFS = [
        'MCQ_SINGLE' => ['section' => 'Objective', 'family' => 'options', 'optCount' => 4],
        'MCQ_MULTIPLE' => ['section' => 'Objective', 'family' => 'options', 'optCount' => 4],
        'TRUE_FALSE' => ['section' => 'Objective', 'family' => 'options', 'optCount' => 2, 'fixedOpts' => ['True', 'False']],
        'YES_NO' => ['section' => 'Objective', 'family' => 'options', 'optCount' => 2, 'fixedOpts' => ['Yes', 'No']],
        'FILL_IN_THE_BLANK' => ['section' => 'Objective', 'family' => 'plain'],
        'MATCHING' => ['section' => 'Objective', 'family' => 'matching_pairs'],
        'ASSERTION_REASON' => ['section' => 'Objective', 'family' => 'options', 'optCount' => 4, 'fixedOpts' => [
            'Both assertion and reason are true, and reason is the correct explanation',
            'Both assertion and reason are true, but reason is not the correct explanation',
            'Assertion is true but reason is false',
            'Assertion is false but reason is true',
        ]],
        'CLOZE' => ['section' => 'Objective', 'family' => 'plain'],
        'REARRANGE' => ['section' => 'Objective', 'family' => 'plain'],
        'NUMERICAL' => ['section' => 'Objective', 'family' => 'plain'],
        'VERY_SHORT_ANSWER' => ['section' => 'Subjective', 'family' => 'plain'],
        'SHORT_ANSWER' => ['section' => 'Subjective', 'family' => 'plain'],
        'LONG_ANSWER' => ['section' => 'Subjective', 'family' => 'plain'],
        'ESSAY' => ['section' => 'Subjective', 'family' => 'plain'],
        'WRITING' => ['section' => 'Subjective', 'family' => 'plain'],
        'GRAMMAR' => ['section' => 'Subjective', 'family' => 'plain'],
        'VOCABULARY' => ['section' => 'Subjective', 'family' => 'plain'],
        'TRANSLATION' => ['section' => 'Subjective', 'family' => 'plain'],
        'READING_COMPREHENSION' => ['section' => 'Subjective', 'family' => 'stimulus_parts', 'partsCount' => 3, 'partLabels' => ['1', '2', '3']],
        'PASSAGE' => ['section' => 'Subjective', 'family' => 'stimulus_parts', 'partsCount' => 3, 'partLabels' => ['1', '2', '3']],
        'CREATIVE' => ['section' => 'Creative / Reference', 'family' => 'stimulus_parts', 'partsCount' => 4, 'partLabels' => ['ক', 'খ', 'গ', 'ঘ']],
        'CASE_STUDY' => ['section' => 'Creative / Reference', 'family' => 'stimulus_parts', 'partsCount' => 3, 'partLabels' => ['a', 'b', 'c']],
        'PROBLEM_SOLVING' => ['section' => 'Creative / Reference', 'family' => 'plain'],
        'PROOF' => ['section' => 'Creative / Reference', 'family' => 'plain'],
        'DIAGRAM' => ['section' => 'Creative / Reference', 'family' => 'plain'],
        'IMAGE_BASED' => ['section' => 'Creative / Reference', 'family' => 'plain'],
        'MAP_BASED' => ['section' => 'Creative / Reference', 'family' => 'plain'],
        'VIVA' => ['section' => 'Practical', 'family' => 'plain'],
        'PRACTICAL' => ['section' => 'Practical', 'family' => 'plain'],
        'PROJECT' => ['section' => 'Practical', 'family' => 'plain'],
    ];

    protected $fillable = [
        'question_paper_id', 'type', 'family', 'section_header',
        'question_text', 'stimulus_text', 'marks', 'figure_path', 'sort_order',
    ];

    protected $casts = [
        'marks' => 'decimal:2',
    ];

    public function paper(): BelongsTo
    {
        return $this->belongsTo(QuestionPaper::class, 'question_paper_id');
    }

    public function options(): HasMany
    {
        return $this->hasMany(QuestionPaperQuestionOption::class)->orderBy('sort_order');
    }

    public function matches(): HasMany
    {
        return $this->hasMany(QuestionPaperQuestionMatch::class)->orderBy('sort_order');
    }

    public function parts(): HasMany
    {
        return $this->hasMany(QuestionPaperQuestionPart::class)->orderBy('sort_order');
    }

    /** The heading this question groups under in the preview/print. */
    public function resolvedSectionHeader(): string
    {
        return $this->section_header ?: (self::TYPE_DEFS[$this->type]['section'] ?? 'General');
    }

    public static function familyFor(string $type): string
    {
        return self::TYPE_DEFS[$type]['family'] ?? 'plain';
    }
}
