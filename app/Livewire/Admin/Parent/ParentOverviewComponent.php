<?php

namespace App\Livewire\Admin\Parent;

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
        return view('livewire.admin.parent.parent-overview-component')
            ->with('parent', $this->parent)
            ->layout('layouts.admin.app', [
                'title' => 'Parent Overview | ' . institution()->name,
            ]);
    }
}
