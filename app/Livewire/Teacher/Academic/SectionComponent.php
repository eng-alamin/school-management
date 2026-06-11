<?php

namespace App\Livewire\Tenant\Teacher\Academic;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\AcademicSection;

class SectionComponent extends Component
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
    public string $capacity = '';

    protected function rules(): array
    {
        return [
            'name'     => 'required|string|max:255',
            'capacity' => 'required|integer|min:1',
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
    }

    public function openEdit(int $id): void
    {
        $record = AcademicSection::findOrFail($id);
        $this->editId    = $id;
        $this->name      = $record->name;
        $this->capacity  = (string) $record->capacity;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'name'     => $this->name,
            'capacity' => $this->capacity,
        ];

        if ($this->editId) {
            AcademicSection::findOrFail($this->editId)->update($data);
            $this->dispatch('toast', type: 'success', message: 'Data updated successfully!');
        } else {
            AcademicSection::create($data);
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
        AcademicSection::findOrFail($this->deleteId)->delete();
        $this->confirmDelete = false;
        $this->deleteId      = null;
        $this->dispatch('toast', type: 'success', message: 'Data deleted successfully!');
    }

    private function resetForm(): void
    {
        $this->reset(['name', 'capacity', 'editId']);
        $this->resetValidation();
    }

    public function render()
    {
        $sections = AcademicSection::query()
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.tenant.teacher.academic.section-component')
            ->with('sections', $sections)
            ->layout('layouts.teacher.app', [
                'title' => "Sections | School SaaS",
            ]);
    }
}