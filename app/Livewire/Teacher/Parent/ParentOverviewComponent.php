<?php

namespace App\Livewire\Tenant\Teacher\Parent;

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
        return view('livewire.tenant.teacher.parent.parent-overview-component')
            ->with('parent', $this->parent)
            ->layout('layouts.teacher.app', [
                'title' => "Parent Overview | School SaaS",
            ]);
    }
}
