<?php

namespace App\Livewire\Teacher\Parent;

use Livewire\Component;
use App\Models\Guardian;

class ParentChildComponent extends Component
{
    public $parent;

    public function mount(int $id)
    {
        $this->parent = Guardian::with([
            'user', 'students'
        ])->findOrFail($id);

    }

    public function render()
    {
        return view('livewire.teacher.parent.parent-child-component')
            ->with('parent', $this->parent)
            ->layout('layouts.teacher.app', [
                'title' => 'Parent Child | ' . institution()->name,
            ]);
    }
}
