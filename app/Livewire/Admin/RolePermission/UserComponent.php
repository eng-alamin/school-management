<?php

namespace App\Livewire\Admin\RolePermission;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\Models\Role;
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

    protected array $sortableFields = ['name'];

    // ══ Assign Role Modal ══
    public bool $showRoleModal = false;
    public ?int $assigningUserId = null;
    public ?int $assigningUserBranchId = null;
    public $selectedRoles = [];

    private const PANEL_ROLES = [
        User::ROLE_ADMIN,
        User::ROLE_IT_SUPPORT,
        User::ROLE_TEACHER,
        User::ROLE_STAFF,
        User::ROLE_ACCOUNTANT,
    ];

    private function institutionId(): int
    {
        return auth()->user()->institution_id;
    }

    private function branchId(): int
    {
        return auth()->user()->branch_id;
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

    public function openRoleModal(int $userId): void
    {
        $institutionId = $this->institutionId();
        $branchId = $this->branchId();

        app(PermissionRegistrar::class)->setPermissionsTeamId($institutionId);

        $user = User::where('institution_id', $institutionId)
            ->where('branch_id', $branchId)
            ->with('roles')
            ->findOrFail($userId);

        $this->assigningUserId       = $user->id;
        $this->assigningUserBranchId = $user->branch_id;
        $this->selectedRoles         = $user->roles->pluck('name')->toArray();

        $this->resetErrorBag();

        $this->showRoleModal = true;
    }

    public function closeRoleModal(): void
    {
        $this->showRoleModal         = false;
        $this->assigningUserId       = null;
        $this->assigningUserBranchId = null;
        $this->selectedRoles         = [];
        $this->resetErrorBag();
    }

    public function assignRoles(): void
    {
        $institutionId = $this->institutionId();
        $userBranchId  = $this->assigningUserBranchId;

        // Roles assignable to this user: strictly scoped to the
        // user's own branch (no institution-wide roles exist anymore).
        $this->validate([
            'selectedRoles'   => 'array',
            'selectedRoles.*' => Rule::exists('roles', 'name')
                ->where('guard_name', 'web')
                ->where('institution_id', $institutionId)
                ->where('branch_id', $userBranchId),
        ]);

        // Defense-in-depth: re-verify role names actually belong to this
        // institution's guard AND are scoped to this user's branch.
        $validRoleNames = Role::where('guard_name', 'web')
            ->where('institution_id', $institutionId)
            ->where('branch_id', $userBranchId)
            ->whereIn('name', $this->selectedRoles)
            ->pluck('name')
            ->all();

        try {
            DB::transaction(function () use ($institutionId, $validRoleNames) {

                app(PermissionRegistrar::class)->setPermissionsTeamId($institutionId);

                $user = User::where('institution_id', $institutionId)
                    ->findOrFail($this->assigningUserId);

                $user->syncRoles($validRoleNames);

                activity()
                    ->performedOn($user)
                    ->causedBy(auth()->user())
                    ->tap(fn($activity) => $activity->institution_id = $institutionId)
                    ->withProperties(['icon' => 'admin_panel_settings', 'type' => 'user_role_updated'])
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
        $branchId = $this->branchId();

        app(PermissionRegistrar::class)->setPermissionsTeamId($institutionId);

        $query = User::query()
            ->where('institution_id', $institutionId)
            ->where('branch_id', $branchId)
            ->whereIn('role', self::PANEL_ROLES)
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', "%{$this->search}%")
                        ->orWhere('phone', 'like', "%{$this->search}%")
                        ->orWhere('role', 'like', "%{$this->search}%");
                });
            });

        $users = $query
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        // Roles available for the currently-open assign modal: only roles
        // scoped to the user being edited's own branch.
        $roles = Role::where('institution_id', $institutionId)
            ->where('guard_name', 'web')
            ->where('branch_id', $this->assigningUserBranchId)
            ->orderBy('name')
            ->get();

        return view('livewire.admin.role-permission.user-component')
            ->with('users', $users)
            ->with('roles', $roles)
            ->layout('layouts.admin.app', [
                'title' => 'User Role Assignment | ' . institution()->name,
            ]);
    }
}