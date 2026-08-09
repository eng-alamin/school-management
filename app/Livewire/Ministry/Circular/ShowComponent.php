<?php

namespace App\Livewire\Ministry\Circular;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Circular;

class ShowComponent extends Component
{
    use WithPagination;

    public Circular $circular;

    public function mount(Circular $circular): void
    {
        $this->circular = $circular->load(['institution', 'creator']);
    }

    public function render()
    {
        $reads = $this->circular->reads()
            ->with('institution:id,name')
            ->orderByRaw('read_at IS NULL DESC')
            ->orderByDesc('read_at')
            ->paginate(15);

        return view('livewire.ministry.circular.show-component', [
            'reads' => $reads,
        ])->layout('layouts.ministry.app', [
            'title' => $this->circular->title . ' | ' . setting('app_name', 'EMS'),
        ]);
    }
}