<?php

namespace App\Livewire\Teacher\Academic;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\AcademicTeacherAssign;
use App\Models\AcademicClass;
use App\Models\AcademicSection;
use App\Models\AcademicClassAssign;
use App\Models\AcademicSubject;
use App\Models\Employee;

class ClassAssignComponent extends Component
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
    public array $subject_array = [];

    // Dependent dropdown
    public array $availableSections = [];

    public function getAvailableClasses()
    {
        return AcademicClass::whereIn('id', AcademicClassAssign::distinct()->pluck('class_id'))
            ->orderBy('name')
            ->get();
    }

    public function getAvailableSections()
    {
        if (!$this->class_id) return collect();

        return AcademicSection::whereIn('id',
            AcademicClassAssign::where('class_id', $this->class_id)->pluck('section_id')
        )->orderBy('name')->get();
    }

    public function getAvailableSubjects()
    {
        if (!$this->class_id) return collect();

        return AcademicClassAssign::where('class_id', $this->class_id)
            ->when(
                $this->section_id && $this->section_id !== 'all' && $this->section_id !== '',
                fn($q) => $q->where('section_id', $this->section_id)
            )
            ->get()
            ->flatMap(fn($row) => $row->subjects ?? [])
            ->unique()
            ->values();
    }

    public function updatedClassId($value): void
    {
        $this->section_id = '';
        $this->subject_array = [];
        $this->dispatch('showModalChanged', selected: []);
    }

    public function updatedSectionId($value): void
    {
        $this->subject_array = [];
        $this->dispatch('showModalChanged', selected: []);
    }

    protected function rules(): array
    {
        return [
            'class_id'        => 'required',
            'section_id'      => 'nullable',
            'subject_array'   => 'nullable|array',
            'subject_array.*' => 'nullable|string',
        ];
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
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
        $this->dispatch('showModalChanged', selected: $this->subject_array);
    }

    public function openEdit(int $id): void
    {
        $record = AcademicTeacherAssign::findOrFail($id);

        $this->editId        = $id;
        $this->class_id      = $record->class_id;
        $this->section_id    = $record->section_id;
        $this->subject_array = $record->subjects ?? [];

        // Load sections via belongsToMany
        $class = AcademicClass::with('sections')->find($record->class_id);
        $this->availableSections = $class
            ? $class->sections->map(fn($s) => ['id' => $s->id, 'name' => $s->name])->toArray()
            : [];

        $this->showModal = true;
        $this->dispatch('showModalChanged', selected: $this->subject_array);
    }

    public function save(): void
    {
        $this->validate();

        $teacherId = auth()->user()->employee->id;

        $sectionId = ($this->section_id && $this->section_id !== 'all')
            ? $this->section_id
            : null;

        // Duplicate check — নিজের record বাদ দিয়ে same combination আছে কিনা দেখো
        $duplicate = AcademicTeacherAssign::where('teacher_id', $teacherId)
            ->where('class_id', $this->class_id)
            ->where('section_id', $sectionId)
            ->when($this->editId, fn($q) => $q->where('id', '!=', $this->editId))
            ->exists();

        if ($duplicate) {
            $this->addError('class_id', 'This class & section combination is already assigned to you.');
            return;
        }

        $data = [
            'class_id'   => $this->class_id,
            'section_id' => $sectionId,
            'teacher_id' => $teacherId,
            'subjects'   => $this->subject_array,
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
        $this->reset(['class_id', 'section_id', 'editId', 'availableSections']);
        $this->resetValidation();
    }

    public function render()
    {
        $assigns = AcademicTeacherAssign::with('class', 'section')
            ->where('teacher_id', auth()->user()->employee->id)
            ->when($this->search, fn($q) => $q
                ->whereHas('teacher', fn($q) => $q->where('name', 'like', "%{$this->search}%"))
                ->orWhereHas('class', fn($q) => $q->where('name', 'like', "%{$this->search}%"))
                ->orWhereHas('section', fn($q) => $q->where('name', 'like', "%{$this->search}%")))
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.teacher.academic.class-assign-component')
            ->with('assigns', $assigns)
            ->with('classes', $this->getAvailableClasses())
            ->with('sections', $this->getAvailableSections())
            ->with('subjects', $this->getAvailableSubjects())
            ->layout('layouts.teacher.app', [
                'title' => 'Class Assignments | ' . institution()->name,
            ]);
    }
}