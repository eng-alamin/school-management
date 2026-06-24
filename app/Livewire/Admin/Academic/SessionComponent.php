<?php

namespace App\Livewire\Admin\Academic;

use Livewire\Component;
use App\Models\AcademicSession;
use Livewire\WithPagination;

class SessionComponent extends Component
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
    public string $start_date = '';
    public string $end_date = '';
    public bool $is_current = true;

    public function mount()
    {
        if (! AcademicSession::exists()) {
            AcademicSession::create([
                'name'       => now()->year,
                'start_date' => now()->startOfYear()->toDateString(),
                'end_date'   => now()->endOfYear()->toDateString(),
                'is_current' => true,
            ]);
        }
    }

    protected function rules(): array
    {
        return [
            'name'       => 'required|string|max:255',
            'start_date' => 'nullable|date',
            'end_date'   => 'nullable|date|after_or_equal:start_date',
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
        $record = AcademicSession::findOrFail($id);
        $this->editId      = $id;
        $this->name        = $record->name;
        $this->start_date  = $record->start_date ?? '';
        $this->end_date    = $record->end_date ?? '';
        $this->is_current  = (bool) $record->is_current;
        $this->showModal   = true;
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'name'       => $this->name,
            'start_date' => $this->start_date ?: null,
            'end_date'   => $this->end_date ?: null,
            'is_current' => $this->is_current,
        ];

        if ($this->editId) {
            AcademicSession::findOrFail($this->editId)->update($data);
            $savedId = $this->editId;
            $this->dispatch('toast', type: 'success', message: 'Data updated successfully!');
        } else {
            $record = AcademicSession::create($data);
            $savedId = $record->id;
            $this->dispatch('toast', type: 'success', message: 'Data created successfully!');
        }

        // current = true হলে বাকি সব inactive
        if ($this->is_current) {
            AcademicSession::where('id', '!=', $savedId)
                ->where('is_current', true)
                ->update(['is_current' => false]);
        }

        $this->showModal = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->reset(['name', 'start_date', 'end_date', 'editId']);
        $this->is_current = true;
        $this->resetValidation();
    }

    public function render()
    {
        $sessions = AcademicSession::query()
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.admin.academic.session-component')
            ->with('sessions', $sessions)
            ->layout('layouts.admin.app', [
                'title' => 'Academic Session | ' . institution()->name,
            ]);
    }

    public function confirmDeleteRecord(int $id): void
    {
        $this->deleteId = $id;
        $this->confirmDelete = true;
    }

    public function deleteRecord(): void
    {
        AcademicSession::findOrFail($this->deleteId)->delete();
        $this->confirmDelete = false;
        $this->deleteId = null;
        $this->dispatch('toast', type: 'success', message: 'Data deleted successfully!');
    }
}