<?php

namespace App\Livewire\SuperAdmin\Log;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\User;
use App\Models\Scopes\SchoolScope;

class LoginLogComponent extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    public string $search  = '';
    public string $role    = '';
    public int    $perPage = 10;

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingRole(): void   { $this->resetPage(); }

    public function render()
    {
        $logs = User::withoutGlobalScope(SchoolScope::class)
            ->when($this->search, fn($q) => $q->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('email', 'like', "%{$this->search}%")
                  ->orWhere('username', 'like', "%{$this->search}%");
            }))
            ->when($this->role, fn($q) => $q->where('role', $this->role))
            ->orderByDesc('last_login_at')
            ->paginate($this->perPage);

        return view('livewire.super-admin.log.login-log-component')
            ->with('logs', $logs)
            ->layout('layouts.superadmin.app', [
                'title' => 'Login Log | ' . setting('app_name', 'EMS'),
            ]);
    }
}