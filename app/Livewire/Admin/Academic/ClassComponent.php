<?php

namespace App\Livewire\Admin\Academic;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\AcademicClass;
use App\Models\AcademicSection;
use App\Models\AcademicClassSection;

class ClassComponent extends Component
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
    public string $name = '';
    public $numeric;
    public array $sectionIds = [];

    protected function rules(): array
    {
        return [
            'name'       => 'required|string|max:255',
            'numeric'    => 'nullable|integer',
            'sectionIds'      => 'nullable|array',
            'sectionIds.*'    => 'exists:academic_sections,id',
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
        $this->dispatch('showModalChanged', selected: $this->sectionIds);
    }

    public function openEdit(int $id): void
    {
        $record = AcademicClass::findOrFail($id);
        $this->editId      = $id;
        $this->name        = $record->name;
        $this->numeric     = $record->numeric;
        $this->sectionIds = $record->sections->pluck('id')->toArray();
        $this->showModal   = true;
        $this->dispatch('showModalChanged', selected: $this->sectionIds);
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'name'       => $this->name,
            'numeric'    => $this->numeric,
        ];

        if ($this->editId) {

            $class = AcademicClass::findOrFail($this->editId);
            $class->update($data);

            // old delete
            AcademicClassSection::where('class_id', $class->id)->delete();

            $message = 'Class updated successfully!';

        } else {

            $class = AcademicClass::create($data);

            $message = 'Class created successfully!';
        }

        // insert sections
        foreach ($this->sectionIds as $sectionId) {

            AcademicClassSection::create([
                'class_id'   => $class->id,
                'section_id' => $sectionId,
            ]);
        }

        $this->dispatch('toast', type: 'success', message: $message);

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
        AcademicClass::findOrFail($this->deleteId)->delete();
        $this->confirmDelete = false;
        $this->deleteId      = null;
        $this->dispatch('toast', type: 'success', message: 'Class deleted successfully!');
    }

    private function resetForm(): void
    {
        $this->reset(['name', 'numeric', 'sectionIds', 'editId']);
        $this->resetValidation();
    }

    public function render()
    {
        $classes = AcademicClass::with('sections')
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%")
                                              ->orWhere('numeric', 'like', "%{$this->search}%")
                                              ->orWhereHas('sections', fn($q) => $q->where('name', 'like', "%{$this->search}%")))
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        $sections = AcademicSection::orderBy('name')->get();

        return view('livewire.admin.academic.class-component')
            ->with('classes', $classes)
            ->with('sections', $sections)
            ->layout('layouts.admin.app', [
                'title' => 'Classes | ' . institution()->name,
            ]);
    }
}