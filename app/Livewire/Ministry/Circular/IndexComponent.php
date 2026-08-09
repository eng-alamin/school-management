<?php

namespace App\Livewire\Ministry\Circular;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Circular;

class IndexComponent extends Component
{
    use WithPagination;

    public string $search = '';
    public string $status = ''; // '', 'active', 'inactive'

    protected $queryString = [
        'search' => ['except' => ''],
        'status' => ['except' => ''],
    ];

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingStatus(): void { $this->resetPage(); }

    public function delete(int $circularId): void
    {
        Circular::findOrFail($circularId)->delete();
        $this->dispatch('toast', type: 'success', message: 'Circular deleted.');
    }

    public function render()
    {
        $circulars = Circular::with(['institution:id,name', 'creator:id,name'])
            ->when($this->search, fn ($q) => $q->where('title', 'like', "%{$this->search}%"))
            ->when($this->status !== '', fn ($q) => $q->where('status', $this->status))
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('livewire.ministry.circular.index-component', [
            'circulars' => $circulars,
        ])->layout('layouts.ministry.app', [
            'title' => 'Circulars | ' . setting('app_name', 'EMS'),
        ]);
    }
}