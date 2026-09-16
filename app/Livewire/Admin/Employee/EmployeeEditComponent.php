<?php

namespace App\Livewire\Admin\Employee;

use Livewire\Component;
use App\Models\Employee;
use App\Models\EmployeeDepartment;
use App\Models\EmployeeDesignation;
use App\Models\Role;
use App\Models\User;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

use Illuminate\Validation\Rule;
use Livewire\WithFileUploads;
use Spatie\Permission\PermissionRegistrar;

class EmployeeEditComponent extends Component
{
    use WithFileUploads;

    public $employeeId;
    public $userId;
    public $employee;

    // Job Details
    public $role;
    public $joining_date;
    public $designation_id;
    public $department_id;
    public $qualification;
    public $experience_detail;
    public $total_experience;
    public $comments;

    // Employee Details
    public $name;
    public $dob;
    public $religion;
    public $mobile;
    public $email;
    public $present_address;
    public $permanent_address;

    public $photo;
    public $photo_upload;

    // Login Details
    public $username;
    public $password;

    public bool $usernameManuallyEdited = true;

    // Bank Info
    public $bank_name;
    public $holder_name;
    public $bank_branch;
    public $bank_address;
    public $ifsc_code;
    public $account_no;

    // Protected roles that cannot be assigned via this employee form
    private const PROTECTED_ROLES = ['admin'];

    // users.role কলাম একটা fixed ENUM — এখানের বাইরের কোনো value দিলে
    // MySQL "Data truncated for column 'role'" error দেয়। এই list
    // migration ফাইলের enum() এর সাথে হুবহু মিলতে হবে।
    private const USER_ROLE_ENUM_VALUES = [
        'super_admin', 'ministry', 'admin', 'it_support',
        'branch', 'staff', 'accountant', 'teacher', 'student', 'parent',
    ];

    // Custom (non-enum) role হলে users.role কলামে এই fallback value বসবে
    private const USER_ROLE_ENUM_FALLBACK = 'staff';

    /**
     * users.role (ENUM) কলামের জন্য safe value বের করে।
     * Custom role হলে enum এ পড়বে না, তখন fallback ('staff') রিটার্ন করে।
     * আসল role assignment এর জন্য এটা ব্যবহার হয় না — সেটা Spatie দিয়ে হয়।
     */
    private function resolveUserRoleColumn(string $roleName): string
    {
        return in_array($roleName, self::USER_ROLE_ENUM_VALUES, true)
            ? $roleName
            : self::USER_ROLE_ENUM_FALLBACK;
    }

    public function mount($id)
    {
        $institutionId = auth()->user()->institution_id;

        $this->employee = Employee::with('user')
            ->where('institution_id', $institutionId)
            ->findOrFail($id);

        $this->employeeId = $this->employee->id;
        $this->userId     = $this->employee->user_id;

        // NOTE: users.role হলো একটা ENUM কলাম, যেখানে custom role এর জন্য
        // fallback ('staff') সেভ থাকে (দেখো resolveUserRoleColumn())। তাই
        // dropdown-এ প্রি-সিলেক্ট করার জন্য users.role কলাম না পড়ে, আসল role
        // Spatie-র model_has_roles পিভট থেকে আনতে হবে — নাহলে custom role
        // assign করা থাকলেও এডিট ফর্মে সবসময় "staff" দেখাবে।
        app(PermissionRegistrar::class)->setPermissionsTeamId($this->employee->institution_id);
        $this->role = $this->employee->user->getRoleNames()->first();
        $this->joining_date       = $this->employee->joining_date;
        $this->designation_id     = $this->employee->designation_id;
        $this->department_id      = $this->employee->department_id;
        $this->qualification      = $this->employee->qualification;
        $this->experience_detail  = $this->employee->experience_detail;
        $this->total_experience   = $this->employee->total_experience;
        $this->comments           = $this->employee->comments;

        $this->name              = $this->employee->name;
        $this->dob               = $this->employee->dob;
        $this->religion          = $this->employee->religion;
        $this->mobile            = $this->employee->mobile;
        $this->email             = $this->employee->email;
        $this->present_address   = $this->employee->present_address;
        $this->permanent_address = $this->employee->permanent_address;

        $this->photo = $this->employee->photo;

        $this->username = $this->employee->user->username;

        $this->bank_name    = $this->employee->bank_name;
        $this->holder_name  = $this->employee->holder_name;
        $this->bank_branch  = $this->employee->bank_branch;
        $this->bank_address = $this->employee->bank_address;
        $this->ifsc_code    = $this->employee->ifsc_code;
        $this->account_no   = $this->employee->account_no;
    }

    /**
     * Role name গুলো ডাটাবেস (Spatie Role) থেকে আনা হয়, এই employee-র
     * নিজের branch-এ scoped, admin/protected role বাদে — assignable role names।
     */
    private function assignableRoleNames(): array
    {
        $institutionId = $this->employee->institution_id;
        $branchId      = $this->employee->branch_id;

        return Role::where('institution_id', $institutionId)
            ->where('branch_id', $branchId)
            ->where('guard_name', 'web')
            ->whereNotIn('name', self::PROTECTED_ROLES)
            ->pluck('name')
            ->all();
    }

    public function rules(): array
    {
        $institutionId = auth()->user()->institution_id;

        return [
            'role' => ['required', Rule::in($this->assignableRoleNames())],
            'joining_date' => 'required|date',
            'designation_id' => [
                'required',
                Rule::exists('employee_designations', 'id')->where('institution_id', $institutionId),
            ],
            'department_id' => [
                'required',
                Rule::exists('employee_departments', 'id')->where('institution_id', $institutionId),
            ],
            'comments' => 'nullable|string|max:1000',

            'name'   => 'required',
            'mobile' => 'nullable|string|max:20',
            'email'  => ['nullable', Rule::unique('users', 'email')->ignore($this->userId)],
            'photo_upload' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',

            'username' => ['required', Rule::unique('users', 'username')->ignore($this->userId)],
            'password' => 'nullable|min:8',
        ];
    }

    public function updatedPhotoUpload($value): void
    {
        $this->validateOnly('photo_upload', [
            'photo_upload' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);
    }

    public function getSelectedDesignationNameProperty(): ?string
    {
        if (!$this->designation_id) {
            return null;
        }

        return EmployeeDesignation::where('institution_id', auth()->user()->institution_id)
            ->find($this->designation_id)?->name;
    }

    public function updatedName($value): void
    {
        if (!$this->usernameManuallyEdited) {
            $this->username = $this->generateUniqueUsername($value, $this->userId);
        }
    }

    public function updatedUsername(): void
    {
        $this->usernameManuallyEdited = true;
    }

    public function enableUsernameAutoSuggest(): void
    {
        $this->usernameManuallyEdited = false;
        $this->username = $this->generateUniqueUsername($this->name, $this->userId);
    }

    private function generateUniqueUsername(?string $name, ?int $ignoreUserId = null): ?string
    {
        if (!$name || trim($name) === '') {
            return null;
        }

        $base = Str::slug($name, '_');

        if ($base === '') {
            return null;
        }

        $username = $base;
        $counter = 1;

        while (
            User::where('username', $username)
                ->when($ignoreUserId, fn($q) => $q->where('id', '!=', $ignoreUserId))
                ->exists()
        ) {
            $username = $base . '_' . $counter;
            $counter++;
        }

        return $username;
    }

    protected function failedValidation($validator)
    {
        $this->dispatch('validation-failed');

        parent::failedValidation($validator);
    }

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName, $this->rules());
    }

    public function update(): void
    {
        $this->validate($this->rules());

        $institutionId = auth()->user()->institution_id;

        // Defense-in-depth: role নাম আবার DB থেকে ভেরিফাই, এই employee-র branch scope এ
        $validRoleName = Role::where('institution_id', $this->employee->institution_id)
            ->where('branch_id', $this->employee->branch_id)
            ->where('guard_name', 'web')
            ->whereNotIn('name', self::PROTECTED_ROLES)
            ->where('name', $this->role)
            ->value('name');

        abort_unless($validRoleName, 403);

        $oldPhoto = null;
        $newPhotoPath = null;

        try {
            DB::transaction(function () use ($institutionId, $validRoleName, &$oldPhoto, &$newPhotoPath) {

                abort_unless($this->employee->institution_id === $institutionId, 403);

                app(PermissionRegistrar::class)->setPermissionsTeamId($this->employee->institution_id);

                $user = User::where('institution_id', $institutionId)->lockForUpdate()->findOrFail($this->userId);

                $userData = [
                    // users.role ENUM কলামের জন্য safe fallback ব্যবহার হচ্ছে।
                    // আসল permission role নিচে syncRoles() দিয়ে সেট হচ্ছে।
                    'role'     => $this->resolveUserRoleColumn($validRoleName),
                    'name'     => $this->name,
                    'username' => $this->username,
                    'email'    => $this->email,
                ];

                if (!empty($this->password)) {
                    $userData['password'] = $this->password;
                }

                $user->update($userData);

                // Spatie pivot table (model_has_roles) sync করা — শুধু 'role' column
                // update করলে permission system reflect করবে না
                $user->syncRoles([$validRoleName]);

                $employeeData = [
                    'joining_date'      => $this->joining_date,
                    'designation_id'    => $this->designation_id,
                    'department_id'     => $this->department_id,
                    'qualification'     => $this->qualification,
                    'experience_detail' => $this->experience_detail,
                    'total_experience'  => $this->total_experience,
                    'comments'          => $this->comments,

                    'name'              => $this->name,
                    'dob'               => $this->dob,
                    'religion'          => $this->religion,
                    'mobile'            => $this->mobile,
                    'email'             => $this->email,
                    'present_address'   => $this->present_address,
                    'permanent_address' => $this->permanent_address,

                    'bank_name'         => $this->bank_name,
                    'holder_name'       => $this->holder_name,
                    'bank_branch'       => $this->bank_branch,
                    'bank_address'      => $this->bank_address,
                    'ifsc_code'         => $this->ifsc_code,
                    'account_no'        => $this->account_no,
                ];

                if ($this->photo_upload) {
                    $oldPhoto = $this->employee->photo;
                    $newPhotoPath = $this->photo_upload->store('employees', 'public');
                    $employeeData['photo'] = $newPhotoPath;
                }

                $this->employee->update($employeeData);

                activity()
                    ->causedBy(auth()->user())
                    ->performedOn($this->employee)
                    ->withProperties(['icon' => 'badge', 'type' => 'employee'])
                    ->tap(function ($activity) {
                        $activity->institution_id = $this->employee->institution_id;
                        $activity->branch_id = $this->employee->branch_id;
                    })
                    ->log('Employee updated: ' . $this->employee->name);
            });

            if ($oldPhoto) {
                Storage::disk('public')->delete($oldPhoto);
            }

            session()->flash('toast_success', 'Employee updated successfully!');
            $this->redirectRoute('admin.employee.list', navigate: true);

        } catch (\Throwable $e) {

            if ($newPhotoPath) {
                Storage::disk('public')->delete($newPhotoPath);
            }

            $this->dispatch('toast', type: 'error', message: 'Something went wrong!');
            throw $e;
        }
    }

    public function render()
    {
        $institutionId = auth()->user()->institution_id;
        $branchId      = $this->employee->branch_id;

        return view('livewire.admin.employee.employee-edit-component', [
            'departments'  => EmployeeDepartment::where('institution_id', $institutionId)->get(),
            'designations' => EmployeeDesignation::where('institution_id', $institutionId)->get(),
            'roles'        => Role::where('institution_id', $institutionId)
                                ->where('branch_id', $branchId)
                                ->where('guard_name', 'web')
                                ->whereNotIn('name', self::PROTECTED_ROLES)
                                ->orderBy('name')
                                ->get(),
        ])->layout('layouts.admin.app', [
            'title' => 'Edit Employee | ' . institution()->name,
        ]);
    }
}