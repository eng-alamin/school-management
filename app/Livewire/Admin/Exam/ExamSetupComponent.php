<?php

namespace App\Livewire\Admin\Exam;

use Livewire\Component;
use App\Models\ExamTerm;
use App\Models\ExamType;
use App\Models\ExamSetup;
use App\Models\ExamSetupDetail;
use App\Models\AcademicClassAssign;
use App\Models\AcademicSession;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\WithPagination;

class ExamSetupComponent extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    // Sort column allowlist — direct Livewire method call দিয়ে arbitrary column
    // পাঠিয়ে orderBy() manipulate করা ঠেকানোর জন্য (ClassComponent pattern অনুসরণ করা হয়েছে)
    private const SORTABLE_FIELDS = ['id', 'name'];

    // List
    public string $search        = '';
    public int    $perPage       = 10;
    public string $sortField     = 'id';
    public string $sortDirection = 'desc';

    // Modal
    public bool $showModal     = false;
    public bool $confirmDelete = false;
    public ?int $deleteId      = null;

    // View modal
    public bool       $showViewModal = false;
    public ?ExamSetup $viewRecord    = null;

    // Common form fields (create + edit উভয় ক্ষেত্রে ব্যবহার হয়)
    public ?int   $editId       = null;
    public string $name         = '';
    public ?int   $exam_term_id = null;
    public ?int   $exam_type_id = null;
    public string $remarks      = '';
    public bool   $is_published = false;

    // ── EDIT MODE: single class ──
    public ?int  $academic_class_assign_id = null;
    public array $subjects                 = []; // subjects[detail_id] = ['subject_name'=>, 'full_mark'=>, ...]

    // ── CREATE MODE: bulk multi-class ──
    public array $selectedClassAssignIds = [];
    public float $default_full_mark      = 100;
    public float $default_pass_mark      = 33;
    public float $default_written_mark   = 0;
    public float $default_mcq_mark       = 0;
    public float $default_practical_mark = 0;

    protected function rules(): array
    {
        $institutionId = institution()->id;

        $common = [
            'name'         => 'required|string|max:255',
            // exists rule Eloquent global scope বাইপাস করে (raw DB query),
            // তাই institution_id দিয়ে explicit scope করা হলো — নাহলে অন্য
            // institution-এর term/type id পাস করেও validation পাশ হয়ে যেত (IDOR)।
            'exam_term_id' => [
                'nullable',
                Rule::exists('exam_terms', 'id')->where('institution_id', $institutionId),
            ],
            'exam_type_id' => [
                'nullable',
                Rule::exists('exam_types', 'id')->where('institution_id', $institutionId),
            ],
            'remarks'      => 'nullable|string',
            'is_published' => 'boolean',
        ];

        if ($this->editId) {
            return $common + [
                'academic_class_assign_id' => [
                    'required',
                    Rule::exists('academic_class_assigns', 'id')->where('institution_id', $institutionId),
                ],
            ];
        }

        return $common + [
            'selectedClassAssignIds'   => 'required|array|min:1',
            'selectedClassAssignIds.*' => Rule::exists('academic_class_assigns', 'id')->where('institution_id', $institutionId),
            'default_full_mark'        => 'required|numeric|min:0',
            'default_pass_mark'        => 'required|numeric|min:0|lte:default_full_mark',
            'default_written_mark'     => 'nullable|numeric|min:0',
            'default_mcq_mark'         => 'nullable|numeric|min:0',
            'default_practical_mark'   => 'nullable|numeric|min:0',
        ];
    }

    protected $messages = [
        'selectedClassAssignIds.required' => 'Select at least one class.',
        'selectedClassAssignIds.min'      => 'Select at least one class.',
        'default_pass_mark.lte'           => 'Pass mark cannot be greater than full mark.',
    ];

    /**
     * প্রতিটা subject row-এর pass_mark যেন full_mark এর চেয়ে বেশি না হয়
     * (edit mode-এর dynamic subjects array-এর জন্য rules() এ static rule লেখা যায় না,
     * তাই withValidator দিয়ে অতিরিক্ত check করা হচ্ছে)।
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->editId) {
                foreach ($this->subjects as $detailId => $row) {
                    $full = (float) ($row['full_mark'] ?? 0);
                    $pass = (float) ($row['pass_mark'] ?? 0);
                    if ($pass > $full) {
                        $validator->errors()->add(
                            "subjects.{$detailId}.pass_mark",
                            "Pass mark cannot be greater than full mark for {$row['subject_name']}."
                        );
                    }
                }
            }
        });
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    // ── Edit mode এ dropdown change হলে subjects reload হবে ──
    public function updatedAcademicClassAssignId(): void
    {
        $this->loadSubjectsByClassAssign();
    }

    private function loadSubjectsByClassAssign(): void
    {
        $this->subjects = [];

        if (!$this->academic_class_assign_id) {
            return;
        }

        $existingDetails = [];
        if ($this->editId) {
            $existingDetails = ExamSetupDetail::where('exam_setup_id', $this->editId)
                ->get()
                ->keyBy('academic_class_assign_detail_id')
                ->toArray();
        }

        $assign = AcademicClassAssign::with('details.subject')
            ->find($this->academic_class_assign_id);

        if (!$assign || $assign->details->isEmpty()) {
            return;
        }

        foreach ($assign->details as $detail) {
            $existing = $existingDetails[$detail->id] ?? null;

            $this->subjects[$detail->id] = [
                'subject_name'   => $detail->subject->name ?? '—',
                'full_mark'      => $existing ? (float) $existing['full_mark']      : 100,
                'pass_mark'      => $existing ? (float) $existing['pass_mark']      : 33,
                'written_mark'   => $existing ? (float) $existing['written_mark']   : 0,
                'mcq_mark'       => $existing ? (float) $existing['mcq_mark']       : 0,
                'practical_mark' => $existing ? (float) $existing['practical_mark'] : 0,
            ];
        }
    }

    // ── Bulk create: checkbox helper ──
    public function selectAllClasses(): void
    {
        $this->selectedClassAssignIds = AcademicClassAssign::pluck('id')->map(fn($id) => (string) $id)->toArray();
    }

    public function deselectAllClasses(): void
    {
        $this->selectedClassAssignIds = [];
    }

    public function sortBy(string $field): void
    {
        // Allowlist check — না থাকলে silently ignore করে দেওয়া হচ্ছে,
        // যাতে সরাসরি Livewire component call করে arbitrary column দিয়ে
        // orderBy() manipulate করা না যায়।
        if (!in_array($field, self::SORTABLE_FIELDS, true)) {
            return;
        }

        $this->sortDirection = ($this->sortField === $field && $this->sortDirection === 'asc') ? 'desc' : 'asc';
        $this->sortField     = $field;
        $this->resetPage();
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $record = ExamSetup::findOrFail($id);

        $this->editId                   = $id;
        $this->name                     = $record->name;
        $this->academic_class_assign_id = $record->academic_class_assign_id;
        $this->exam_term_id             = $record->exam_term_id;
        $this->exam_type_id             = $record->exam_type_id;
        $this->remarks                  = $record->remarks ?? '';
        $this->is_published             = $record->is_published;

        $this->loadSubjectsByClassAssign();
        $this->showModal = true;
    }

    public function openView(int $id): void
    {
        // FIX: 'class'/'section' নামে কোনো relation model-এ নাই — real relation
        // নাম হলো academicClass/academicSection।
        $this->viewRecord    = ExamSetup::with([
            'term', 'type',
            'classAssign.academicClass', 'classAssign.academicSection',
            'details.classAssignDetail.subject',
        ])->findOrFail($id);
        $this->showViewModal = true;
    }

    public function save(): void
    {
        $this->validate();

        $currentSession = AcademicSession::where('is_current', true)->first();
        $institutionId  = institution()->id;

        // ══════════════ EDIT MODE (single class) ══════════════
        if ($this->editId) {
            // Duplicate prevention: একই class+term+type combo-তে অন্য কোনো
            // setup ইতিমধ্যে আছে কিনা (নিজেকে বাদ দিয়ে)
            $duplicate = ExamSetup::where('academic_class_assign_id', $this->academic_class_assign_id)
                ->where('exam_term_id', $this->exam_term_id)
                ->where('exam_type_id', $this->exam_type_id)
                ->where('id', '!=', $this->editId)
                ->exists();

            if ($duplicate) {
                $this->addError('academic_class_assign_id', 'এই Class + Term + Type combination-এ ইতিমধ্যে একটি Exam Setup আছে।');
                return;
            }

            DB::beginTransaction();
            try {
                $record = ExamSetup::findOrFail($this->editId);
                $record->update([
                    'name'                     => $this->name,
                    'academic_session_id'      => $currentSession?->id,
                    'academic_class_assign_id' => $this->academic_class_assign_id,
                    'exam_term_id'             => $this->exam_term_id,
                    'exam_type_id'             => $this->exam_type_id,
                    'remarks'                  => $this->remarks,
                    'is_published'             => $this->is_published,
                ]);

                ExamSetupDetail::where('exam_setup_id', $record->id)->delete();

                $serial = 1;
                foreach ($this->subjects as $detailId => $marks) {
                    ExamSetupDetail::create([
                        // FIX: institution_id ছিল না — NOT NULL কলামের জন্য
                        // insert fail করতে পারত, বা cross-tenant row তৈরি হতে পারত।
                        'institution_id'                   => $institutionId,
                        'exam_setup_id'                    => $record->id,
                        'academic_class_assign_detail_id'  => $detailId,
                        'full_mark'                        => $marks['full_mark']      ?? 100,
                        'pass_mark'                        => $marks['pass_mark']      ?? 33,
                        'written_mark'                     => $marks['written_mark']   ?? 0,
                        'mcq_mark'                         => $marks['mcq_mark']       ?? 0,
                        'practical_mark'                   => $marks['practical_mark'] ?? 0,
                        'serial'                           => $serial++,
                    ]);
                }

                activity()
                    ->causedBy(auth()->user())
                    ->performedOn($record)
                    ->withProperties(['icon' => 'assignment', 'type' => 'exam_setup'])
                    ->tap(fn($a) => $a->institution_id = $record->institution_id)
                    ->log('Exam setup updated: ' . $record->name);

                DB::commit();
            } catch (\Throwable $e) {
                DB::rollBack();
                $this->dispatch('toast', type: 'error', message: 'Something went wrong!');
                return;
            }

            $this->dispatch('toast', type: 'success', message: 'Data updated successfully!');
            $this->showModal = false;
            $this->resetForm();
            return;
        }

        // ══════════════ CREATE MODE (bulk, multi-class) ══════════════
        DB::beginTransaction();
        try {
            $createdCount   = 0;
            $skippedNoSubj  = [];
            $skippedDup     = [];

            // FIX: 'class'/'section' এর বদলে real relation নাম academicClass/academicSection
            $assigns = AcademicClassAssign::with(['details', 'academicClass', 'academicSection'])
                ->whereIn('id', $this->selectedClassAssignIds)
                ->get();

            // Duplicate prevention: এই term+type-এ কোন কোন class assign এর জন্য
            // আগে থেকেই setup আছে তা একসাথে বের করা হচ্ছে (N+1 এড়াতে)
            $existingSetupClassAssignIds = ExamSetup::where('exam_term_id', $this->exam_term_id)
                ->where('exam_type_id', $this->exam_type_id)
                ->whereIn('academic_class_assign_id', $this->selectedClassAssignIds)
                ->pluck('academic_class_assign_id')
                ->all();

            foreach ($assigns as $assign) {
                if (in_array($assign->id, $existingSetupClassAssignIds, true)) {
                    $skippedDup[] = $this->buildClassLabel($assign);
                    continue; // এই class+term+type এর জন্য setup আগে থেকেই আছে
                }

                if ($assign->details->isEmpty()) {
                    $skippedNoSubj[] = $this->buildClassLabel($assign);
                    continue; // এই class এ কোনো subject assign করা নেই, তাই skip
                }

                $record = ExamSetup::create([
                    // FIX: institution_id ছিল না — bulk create-এ প্রতিটা row
                    // NOT NULL constraint এ fail করত অথবা cross-tenant leak হতো।
                    'institution_id'           => $institutionId,
                    'name'                     => $this->name,
                    'academic_session_id'      => $currentSession?->id,
                    'academic_class_assign_id' => $assign->id,
                    'exam_term_id'             => $this->exam_term_id,
                    'exam_type_id'             => $this->exam_type_id,
                    'remarks'                  => $this->remarks,
                    'is_published'             => $this->is_published,
                ]);

                $serial = 1;
                foreach ($assign->details as $detail) {
                    ExamSetupDetail::create([
                        // FIX: institution_id ছিল না
                        'institution_id'                   => $institutionId,
                        'exam_setup_id'                    => $record->id,
                        'academic_class_assign_detail_id'  => $detail->id,
                        'full_mark'                        => $this->default_full_mark,
                        'pass_mark'                        => $this->default_pass_mark,
                        'written_mark'                     => $this->default_written_mark,
                        'mcq_mark'                         => $this->default_mcq_mark,
                        'practical_mark'                   => $this->default_practical_mark,
                        'serial'                           => $serial++,
                    ]);
                }

                activity()
                    ->causedBy(auth()->user())
                    ->performedOn($record)
                    ->withProperties(['icon' => 'assignment', 'type' => 'exam_setup'])
                    ->tap(fn($a) => $a->institution_id = $record->institution_id)
                    ->log('Exam setup created: ' . $record->name);

                $createdCount++;
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->dispatch('toast', type: 'error', message: 'Something went wrong!');
            return;
        }

        $message = "{$createdCount}টি Exam Setup তৈরি হয়েছে!";
        if (!empty($skippedNoSubj)) {
            $message .= ' | Subject না থাকায় বাদ পড়েছে: ' . implode(', ', $skippedNoSubj);
        }
        if (!empty($skippedDup)) {
            $message .= ' | আগে থেকেই Setup থাকায় বাদ পড়েছে: ' . implode(', ', $skippedDup);
        }

        $this->dispatch('toast', type: 'success', message: $message);
        $this->showModal = false;
        $this->resetForm();
    }

    public function togglePublished(int $id): void
    {
        $setup = ExamSetup::findOrFail($id);
        $setup->update(['is_published' => ! $setup->is_published]);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($setup)
            ->withProperties(['icon' => 'assignment', 'type' => 'exam_setup'])
            ->tap(fn($a) => $a->institution_id = $setup->institution_id)
            ->log('Exam publish toggled: ' . $setup->name);

        $this->dispatch('toast', type: 'success', message: 'Status updated!');
    }

    public function toggleResultPublished(int $id): void
    {
        $setup = ExamSetup::findOrFail($id);
        $setup->update(['is_result_published' => ! $setup->is_result_published]);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($setup)
            ->withProperties(['icon' => 'assignment', 'type' => 'exam_setup'])
            ->tap(fn($a) => $a->institution_id = $setup->institution_id)
            ->log('Exam result publish toggled: ' . $setup->name);

        $this->dispatch('toast', type: 'success', message: 'Result status updated!');
    }

    public function confirmDeleteRecord(int $id): void
    {
        $this->deleteId      = $id;
        $this->confirmDelete = true;
    }

    public function deleteRecord(): void
    {
        DB::beginTransaction();
        try {
            $record = ExamSetup::findOrFail($this->deleteId);

            activity()
                ->causedBy(auth()->user())
                ->performedOn($record)
                ->withProperties(['icon' => 'assignment', 'type' => 'exam_setup'])
                ->tap(fn($a) => $a->institution_id = $record->institution_id)
                ->log('Exam setup deleted: ' . $record->name);

            ExamSetupDetail::where('exam_setup_id', $record->id)->delete();
            $record->delete();

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->dispatch('toast', type: 'error', message: 'Something went wrong!');
            return;
        }

        $this->confirmDelete = false;
        $this->deleteId      = null;
        $this->dispatch('toast', type: 'success', message: 'Data deleted successfully!');
    }

    /**
     * Build a human-readable label for a Class Assign.
     * section_id null থাকা মানে এই class-এর জন্য section প্রযোজ্যই না
     * (has_section = false), তাই এখানে কোনো placeholder টেক্সট ("All Section")
     * দেখানো হয় না — শুধু section থাকলেই তা label-এ যোগ হয়।
     *
     * FIX: আগে $assign->class / $assign->section ব্যবহার হচ্ছিল যা model-এ
     * অস্তিত্বহীন relation — real relation নাম academicClass/academicSection।
     */
    private function buildClassLabel(AcademicClassAssign $assign): string
    {
        $label = $assign->academicClass->name ?? 'Unknown';

        // যে class-এ section নেই, সেখানে "All Section" এর মতো ভুয়া টেক্সট
        // না দেখিয়ে শুধু class name দেখানো হচ্ছে। Section থাকলে তবেই জোড়া লাগবে।
        if ($assign->academicSection) {
            $label .= ' - ' . $assign->academicSection->name;
        }

        return $label;
    }

    private function resetForm(): void
    {
        $this->reset([
            'editId', 'name', 'academic_class_assign_id', 'exam_term_id', 'exam_type_id',
            'remarks', 'is_published', 'subjects',
            'selectedClassAssignIds', 'default_full_mark', 'default_pass_mark',
            'default_written_mark', 'default_mcq_mark', 'default_practical_mark',
        ]);
        $this->resetValidation();
    }

    public function render()
    {
        $terms = ExamTerm::pluck('name', 'id');
        $types = ExamType::pluck('name', 'id');

        $classAssigns = AcademicClassAssign::with(['academicClass', 'academicSection', 'details'])
            ->get()
            ->map(function ($assign) {
                return [
                    'id'            => $assign->id,
                    'label'         => $this->buildClassLabel($assign),
                    'subject_count' => $assign->details->count(),
                ];
            });

        $setups = ExamSetup::with(['term', 'type', 'classAssign.academicClass', 'classAssign.academicSection', 'details'])
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.admin.exam.exam-setup-component')
            ->with('terms', $terms)
            ->with('types', $types)
            ->with('classAssigns', $classAssigns)
            ->with('setups', $setups)
            ->layout('layouts.admin.app', [
                'title' => 'Exam Setup | ' . institution()->name,
            ]);
    }
}