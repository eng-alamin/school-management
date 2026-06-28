<?php

namespace App\Livewire\Accountant\Salary;

use Livewire\Component;
use App\Models\SalaryTemplate;
use Livewire\WithPagination;

class ListTemplateComponent extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    // List
    public string $search        = '';
    public int    $perPage       = 10;
    public string $sortField     = 'id';
    public string $sortDirection = 'asc';

    // Delete
    public bool $confirmDelete = false;
    public ?int $deleteId      = null;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField     = $field;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
    }

    public function confirmDeleteRecord(int $id): void
    {
        $this->deleteId      = $id;
        $this->confirmDelete = true;
    }

    public function deleteRecord(): void
    {
        $record = SalaryTemplate::findOrFail($this->deleteId);
        $record->delete();

        $this->confirmDelete = false;
        $this->deleteId      = null;

        $this->dispatch('toast', type: 'success', message: 'Salary template deleted successfully!');
    }

    public function render()
    {
        $templates = SalaryTemplate::query()
            ->when($this->search, fn($q) => $q->where('salary_grade', 'like', "%{$this->search}%"))
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.accountant.salary.list-template-component')
            ->with('templates', $templates)
            ->layout('layouts.accountant.app', [
                'title' => 'Salary Templates | ' . institution()->name,
            ]);
    }
}