<?php

namespace App\Livewire\Accountant\Student;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Student;
use App\Models\User;
use App\Models\AcademicClass;

class StudentListComponent extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    // Filter
    public string $search           = '';
    public string $filter_class_id  = '';
    public string $filter_section_id = '';
    public int $perPage             = 10;
    public bool $hasFilter          = false;

    // Dependent dropdown
    public array $availableSections = [];

    // Delete
    public bool $confirmDelete = false;
    public ?int $deleteId      = null;

    public function updatingSearch(): void           { $this->resetPage(); }
    public function updatingFilterClassId(): void    { $this->resetPage(); }

    public function updatedFilterClassId(string $value): void
    {
        $this->filter_section_id = '';
        $this->availableSections = [];
        $this->hasFilter         = false;
        $this->resetPage();

        if ($value) {
            $class = AcademicClass::with('sections')->find($value);
            if ($class) {
                $this->availableSections = $class->sections
                    ->map(fn($s) => ['id' => $s->id, 'name' => $s->name])
                    ->toArray();
            }
        }
    }

    public function filter(): void
    {
        // BUG FIX 1: correct field names filter_class_id / filter_section_id
        $this->validate([
            'filter_class_id'   => 'required|exists:academic_classes,id',
            'filter_section_id' => 'required|exists:academic_sections,id',
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
            $student->user()->delete(); // cascading হলে এটাই যথেষ্ট
            // অথবা: User::findOrFail($student->user_id)->delete();
            $this->confirmDelete = false;
            $this->deleteId      = null;
            $this->dispatch('toast', type: 'success', message: 'Student deleted successfully!');
        } catch (\Exception $e) {
            $this->dispatch('toast', type: 'error', message: 'Delete failed: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $students = Student::with(['guardians', 'class', 'section'])
            ->when($this->hasFilter, function ($q) {
                $q->where('class_id', $this->filter_class_id)
                  ->where('section_id', $this->filter_section_id);
            })
            ->when($this->search, fn($q) => $q->where(fn($q) => $q
                ->where('name', 'like', "%{$this->search}%")
                ->orWhere('register_no', 'like', "%{$this->search}%")
                ->orWhere('roll_no', 'like', "%{$this->search}%")))
            ->latest()
            ->paginate($this->perPage);

        $classes = AcademicClass::orderBy('id')->get();

        return view('livewire.accountant.student.student-list-component')
            ->with('students', $students)
            ->with('classes', $classes)
            ->layout('layouts.accountant.app', [
                'title' => "Student List | School SaaS",
            ]);
    }
}