<?php

namespace App\Livewire\Student\Notice;

use Livewire\Component;
use App\Models\Notice;
use Livewire\WithPagination;

class NoticeComponent extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    // List
    public string $search = '';
    public string $filterPriority = '';
    public string $filterStatus = '';
    public int $perPage = 10;

    // View Modal
    public bool $showViewModal = false;
    public ?Notice $viewRecord = null;

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingFilterPriority(): void { $this->resetPage(); }
    public function updatingFilterStatus(): void { $this->resetPage(); }

    public function openView(int $id): void
    {
        $this->viewRecord = Notice::with('creator')->findOrFail($id);
        $this->showViewModal = true;
    }

    public function render()
    {
        $notices = Notice::with('creator')
            ->when($this->search, fn ($q) =>
                $q->where(fn ($q2) =>
                    $q2->where('title', 'like', "%{$this->search}%")
                       ->orWhere('description', 'like', "%{$this->search}%")
                )
            )
            ->when($this->filterPriority, fn ($q) => $q->where('priority', $this->filterPriority))
            ->when($this->filterStatus, fn ($q) => $q->where('status', $this->filterStatus))
            ->latest()
            ->paginate($this->perPage);

        return view('livewire.student.notice.notice-component')
            ->with('notices', $notices)
            ->layout('layouts.student.app', [
                'title' => 'Notice Board | ' . institution()->name,
            ]);
    }
}