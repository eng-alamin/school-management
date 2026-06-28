<?php

namespace App\Livewire\Accountant\Parent;

use Livewire\Component;
use App\Models\Guardian;

class ParentOverviewComponent extends Component
{
    public $parent;

    public function mount(int $id)
    {
        $this->parent = Guardian::with([
            'user',
        ])->findOrFail($id);
    }

    public function render()
    {
        return view('livewire.accountant.parent.parent-overview-component')
            ->with('parent', $this->parent)
            ->layout('layouts.accountant.app', [
                'title' => 'Parent Overview | ' . institution()->name,
            ]);
    }
}
