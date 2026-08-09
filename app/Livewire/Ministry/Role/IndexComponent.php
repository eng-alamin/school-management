<?php

namespace App\Livewire\Ministry\Role;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

#[Layout('layouts.ministry.app')]
class IndexComponent extends Component
{
    private const SUPER_ADMIN_ROLE = 'Ministry Super Admin';
    private const ROLE_PREFIX = 'Ministry ';

    // =====================
    // Tab state
    // =====================
    public string $activeTab = 'roles'; // roles | permissions

    // =====================
    // Role form state
    // =====================
    public bool $showRoleModal = false;
    public bool $showRoleViewModal = false;
    public bool $showRoleDeleteModal = false;
    public bool $isRoleEditMode = false;

    public ?int $roleId = null;
    public string $roleName = '';
    public array $selectedPermissions = [];
    public ?Role $viewingRole = null;
    public ?int $deletingRoleId = null;

    // =====================
    // Permission form state
    // =====================
    public bool $showPermissionModal = false;
    public bool $showPermissionDeleteModal = false;

    public string $permissionModule = '';
    public string $permissionAction = '';
    public ?int $deletingPermissionId = null;

    public function mount(): void
    {
        $this->authorizeSuperAdmin();
    }

    /**
     * শুধু Ministry Super Admin এখানে ঢুকতে পারবে — permission-ভিত্তিক না, role-ভিত্তিক,
     * যাতে privilege-escalation (নিজেকে নিজে permission দেওয়া) সম্ভব না হয়।
     */
    private function authorizeSuperAdmin(): void
    {
        abort_unless(
            Auth::user()?->isMinistry() && Auth::user()->hasRole(self::SUPER_ADMIN_ROLE),
            403,
            'শুধু Ministry Super Admin এই section access করতে পারবে।'
        );
    }

    public function setActiveTab(string $tab): void
    {
        $this->activeTab = in_array($tab, ['roles', 'permissions'], true) ? $tab : 'roles';
    }

    // =====================
    // Computed
    // =====================

    public function getRolesProperty()
    {
        return Role::where('guard_name', 'web')
            ->where('name', 'like', self::ROLE_PREFIX . '%')
            ->withCount(['permissions', 'users'])
            ->orderBy('name')
            ->get();
    }

    public function getAllPermissionsProperty()
    {
        return Permission::where('guard_name', 'web')->orderBy('name')->get();
    }

    /**
     * Permission গুলো module অনুযায়ী group করা (name.action প্যাটার্ন থেকে module বের করে)
     * যাতে Role form-এ checkbox গুলো সুন্দরভাবে category ধরে দেখানো যায়।
     */
    public function getGroupedPermissionsProperty()
    {
        return $this->allPermissions->groupBy(function (Permission $permission) {
            return explode('.', $permission->name)[0] ?? 'other';
        });
    }

    public function render()
    {
        return view('livewire.ministry.role.index-component');
    }

    // =====================
    // Role: Create / Edit
    // =====================

    public function openCreateRoleModal(): void
    {
        $this->authorizeSuperAdmin();
        $this->resetRoleForm();
        $this->isRoleEditMode = false;
        $this->showRoleModal = true;
    }

    public function openEditRoleModal(int $id): void
    {
        $this->authorizeSuperAdmin();

        $role = Role::where('guard_name', 'web')->findOrFail($id);

        $this->roleId = $role->id;
        // prefix ছাড়া বাকি অংশ input-এ দেখানো হবে, সংরক্ষণের সময় আবার prefix জোড়া লাগবে
        $this->roleName = str_starts_with($role->name, self::ROLE_PREFIX)
            ? substr($role->name, strlen(self::ROLE_PREFIX))
            : $role->name;
        $this->selectedPermissions = $role->permissions->pluck('name')->toArray();

        $this->isRoleEditMode = true;
        $this->showRoleModal = true;
    }

    public function openViewRoleModal(int $id): void
    {
        $this->viewingRole = Role::with('permissions')->withCount('users')->findOrFail($id);
        $this->showRoleViewModal = true;
    }

    protected function roleRules(): array
    {
        return [
            'roleName' => ['required', 'string', 'max:80'],
            'selectedPermissions' => ['array'],
            'selectedPermissions.*' => ['string', 'exists:permissions,name'],
        ];
    }

    public function saveRole(): void
    {
        $this->authorizeSuperAdmin();
        $this->validate($this->roleRules());

        $fullRoleName = self::ROLE_PREFIX . trim($this->roleName);

        DB::beginTransaction();
        try {
            if ($this->isRoleEditMode) {
                $role = Role::where('guard_name', 'web')->findOrFail($this->roleId);

                // Super Admin role-এর নাম বদলানো/protect করা — সবসময় পুরো permission set থাকবে
                if ($role->name === self::SUPER_ADMIN_ROLE) {
                    $fullRoleName = self::SUPER_ADMIN_ROLE;
                    $this->selectedPermissions = Permission::where('guard_name', 'web')->pluck('name')->toArray();
                } else {
                    $this->guardDuplicateRoleName($fullRoleName, $role->id);
                }

                $role->update(['name' => $fullRoleName]);
                $role->syncPermissions($this->selectedPermissions);

                activity()
                    ->causedBy(Auth::user())
                    ->performedOn($role)
                    ->withProperties(['icon' => 'edit', 'type' => 'update'])
                    ->log('Ministry role updated: ' . $role->name);

                $message = 'Role সফলভাবে আপডেট হয়েছে।';
            } else {
                $this->guardDuplicateRoleName($fullRoleName);

                $role = Role::create(['name' => $fullRoleName, 'guard_name' => 'web']);
                $role->syncPermissions($this->selectedPermissions);

                activity()
                    ->causedBy(Auth::user())
                    ->performedOn($role)
                    ->withProperties(['icon' => 'add_moderator', 'type' => 'create'])
                    ->log('Ministry role created: ' . $role->name);

                $message = 'নতুন Role সফলভাবে তৈরি হয়েছে।';
            }

            app(PermissionRegistrar::class)->forgetCachedPermissions();
            DB::commit();
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->dispatch('toast', type: 'error', message: 'কিছু একটা ভুল হয়েছে, আবার চেষ্টা করুন।');
            report($e);
            return;
        }

        $this->showRoleModal = false;
        $this->resetRoleForm();
        $this->dispatch('toast', type: 'success', message: $message);
    }

    private function guardDuplicateRoleName(string $fullRoleName, ?int $ignoreId = null): void
    {
        $exists = Role::where('guard_name', 'web')
            ->where('name', $fullRoleName)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists();

        if ($exists) {
            $this->addError('roleName', 'এই নামে একটা Role ইতিমধ্যে আছে।');
            throw \Illuminate\Validation\ValidationException::withMessages([
                'roleName' => 'এই নামে একটা Role ইতিমধ্যে আছে।',
            ]);
        }
    }

    // =====================
    // Role: Delete
    // =====================

    public function confirmDeleteRole(int $id): void
    {
        $this->authorizeSuperAdmin();

        $role = Role::where('guard_name', 'web')->withCount('users')->findOrFail($id);

        if ($role->name === self::SUPER_ADMIN_ROLE) {
            $this->dispatch('toast', type: 'error', message: 'Ministry Super Admin role delete করা যাবে না।');
            return;
        }

        if ($role->users_count > 0) {
            $this->dispatch('toast', type: 'error', message: 'এই Role-এ user assigned আছে, আগে তাদের অন্য role দিন।');
            return;
        }

        $this->deletingRoleId = $id;
        $this->showRoleDeleteModal = true;
    }

    public function deleteRole(): void
    {
        $this->authorizeSuperAdmin();

        $role = Role::where('guard_name', 'web')->withCount('users')->findOrFail($this->deletingRoleId);

        if ($role->name === self::SUPER_ADMIN_ROLE || $role->users_count > 0) {
            $this->showRoleDeleteModal = false;
            $this->dispatch('toast', type: 'error', message: 'এই Role delete করা সম্ভব না।');
            return;
        }

        activity()
            ->causedBy(Auth::user())
            ->performedOn($role)
            ->withProperties(['icon' => 'delete', 'type' => 'delete'])
            ->log('Ministry role deleted: ' . $role->name);

        $role->delete();

        $this->showRoleDeleteModal = false;
        $this->deletingRoleId = null;
        $this->dispatch('toast', type: 'success', message: 'Role সফলভাবে delete হয়েছে।');
    }

    // =====================
    // Permission: Create
    // =====================

    public function openCreatePermissionModal(): void
    {
        $this->authorizeSuperAdmin();
        $this->reset(['permissionModule', 'permissionAction']);
        $this->resetErrorBag();
        $this->showPermissionModal = true;
    }

    protected function permissionRules(): array
    {
        return [
            'permissionModule' => ['required', 'string', 'max:40', 'regex:/^[a-z0-9\-]+$/'],
            'permissionAction' => ['required', 'string', 'max:40', 'regex:/^[a-z0-9\-]+$/'],
        ];
    }

    public function savePermission(): void
    {
        $this->authorizeSuperAdmin();
        $this->validate($this->permissionRules());

        $permissionName = strtolower(trim($this->permissionModule)) . '.' . strtolower(trim($this->permissionAction));

        if (Permission::where('guard_name', 'web')->where('name', $permissionName)->exists()) {
            $this->addError('permissionAction', 'এই permission ইতিমধ্যে আছে।');
            return;
        }

        DB::beginTransaction();
        try {
            $permission = Permission::create(['name' => $permissionName, 'guard_name' => 'web']);

            // নতুন permission তৈরি হলেই Super Admin-কে auto-grant করা হবে,
            // যাতে Super Admin সবসময় সব permission রাখে (manual sync ভুলে যাওয়া রোধ)
            $superAdmin = Role::where('guard_name', 'web')->where('name', self::SUPER_ADMIN_ROLE)->first();
            $superAdmin?->givePermissionTo($permission);

            activity()
                ->causedBy(Auth::user())
                ->performedOn($permission)
                ->withProperties(['icon' => 'add_task', 'type' => 'create'])
                ->log('Ministry permission created: ' . $permission->name);

            app(PermissionRegistrar::class)->forgetCachedPermissions();
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->dispatch('toast', type: 'error', message: 'কিছু একটা ভুল হয়েছে, আবার চেষ্টা করুন।');
            report($e);
            return;
        }

        $this->showPermissionModal = false;
        $this->reset(['permissionModule', 'permissionAction']);
        $this->dispatch('toast', type: 'success', message: 'নতুন Permission সফলভাবে তৈরি হয়েছে।');
    }

    // =====================
    // Permission: Delete
    // =====================

    public function confirmDeletePermission(int $id): void
    {
        $this->authorizeSuperAdmin();

        $permission = Permission::where('guard_name', 'web')->withCount('roles')->findOrFail($id);

        if ($permission->roles_count > 0) {
            $this->dispatch('toast', type: 'error', message: 'এই Permission কোনো Role-এ ব্যবহৃত হচ্ছে, আগে সেখান থেকে সরান।');
            return;
        }

        $this->deletingPermissionId = $id;
        $this->showPermissionDeleteModal = true;
    }

    public function deletePermission(): void
    {
        $this->authorizeSuperAdmin();

        $permission = Permission::where('guard_name', 'web')->withCount('roles')->findOrFail($this->deletingPermissionId);

        if ($permission->roles_count > 0) {
            $this->showPermissionDeleteModal = false;
            $this->dispatch('toast', type: 'error', message: 'এই Permission delete করা সম্ভব না।');
            return;
        }

        activity()
            ->causedBy(Auth::user())
            ->withProperties(['icon' => 'delete', 'type' => 'delete', 'permission' => $permission->name])
            ->log('Ministry permission deleted: ' . $permission->name);

        $permission->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->showPermissionDeleteModal = false;
        $this->deletingPermissionId = null;
        $this->dispatch('toast', type: 'success', message: 'Permission সফলভাবে delete হয়েছে।');
    }

    // =====================
    // Helpers
    // =====================

    private function resetRoleForm(): void
    {
        $this->reset(['roleId', 'roleName', 'selectedPermissions']);
        $this->resetErrorBag();
        $this->resetValidation();
    }

    public function closeModals(): void
    {
        $this->showRoleModal = false;
        $this->showRoleViewModal = false;
        $this->showRoleDeleteModal = false;
        $this->showPermissionModal = false;
        $this->showPermissionDeleteModal = false;
    }
}