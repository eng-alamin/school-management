<?php

namespace App\Livewire\Teacher\Student;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Student;
use App\Models\AcademicClass;
use App\Models\AcademicSection;
use App\Models\AcademicClassAssign;

class StudentListComponent extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    // Filter
    public $filterClass   = '';
    public $filterSection = '';
    public string $search = '';
    public int $perPage   = 10;
    public bool $hasFilter = false;

    // Delete
    public bool $confirmDelete = false;
    public ?int $deleteId      = null;

    public function updatingSearch(): void { $this->resetPage(); }

    public function getAvailableClasses()
    {
        return AcademicClass::whereIn('id', AcademicClassAssign::distinct()->pluck('class_id'))
            ->orderBy('name')
            ->get();
    }

    public function getAvailableSections()
    {
        if (!$this->filterClass) return [];

        return AcademicSection::whereIn('id',
            AcademicClassAssign::where('class_id', $this->filterClass)->pluck('section_id')
        )->orderBy('name')->get();
    }

    public function updatedFilterClass(): void
    {
        $this->filterSection = '';
        $this->hasFilter     = false;
        $this->resetPage();
    }

    public function updatedFilterSection(): void
    {
        $this->hasFilter = false;
        $this->resetPage();
    }

    public function filter(): void
    {
        if (!$this->filterClass) {
            $this->dispatch('toast', type: 'error', message: 'Please select a class.');
            return;
        }

        $this->validate([
            'filterClass'   => 'required|exists:academic_classes,id',
            'filterSection' => 'nullable',
        ]);

        $this->hasFilter = true;
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

    public function resetForm(): void
    {
        $this->filterClass   = '';
        $this->filterSection = '';
        $this->search        = '';
        $this->hasFilter     = false;
        $this->confirmDelete = false;
        $this->deleteId      = null;
        $this->resetPage();
        $this->resetValidation();
    }

    public function render()
    {
        $students = Student::with(['guardians', 'class', 'section'])
            ->when($this->hasFilter, function ($q) {
                $q->where('class_id', $this->filterClass);
                if ($this->filterSection && $this->filterSection !== 'all') {
                    $q->where('section_id', $this->filterSection);
                }
            })
            ->when($this->search, fn($q) => $q->where(fn($q) => $q
                ->where('name', 'like', "%{$this->search}%")
                ->orWhere('register_no', 'like', "%{$this->search}%")
                ->orWhere('roll_no', 'like', "%{$this->search}%")))
            ->latest()
            ->paginate($this->perPage);

        return view('livewire.teacher.student.student-list-component')
            ->with('students', $students)
            ->with('classes', $this->getAvailableClasses())
            ->with('sections', $this->getAvailableSections())
            ->layout('layouts.teacher.app', [
                'title' => 'Student List | ' . institution()->name,
            ]);
    }
}