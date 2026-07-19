<?php

namespace App\Livewire\Admin\Academic;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\AcademicGroup;

class GroupComponent extends Component
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
    public bool $is_current = true;

    protected function rules(): array
    {
        return [
            'name'      => 'required|string|max:255|unique:academic_groups,name,' . $this->editId . ',id,institution_id,' . institution()->id,
            'is_current' => 'boolean',
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
        $record = AcademicGroup::where('institution_id', institution()->id)
            ->findOrFail($id);

        $this->editId    = $id;
        $this->name      = $record->name;
        $this->is_current = (bool) $record->is_current;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'name'      => $this->name,
            'is_current' => $this->is_current,
        ];

        if ($this->editId) {
            AcademicGroup::where('institution_id', institution()->id)
                ->findOrFail($this->editId)
                ->update($data);

            $savedId = $this->editId;
            $this->dispatch('toast', type: 'success', message: 'Data updated successfully!');
        } else {
            $data['institution_id'] = institution()->id;

            $record  = AcademicGroup::create($data);
            $savedId = $record->id;

            $this->dispatch('toast', type: 'success', message: 'Data created successfully!');
        }

        // current = true হলে একই institution-এর বাকি সব group inactive হয়ে যাবে
        if ($this->is_current) {
            AcademicGroup::where('institution_id', institution()->id)
                ->where('id', '!=', $savedId)
                ->where('is_current', true)
                ->update(['is_current' => false]);
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
        AcademicGroup::where('institution_id', institution()->id)
            ->findOrFail($this->deleteId)
            ->delete();

        $this->confirmDelete = false;
        $this->deleteId      = null;
        $this->dispatch('toast', type: 'success', message: 'Data deleted successfully!');
    }

    private function resetForm(): void
    {
        $this->reset(['name', 'editId']);
        $this->is_current = true;
        $this->resetValidation();
    }

    public function render()
    {
        $groups = AcademicGroup::query()
            ->where('institution_id', institution()->id)
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.admin.academic.group-component')
            ->with('groups', $groups)
            ->layout('layouts.admin.app', [
                'title' => 'Groups | ' . institution()->name,
            ]);
    }
}