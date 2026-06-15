<?php

namespace App\Livewire\Admin\Academic;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\AcademicClassAssign;
use App\Models\AcademicClass;
use App\Models\AcademicSubject;

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

    protected function rules(): array
    {
        return [
            'class_id'        => 'required|exists:academic_classes,id',
            'section_id'      => 'nullable|exists:academic_sections,id',
            'subject_array'   => 'nullable|array',
            'subject_array.*' => 'nullable|string',
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
        $this->dispatch('showModalChanged', selected: $this->subject_array);
    }

    public function openEdit(int $id): void
    {
        $record = AcademicClassAssign::findOrFail($id);

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

        $data = [
            'class_id'   => $this->class_id,
            'section_id' => $this->section_id,
            'subjects'   => $this->subject_array,
        ];

        if ($this->editId) {
            AcademicClassAssign::findOrFail($this->editId)->update($data);
            $this->dispatch('toast', type: 'success', message: 'Assignment updated successfully!');
        } else {
            AcademicClassAssign::create($data);
            $this->dispatch('toast', type: 'success', message: 'Class assigned successfully!');
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
        AcademicClassAssign::findOrFail($this->deleteId)->delete();
        $this->confirmDelete = false;
        $this->deleteId      = null;
        $this->dispatch('toast', type: 'success', message: 'Assignment deleted successfully!');
    }

    private function resetForm(): void
    {
        $this->reset(['class_id', 'section_id', 'subject_array', 'editId', 'availableSections']);
        $this->resetValidation();
    }

    public function render()
    {
        $assigns = AcademicClassAssign::with('class', 'section')
            ->when($this->search, fn($q) => $q
                ->whereHas('class', fn($q) => $q->where('name', 'like', "%{$this->search}%"))
                ->orWhereHas('section', fn($q) => $q->where('name', 'like', "%{$this->search}%")))
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        $classes  = AcademicClass::orderBy('id')->get();
        $subjects = AcademicSubject::orderBy('name')->pluck('name', 'id');

        return view('livewire.admin.academic.class-assign-component')
            ->with('assigns', $assigns)
            ->with('classes', $classes)
            ->with('subjects', $subjects)
            ->layout('layouts.admin.app', [
                'title' => "Class Assignments | School SaaS",
            ]);
    }
}