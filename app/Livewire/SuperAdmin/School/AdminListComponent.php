<?php

namespace App\Livewire\SuperAdmin\School;

use App\Models\School;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Storage;

class AdminListComponent extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    // List
    public string $search       = '';
    public string $filterStatus = '';
    public int    $perPage      = 10;

    // Modal
    public bool    $showViewModal = false;
    public bool    $confirmDelete = false;
    public ?int    $deleteId      = null;
    public ?School $viewRecord    = null;

    public function updatingSearch(): void       { $this->resetPage(); }
    public function updatingFilterStatus(): void { $this->resetPage(); }

    public function openView(int $id): void
    {
        $this->viewRecord    = School::findOrFail($id);
        $this->showViewModal = true;
    }

    public function confirmDeleteRecord(int $id): void
    {
        $this->deleteId      = $id;
        $this->confirmDelete = true;
    }

    public function deleteRecord(): void
    {
        $record = School::findOrFail($this->deleteId);

        // ── Activity Log ───────────────────────────────────────────
        activity()
            ->causedBy(auth()->user())
            ->performedOn($record)
            ->withProperties(['icon' => 'school', 'type' => 'school'])
            ->log('School deleted: ' . $record->name);

        if ($record->logo) {
            Storage::disk('public')->delete($record->logo);
        }

        $record->delete();
        $this->confirmDelete = false;
        $this->deleteId      = null;
        $this->dispatch('toast', type: 'success', message: 'Data deleted successfully!');
    }

    public function toggleStatus(int $id): void
    {
        $record    = School::findOrFail($id);
        $newStatus = $record->is_active ? 0 : 1;

        $record->update(['is_active' => $newStatus]);

        // ── Activity Log ───────────────────────────────────────────
        activity()
            ->causedBy(auth()->user())
            ->performedOn($record)
            ->withProperties(['icon' => 'school', 'type' => 'school'])
            ->log('School status changed to ' . ($newStatus ? 'Active' : 'Inactive') . ': ' . $record->name);

        $this->dispatch('toast', type: 'success', message: 'Data updated successfully!');
    }

    public function render()
    {
        $schools = School::query()
            ->when($this->search, fn ($q) =>
                $q->where(fn ($q2) =>
                    $q2->where('name', 'like', "%{$this->search}%")
                       ->orWhere('email', 'like', "%{$this->search}%")
                       ->orWhere('phone', 'like', "%{$this->search}%")
                )
            )
            ->when($this->filterStatus !== '', fn ($q) => $q->where('is_active', $this->filterStatus))
            ->latest()
            ->paginate($this->perPage);

        return view('livewire.super-admin.school.admin-list-component')
            ->with('schools', $schools)
            ->layout('layouts.superadmin.app', [
                'title' => "Schools | School SaaS",
            ]);
    }
}