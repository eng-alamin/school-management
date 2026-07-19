<?php

namespace App\Livewire\Admin\Employee;

use Livewire\Component;
use App\Models\Employee;
use App\Models\EmployeeDepartment;
use App\Models\EmployeeDesignation;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;

class EmployeeEditComponent extends Component
{
    use WithFileUploads;

    public $employeeId;
    public $userId;
    public $employee;

    // Academic Details
    public $role;
    public $joining_date;
    public $designation_id;
    public $department_id;
    public $qualification;
    public $experience_detail;
    public $total_experience;

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

    // ── Username auto-generate theke manually edit hoyeche kina track korar flag.
    // Edit page e existing username DB theke pre-fill hoy, tai mount() e eta
    // 'true' rakha hocche jate purono employee-er username accidentally
    // overwrite na hoye jay (nahole tar login vengge jabe).
    public bool $usernameManuallyEdited = true;

    // Bank Info
    public $bank_name;
    public $holder_name;
    public $bank_branch;
    public $bank_address;
    public $ifsc_code;
    public $account_no;

    public function mount($id)
    {
        $this->employeeId = $id;
        $this->employee   = Employee::with('user')->findOrFail($id);
        $this->userId     = $this->employee->user_id;

        $this->role               = $this->employee->user->role;
        $this->joining_date       = $this->employee->joining_date;
        $this->designation_id     = $this->employee->designation_id;
        $this->department_id      = $this->employee->department_id;
        $this->qualification      = $this->employee->qualification;
        $this->experience_detail  = $this->employee->experience_detail;
        $this->total_experience   = $this->employee->total_experience;

        // Employee Details
        $this->name              = $this->employee->name;
        $this->dob               = $this->employee->dob;
        $this->religion          = $this->employee->religion;
        $this->mobile            = $this->employee->mobile;
        $this->email             = $this->employee->email;
        $this->present_address   = $this->employee->present_address;
        $this->permanent_address = $this->employee->permanent_address;

        $this->photo = $this->employee->photo;

        // Login Details
        $this->username = $this->employee->user->username;

        // Bank Info
        $this->bank_name    = $this->employee->bank_name;
        $this->holder_name  = $this->employee->holder_name;
        $this->bank_branch  = $this->employee->bank_branch;
        $this->bank_address = $this->employee->bank_address;
        $this->ifsc_code    = $this->employee->ifsc_code;
        $this->account_no   = $this->employee->account_no;
    }

    /**
     * Name change hole -> Username auto-generate hobe, KINTU shudhu tokhoni
     * jokhon usernameManuallyEdited flag manually 'false' kora hoyeche
     * (mane user "Suggest" button diye notun suggestion cheyeche).
     * Default e existing employee-er username protected thake.
     */
    public function updatedName($value): void
    {
        if (!$this->usernameManuallyEdited) {
            $this->username = $this->generateUniqueUsername($value, $this->userId);
        }
    }

    /**
     * User Username field e hate diye change korle flag lock hoye jabe,
     * jate porer name change eta overwrite na kore.
     */
    public function updatedUsername(): void
    {
        $this->usernameManuallyEdited = true;
    }

    /**
     * Admin ইচ্ছাকৃতভাবে "Suggest" button click korle notun username
     * suggest hobe current name theke, ebong pore Name change hole
     * ar auto-update hobe.
     */
    public function enableUsernameAutoSuggest(): void
    {
        $this->usernameManuallyEdited = false;
        $this->username = $this->generateUniqueUsername($this->name, $this->userId);
    }

    /**
     * Name theke unique username slug generate kore.
     * "Abc 123" -> "abc_123". Duplicate thakle "_1", "_2"... suffix add hobe.
     * $ignoreUserId dile nijer current username-ke duplicate hisebe dhora hobe na.
     */
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

    public function rules()
    {
        return [
            'role' => 'required',
            'joining_date' => 'required|date',
            'designation_id' => 'required|exists:employee_designations,id',
            'department_id' => 'required|exists:employee_departments,id',

            'name' => 'required',
            'mobile' => 'nullable|string|max:20',
            'email'    => ['nullable', Rule::unique('users', 'email')->ignore($this->userId)],
            'photo_upload' => 'nullable|image|max:2048',

            'username'    => ['required', Rule::unique('users', 'username')->ignore($this->userId)],
            'password'    => 'nullable|min:4',
        ];
    }

    protected function failedValidation($validator)
    {
        $this->dispatch('validation-failed');
    }

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName, $this->rules());
    }

    public function update()
    {
        DB::beginTransaction();

        try {

            $this->validate($this->rules());

            // ── User update ──────────────────────────────
            $userData = [
                'role'     => $this->role,
                'name'     => $this->name,
                'username' => $this->username,
                'email'    => $this->email,
            ];

            if (!empty($this->password)) {
                $userData['password'] = $this->password;
            }

            $user = User::findOrFail($this->userId);
            $user->update($userData);

            // ── Employee update ───────────────────────────
            $employeeData = [
                'joining_date'      => $this->joining_date,
                'designation_id'    => $this->designation_id,
                'department_id'     => $this->department_id,
                'qualification'     => $this->qualification,
                'experience_detail' => $this->experience_detail,
                'total_experience'  => $this->total_experience,

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

                $employeeData['photo'] = $this->photo_upload->store('employees', 'public');

                if ($oldPhoto) {
                    Storage::disk('public')->delete($oldPhoto);
                }
            }

            $this->employee->update($employeeData);

            DB::commit();

            $this->dispatch('toast', type: 'success', message: 'Employee updated successfully!');

        } catch (\Throwable $e) {

            DB::rollBack();

            $this->dispatch('toast', type: 'error', message: 'Something went wrong!');
            throw $e;
        }
    }

    public function render()
    {
        $employees    = Employee::all();
        $departments  = EmployeeDepartment::all();
        $designations = EmployeeDesignation::all();

        return view('livewire.admin.employee.employee-edit-component')
            ->with('employees', $employees)
            ->with('departments', $departments)
            ->with('designations', $designations)
            ->layout('layouts.admin.app', [
                'title' => 'Edit Employee | ' . institution()->name,
            ]);
    }
}