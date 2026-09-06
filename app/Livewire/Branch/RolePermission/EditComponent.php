<?php

namespace App\Livewire\Branch\RolePermission;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Database\Seeders\InstitutionRolePermissionSeeder;
use App\Livewire\Branch\RolePermission\Concerns\InteractsWithPermissionMatrix;

class EditComponent extends Component
{
    use InteractsWithPermissionMatrix;

    public $roleId;
    public $name;

    // Read-only display value — the branch a role belongs to is fixed at
    // creation time and is never re-assigned through edit (no dropdown).
    public ?int $branch_id = null;

    private function institutionId(): int
    {
        return auth()->user()->institution_id;
    }

    public function mount($id)
    {
        $institutionId = $this->institutionId();

        $role = Role::where('institution_id', $institutionId)
            ->where('guard_name', 'web')
            ->findOrFail($id);

        $this->roleId              = $role->id;
        $this->name                = $role->name;
        $this->branch_id           = $role->branch_id;
        $this->selectedPermissions = $role->permissions()->pluck('name')->toArray();
    }

    public function rules(): array
    {
        $institutionId = $this->institutionId();
        $branchId       = $this->branch_id;

        return [
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('roles', 'name')
                    ->where('guard_name', 'web')
                    ->where('institution_id', $institutionId)
                    ->where('branch_id', $branchId)
                    ->ignore($this->roleId),
            ],
            'selectedPermissions'   => 'array',
            // Only 'institution.*' permissions may be selected from the
            // Admin panel matrix — see CreateComponent for why this prefix
            // filter is required (guard_name alone can't separate Ministry
            // vs Institution permissions since both use 'web').
            //
            // IMPORTANT: Rule::exists()->where() only supports 2-arg
            // equality ($column, $value) — a 3rd arg (operator) is SILENTLY
            // IGNORED, previously collapsing this into `name = 'like'`,
            // matching nothing and failing validation on every submit.
            // A Closure is required to run a real LIKE condition here.
            'selectedPermissions.*' => Rule::exists('permissions', 'name')
                ->where(function ($query) {
                    $query->where('guard_name', 'web')
                          ->where('name', 'like', InstitutionRolePermissionSeeder::PREFIX . '%');
                }),
        ];
    }

    protected function failedValidation($validator)
    {
        $this->dispatch('validation-failed');

        parent::failedValidation($validator);
    }

    public function updated($propertyName): void
    {
        if ($propertyName === 'name') {
            $this->validateOnly($propertyName, $this->rules());
        }
    }

    public function save(): void
    {
        $this->validate($this->rules());

        $institutionId = $this->institutionId();
        $branchId       = $this->branch_id;

        // Defense-in-depth: re-verify the permission names actually belong
        // to the fixed global 'institution.*' permission list (guard=web)
        // before persisting.
        $validPermissionNames = Permission::where('guard_name', 'web')
            ->where('name', 'like', InstitutionRolePermissionSeeder::PREFIX . '%')
            ->whereIn('name', $this->selectedPermissions)
            ->pluck('name')
            ->all();

        try {
            DB::transaction(function () use ($institutionId, $branchId, $validPermissionNames) {

                app(PermissionRegistrar::class)->setPermissionsTeamId($institutionId);

                // Re-verify ownership server-side (IDOR guard) even though
                // mount() already checked — public property $roleId could
                // theoretically be tampered with client-side between requests.
                $role = Role::where('institution_id', $institutionId)
                    ->where('guard_name', 'web')
                    ->findOrFail($this->roleId);

                // branch_id is passed explicitly on purpose: the trait's
                // auto-fill only runs on creating(), not on update(), and
                // this is a read-only carry-forward of the existing value.
                $role->update([
                    'name'      => $this->name,
                    'branch_id' => $branchId,
                ]);
                $role->syncPermissions($validPermissionNames);

                activity()
                    ->performedOn($role)
                    ->causedBy(auth()->user())
                    ->tap(fn($activity) => $activity->institution_id = $institutionId)
                    ->withProperties([
                        'icon'      => 'edit',
                        'type'      => 'role_updated',
                        'branch_id' => $branchId,
                    ])
                    ->log('Role updated: ' . $role->name);
            });

            session()->flash('toast_success', 'Role updated successfully!');
            $this->redirectRoute('branch.role-permission.index', navigate: true);

        } catch (\Throwable $e) {
            $this->dispatch('toast', type: 'error', message: 'Something went wrong!');
            throw $e;
        }
    }

    public function render()
    {
        return view('livewire.admin.role-permission.edit-component', [
            'matrix'  => $this->buildPermissionMatrix(),
            'actions' => self::ACTIONS,
        ])->layout('layouts.branch.app', [
            'title' => 'Edit Role | ' . institution()->name,
        ]);
    }
}