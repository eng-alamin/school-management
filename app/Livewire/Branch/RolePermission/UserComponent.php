<?php

namespace App\Livewire\Branch\RolePermission;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\User;
use App\Models\Employee;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class UserComponent extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    // ══ Listing ══
    public string $search = '';
    public int $perPage = 10;
    public string $sortField = 'name';
    public string $sortDirection = 'asc';

    // ══ Assign Role Modal ══
    public bool $showRoleModal = false;
    public ?int $assigningEmployeeId = null;
    public $selectedRoles = [];

    private function institutionId(): int
    {
        return auth()->user()->institution_id;
    }

    // ══════════════════════ LISTING ══════════════════════

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if (!in_array($field, $this->sortableFields, true)) {
            return;
        }

        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
    }

    // ══════════════════════ ASSIGN ROLE MODAL ══════════════════════

    public function openRoleModal(int $employeeId): void
    {
        $institutionId = $this->institutionId();

        $employee = Employee::where('institution_id', $institutionId)
            ->with('user.roles')
            ->findOrFail($employeeId);

        $this->assigningEmployeeId = $employee->id;
        $this->selectedRoles       = $employee->user?->roles->pluck('name')->toArray() ?? [];
        $this->resetErrorBag();

        $this->showRoleModal = true;
    }

    public function closeRoleModal(): void
    {
        $this->showRoleModal       = false;
        $this->assigningEmployeeId = null;
        $this->selectedRoles       = [];
        $this->resetErrorBag();
    }

    private function generateUniqueUsername(?string $name): string
    {
        $base = Str::slug($name ?: 'user', '_');
        $base = $base !== '' ? $base : 'user';

        $username = $base;
        $counter  = 1;

        while (User::where('username', $username)->exists()) {
            $username = $base . '_' . $counter;
            $counter++;
        }

        return $username;
    }

    public function assignRoles(): void
    {
        $institutionId = $this->institutionId();

        $this->validate([
            'selectedRoles'   => 'array',
            'selectedRoles.*' => Rule::exists('roles', 'name')
                ->where('guard_name', 'web')
                ->where('institution_id', $institutionId),
        ]);

        // Defense-in-depth: re-verify role names actually belong to this institution's guard.
        $validRoleNames = Role::where('guard_name', 'web')
            ->where('institution_id', $institutionId)
            ->whereIn('name', $this->selectedRoles)
            ->pluck('name')
            ->all();

        try {
            DB::transaction(function () use ($institutionId, $validRoleNames) {

                app(PermissionRegistrar::class)->setPermissionsTeamId($institutionId);

                $employee = Employee::where('institution_id', $institutionId)
                    ->findOrFail($this->assigningEmployeeId);

                $user = $employee->user;

                $logType = 'user_role_updated';

                if (!$user) {
                    $user = User::create([
                        'institution_id' => $institutionId,
                        'name'           => $employee->name,
                        'username'       => $this->generateUniqueUsername($employee->name),
                        'email'          => $employee->email,
                        'password'       => '12345678',
                        'status'         => 'active',
                    ]);

                    $employee->update(['user_id' => $user->id]);

                    $logType = 'user_created';
                }

                $user->syncRoles($validRoleNames);

                activity()
                    ->performedOn($user)
                    ->causedBy(auth()->user())
                    ->tap(fn($activity) => $activity->institution_id = $institutionId)
                    ->withProperties(['icon' => 'admin_panel_settings', 'type' => $logType])
                    ->log('Roles assigned to: ' . $user->name);
            });

            $this->closeRoleModal();

            $this->dispatch('toast', type: 'success', message: 'Roles assigned successfully!');

        } catch (\Throwable $e) {
            $this->dispatch('toast', type: 'error', message: 'Something went wrong!');
            throw $e;
        }
    }


    // ══════════════════════ RENDER ══════════════════════

    public function render()
    {
        $institutionId = $this->institutionId();

        $query = Employee::with('user.roles')
            ->where('institution_id', $institutionId)
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', "%{$this->search}%")
                        ->orWhere('employee_id', 'like', "%{$this->search}%")
                        ->orWhereHas('user.roles', fn($q2) => $q2->where('name', 'like', "%{$this->search}%"));
                });
            });

        $employees = $query
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        $roles = Role::where('institution_id', $institutionId)
            ->where('guard_name', 'web')
            ->orderBy('name')
            ->get();

        return view('livewire.admin.role-permission.user-component')
            ->with('employees', $employees)
            ->with('roles', $roles)
            ->layout('layouts.branch.app', [
                'title' => 'User Role Assignment | ' . institution()->name,
            ]);
    }
} 