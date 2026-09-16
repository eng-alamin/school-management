<?php

namespace App\Livewire\Admin\QuestionPaper;

use Livewire\Component;
use App\Models\Branch;
use App\Models\QuestionPaper;
use App\Models\QuestionPaperQuestion;
use App\Models\QuestionPaperQuestionOption;
use App\Models\QuestionPaperQuestionMatch;
use App\Models\QuestionPaperQuestionPart;
use App\Models\ExamSetup;
use App\Models\AcademicSubject;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Carbon;
use Livewire\WithFileUploads;

class QuestionPaperBuilderComponent extends Component
{
    use WithFileUploads;

    public ?int $paperId = null;
    public bool $isLocked = false;

    /**
     * The paper's actual branch_id. Resolved once in mount() and reused
     * everywhere else (save(), lockPaper()) so it can never drift.
     *
     * IMPORTANT: for an existing paper this must stay the paper's own
     * branch_id — NOT recomputed via activeBranchId() on every save.
     * Admin has branch_id = null and can open/edit a paper that belongs
     * to any branch; recomputing "the active branch" on save would try to
     * silently move that paper to admin's resolved main branch (or worse,
     * collide with the unique id + fail the query) instead of keeping it
     * where it actually lives.
     */
    public ?int $branchId = null;

    // ---- meta fields (mirrors the builder's "Paper Details" panel) ----
    public int $examId;
    public int $subjectId;
    public ?int $academicClassId = null;
    public string $instituteName = '';
    public string $examName = '';
    public string $classLabel = '';
    public string $subjectLabel = '';
    public float $fullMarks = 0;
    public string $timeLabel = '';
    public string $language = 'en';

    public array $questions = [];
    protected array $typeDefs = QuestionPaperQuestion::TYPE_DEFS;

    public function mount(int $examId, int $subjectId, ?int $paperId = null): void
    {
        $institutionId = Auth::user()->institution_id;
        $branchId = $this->activeBranchId();

        if ($paperId) {
            $paper = QuestionPaper::query()
                ->where('institution_id', $institutionId)
                ->with(['questions.options', 'questions.matches', 'questions.parts'])
                ->findOrFail($paperId);

            $this->paperId = $paper->id;
            $this->branchId = $paper->branch_id;
            $this->examId = $paper->exam_id;
            $this->subjectId = $paper->subject_id;
            $this->isLocked = $paper->is_locked;
            $this->academicClassId = $paper->academic_class_id;
            $this->instituteName = $paper->institute_name ?? '';
            $this->examName = $paper->exam_name ?? '';
            $this->classLabel = $paper->class_label ?? '';
            $this->subjectLabel = $paper->subject_label ?? '';
            $this->fullMarks = (float) $paper->full_marks;
            $this->timeLabel = $paper->time_label ?? '';
            $this->language = $paper->language;

            $this->questions = $paper->questions->map(fn (QuestionPaperQuestion $q) => [
                'id' => 'existing-' . $q->id,
                'db_id' => $q->id,
                'type' => $q->type,
                'family' => $q->family,
                'section_header' => $q->section_header,
                'question_text' => $q->question_text,
                'stimulus_text' => $q->stimulus_text,
                'marks' => (float) $q->marks,
                'figure' => null,
                'figure_path' => $q->figure_path,
                'options' => $q->options->map(fn ($o) => [
                    'text' => $o->option_text, 'is_correct' => $o->is_correct,
                ])->toArray(),
                'matches' => $q->matches->map(fn ($m) => [
                    'left' => $m->left_text, 'right' => $m->right_text,
                ])->toArray(),
                'parts' => $q->parts->map(fn ($p) => [
                    'label' => $p->part_label, 'text' => $p->part_text, 'marks' => (float) $p->marks,
                ])->toArray(),
            ])->toArray();
        } else {
            $this->examId = $examId;
            $this->subjectId = $subjectId;
            $this->branchId = $branchId;

            // Only the relations actually used below are eager loaded.
            // (Previously "details.classAssignDetail.subject" was loaded but
            // never referenced anywhere — pure wasted joins/queries on every
            // fresh builder page load.)
            $examSetup = ExamSetup::with(['classAssign.academicClass'])
                ->where('institution_id', $institutionId)
                ->where('branch_id', $branchId)
                ->find($examId);
            $academicSubject = AcademicSubject::where('institution_id', $institutionId)
                ->where('branch_id', $branchId)
                ->find($subjectId);

            // abort_unless($examSetup && $academicSubject, 404, 'Exam or subject not found.');

            $this->instituteName = institution()->name;
            $this->examName = $examSetup->name ?? '';
            $this->classLabel = $examSetup->classAssign->academicClass->name ?? '';
            $this->subjectLabel = $academicSubject->name ?? '';
            // BUG FIX: this was never being set on a fresh paper, so
            // academic_class_id was always saved as NULL for newly created
            // papers (breaking any report/filter that relies on it).
            $this->academicClassId = $examSetup->classAssign->academic_class_id ?? null;
            $this->fullMarks = 100;
            $this->timeLabel = '1 Hours';
        }
    }

    private function activeBranchId(): ?int
    {
        return auth()->user()->branch_id
            ?? Branch::resolveMainBranchId(institution()->id);
    }

    /** Adds a new block of the given type, pre-filled per its family defaults. */
    public function addQuestion(string $type): void
    {
        abort_if($this->isLocked, 409, 'This paper is locked and can no longer be edited.');
        // abort_unless(isset($this->typeDefs[$type]), 422, 'Unknown question type.');

        $def = $this->typeDefs[$type];
        $family = $def['family'];

        $block = [
            'id' => 'new-' . uniqid('', true),
            'db_id' => null,
            'type' => $type,
            'family' => $family,
            'section_header' => null,
            'question_text' => '',
            'stimulus_text' => '',
            'marks' => 0,
            'figure' => null,
            'figure_path' => null,
            'options' => [],
            'matches' => [],
            'parts' => [],
        ];

        if ($family === 'options') {
            $optCount = $def['optCount'] ?? 4;
            $fixed = $def['fixedOpts'] ?? null;
            for ($i = 0; $i < $optCount; $i++) {
                $block['options'][] = [
                    'text' => $fixed[$i] ?? '',
                    'is_correct' => false,
                ];
            }
        } elseif ($family === 'matching_pairs') {
            for ($i = 0; $i < 4; $i++) {
                $block['matches'][] = ['left' => '', 'right' => ''];
            }
        } elseif ($family === 'stimulus_parts') {
            $labels = $def['partLabels'] ?? ['1', '2', '3', '4'];
            foreach ($labels as $label) {
                $block['parts'][] = ['label' => $label, 'text' => '', 'marks' => 0];
            }
        }

        $this->questions[] = $block;
    }

    public function removeQuestion(int $index): void
    {
        abort_if($this->isLocked, 409);

        if (isset($this->questions[$index]['figure_path']) && $this->questions[$index]['figure_path']) {
            // Only delete from disk on save() — not here — because the user
            // may still hit browser-back / Livewire may re-render before the
            // remove is persisted. Deleting the physical figure immediately
            // (before save()) would silently lose an image if the user never
            // actually saves this deletion. Left for save()'s orphan cleanup.
        }

        unset($this->questions[$index]);
        $this->questions = array_values($this->questions);
    }

    /** Called by the frontend drag-drop handler with the new index order. */
    public function reorder(array $orderedIds): void
    {
        abort_if($this->isLocked, 409);

        $byId = collect($this->questions)->keyBy('id');
        $this->questions = collect($orderedIds)
            ->map(fn ($id) => $byId->get($id))
            ->filter()
            ->values()
            ->toArray();
    }

    public function addOption(int $qIndex): void
    {
        abort_if($this->isLocked, 409);
        $this->questions[$qIndex]['options'][] = ['text' => '', 'is_correct' => false];
    }

    public function removeOption(int $qIndex, int $optIndex): void
    {
        abort_if($this->isLocked, 409);
        unset($this->questions[$qIndex]['options'][$optIndex]);
        $this->questions[$qIndex]['options'] = array_values($this->questions[$qIndex]['options']);
    }

    public function addMatchRow(int $qIndex): void
    {
        abort_if($this->isLocked, 409);
        $this->questions[$qIndex]['matches'][] = ['left' => '', 'right' => ''];
    }

    public function removeMatchRow(int $qIndex, int $mIndex): void
    {
        abort_if($this->isLocked, 409);
        unset($this->questions[$qIndex]['matches'][$mIndex]);
        $this->questions[$qIndex]['matches'] = array_values($this->questions[$qIndex]['matches']);
    }

    /**
     * Sets a question block's section_header directly from one of the
     * "reuse an existing heading" chip buttons in the UI.
     *
     * This replaces the old native <input list="..."> / <datalist> combo.
     * That combo let the browser's own (non-Livewire) autocomplete popup
     * open while typing; if the user then clicked anywhere else on the
     * page (e.g. an "Add Question" dropdown item) while that native popup
     * was still showing, the click was consumed by the browser just to
     * dismiss the popup and never reached the intended wire:click handler.
     * An ordinary wire:click button can never do that, so reuse is now
     * driven entirely through this method instead.
     */
    public function setSectionHeader(int $qIndex, string $header): void
    {
        abort_if($this->isLocked, 409);

        if (!isset($this->questions[$qIndex])) {
            return;
        }

        $this->questions[$qIndex]['section_header'] = $header;
    }

    public function removeFigure(int $qIndex): void
    {
        abort_if($this->isLocked, 409);

        if (!isset($this->questions[$qIndex])) {
            return;
        }

        $existingPath = $this->questions[$qIndex]['figure_path'] ?? null;
        if ($existingPath) {
            Storage::disk('public')->delete($existingPath);
        }

        $this->questions[$qIndex]['figure'] = null;
        $this->questions[$qIndex]['figure_path'] = null;
    }

    /** Every distinct section_header currently in use, in first-appearance order. */
    public function getSectionHeadersProperty(): array
    {
        $order = [];
        foreach ($this->questions as $q) {
            $header = $q['section_header'] ?: ($this->typeDefs[$q['type']]['section'] ?? 'General');
            if (!in_array($header, $order, true)) {
                $order[] = $header;
            }
        }
        return $order;
    }

    public function getTotalMarksProperty(): float
    {
        return collect($this->questions)->sum(function ($q) {
            return $q['family'] === 'stimulus_parts'
                ? collect($q['parts'])->sum('marks')
                : (float) $q['marks'];
        });
    }

    public function save(array $sectionLabelsBn = []): void
    {
        abort_if($this->isLocked, 409, 'This paper is locked and can no longer be edited.');

        $institutionId = Auth::user()->institution_id;
        // Reuse the branch id resolved once in mount() — see the $branchId
        // property doc-block for why this must not be recomputed here.
        $branchId = $this->branchId ?? $this->activeBranchId();

        $this->validate([
            'instituteName' => 'nullable|string|max:255',
            'examName' => 'nullable|string|max:255',
            'classLabel' => 'nullable|string|max:100',
            'subjectLabel' => 'nullable|string|max:100',
            'fullMarks' => 'required|numeric|min:0|max:9999.99',
            'timeLabel' => 'nullable|string|max:100',
            'language' => 'required|in:en,bn',
            'questions' => 'required|array|min:1',
            'questions.*.type' => ['required', 'string', function ($attribute, $value, $fail) {
                if (!isset($this->typeDefs[$value])) {
                    $fail('Unknown question type: ' . $value);
                }
            }],
            'questions.*.section_header' => 'nullable|string|max:255',
            'questions.*.question_text' => 'nullable|string|max:5000',
            'questions.*.stimulus_text' => 'nullable|string|max:8000',
            'questions.*.marks' => 'nullable|numeric|min:0|max:999.99',
            'questions.*.figure' => 'nullable|image|max:5120',
            'questions.*.options.*.text' => 'nullable|string|max:1000',
            'questions.*.options.*.is_correct' => 'nullable|boolean',
            'questions.*.matches.*.left' => 'nullable|string|max:500',
            'questions.*.matches.*.right' => 'nullable|string|max:500',
            'questions.*.parts.*.label' => 'nullable|string|max:10',
            'questions.*.parts.*.text' => 'nullable|string|max:2000',
            'questions.*.parts.*.marks' => 'nullable|numeric|min:0|max:999.99',
        ], [
            'questions.required' => 'At least one question is required before saving.',
            'questions.min' => 'At least one question is required before saving.',
        ]);

        foreach ($this->questions as $index => $block) {
            $def = $this->typeDefs[$block['type']] ?? null;
            // abort_unless($def, 422, "Unknown question type: {$block['type']}");
            $this->questions[$index]['family'] = $def['family'];
        }

        DB::transaction(function () use ($institutionId, $branchId, $sectionLabelsBn) {
            $attributes = [
                'institution_id' => $institutionId,
                'branch_id' => $branchId,
                'exam_id' => $this->examId,
                'subject_id' => $this->subjectId,
                'academic_class_id' => $this->academicClassId,
                'institute_name' => $this->instituteName,
                'exam_name' => $this->examName,
                'class_label' => $this->classLabel,
                'subject_label' => $this->subjectLabel,
                'full_marks' => $this->fullMarks,
                'time_label' => $this->timeLabel,
                'language' => $this->language,
                'created_by' => $this->paperId ? DB::raw('created_by') : Auth::id(),
            ];

            /*
                BUG FIX: SQLSTATE[23000] duplicate entry on
                qp_ins_bra_exa_sub_unique (institution_id, branch_id,
                exam_id, subject_id).

                Root cause: updateOrCreate()'s FIRST argument is what
                decides which row gets looked up — the ->where(...)
                clauses chained before it do NOT constrain that lookup.
                The old code always looked up by ['id' => $this->paperId].
                On a brand-new paper $this->paperId is null, so it always
                attempted an INSERT — even if a paper already existed for
                this exact institution+branch+exam+subject tuple (e.g. the
                builder was opened again via the "create" route for an
                exam/subject that already has a saved paper, after a page
                refresh or back-navigation lost the paperId route param).
                That second insert collided with the unique constraint.

                Fix: when editing (paperId known), look the row up by id,
                scoped to this institution+branch for safety. When
                creating (paperId unknown), look the row up by the SAME
                tuple the unique constraint protects, so a second "create"
                for an exam/subject that already has a paper safely UPDATEs
                the existing paper instead of colliding on insert.
            */
            if ($this->paperId) {
                $paper = QuestionPaper::query()
                    ->where('institution_id', $institutionId)
                    ->where('branch_id', $branchId)
                    ->findOrFail($this->paperId);
                $paper->fill($attributes);
                $paper->save();
            } else {
                $paper = QuestionPaper::query()->updateOrCreate(
                    [
                        'institution_id' => $institutionId,
                        'branch_id' => $branchId,
                        'exam_id' => $this->examId,
                        'subject_id' => $this->subjectId,
                    ],
                    $attributes
                );
            }

            $this->paperId = $paper->id;

            $oldFigurePaths = $paper->questions()->pluck('figure_path')->filter()->values()->all();

            // PERFORMANCE FIX: the previous code loaded every existing
            // question model and called ->delete() one row at a time inside
            // a loop. For a paper with 40-50 questions that is 40-50 separate
            // DELETE queries (Eloquent), on top of which every options/
            // matches/parts child row was inserted one at a time as well
            // (another 100-200+ INSERT queries per save). That is what was
            // making "Save" feel slow on real papers.
            //
            // The FK columns are defined with ->cascadeOnDelete() at the DB
            // level, so a single mass-delete on the questions table is
            // enough — MySQL itself cascades the child rows for us.
            $paper->questions()->delete();

            $keepFigurePaths = [];
            $resolvedFigurePaths = []; // order => final on-disk path, used to sync Livewire state after commit

            $questionRows = [];
            foreach ($this->questions as $order => $block) {
                $figurePath = $block['figure_path'] ?? null;
                if ($block['figure'] ?? null) {
                    // New upload/drawing replaces whatever was there before.
                    if ($figurePath) {
                        Storage::disk('public')->delete($figurePath);
                    }
                    $figurePath = $block['figure']->store('question-paper-figures/' . $institutionId, 'public');
                }

                if ($figurePath) {
                    $keepFigurePaths[] = $figurePath;
                }
                $resolvedFigurePaths[$order] = $figurePath;

                $questionRows[$order] = [
                    'block' => $block,
                    'figure_path' => $figurePath,
                ];
            }

            // Insert parent rows first (need each auto-increment id before we
            // can attach children), but collect ALL child rows across every
            // question into three flat arrays and bulk-insert each array in
            // a single query — instead of one query per option/match/part.
            $now = Carbon::now();
            $optionRows = [];
            $matchRows = [];
            $partRows = [];

            foreach ($questionRows as $order => $data) {
                $block = $data['block'];

                $question = $paper->questions()->create([
                    'type' => $block['type'],
                    'family' => $block['family'],
                    'section_header' => $block['section_header'],
                    'question_text' => $block['question_text'],
                    'stimulus_text' => $block['stimulus_text'],
                    'marks' => $block['family'] === 'stimulus_parts' ? 0 : $block['marks'],
                    'figure_path' => $data['figure_path'],
                    'sort_order' => $order,
                ]);

                if ($block['family'] === 'options') {
                    foreach ($block['options'] as $i => $opt) {
                        $optionRows[] = [
                            'question_paper_question_id' => $question->id,
                            'option_text' => $opt['text'],
                            'is_correct' => (bool) ($opt['is_correct'] ?? false),
                            'sort_order' => $i,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                    }
                } elseif ($block['family'] === 'matching_pairs') {
                    foreach ($block['matches'] as $i => $m) {
                        $matchRows[] = [
                            'question_paper_question_id' => $question->id,
                            'left_text' => $m['left'],
                            'right_text' => $m['right'],
                            'sort_order' => $i,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                    }
                } elseif ($block['family'] === 'stimulus_parts') {
                    foreach ($block['parts'] as $i => $p) {
                        $partRows[] = [
                            'question_paper_question_id' => $question->id,
                            'part_label' => $p['label'],
                            'part_text' => $p['text'],
                            'marks' => $p['marks'],
                            'sort_order' => $i,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                    }
                }
            }

            // 3 bulk inserts total instead of hundreds of individual ones.
            if ($optionRows) {
                QuestionPaperQuestionOption::insert($optionRows);
            }
            if ($matchRows) {
                QuestionPaperQuestionMatch::insert($matchRows);
            }
            if ($partRows) {
                QuestionPaperQuestionPart::insert($partRows);
            }

            foreach (array_diff($oldFigurePaths, $keepFigurePaths) as $orphanPath) {
                Storage::disk('public')->delete($orphanPath);
            }

            // Persist dynamic Bangla section label overrides.
            $paper->sectionLabels()->delete();
            $labelRows = [];
            foreach ($sectionLabelsBn as $order => $entry) {
                $labelRows[] = [
                    'question_paper_id' => $paper->id,
                    'section_key' => $entry['key'],
                    'label_bn' => $entry['label_bn'],
                    'sort_order' => $order,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
            if ($labelRows) {
                $paper->sectionLabels()->insert($labelRows);
            }

            activity()
                ->causedBy(auth()->user())
                ->performedOn($paper)
                ->withProperties(['icon' => 'description', 'type' => 'question'])
                ->tap(function ($activity) use ($paper) {
                    $activity->institution_id = $paper->institution_id;
                    $activity->branch_id = $paper->branch_id;
                })
                ->log('Question paper saved: ' . count($this->questions));

            foreach ($resolvedFigurePaths as $order => $path) {
                if (isset($this->questions[$order])) {
                    $this->questions[$order]['figure'] = null;
                    $this->questions[$order]['figure_path'] = $path;
                }
            }
        });

        $this->dispatch('toast', type: 'success', message: 'Question paper saved.');
    }

    /** Locks the paper — irreversible; call only once it's ready to enter the print pipeline. */
    public function lockPaper(): void
    {
        // abort_unless($this->paperId, 422, 'Save the paper before locking it.');

        $institutionId = Auth::user()->institution_id;
        $paper = QuestionPaper::query()
            ->where('institution_id', $institutionId)
            ->findOrFail($this->paperId);

        $paper->lock(Auth::id());
        $this->isLocked = true;

        $this->dispatch('toast', type: 'success', message: 'Paper locked. It can now be scheduled for printing.');
    }

    public function render()
    {
        return view('livewire.admin.question-paper.question-paper-builder-component', [
            'typeDefs' => $this->typeDefs,
        ])
        ->layout('layouts.app', [
            'title' => 'Question Paper Builder | ' . institution()->name,
        ]);
    }
}