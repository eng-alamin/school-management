<?php

namespace App\Livewire\Admin\Exam;

use Livewire\Component;
use App\Models\ExamTerm;
use App\Models\ExamType;
use App\Models\ExamSetup;
use App\Models\ExamSetupDetail;
use App\Models\AcademicClass;
use App\Models\AcademicClassAssign;
use App\Models\AcademicClassAssignDetail;
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

    // Form
    public ?int   $editId       = null;
    public string $name         = '';
    public ?int   $exam_term_id = null;
    public ?int   $exam_type_id = null;
    public string $remarks      = '';
    public bool   $is_published = false;

    // subjects grouped by class
    // groupedSubjects[class_name][detail_id] = ['subject_name'=>..., 'full_mark'=>..., ...]
    public array $groupedSubjects = [];

    protected function rules(): array
    {
        return [
            'name'         => 'required|string|max:255',
            'exam_term_id' => 'nullable|exists:exam_terms,id',
            'exam_type_id' => 'nullable|exists:exam_types,id',
            'remarks'      => 'nullable|string',
            'is_published' => 'boolean',
        ];
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    // ── সব class এর subjects grouped করে load করো ────────────────────────
    private function loadAllSubjects(): void
    {
        $existingDetails = [];
        if ($this->editId) {
            $existingDetails = ExamSetupDetail::where('exam_setup_id', $this->editId)
                ->get()
                ->keyBy('academic_class_assign_detail_id')
                ->toArray();
        }

        $this->groupedSubjects = [];

        // সব AcademicClassAssign + details + subject + class + section eager load
        $assigns = AcademicClassAssign::with([
            'class',
            'section',
            'details.subject',
        ])->get();

        foreach ($assigns as $assign) {
            if ($assign->details->isEmpty()) {
                continue;
            }

            // Group key: Class Name (Section Name যদি থাকে)
            $groupKey = $assign->class->name ?? 'Unknown';
            if ($assign->section) {
                $groupKey .= ' - ' . $assign->section->name;
            }

            foreach ($assign->details as $detail) {
                $existing = $existingDetails[$detail->id] ?? null;

                $this->groupedSubjects[$groupKey][$detail->id] = [
                    'subject_name'   => $detail->subject->name ?? '—',
                    'full_mark'      => $existing ? (float) $existing['full_mark']      : 100,
                    'pass_mark'      => $existing ? (float) $existing['pass_mark']      : 33,
                    'written_mark'   => $existing ? (float) $existing['written_mark']   : 0,
                    'mcq_mark'       => $existing ? (float) $existing['mcq_mark']       : 0,
                    'practical_mark' => $existing ? (float) $existing['practical_mark'] : 0,
                ];
            }
        }
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
        $this->loadAllSubjects();
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $record = ExamSetup::findOrFail($id);

        $this->editId       = $id;
        $this->name         = $record->name;
        $this->exam_term_id = $record->exam_term_id;
        $this->exam_type_id = $record->exam_type_id;
        $this->remarks      = $record->remarks ?? '';
        $this->is_published = $record->is_published;

        $this->loadAllSubjects();
        $this->showModal = true;
    }

    public function openView(int $id): void
    {
        $this->viewRecord    = ExamSetup::with([
            'term', 'type',
            'details.classAssignDetail.subject',
            'details.classAssignDetail.classAssign.class',
            'details.classAssignDetail.classAssign.section',
        ])->findOrFail($id);
        $this->showViewModal = true;
    }

    public function save(): void
    {
        $this->validate();

        $currentSession = \App\Models\AcademicSession::where('is_current', true)->first();

        $data = [
            'name'                => $this->name,
            'academic_session_id' => $currentSession?->id,
            'exam_term_id'        => $this->exam_term_id,
            'exam_type_id'        => $this->exam_type_id,
            'remarks'             => $this->remarks,
            'is_published'        => $this->is_published,
        ];

        if ($this->editId) {
            $record = ExamSetup::findOrFail($this->editId);
            $record->update($data);

            ExamSetupDetail::where('exam_setup_id', $record->id)->delete();

            activity()
                ->causedBy(auth()->user())
                ->performedOn($record)
                ->withProperties(['icon' => 'assignment', 'type' => 'exam_setup'])
                ->tap(fn($a) => $a->institution_id = $record->institution_id)
                ->log('Exam setup updated: ' . $record->name);

            $this->dispatch('toast', type: 'success', message: 'Data updated successfully!');
        } else {
            $record = ExamSetup::create($data);

            activity()
                ->causedBy(auth()->user())
                ->performedOn($record)
                ->withProperties(['icon' => 'assignment', 'type' => 'exam_setup'])
                ->tap(fn($a) => $a->institution_id = $record->institution_id)
                ->log('Exam setup created: ' . $record->name);

            $this->dispatch('toast', type: 'success', message: 'Data created successfully!');
        }

        // সব grouped subjects insert করো
        $serial = 1;
        foreach ($this->groupedSubjects as $groupKey => $subjects) {
            foreach ($subjects as $detailId => $marks) {
                ExamSetupDetail::create([
                    'exam_setup_id'                   => $record->id,
                    'academic_class_assign_detail_id' => $detailId,
                    'full_mark'                       => $marks['full_mark']      ?? 100,
                    'pass_mark'                       => $marks['pass_mark']      ?? 33,
                    'written_mark'                    => $marks['written_mark']   ?? 0,
                    'mcq_mark'                        => $marks['mcq_mark']       ?? 0,
                    'practical_mark'                  => $marks['practical_mark'] ?? 0,
                    'serial'                          => $serial++,
                ]);
            }
        }

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
        $record = ExamSetup::findOrFail($this->deleteId);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($record)
            ->withProperties(['icon' => 'assignment', 'type' => 'exam_setup'])
            ->tap(fn($a) => $a->institution_id = $record->institution_id)
            ->log('Exam setup deleted: ' . $record->name);

        ExamSetupDetail::where('exam_setup_id', $record->id)->delete();
        $record->delete();

        $this->confirmDelete = false;
        $this->deleteId      = null;
        $this->dispatch('toast', type: 'success', message: 'Data deleted successfully!');
    }

    private function resetForm(): void
    {
        $this->reset([
            'editId', 'name', 'exam_term_id', 'exam_type_id',
            'remarks', 'is_published', 'groupedSubjects',
        ]);
        $this->resetValidation();
    }

    public function render()
    {
        $terms   = ExamTerm::pluck('name', 'id');
        $types   = ExamType::pluck('name', 'id');

        $setups = ExamSetup::with(['term', 'type', 'details'])
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.admin.exam.exam-setup-component')
            ->with('terms', $terms)
            ->with('types', $types)
            ->with('setups', $setups)
            ->layout('layouts.admin.app', [
                'title' => 'Exam Setup | ' . institution()->name,
            ]);
    }
}