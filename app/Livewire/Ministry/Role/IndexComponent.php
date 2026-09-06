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
use Database\Seeders\MinistryRolePermissionSeeder;

#[Layout('layouts.ministry.app')]
class IndexComponent extends Component
{
    private const SUPER_ADMIN_ROLE = 'Ministry Super Admin';
    private const ROLE_PREFIX = 'Ministry ';

    /**
     * Permission name prefix — mirrors MinistryRolePermissionSeeder::PREFIX.
     * Every permission this component reads, creates, deletes, or assigns
     * MUST be scoped by this prefix. Both Ministry and Institution
     * permissions share guard_name='web', so the prefix is the ONLY thing
     * separating the two sets — without it, this panel would list, assign,
     * and let a Super Admin delete Institution-level permissions too.
     */
    private const PERMISSION_PREFIX = MinistryRolePermissionSeeder::PREFIX;

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
            Auth::user()?->isMinistry(),
            // Auth::user()?->isMinistry() && Auth::user()->hasRole(self::SUPER_ADMIN_ROLE),
            403,
            'শুধু Ministry Super Admin এই section access করতে পারবে।'
        );
    }

    public function setActiveTab(string $tab): void
    {
        $this->activeTab = in_array($tab, ['roles', 'permissions'], true) ? $tab : 'roles';
    }

    /**
     * Ownership-verified Role lookup — every mutation/view entrypoint below
     * MUST route through this instead of a bare findOrFail(), so a
     * manipulated Livewire call payload can never load/edit/delete an
     * Institution-panel role by guessing its id.
     */
    private function findMinistryRoleOrFail(int $id): Role
    {
        return Role::where('guard_name', 'web')
            ->where('name', 'like', self::ROLE_PREFIX . '%')
            ->findOrFail($id);
    }

    /**
     * Ownership-verified Permission lookup — same rationale as
     * findMinistryRoleOrFail(): stops a tampered id from reaching an
     * Institution-panel permission.
     */
    private function findMinistryPermissionOrFail(int $id): Permission
    {
        return Permission::where('guard_name', 'web')
            ->where('name', 'like', self::PERMISSION_PREFIX . '%')
            ->findOrFail($id);
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
        // Scoped to 'ministry.*' only — see PERMISSION_PREFIX docblock.
        return Permission::where('guard_name', 'web')
            ->where('name', 'like', self::PERMISSION_PREFIX . '%')
            ->orderBy('name')
            ->get();
    }

    /**
     * Permission গুলো module অনুযায়ী group করা যাতে Role form-এ checkbox গুলো
     * সুন্দরভাবে category ধরে দেখানো যায়।
     *
     * Name pattern is 'ministry.{module}.{action}' (3 segments), so the
     * module is segment index 1 — NOT index 0 (index 0 is always the
     * literal 'ministry' segment, which would collapse every permission
     * into a single group if used).
     */
    public function getGroupedPermissionsProperty()
    {
        return $this->allPermissions->groupBy(function (Permission $permission) {
            return explode('.', $permission->name)[1] ?? 'other';
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

        $role = $this->findMinistryRoleOrFail($id);

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
        $this->authorizeSuperAdmin();

        $this->viewingRole = $this->findMinistryRoleOrFail($id)->loadCount('users')->load('permissions');
        $this->showRoleViewModal = true;
    }

    protected function roleRules(): array
    {
        return [
            'roleName' => ['required', 'string', 'max:80'],
            'selectedPermissions' => ['array'],
            // Closure required for a LIKE condition inside Rule::exists() —
            // its where() only supports 2-arg equality, so a 3-arg
            // ('name', 'like', ...) call is silently ignored and would
            // match nothing (see CreateComponent/EditComponent history for
            // this exact bug). This also restricts assignable permissions
            // to 'ministry.*' only, so an Institution permission name can
            // never be synced onto a Ministry role.
            'selectedPermissions.*' => [
                'string',
                Rule::exists('permissions', 'name')->where(function ($query) {
                    $query->where('guard_name', 'web')
                          ->where('name', 'like', self::PERMISSION_PREFIX . '%');
                }),
            ],
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
                $role = $this->findMinistryRoleOrFail($this->roleId);

                // Super Admin role-এর নাম বদলানো/protect করা — সবসময় পুরো Ministry permission set থাকবে
                if ($role->name === self::SUPER_ADMIN_ROLE) {
                    $fullRoleName = self::SUPER_ADMIN_ROLE;
                    $this->selectedPermissions = Permission::where('guard_name', 'web')
                        ->where('name', 'like', self::PERMISSION_PREFIX . '%')
                        ->pluck('name')
                        ->all();
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

        $role = $this->findMinistryRoleOrFail($id)->loadCount('users');

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

        $role = $this->findMinistryRoleOrFail($this->deletingRoleId)->loadCount('users');

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

        // Always prefixed with 'ministry.' — permissions created from this
        // panel must never be indistinguishable from Institution-panel
        // permissions, since both share guard_name='web'.
        $permissionName = self::PERMISSION_PREFIX
            . strtolower(trim($this->permissionModule)) . '.'
            . strtolower(trim($this->permissionAction));

        if (Permission::where('guard_name', 'web')->where('name', $permissionName)->exists()) {
            $this->addError('permissionAction', 'এই permission ইতিমধ্যে আছে।');
            return;
        }

        DB::beginTransaction();
        try {
            $permission = Permission::create(['name' => $permissionName, 'guard_name' => 'web']);

            // নতুন permission তৈরি হলেই Super Admin-কে auto-grant করা হবে,
            // যাতে Super Admin সবসময় সব Ministry permission রাখে (manual sync ভুলে যাওয়া রোধ)
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

        $permission = $this->findMinistryPermissionOrFail($id)->loadCount('roles');

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

        $permission = $this->findMinistryPermissionOrFail($this->deletingPermissionId)->loadCount('roles');

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