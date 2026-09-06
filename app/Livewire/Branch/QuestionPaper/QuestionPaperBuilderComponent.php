<?php

namespace App\Livewire\Branch\QuestionPaper;

use Livewire\Component;
use App\Models\Branch;
use App\Models\QuestionPaper;
use App\Models\QuestionPaperQuestion;
use App\Models\ExamSetup;
use App\Models\AcademicSubject;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\WithFileUploads;

class QuestionPaperBuilderComponent extends Component
{
    use WithFileUploads;

    public ?int $paperId = null;
    public bool $isLocked = false;

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
    public string $language = 'bn';

    // ---- working question list (array of assoc arrays) ----
    // Each item: [id, type, family, section_header, question_text, stimulus_text,
    //             marks, figure (UploadedFile|null), figure_path (existing),
    //             options[], matches[], parts[]]
    public array $questions = [];

    // Server-side type registry — same source of truth used for validation.
    // Kept protected (not public) so it never rides along in every Livewire
    // request payload; the model constant is referenced directly wherever
    // it's needed instead of syncing a copy of it as component state.
    protected array $typeDefs = QuestionPaperQuestion::TYPE_DEFS;

    public function mount(int $examId, int $subjectId, ?int $paperId = null): void
    {
        if ($paperId) {
            $institutionId = Auth::user()->institution_id;
            $branchId = Auth::user()->branch_id;

            $paper = QuestionPaper::query()
                ->where('institution_id', $institutionId)
                ->where('branch_id', $branchId)
                ->with(['questions.options', 'questions.matches', 'questions.parts'])
                ->findOrFail($paperId);

            // The loaded paper row is the source of truth for which
            // exam/subject it belongs to — NOT the URL's route parameters.
            // Trusting the URL here would let a hand-edited link (or a
            // stale bookmark from before the paper was re-filed) silently
            // rewrite exam_id/subject_id on save(), corrupting existing
            // data. The URL params are still accepted (Livewire route
            // binding requires them) but are only used as a display fallback
            // before the paper loads — never to build the save payload.
            $this->paperId = $paper->id;
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

            $institutionId = Auth::user()->institution_id;
            $branchId = $this->activeBranchId();

            $examSetup = ExamSetup::with('classAssign.academicClass', 'classAssign.academicSection', 'details.classAssignDetail.subject')
                ->where('institution_id', $institutionId)
                ->where('branch_id', $branchId)
                ->find($examId);
            $academicSubject = AcademicSubject::where('institution_id', $institutionId)
                ->where('branch_id', $branchId)
                ->find($subjectId);

            // Without this guard, a bad/stale/cross-institution examId or
            // subjectId in the URL silently produced empty labels (from
            // null-safe fallbacks) and only failed later, ungracefully,
            // with a raw FK-constraint database exception on save().
            abort_unless($examSetup && $academicSubject, 404, 'Exam or subject not found.');

            $this->instituteName = institution()->name;
            $this->examName = $examSetup->name ?? '';
            $this->classLabel = $examSetup->classAssign->academicClass->name ?? '';
            $this->subjectLabel = $academicSubject->name ?? '';
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
        abort_unless(isset($this->typeDefs[$type]), 422, 'Unknown question type.');

        $def = $this->typeDefs[$type];
        $family = $def['family'];

        $block = [
            'id' => 'new-' . uniqid(),
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

    /**
     * Clears a question's figure (whether it's a freshly uploaded/drawn temp
     * file that hasn't been saved yet, or an already-persisted figure_path).
     * Persisted files are deleted from disk immediately since the user has
     * explicitly asked for them to go; unsaved temp uploads are simply
     * dropped by Livewire's own temp-file GC.
     */
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
        $branchId = Auth::user()->branch_id;

        $this->validate([
            'instituteName' => 'nullable|string|max:255',
            'examName' => 'nullable|string|max:255',
            'classLabel' => 'nullable|string|max:100',
            'subjectLabel' => 'nullable|string|max:100',
            'fullMarks' => 'required|numeric|min:0',
            'timeLabel' => 'nullable|string|max:100',
            'language' => 'required|in:en,bn',
            'questions' => 'required|array|min:1',
            'questions.*.type' => 'required|string',
            'questions.*.figure' => 'nullable|image|max:5120',
        ], [
            'questions.required' => 'At least one question is required before saving.',
            'questions.min' => 'At least one question is required before saving.',
        ]);

        // Defense-in-depth: re-derive each question's `family` from the
        // canonical TYPE_DEFS registry by its `type`, rather than trusting
        // whatever `family` string is currently sitting in the client-synced
        // $this->questions array. `family` controls which child table
        // (options/matches/parts) gets written below, so it must come from
        // the server-side source of truth, not client state.
        foreach ($this->questions as $index => $block) {
            $def = $this->typeDefs[$block['type']] ?? null;
            abort_unless($def, 422, "Unknown question type: {$block['type']}");
            $this->questions[$index]['family'] = $def['family'];
        }

        DB::transaction(function () use ($institutionId, $branchId, $sectionLabelsBn) {
            $paper = QuestionPaper::query()
                ->where('institution_id', $institutionId)
                ->where('branch_id', $branchId)
                ->updateOrCreate(
                    ['id' => $this->paperId],
                    [
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
                    ]
                );

            $this->paperId = $paper->id;

            // Snapshot which figure files currently exist on disk *before*
            // we wipe the question rows, so we can tell afterwards which of
            // them are still referenced (untouched) vs. genuinely orphaned.
            $oldFigurePaths = $paper->questions()->pluck('figure_path')->filter()->values()->all();

            // Rebuild questions wholesale — simplest correct approach for a
            // reorderable builder; the paper isn't "live" until locked, so
            // there's no external reference to preserve mid-edit.
            $paper->questions()->each(function (QuestionPaperQuestion $q) {
                $q->delete(); // cascades options/matches/parts via FK
            });

            $keepFigurePaths = [];
            $resolvedFigurePaths = []; // order => final on-disk path, used to sync Livewire state after commit

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

                $question = $paper->questions()->create([
                    'type' => $block['type'],
                    'family' => $block['family'],
                    'section_header' => $block['section_header'],
                    'question_text' => $block['question_text'],
                    'stimulus_text' => $block['stimulus_text'],
                    'marks' => $block['family'] === 'stimulus_parts' ? 0 : $block['marks'],
                    'figure_path' => $figurePath,
                    'sort_order' => $order,
                ]);

                if ($block['family'] === 'options') {
                    foreach ($block['options'] as $i => $opt) {
                        $question->options()->create([
                            'option_text' => $opt['text'],
                            'is_correct' => $opt['is_correct'],
                            'sort_order' => $i,
                        ]);
                    }
                } elseif ($block['family'] === 'matching_pairs') {
                    foreach ($block['matches'] as $i => $m) {
                        $question->matches()->create([
                            'left_text' => $m['left'],
                            'right_text' => $m['right'],
                            'sort_order' => $i,
                        ]);
                    }
                } elseif ($block['family'] === 'stimulus_parts') {
                    foreach ($block['parts'] as $i => $p) {
                        $question->parts()->create([
                            'part_label' => $p['label'],
                            'part_text' => $p['text'],
                            'marks' => $p['marks'],
                            'sort_order' => $i,
                        ]);
                    }
                }
            }

            // Any figure that existed before this save but isn't referenced
            // by the rebuilt question set anymore (question removed, or its
            // figure was replaced/removed) is now a genuine orphan — clean
            // it up. Figures that are simply carried over untouched are
            // never deleted.
            foreach (array_diff($oldFigurePaths, $keepFigurePaths) as $orphanPath) {
                Storage::disk('public')->delete($orphanPath);
            }

            // Persist dynamic Bangla section label overrides.
            foreach ($sectionLabelsBn as $order => $entry) {
                $paper->sectionLabels()->updateOrCreate(
                    ['section_key' => $entry['key']],
                    ['label_bn' => $entry['label_bn'], 'sort_order' => $order]
                );
            }

            activity()
                ->tap(fn ($a) => $a->institution_id = $institutionId)
                ->causedBy(Auth::id())
                ->performedOn($paper)
                ->withProperties([
                    'icon' => 'description',
                    'type' => 'question_paper_saved',
                    'question_count' => count($this->questions),
                ])
                ->log('Question paper saved');

            // Sync in-memory builder state with what actually landed on
            // disk/DB: clear the transient UploadedFile (already stored)
            // and point figure_path at the final resolved path.
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
        abort_unless($this->paperId, 422, 'Save the paper before locking it.');

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