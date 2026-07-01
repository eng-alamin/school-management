<?php

namespace App\Livewire\Admin\Exam;

use Livewire\Component;
use App\Models\ExamSetup;
use App\Models\ExamSchedule;
use Livewire\WithPagination;

class ScheduleListComponent extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    // List
    public string $search        = '';
    public int    $perPage       = 10;
    public string $sortField     = 'name';
    public string $sortDirection = 'asc';

    // Modal
    public bool       $showViewModal = false;
    public bool       $confirmDelete = false;
    public ?int        $deleteId      = null;
    public ?ExamSetup $viewRecord    = null;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField     = $field;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
    }

    public function openView(int $examSetupId): void
    {
        $this->viewRecord = ExamSetup::with([
            'classAssign.class',
            'classAssign.section',
            'details.classAssignDetail.subject',   // এখানে path সঠিক আছে (details, examSetupDetail না)
        ])->findOrFail($examSetupId);

        $this->viewRecord->setRelation(
            'schedules',
            $this->viewRecord->schedules->sortBy(['exam_date', 'start_time'])
        );

        $this->showViewModal = true;
    }

    public function confirmDeleteRecord(int $examSetupId): void
    {
        $this->deleteId      = $examSetupId;
        $this->confirmDelete = true;
    }

    // ── পুরো Exam এর সব subject-schedule একসাথে মুছে ফেলা হবে ──
    public function deleteRecord(): void
    {
        ExamSchedule::where('exam_setup_id', $this->deleteId)->delete();

        $this->confirmDelete = false;
        $this->deleteId      = null;
        $this->dispatch('toast', type: 'success', message: 'Schedule deleted successfully!');
    }

    public function render()
    {
        // শুধু সেই Exam Setup গুলো দেখাবো যাদের অন্তত একটা schedule আছে
        $setups = ExamSetup::with(['classAssign.class', 'classAssign.section'])
            ->withCount([
                'schedules as total_subjects',
                'schedules as published_count' => fn($q) => $q->where('is_published', true),
            ])
            ->whereHas('schedules')
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.admin.exam.schedule-list-component')
            ->with('schedules', $setups)
            ->layout('layouts.admin.app', [
                'title' => 'Exam Schedule | ' . institution()->name,
            ]);
    }
}