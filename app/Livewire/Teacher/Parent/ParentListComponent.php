<?php

namespace App\Livewire\Teacher\Parent;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Guardian;

class ParentListComponent extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    // Filter
    public string $search  = '';
    public int    $perPage = 10;

    // Delete
    public bool  $confirmDelete = false;
    public ?int  $deleteId      = null;

    public function confirmDeleteRecord(int $id): void
    {
        $this->deleteId      = $id;
        $this->confirmDelete = true;
    }

    public function deleteRecord(): void
    {
        try {
            Guardian::findOrFail($this->deleteId)->delete();
            $this->confirmDelete = false;
            $this->deleteId      = null;
            $this->dispatch('toast', type: 'success', message: 'Parent deleted successfully!');
        } catch (\Exception $e) {
            $this->dispatch('toast', type: 'error', message: 'Delete failed: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $parents = Guardian::with('students')
            ->when($this->search, fn($q) => $q->where(fn($q) => $q
                ->where('name', 'like', "%{$this->search}%")
                ->orWhere('email', 'like', "%{$this->search}%")
                ->orWhere('mobile', 'like', "%{$this->search}%")
                ->orWhere('occupation', 'like', "%{$this->search}%")
            ))
            ->latest()
            ->paginate($this->perPage);

        return view('livewire.teacher.parent.parent-list-component')
            ->with('parents', $parents)
            ->layout('layouts.teacher.app', [
                'title' => 'Parent List | ' . institution()->name,
            ]);
    }
}