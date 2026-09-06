<?php

namespace App\Livewire\Branch\RolePermission;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\Models\Role;
use App\Models\Branch;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Database\Seeders\InstitutionRolePermissionSeeder;
use App\Livewire\Branch\RolePermission\Concerns\InteractsWithPermissionMatrix;

class CreateComponent extends Component
{
    use InteractsWithPermissionMatrix;

    public string $name = '';

    private function institutionId(): int
    {
        return auth()->user()->institution_id;
    }

    private function activeBranchId(): ?int
    {
        $institutionId = $this->institutionId();

        return auth()->user()->branch_id ?? Branch::resolveMainBranchId($institutionId);
    }

    public function rules(): array
    {
        $institutionId = $this->institutionId();
        $branchId       = $this->activeBranchId();

        return [
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('roles', 'name')
                    ->where('guard_name', 'web')
                    ->where('institution_id', $institutionId)
                    ->where('branch_id', $branchId),
            ],
            'selectedPermissions'   => 'array',
            // Only 'institution.*' permissions may be selected from the
            // Admin panel matrix. Without this prefix filter, a tampered
            // client-side payload containing a Ministry permission name
            // (e.g. 'ministry.institution.approve') would pass validation,
            // since Ministry permissions also live under guard_name='web'.
            //
            // IMPORTANT: Rule::exists()->where() only supports 2-arg
            // equality ($column, $value) — passing a 3rd arg (an operator
            // like 'like') is SILENTLY IGNORED, which previously collapsed
            // this into `where('name', 'like')` i.e. `name = 'like'`,
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

    public function resetForm(): void
    {
        $this->reset('name');
        $this->resetPermissionMatrix();
        $this->dispatch('form-reset');
    }

    public function save(): void
    {
        $this->validate($this->rules());

        $institutionId = $this->institutionId();

        // Defense-in-depth: re-verify the permission names actually belong
        // to the fixed global 'institution.*' permission list (guard=web)
        // before persisting. Scoping by the 'institution.' prefix here is
        // what stops a Ministry permission name from ever being synced to
        // an Institution-level role, since both share guard_name='web'.
        $validPermissionNames = Permission::where('guard_name', 'web')
            ->where('name', 'like', InstitutionRolePermissionSeeder::PREFIX . '%')
            ->whereIn('name', $this->selectedPermissions)
            ->pluck('name')
            ->all();

        try {
            DB::transaction(function () use ($institutionId, $validPermissionNames) {

                // Ensure Spatie's team context matches the current institution
                app(PermissionRegistrar::class)->setPermissionsTeamId($institutionId);

                // institution_id is auto-filled by Spatie's team scoping;
                // branch_id is auto-filled by the BelongsToBranch trait on
                // App\Models\Role — do not pass either explicitly here.
                $role = Role::create([
                    'name'       => $this->name,
                    'guard_name' => 'web',
                ]);

                $role->syncPermissions($validPermissionNames);

                activity()
                    ->performedOn($role)
                    ->causedBy(auth()->user())
                    ->tap(fn($activity) => $activity->institution_id = $institutionId)
                    ->withProperties([
                        'icon'      => 'add_moderator',
                        'type'      => 'role_created',
                        'branch_id' => $role->branch_id,
                    ])
                    ->log('Role created: ' . $role->name);
            });

            session()->flash('toast_success', 'Role created successfully!');
            $this->redirectRoute('branch.role-permission.index', navigate: true);

        } catch (\Throwable $e) {
            $this->dispatch('toast', type: 'error', message: 'Something went wrong!');
            throw $e;
        }
    }

    public function render()
    {
        return view('livewire.admin.role-permission.create-component', [
            'matrix'  => $this->buildPermissionMatrix(),
            'actions' => self::ACTIONS,
        ])->layout('layouts.branch.app', [
            'title' => 'Create Role | ' . institution()->name,
        ]);
    }
}