<?php

namespace App\Livewire\Admin\Academic;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\AcademicTeacherAssign;
use App\Models\AcademicClass;
use App\Models\Employee;
use Illuminate\Validation\Rule;

class TeacherAssignComponent extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    // List
    public string $search = '';
    public int $perPage = 10;
    public string $sortField = 'id';
    public string $sortDirection = 'asc';

    // Modal
    public bool $showModal = false;
    public bool $confirmDelete = false;
    public ?int $deleteId = null;

    // Form
    public ?int $editId = null;
    public string $class_id = '';
    public $section_id;
    public string $teacher_id = '';

    // Dependent dropdown
    public array $availableSections = [];

    protected function rules(): array
    {
        return [
            'class_id'        => 'required|exists:academic_classes,id',
            'section_id'      => 'nullable|exists:academic_sections,id',

            'teacher_id' => [
                'required',
                'exists:employees,id',

                Rule::unique('academic_teacher_assigns')
                    ->where(function ($query) {
                        return $query
                            ->where('class_id', $this->class_id)
                            ->where('section_id', $this->section_id);
                    })
                    ->ignore($this->editId),
            ],
        ];
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatedClassId(string $value): void
    {
        $this->section_id;
        $this->availableSections = [];

        if ($value) {
            $class = AcademicClass::with('sections')->find($value);
            if ($class) {
                $this->availableSections = $class->sections
                    ->map(fn($s) => ['id' => $s->id, 'name' => $s->name])
                    ->toArray();
            }
        }
    }

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

    public function openCreate(): void
    {
        $this->resetForm();
        $this->editId = null;
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $record = AcademicTeacherAssign::findOrFail($id);

        $this->editId        = $id;
        $this->class_id      = $record->class_id;
        $this->section_id    = $record->section_id;
        $this->teacher_id    = $record->teacher_id;

        // Load sections via belongsToMany
        $class = AcademicClass::with('sections')->find($record->class_id);
        $this->availableSections = $class
            ? $class->sections->map(fn($s) => ['id' => $s->id, 'name' => $s->name])->toArray()
            : [];

        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'class_id'   => $this->class_id,
            'section_id' => $this->section_id,
            'teacher_id' => $this->teacher_id,
        ];

        if ($this->editId) {
            AcademicTeacherAssign::findOrFail($this->editId)->update($data);
            $this->dispatch('toast', type: 'success', message: 'Data updated successfully!');
        } else {
            AcademicTeacherAssign::create($data);
            $this->dispatch('toast', type: 'success', message: 'Data created successfully!');
        }

        $this->showModal = false;
        $this->resetForm();
    }

    public function confirmDeleteRecord(int $id): void
    {
        $this->deleteId      = $id;
        $this->confirmDelete = true;
    }

    public function deleteRecord(): void
    {
        AcademicTeacherAssign::findOrFail($this->deleteId)->delete();
        $this->confirmDelete = false;
        $this->deleteId      = null;
        $this->dispatch('toast', type: 'success', message: 'Data deleted successfully!');
    }

    private function resetForm(): void
    {
        $this->reset(['class_id', 'section_id', 'teacher_id', 'editId', 'availableSections']);
        $this->resetValidation();
    }

    public function render()
    {
        $assigns = AcademicTeacherAssign::with('class', 'section')
            ->when($this->search, fn($q) => $q
                ->whereHas('teacher', fn($q) => $q->where('name', 'like', "%{$this->search}%"))
                ->orWhereHas('class', fn($q) => $q->where('name', 'like', "%{$this->search}%"))
                ->orWhereHas('section', fn($q) => $q->where('name', 'like', "%{$this->search}%")))
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        $classes  = AcademicClass::orderBy('id')->get();
        $teachers = Employee::with(['designation', 'department', 'user'])
            ->whereHas('user', function ($q) {
                $q->where('role', 'teacher');
            })
            ->orderBy('id', 'asc')
            ->get();

        return view('livewire.admin.academic.teacher-assign-component')
            ->with('assigns', $assigns)
            ->with('classes', $classes)
            ->with('teachers', $teachers)
            ->layout('layouts.admin.app', [
                'title' => 'Class Assignments | ' . institution()->name,
            ]);
    }
}