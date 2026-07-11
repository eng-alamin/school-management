<?php

namespace App\Livewire\Student;

use App\Models\AcademicClassAssign;
use App\Models\ExamSchedule;
use Livewire\Component;
use Livewire\WithPagination;

class ExamComponent extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    /**
     * exam_schedules table-e exam_id/class_id/section_id kono column nei —
     * shegulo exam_setups -> academic_class_assigns relation diye ashe.
     * Tai sorting shudhu exam_schedules-er nijer real column-er upor
     * whitelist kora hocche.
     */
    private const SORTABLE_FIELDS = ['exam_date', 'start_time', 'class_room'];

    public string $search        = '';
    public int    $perPage       = 10;
    public string $sortField     = 'exam_date';
    public string $sortDirection = 'asc';

    public bool $showViewModal  = false;
    public ?ExamSchedule $viewRecord = null;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if (! in_array($field, self::SORTABLE_FIELDS, true)) {
            return;
        }

        $this->sortDirection = ($this->sortField === $field && $this->sortDirection === 'asc')
            ? 'desc' : 'asc';
        $this->sortField = $field;
        $this->resetPage();
    }

    public function openView(int $id): void
    {
        $this->viewRecord = ExamSchedule::with([
            'examSetup.term',
            'examSetup.type',
            'examSetup.classAssign.class',
            'examSetup.classAssign.section',
            'examSetupDetail.classAssignDetail.subject',
            'examSetupDetail.classAssignDetail.teacher',
        ])->findOrFail($id);

        $this->showViewModal = true;
    }

    public function render()
    {
        $student = auth()->user()->student;
        $search  = $this->search;

        // Student-er nijer class + section-er AcademicClassAssign row
        // ber kora hocche — exam_setups etar shathe link kora thake.
        $assign = null;

        if ($student?->class_id) {
            $assign = AcademicClassAssign::where('class_id', $student->class_id)
                ->where('section_id', $student->section_id)
                ->first();
        }

        $schedules = ExamSchedule::with([
                'examSetup.classAssign.class',
                'examSetup.classAssign.section',
                'examSetupDetail.classAssignDetail.subject',
                'examSetupDetail.classAssignDetail.teacher',
            ])
            ->when(
                $assign,
                fn ($q) => $q->whereHas('examSetup', fn ($setup) => $setup->where('academic_class_assign_id', $assign->id)),
                // Student-er kono class assign na thakle kono result e jeno na
                // ashe, tai forcibly empty result set banano hocche.
                fn ($q) => $q->whereRaw('1 = 0')
            )
            ->when($search, function ($q) use ($search) {
                // Grouped where() — jate ei OR condition class scope-er
                // baire leak na kore (ExamComponent-er age-er search bug-er
                // moto).
                $q->where(function ($sub) use ($search) {
                    $sub->whereHas('examSetup', fn ($e) => $e->where('name', 'like', "%{$search}%"))
                        ->orWhereHas(
                            'examSetupDetail.classAssignDetail.subject',
                            fn ($e) => $e->where('name', 'like', "%{$search}%")
                        );
                });
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.student.exam-component')
            ->with('schedules', $schedules)
            ->layout('layouts.student.app', [
                'title' => 'Exam Schedule | Monarchy School',
            ]);
    }
}