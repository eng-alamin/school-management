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
use Livewire\WithPagination;

class ExamSetupComponent extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    // List
    public string $search        = '';
    public int    $perPage       = 10;
    public string $sortField     = 'id';
    public string $sortDirection = 'asc';

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
        $common = [
            'name'         => 'required|string|max:255',
            'exam_term_id' => 'nullable|exists:exam_terms,id',
            'exam_type_id' => 'nullable|exists:exam_types,id',
            'remarks'      => 'nullable|string',
            'is_published' => 'boolean',
        ];

        if ($this->editId) {
            return $common + [
                'academic_class_assign_id' => 'required|exists:academic_class_assigns,id',
            ];
        }

        return $common + [
            'selectedClassAssignIds'   => 'required|array|min:1',
            'selectedClassAssignIds.*' => 'exists:academic_class_assigns,id',
            'default_full_mark'        => 'required|numeric|min:0',
            'default_pass_mark'        => 'required|numeric|min:0',
            'default_written_mark'     => 'nullable|numeric|min:0',
            'default_mcq_mark'         => 'nullable|numeric|min:0',
            'default_practical_mark'   => 'nullable|numeric|min:0',
        ];
    }

    protected $messages = [
        'selectedClassAssignIds.required' => 'Select at least one class.',
        'selectedClassAssignIds.min'      => 'Select at least one class.',
    ];

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
        $this->sortField     = $field;
        $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
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
        $this->viewRecord    = ExamSetup::with([
            'term', 'type',
            'classAssign.class', 'classAssign.section',
            'details.classAssignDetail.subject',
        ])->findOrFail($id);
        $this->showViewModal = true;
    }

    public function save(): void
    {
        $this->validate();

        $currentSession = AcademicSession::where('is_current', true)->first();

        // ══════════════ EDIT MODE (single class) ══════════════
        if ($this->editId) {
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
                        'exam_setup_id'                   => $record->id,
                        'academic_class_assign_detail_id' => $detailId,
                        'full_mark'                        => $marks['full_mark']      ?? 100,
                        'pass_mark'                        => $marks['pass_mark']      ?? 33,
                        'written_mark'                     => $marks['written_mark']   ?? 0,
                        'mcq_mark'                          => $marks['mcq_mark']       ?? 0,
                        'practical_mark'                    => $marks['practical_mark'] ?? 0,
                        'serial'                            => $serial++,
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
            $createdCount = 0;
            $skipped      = [];

            $assigns = AcademicClassAssign::with(['details', 'class', 'section'])
                ->whereIn('id', $this->selectedClassAssignIds)
                ->get();

            foreach ($assigns as $assign) {
                if ($assign->details->isEmpty()) {
                    $label = ($assign->class->name ?? 'Unknown') . ($assign->section ? ' - ' . $assign->section->name : '');
                    $skipped[] = $label;
                    continue; // এই class এ কোনো subject assign করা নেই, তাই skip
                }

                $record = ExamSetup::create([
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
                        'exam_setup_id'                   => $record->id,
                        'academic_class_assign_detail_id' => $detail->id,
                        'full_mark'                        => $this->default_full_mark,
                        'pass_mark'                        => $this->default_pass_mark,
                        'written_mark'                     => $this->default_written_mark,
                        'mcq_mark'                          => $this->default_mcq_mark,
                        'practical_mark'                    => $this->default_practical_mark,
                        'serial'                            => $serial++,
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
        if (!empty($skipped)) {
            $message .= ' (Subject না থাকায় বাদ পড়েছে: ' . implode(', ', $skipped) . ')';
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

        $classAssigns = AcademicClassAssign::with(['class', 'section', 'details'])
            ->get()
            ->map(function ($assign) {
                $label = $assign->class->name ?? 'Unknown';
                if ($assign->section) {
                    $label .= ' - ' . $assign->section->name;
                }
                return [
                    'id'            => $assign->id,
                    'label'         => $label,
                    'subject_count' => $assign->details->count(),
                ];
            });

        $setups = ExamSetup::with(['term', 'type', 'classAssign.class', 'classAssign.section', 'details'])
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