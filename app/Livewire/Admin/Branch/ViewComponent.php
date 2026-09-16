<?php

namespace App\Livewire\Admin\Branch;

use Livewire\Component;
use App\Models\Branch;

class ViewComponent extends Component
{
    public $branch;

    public string $routePrefix = '';

    public function mount(int $id)
    {
        $this->branch = Branch::with([
            'institution',
            'users',
        ])->withCount('users')->findOrFail($id);

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
        return view('livewire.admin.branch.view-component')
            ->with('branch', $this->branch)
            ->layout('layouts.admin.app', [
                'title' => 'Branch Overview | ' . institution()->name,
            ]);
    }
}