<?php

namespace App\Livewire\Admin\Parent;

use Livewire\Component;
use App\Models\Guardian;

class ParentChildComponent extends Component
{
    public Guardian $guardian;
    public $user;

    public string $routePrefix = '';
    
    public function mount(int $id)
    {
        $this->routePrefix = $this->resolveRoutePrefix();
        
        $this->guardian = Guardian::with(['user', 'students'])->findOrFail($id);
        $this->user     = $this->guardian->user;

        if (! $this->user) {
            abort(404, 'No linked user account found for this parent.');
        }

    }

    protected function resolveRoutePrefix(): string
    {
        $routeName = request()->route()?->getName();

        if ($routeName && str_contains($routeName, '.')) {
            return explode('.', $routeName)[0] . '.';
        }

        $segment = request()->segment(1);

        return $segment ? $segment . '.' : '';
    }

    public function render()
    {
        return view('livewire.admin.parent.parent-child-component')
            ->with('guardian', $this->guardian)
            ->layout('layouts.admin.app', [
                'title' => 'Parent Child | ' . institution()->name,
            ]);
    }
}
