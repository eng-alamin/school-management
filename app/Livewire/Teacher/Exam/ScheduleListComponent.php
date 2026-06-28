<?php

namespace App\Livewire\Teacher\Exam;

use Livewire\Component;
use App\Models\ExamSchedule;
use Livewire\WithPagination;

class ScheduleListComponent extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    // List
    public string $search = '';
    public int $perPage = 10;
    public string $sortField = 'id';
    public string $sortDirection = 'asc';

    // Modal
    public bool $showViewModal = false;
    public bool $confirmDelete = false;
    public ?int $deleteId = null;
    public ?ExamSchedule $viewRecord = null;

    public function updatingSearch(): void { $this->resetPage(); }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
    }

    public function openView(int $id): void
    {
        $this->viewRecord = ExamSchedule::findOrFail($id);
        $this->showViewModal = true;
    }

    public function render()
    {
        $schedules = ExamSchedule::query()
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.teacher.exam.schedule-list-component')
            ->with('schedules', $schedules)
            ->layout('layouts.teacher.app', [
                'title' => 'Exam Schedule | ' . institution()->name,
            ]);

    }

    public function confirmDeleteRecord(int $id): void
    {
        $this->deleteId = $id;
        $this->confirmDelete = true;
    }

    public function deleteRecord(): void
    {
        $record = ExamSchedule::findOrFail($this->deleteId);
        $record->delete();
        $this->confirmDelete = false;
        $this->deleteId = null;
        session()->flash('success', 'Data deleted successfully!');
    }
}
