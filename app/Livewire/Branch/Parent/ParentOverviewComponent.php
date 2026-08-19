<?php

namespace App\Livewire\Branch\Parent;

use Livewire\Component;
use App\Models\Guardian;

class ParentOverviewComponent extends Component
{
    public Guardian $guardian;
    public $user;

    public string $routePrefix = '';

    public function mount(int $id)
    {
        $this->guardian = Guardian::with('user')->findOrFail($id);
        $this->user     = $this->guardian->user;

        if (! $this->user) {
            abort(404, 'No linked user account found for this parent.');
        }

        $this->routePrefix = $this->resolveRoutePrefix();
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
        return view('livewire.admin.parent.parent-overview-component')
            ->with('guardian', $this->guardian)
            ->layout('layouts.branch.app', [
                'title' => 'Parent Overview | ' . institution()->name,
            ]);
    }
}
