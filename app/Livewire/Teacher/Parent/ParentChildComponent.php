<?php

namespace App\Livewire\Tenant\Teacher\Parent;

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
        return view('livewire.tenant.teacher.parent.parent-child-component')
            ->with('parent', $this->parent)
            ->layout('layouts.teacher.app', [
                'title' => "Parent Child | School SaaS",
            ]);
    }
}
