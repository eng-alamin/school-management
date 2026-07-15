<?php

namespace App\Livewire\Admin\Parent;

use Livewire\Component;
use App\Models\Guardian;

class ParentOverviewComponent extends Component
{
    public Guardian $guardian;
    public $user;

    public function mount(int $id)
    {
        $this->guardian = Guardian::with('user')->findOrFail($id);
        $this->user     = $this->guardian->user;

        if (! $this->user) {
            abort(404, 'No linked user account found for this parent.');
        }
    }

    public function render()
    {
        return view('livewire.admin.parent.parent-overview-component')
            ->with('guardian', $this->guardian)
            ->layout('layouts.admin.app', [
                'title' => 'Parent Overview | ' . institution()->name,
            ]);
    }
}
