<?php

namespace App\Livewire\Admin\Log;

use Livewire\Component;
use App\Models\Branch;
use Livewire\WithPagination;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class LoginLogComponent extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    public string $search  = '';
    public string $role    = '';
    public int    $perPage = 10;

    public ?int $institutionId = null;
    public ?int $branchId      = null;

    public function mount(): void
    {
        if (! Auth::check() || Auth::user()->role !== 'admin') {
            throw new AccessDeniedHttpException('Unauthorized access to login log.');
        }

        $institutionId = Auth::user()->institution_id;

        if (blank($institutionId)) {
            throw new AccessDeniedHttpException('Admin account is not linked to an institution.');
        }

        $this->institutionId = $institutionId;
        $this->branchId       = $this->resolveActiveBranchId();
    }

    protected function resolveActiveBranchId(): ?int
    {
        $user = Auth::user();

        return $user->branch_id
            ?? Branch::resolveMainBranchId($user->institution_id);
    }

    public function updatingSearch(): void  { $this->resetPage(); }
    public function updatingRole(): void    { $this->resetPage(); }
    public function updatingPerPage(): void { $this->resetPage(); }

    public function render()
    {
        $logs = User::query()
            ->where('institution_id', $this->institutionId)
            ->when($this->branchId, fn($q) => $q->where('branch_id', $this->branchId))
            ->when($this->search, fn($q) => $q->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('email', 'like', "%{$this->search}%")
                  ->orWhere('username', 'like', "%{$this->search}%");
            }))
            ->when($this->role, fn($q) => $q->where('role', $this->role))
            ->orderByDesc('last_login_at')
            ->paginate($this->perPage);

        return view('livewire.admin.log.login-log-component')
            ->with('logs', $logs)
            ->layout('layouts.admin.app', [
                'title' => 'Login Log | ' . setting('app_name', 'EMS'),
            ]);
    }
}