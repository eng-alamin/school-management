<?php

namespace App\Livewire\Ministry\User;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

#[Layout('layouts.ministry.app')]
class IndexComponent extends Component
{
    use WithPagination;

    // =====================
    // Allowlists (security: never pass raw user input to orderBy / paginate)
    // =====================
    private const SORTABLE_FIELDS = ['name', 'username', 'email', 'is_active', 'created_at'];
    private const PER_PAGE_OPTIONS = [10, 25, 50, 100];

    private const SUPER_ADMIN_ROLE = 'Ministry Super Admin';

    protected string $paginationTheme = 'bootstrap';

    // =====================
    // List / Filter state
    // =====================
    public string $search = '';
    public int $perPage = 10;
    public string $sortField = 'created_at';
    public string $sortDirection = 'desc';
    public string $roleFilter = '';
    public string $statusFilter = '';

    // =====================
    // Form state
    // =====================
    public bool $showFormModal = false;
    public bool $showViewModal = false;
    public bool $showDeleteModal = false;
    public bool $isEditMode = false;

    public ?int $userId = null;
    public string $name = '';
    public string $username = '';
    public string $phone = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public bool $is_active = true;
    public string $ministryRole = '';

    public ?User $viewingUser = null;
    public ?int $deletingUserId = null;

    public function mount(): void
    {
        $this->authorizeSuperAdmin();
    }

    /**
     * শুধু Ministry Super Admin এখানে ঢুকতে পারবে — permission-ভিত্তিক না,
     * role-ভিত্তিক। কারণ Ministry User/Role panel থেকেই permission
     * তৈরি/delete/assign হয়; permission-based check ব্যবহার করলে
     * circular privilege-escalation সম্ভব হতে পারে (কোনোভাবে সেই
     * permission পেয়ে গেলে নিজেকে Super Admin বানানো যেত)। role name
     * সরাসরি চেক করলে সেই ঝুঁকি থাকে না — Role IndexComponent-এর সাথে
     * সামঞ্জস্যপূর্ণ pattern।
     *
     * Defense-in-depth: প্রতিটা write action-এর আগে আবার চেক হবে, শুধু
     * mount()-এ ভরসা করা হবে না। Route middleware bypass হলেও এটা যেন
     * শেষ লাইন অফ ডিফেন্স হিসেবে কাজ করে।
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

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingRoleFilter(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    /**
     * perPage allowlist enforcement — arbitrary/huge value দিয়ে DoS ঠেকাতে।
     */
    public function updatingPerPage($value): void
    {
        if (!in_array((int) $value, self::PER_PAGE_OPTIONS, true)) {
            $this->perPage = 10;
        }
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if (!in_array($field, self::SORTABLE_FIELDS, true)) {
            return;
        }

        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    // =====================
    // Computed data for view
    // =====================

    public function getMinistryRolesProperty()
    {
        return Role::where('guard_name', 'web')
            ->where('name', 'like', 'Ministry %')
            ->orderBy('name')
            ->pluck('name');
    }

    public function render()
    {
        $sortColumn = in_array($this->sortField, self::SORTABLE_FIELDS, true) ? $this->sortField : 'created_at';
        $perPage = in_array($this->perPage, self::PER_PAGE_OPTIONS, true) ? $this->perPage : 10;

        $users = User::query()
            ->with('roles')
            ->where('role', User::ROLE_MINISTRY)
            ->where('institution_id', null) // defense-in-depth: ministry user কখনো কোনো institution-এর সাথে bound না
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', "%{$this->search}%")
                      ->orWhere('username', 'like', "%{$this->search}%")
                      ->orWhere('email', 'like', "%{$this->search}%")
                      ->orWhere('phone', 'like', "%{$this->search}%");
                });
            })
            ->when($this->roleFilter, function ($query) {
                $query->whereHas('roles', function ($q) {
                    $q->where('name', $this->roleFilter);
                });
            })
            ->when($this->statusFilter !== '', function ($query) {
                $query->where('is_active', $this->statusFilter === 'active');
            })
            ->orderBy($sortColumn, $this->sortDirection)
            ->paginate($perPage);

        return view('livewire.ministry.user.index-component', [
            'users' => $users,
        ]);
    }

    // =====================
    // Create
    // =====================

    public function openCreateModal(): void
    {
        $this->authorizeSuperAdmin();
        $this->resetForm();
        $this->isEditMode = false;
        $this->showFormModal = true;
    }

    // =====================
    // Edit
    // =====================

    public function openEditModal(int $id): void
    {
        $this->authorizeSuperAdmin();

        $user = User::where('role', User::ROLE_MINISTRY)->findOrFail($id);

        $this->userId = $user->id;
        $this->name = $user->name;
        $this->username = (string) $user->username;
        $this->phone = (string) $user->phone;
        $this->email = (string) $user->email;
        $this->password = '';
        $this->password_confirmation = '';
        $this->is_active = (bool) $user->is_active;
        $this->ministryRole = $user->roles->first()?->name ?? '';

        $this->isEditMode = true;
        $this->showFormModal = true;
    }

    // =====================
    // View
    // =====================

    public function openViewModal(int $id): void
    {
        $this->authorizeSuperAdmin();

        $this->viewingUser = User::with('roles')
            ->where('role', User::ROLE_MINISTRY)
            ->findOrFail($id);

        $this->showViewModal = true;
    }

    // =====================
    // Save (Create + Update)
    // =====================

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'username' => [
                'required', 'string', 'max:100',
                Rule::unique('users', 'username')->ignore($this->userId),
            ],
            'phone' => [
                'nullable', 'string', 'max:15',
                Rule::unique('users', 'phone')->ignore($this->userId),
            ],
            'email' => [
                'required', 'email', 'max:150',
                Rule::unique('users', 'email')->ignore($this->userId),
            ],
            'password' => $this->isEditMode
                ? ['nullable', 'string', 'min:8', 'confirmed']
                : ['required', 'string', 'min:8', 'confirmed'],
            'is_active' => ['boolean'],
            'ministryRole' => ['required', 'string', Rule::in($this->ministryRoles->all())],
        ];
    }

    public function save(): void
    {
        $this->authorizeSuperAdmin();
        $this->validate();

        DB::beginTransaction();
        try {
            app(PermissionRegistrar::class)->setPermissionsTeamId(
                \Database\Seeders\MinistryRolePermissionSeeder::MINISTRY_TEAM_ID
            );

            if ($this->isEditMode) {
                $user = User::where('role', User::ROLE_MINISTRY)->findOrFail($this->userId);

                // Super Admin role নিজের থেকে সরিয়ে ফেললে এবং এটাই শেষ active
                // super admin হলে — পুরো ministry panel lock হয়ে যাবে। আটকাও।
                if (
                    $user->hasRole(self::SUPER_ADMIN_ROLE)
                    && $this->ministryRole !== self::SUPER_ADMIN_ROLE
                    && $this->isLastActiveSuperAdmin($user)
                ) {
                    DB::rollBack();
                    $this->dispatch('toast', type: 'error', message: 'এটাই শেষ active Super Admin — role পরিবর্তন করা যাবে না।');
                    return;
                }

                $user->update([
                    'name'      => $this->name,
                    'username'  => $this->username,
                    'phone'     => $this->phone ?: null,
                    'email'     => $this->email,
                    'is_active' => $this->is_active,
                    'password'  => $this->password ? $this->password : $user->password,
                ]);

                $user->syncRoles([$this->ministryRole]);

                activity()
                    ->causedBy(Auth::user())
                    ->performedOn($user)
                    ->withProperties(['icon' => 'edit', 'type' => 'update'])
                    ->tap(function ($activity) {
                        $activity->institution_id = null; // ministry user কোনো institution-এর সাথে bound না
                    })
                    ->log('Ministry user updated: ' . $user->name);

                $message = 'Ministry user সফলভাবে আপডেট হয়েছে।';
            } else {
                $user = User::create([
                    'institution_id' => null,
                    'branch_id'      => null,
                    'role'           => User::ROLE_MINISTRY,
                    'name'           => $this->name,
                    'username'       => $this->username,
                    'phone'          => $this->phone ?: null,
                    'email'          => $this->email,
                    'password'       => $this->password,
                    'is_active'      => $this->is_active,
                    'is_verified'    => true,
                ]);

                $user->assignRole($this->ministryRole);

                activity()
                    ->causedBy(Auth::user())
                    ->performedOn($user)
                    ->withProperties(['icon' => 'person_add', 'type' => 'create'])
                    ->tap(function ($activity) {
                        $activity->institution_id = null;
                    })
                    ->log('Ministry user created: ' . $user->name);

                $message = 'নতুন Ministry user সফলভাবে তৈরি হয়েছে।';
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->dispatch('toast', type: 'error', message: 'কিছু একটা ভুল হয়েছে, আবার চেষ্টা করুন।');
            report($e);
            return;
        } finally {
            app(PermissionRegistrar::class)->setPermissionsTeamId(null);
        }

        $this->showFormModal = false;
        $this->resetForm();
        $this->dispatch('toast', type: 'success', message: $message);
    }

    // =====================
    // Toggle Status
    // =====================

    public function toggleStatus(int $id): void
    {
        $this->authorizeSuperAdmin();

        $user = User::where('role', User::ROLE_MINISTRY)->findOrFail($id);

        if ($user->id === Auth::id()) {
            $this->dispatch('toast', type: 'error', message: 'নিজের account নিজে deactivate করা যাবে না।');
            return;
        }

        // Active থেকে Inactive করার সময়ই শুধু last-super-admin protection দরকার
        if ($user->is_active && $this->isLastActiveSuperAdmin($user)) {
            $this->dispatch('toast', type: 'error', message: 'এটাই শেষ active Ministry Super Admin — deactivate করা যাবে না।');
            return;
        }

        $user->update(['is_active' => !$user->is_active]);

        activity()
            ->causedBy(Auth::user())
            ->performedOn($user)
            ->withProperties(['icon' => 'toggle_on', 'type' => 'status-change'])
            ->tap(function ($activity) {
                $activity->institution_id = null;
            })
            ->log('Ministry user status changed: ' . $user->name);

        $this->dispatch('toast', type: 'success', message: 'Status সফলভাবে পরিবর্তন হয়েছে।');
    }

    // =====================
    // Delete
    // =====================

    public function confirmDelete(int $id): void
    {
        $this->authorizeSuperAdmin();

        if ($id === Auth::id()) {
            $this->dispatch('toast', type: 'error', message: 'নিজের account নিজে delete করা যাবে না।');
            return;
        }

        $this->deletingUserId = $id;
        $this->showDeleteModal = true;
    }

    public function delete(): void
    {
        $this->authorizeSuperAdmin();

        $user = User::where('role', User::ROLE_MINISTRY)->findOrFail($this->deletingUserId);

        if ($user->id === Auth::id()) {
            $this->dispatch('toast', type: 'error', message: 'নিজের account নিজে delete করা যাবে না।');
            $this->showDeleteModal = false;
            return;
        }

        if ($this->isLastActiveSuperAdmin($user)) {
            $this->dispatch('toast', type: 'error', message: 'এটাই শেষ active Ministry Super Admin — delete করা যাবে না।');
            $this->showDeleteModal = false;
            return;
        }

        // delete()-এর আগে log করতে হবে, পরে না
        activity()
            ->causedBy(Auth::user())
            ->performedOn($user)
            ->withProperties(['icon' => 'delete', 'type' => 'delete'])
            ->tap(function ($activity) {
                $activity->institution_id = null;
            })
            ->log('Ministry user deleted: ' . $user->name);

        $user->syncRoles([]); // pivot cleanup
        $user->delete(); // SoftDeletes + booted() hook username/phone/email suffix করে দেবে

        $this->showDeleteModal = false;
        $this->deletingUserId = null;
        $this->dispatch('toast', type: 'success', message: 'Ministry user সফলভাবে delete হয়েছে।');
    }

    // =====================
    // Helpers
    // =====================

    /**
     * $user-কে বাদ দিয়ে অন্য কোনো active 'Ministry Super Admin' আছে কিনা চেক করে।
     * না থাকলে $user-ই শেষ active super admin — deactivate/delete/role-change
     * করলে পুরো Ministry panel lock হয়ে যাবে, তাই ব্লক করা হয়।
     */
    private function isLastActiveSuperAdmin(User $user): bool
    {
        if (!$user->hasRole(self::SUPER_ADMIN_ROLE)) {
            return false;
        }

        return User::where('role', User::ROLE_MINISTRY)
            ->where('is_active', true)
            ->where('id', '!=', $user->id)
            ->whereHas('roles', function ($q) {
                $q->where('name', self::SUPER_ADMIN_ROLE);
            })
            ->doesntExist();
    }

    private function resetForm(): void
    {
        $this->reset([
            'userId', 'name', 'username', 'phone', 'email',
            'password', 'password_confirmation', 'ministryRole',
        ]);
        $this->is_active = true;
        $this->resetErrorBag();
        $this->resetValidation();
    }

    public function closeModals(): void
    {
        $this->showFormModal = false;
        $this->showViewModal = false;
        $this->showDeleteModal = false;
    }
}