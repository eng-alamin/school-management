<?php

namespace App\Livewire\Branch\Student;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Student;
use App\Models\AcademicClass;
use App\Models\AcademicClassAssign;

class StudentListComponent extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    public $search    = '';
    public $perPage   = 10;
    public $sortField = 'created_at';
    public $sortDir   = 'desc';

    public $filterClass   = '';
    public $filterSection = '';

    public array $availableSections = [];

    // Delete
    public bool $confirmDelete = false;
    public ?int $deleteId      = null;

    // Status Update
    public bool $showStatusModal = false;
    public ?int $statusId        = null;
    public string $newStatus     = '';

    protected array $statusOptions = ['active', 'inactive', 'graduated', 'transferred', 'dropped_out'];

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    // ── Class filter change → sections reload, page reset ──
    public function updatedFilterClass($value): void
    {
        $this->filterSection     = '';
        $this->availableSections = [];
        $this->resetPage();

        if (!$value) {
            return;
        }

        $assigns = AcademicClassAssign::with('section')
            ->where('class_id', $value)
            ->whereNotNull('section_id')
            ->get();

        $this->availableSections = $assigns
            ->filter(fn($a) => $a->section)
            ->map(fn($a) => ['id' => $a->section->id, 'name' => $a->section->name])
            ->unique('id')
            ->values()
            ->toArray();
    }

    // ── Section filter change → just reset page, table auto-refresh hobe ──
    public function updatedFilterSection(): void
    {
        $this->resetPage();
    }

    public function sortBy($field): void
    {
        if ($this->sortField === $field) {
            $this->sortDir = $this->sortDir === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDir   = 'asc';
        }

        $this->resetPage();
    }

    public function confirmDeleteRecord(int $id): void
    {
        $this->deleteId      = $id;
        $this->confirmDelete = true;
    }

    public function deleteRecord(): void
    {
        try {
            $student = Student::findOrFail($this->deleteId);
            $student->user()->delete();

            $this->confirmDelete = false;
            $this->deleteId      = null;

            $this->dispatch('toast', type: 'success', message: 'Student deleted successfully!');
        } catch (\Exception $e) {
            $this->dispatch('toast', type: 'error', message: 'Delete failed: ' . $e->getMessage());
        }
    }

    /**
     * Status icon click korle eta call hobe — modal open kore, current
     * status ta preselect kore rakhe.
     */
    public function openStatusModal(int $id): void
    {
        $student = Student::findOrFail($id);

        $this->statusId        = $student->id;
        $this->newStatus       = $student->status;
        $this->showStatusModal = true;
    }

    public function closeStatusModal(): void
    {
        $this->showStatusModal = false;
        $this->statusId        = null;
        $this->newStatus       = '';
    }

    public function updateStatus(): void
    {
        $this->validate([
            'statusId'  => 'required|integer|exists:students,id',
            'newStatus' => 'required|string|in:' . implode(',', $this->statusOptions),
        ]);

        $student   = Student::findOrFail($this->statusId);
        $oldStatus = $student->status;

        $student->update(['status' => $this->newStatus]);

        activity()
            ->performedOn($student)
            ->withProperties([
                'institution_id' => $student->institution_id,
                'icon' => 'toggle_on',
                'type' => 'student',
                'old_status' => $oldStatus,
                'new_status' => $this->newStatus,
            ])
            ->log('Student status changed: ' . $student->name . ' (' . $oldStatus . ' → ' . $this->newStatus . ')');

        $this->dispatch('toast', type: 'success', message: 'Status updated successfully!');

        $this->closeStatusModal();
    }

    public function render()
    {
        $classes = AcademicClass::whereIn('id', AcademicClassAssign::distinct()->pluck('class_id'))
            ->orderBy('name')
            ->get();

        $query = Student::with(['guardians', 'class', 'section'])
            ->when($this->filterClass, fn($q) =>
                $q->where('class_id', $this->filterClass)
            )
            ->when($this->filterSection && $this->filterSection !== 'all', fn($q) =>
                $q->where('section_id', $this->filterSection)
            )
            ->when($this->search, fn($q) => $q->where(fn($q) => $q
                ->where('name', 'like', "%{$this->search}%")
                ->orWhere('student_id', 'like', "%{$this->search}%")
                ->orWhere('registration_no', 'like', "%{$this->search}%")
                ->orWhere('roll_no', 'like', "%{$this->search}%")))
            ->orderBy($this->sortField, $this->sortDir);

        $students = $query->paginate($this->perPage);

        return view('livewire.branch.student.student-list-component')
            ->with('students', $students)
            ->with('classes', $classes)
            ->with('statusOptions', $this->statusOptions)
            ->layout('layouts.branch.app', [
                'title' => 'Student List | ' . institution()->name,
            ]);
    }
}