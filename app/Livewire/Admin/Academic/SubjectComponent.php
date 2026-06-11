<?php

namespace App\Livewire\Admin\Academic;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\AcademicSubject;

class SubjectComponent extends Component
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
    public string $code = '';
    public string $author = '';
    public string $type = '';

    protected function rules(): array
    {
        return [
            'name'   => 'required|string|max:255',
            'code'   => 'nullable|string|max:50',
            'author' => 'nullable|string|max:255',
            'type'   => 'nullable|string|max:100',
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
        $record = AcademicSubject::findOrFail($id);
        $this->editId    = $id;
        $this->name      = $record->name;
        $this->code      = $record->code   ?? '';
        $this->author    = $record->author ?? '';
        $this->type      = $record->type   ?? '';
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'name'   => $this->name,
            'code'   => $this->code,
            'author' => $this->author,
            'type'   => $this->type,
        ];

        if ($this->editId) {
            AcademicSubject::findOrFail($this->editId)->update($data);
            $this->dispatch('toast', type: 'success', message: 'Data updated successfully!');
        } else {
            AcademicSubject::create($data);
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
        AcademicSubject::findOrFail($this->deleteId)->delete();
        $this->confirmDelete = false;
        $this->deleteId      = null;
        $this->dispatch('toast', type: 'success', message: 'Data deleted successfully!');
    }

    private function resetForm(): void
    {
        $this->reset(['name', 'code', 'author', 'type', 'editId']);
        $this->resetValidation();
    }

    public function render()
    {
        $subjects = AcademicSubject::query()
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%")
                                              ->orWhere('code', 'like', "%{$this->search}%")
                                              ->orWhere('author', 'like', "%{$this->search}%"))
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.admin.academic.subject-component')
            ->with('subjects', $subjects)
            ->layout('layouts.admin.app', [
                'title' => "Subjects | School SaaS",
            ]);
    }
}