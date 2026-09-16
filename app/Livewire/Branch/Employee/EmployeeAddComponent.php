<?php

namespace App\Livewire\Branch\Employee;

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

class EmployeeAddComponent extends Component
{
    use WithFileUploads;

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
    public $photo_upload;

    // Employee ID (preview)
    public $employee_id;

    // Login Details
    public $username;
    public $password;

    public bool $usernameManuallyEdited = false;

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

    public function mount()
    {
        $this->joining_date = now()->format('Y-m-d');
        $this->employee_id  = $this->previewNextEmployeeId(auth()->user()->institution_id);
    }

    /**
     * Role name গুলো ডাটাবেস (Spatie Role) থেকে আনা হয়, নিজের branch-এ
     * scoped, admin/branch role বাদে — assignable role names।
     */
    private function assignableRoleNames(): array
    {
        $institutionId = auth()->user()->institution_id;
        $branchId      = auth()->user()->branch_id;

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
            'joining_date'   => 'required|date',
            'designation_id' => [
                'required',
                Rule::exists('employee_designations', 'id')->where('institution_id', $institutionId),
            ],
            'department_id' => [
                'required',
                Rule::exists('employee_departments', 'id')->where('institution_id', $institutionId),
            ],
            'comments'     => 'nullable|string|max:1000',
            'name'         => 'required',
            'mobile'       => 'nullable|string|max:20',
            'email'        => 'nullable|unique:users,email',
            'photo_upload' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'employee_id'  => 'nullable|unique:employees,employee_id',
            'username'     => 'required|unique:users,username',
            'password'     => 'nullable|min:8',
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
            $this->username = $this->generateUniqueUsername($value);
        }
    }

    public function updatedUsername(): void
    {
        $this->usernameManuallyEdited = true;
    }

    private function generateUniqueUsername(?string $name): ?string
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

        while (User::where('username', $username)->exists()) {
            $username = $base . '_' . $counter;
            $counter++;
        }

        return $username;
    }

    private function previewNextEmployeeId(int $institutionId): string
    {
        $inst = institution();

        $digit     = (int) ($inst->employee_id_digit_length ?? 6);
        $startFrom = (int) ($inst->employee_id_start_from ?? 1);

        $prefix = ($inst->enable_employee_id_prefix && $inst->employee_id_code_prefix)
            ? $inst->employee_id_code_prefix
            : 'EMP' . str_pad($institutionId, 2, '0', STR_PAD_LEFT);

        $year = now()->format('y');

        $lastEmployee = Employee::where('institution_id', $institutionId)
            ->whereNotNull('employee_id')
            ->orderByDesc('id')
            ->first();

        $serial = $lastEmployee
            ? ((int) substr($lastEmployee->employee_id, -$digit)) + 1
            : $startFrom;

        return $prefix . $year . str_pad($serial, $digit, '0', STR_PAD_LEFT);
    }

    private function generateNextEmployeeId(int $institutionId): string
    {
        $inst = institution();

        $digit     = (int) ($inst->employee_id_digit_length ?? 6);
        $startFrom = (int) ($inst->employee_id_start_from ?? 1);

        $prefix = ($inst->enable_employee_id_prefix && $inst->employee_id_code_prefix)
            ? $inst->employee_id_code_prefix
            : 'EMP' . str_pad($institutionId, 2, '0', STR_PAD_LEFT);

        $year = now()->format('y');

        $lastEmployee = Employee::where('institution_id', $institutionId)
            ->whereNotNull('employee_id')
            ->lockForUpdate()
            ->orderByDesc('id')
            ->first();

        $serial = $lastEmployee
            ? ((int) substr($lastEmployee->employee_id, -$digit)) + 1
            : $startFrom;

        return $prefix . $year . str_pad($serial, $digit, '0', STR_PAD_LEFT);
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

    public function resetForm(): void
    {
        $this->reset();
        $this->usernameManuallyEdited = false;
        $this->joining_date = now()->format('Y-m-d');
        $this->employee_id  = $this->previewNextEmployeeId(auth()->user()->institution_id);
        $this->dispatch('form-reset');
    }

    public function save(): void
    {
        $this->validate($this->rules());

        $institutionId = auth()->user()->institution_id;
        $branchId = auth()->user()->branch_id;

        // Defense-in-depth: role নাম আবার DB থেকে ভেরিফাই
        $validRoleName = Role::where('institution_id', $institutionId)
            ->where('branch_id', $branchId)
            ->where('guard_name', 'web')
            ->whereNotIn('name', self::PROTECTED_ROLES)
            ->where('name', $this->role)
            ->value('name');

        abort_unless($validRoleName, 403);

        $photoPath = null;

        try {
            [$user, $employee] = DB::transaction(function () use ($institutionId, $branchId, $validRoleName, &$photoPath) {

                DB::table('institutions')->where('id', $institutionId)->lockForUpdate()->first();

                app(PermissionRegistrar::class)->setPermissionsTeamId($institutionId);

                $user = User::create([
                    'institution_id' => $institutionId,
                    'branch_id'      => $branchId,
                    'role'           => $this->resolveUserRoleColumn($validRoleName),
                    'name'           => $this->name,
                    'username'       => $this->username,
                    'email'          => $this->email,
                    'password'       => !empty($this->password) ? $this->password : '12345678',
                ]);

                $user->assignRole($validRoleName);

                $photoPath = $this->photo_upload
                    ? $this->photo_upload->store('employees', 'public')
                    : null;

                $employeeId = $this->generateNextEmployeeId($institutionId);

                $employee = Employee::create([
                    'institution_id'    => $institutionId,
                    'user_id'           => $user->id,
                    'employee_id'       => $employeeId,
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
                    'photo'             => $photoPath,
                    'bank_name'         => $this->bank_name,
                    'holder_name'       => $this->holder_name,
                    'bank_branch'       => $this->bank_branch,
                    'bank_address'      => $this->bank_address,
                    'ifsc_code'         => $this->ifsc_code,
                    'account_no'        => $this->account_no,
                ]);

                activity()
                    ->causedBy(auth()->user())
                    ->performedOn($employee)
                    ->withProperties(['icon' => 'badge', 'type' => 'employee'])
                    ->tap(function ($activity) use ($employee) {
                        $activity->institution_id = $employee->institution_id;
                        $activity->branch_id = $employee->branch_id;
                    })
                    ->log('Employee created: ' . $employee->name);

                return [$user, $employee];
            });

            session()->flash('toast_success', 'Employee created successfully!');
            $this->redirectRoute('admin.employee.list', navigate: true);

        } catch (\Throwable $e) {

            if ($photoPath) {
                Storage::disk('public')->delete($photoPath);
            }

            $this->dispatch('toast', type: 'error', message: 'Something went wrong!');
            throw $e;
        }
    }

    public function render()
    {
        return view('livewire.admin.employee.employee-add-component', [
            'departments'  => EmployeeDepartment::where('institution_id', auth()->user()->institution_id)->get(),
            'designations' => EmployeeDesignation::where('institution_id', auth()->user()->institution_id)->get(),
            'roles'        => Role::where('institution_id', auth()->user()->institution_id)
                                ->where('branch_id', auth()->user()->branch_id)
                                ->where('guard_name', 'web')
                                ->whereNotIn('name', self::PROTECTED_ROLES)
                                ->orderBy('name')
                                ->get(),
        ])->layout('layouts.branch.app', [
            'title' => 'Create Employee | ' . institution()->name,
        ]);
    }
}