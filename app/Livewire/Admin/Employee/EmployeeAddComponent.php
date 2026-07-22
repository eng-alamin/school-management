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

    // ── Username auto-generate theke manually edit hoyeche kina track korar flag ──
    public bool $usernameManuallyEdited = false;

    // Bank Info
    public $bank_name;
    public $holder_name;
    public $bank_branch;
    public $bank_address;
    public $ifsc_code;
    public $account_no;

    public function mount()
    {
        $this->joining_date = now()->format('Y-m-d');
        $this->generateEmployeeId();
    }

    public function getSelectedDesignationNameProperty(): ?string
    {
        if (!$this->designation_id) {
            return null;
        }

        return EmployeeDesignation::find($this->designation_id)?->name;
    }


    public function updatedName($value): void
    {
        if (!$this->usernameManuallyEdited) {
            $this->username = $this->generateUniqueUsername($value);
        }
    }

    /**
     * User nijer hate Username field e type korle, auto-generate bondho hoye jabe.
     */
    public function updatedUsername(): void
    {
        $this->usernameManuallyEdited = true;
    }

    /**
     * Name theke unique username slug generate kore.
     * "Abc 123" -> "abc_123". Duplicate thakle "_1", "_2"... suffix add hobe.
     */
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

    private function generateEmployeeId(): void
    {
        $institutionId = auth()->user()->institution_id;
        $inst = institution();

        $digit     = (int) ($inst->employee_id_digit_length ?? 6);
        $startFrom = (int) ($inst->employee_id_start_from ?? 1);

        $prefix = ($inst->enable_employee_id_prefix && $inst->employee_id_code_prefix)
            ? $inst->employee_id_code_prefix
            : 'SCH' . str_pad($institutionId, 2, '0', STR_PAD_LEFT);

        $year = now()->format('y');

        $lastEmployee = Employee::where('institution_id', $institutionId)
            ->whereNotNull('employee_id')
            ->orderByDesc('id')
            ->first();

        $serial = $lastEmployee
            ? ((int) substr($lastEmployee->employee_id, -$digit)) + 1
            : $startFrom;

        $this->employee_id = $prefix . $year . str_pad($serial, $digit, '0', STR_PAD_LEFT);
    }

    public function rules(): array
    {
        return [
            'role'                   => 'required',
            'joining_date'           => 'required|date',
            'designation_id'         => 'required|exists:employee_designations,id',
            'department_id'          => 'required|exists:employee_departments,id',
            'comments'               => 'nullable|string|max:1000',
            'name'                   => 'required',
            'mobile'                 => 'nullable|string|max:20',
            'email'                  => 'nullable|unique:users,email',
            'photo_upload'           => 'nullable|image|max:2048',
            'employee_id'            => 'nullable|unique:employees,employee_id',
            'username'               => 'required|unique:users,username',
            'password'               => 'nullable|min:8',
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

    public function resetForm(): void
    {
        $this->reset();
        $this->usernameManuallyEdited = false;
        $this->joining_date = now()->format('Y-m-d');
        $this->generateEmployeeId();
        $this->dispatch('form-reset');
    }

    public function save(): void
    {
        DB::beginTransaction();

        try {

            $this->validate($this->rules());

            $institutionId = auth()->user()->institution_id;

            $user = User::create([
                'institution_id'=> $institutionId,
                'role'     => $this->role,
                'name'     => $this->name,
                'username' => $this->username,
                'email'    => $this->email,
                'password' => !empty($this->password) ? $this->password : '12345678',
            ]);

            $photoPath = $this->photo_upload
                ? $this->photo_upload->store('employees', 'public')
                : null;

            // ── Race-condition safety: lock kore last employee dhore
            // employee_id ke re-verify/re-generate kora hocche, preview-e generate
            // kora value use na kore ekhane lock-safe kora holo ──
            $inst = institution();
            $digit     = (int) ($inst->employee_id_digit_length ?? 6);
            $startFrom = (int) ($inst->employee_id_start_from ?? 1);

            $prefix = ($inst->enable_employee_id_prefix && $inst->employee_id_code_prefix)
                ? $inst->employee_id_code_prefix
                : 'SCH' . str_pad($institutionId, 2, '0', STR_PAD_LEFT);

            $year = now()->format('y');

            $lastEmployeeForId = Employee::where('institution_id', $institutionId)
                ->whereNotNull('employee_id')
                ->lockForUpdate()
                ->orderByDesc('id')
                ->first();

            $serial = $lastEmployeeForId
                ? ((int) substr($lastEmployeeForId->employee_id, -$digit)) + 1
                : $startFrom;

            $employeeId = $prefix . $year . str_pad($serial, $digit, '0', STR_PAD_LEFT);

            Employee::create([
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

            DB::commit();

            $this->resetForm();

            $this->dispatch('toast', type: 'success', message: 'Employee created successfully!');

        } catch (\Throwable $e) {

            DB::rollBack();

            $this->dispatch('toast', type: 'error', message: 'Something went wrong!');
            throw $e;
        }
    }

    public function render()
    {
        return view('livewire.admin.employee.employee-add-component', [
            'departments'  => EmployeeDepartment::all(),
            'designations' => EmployeeDesignation::all(),
        ])->layout('layouts.admin.app', [
            'title' => 'Create Employee | ' . institution()->name,
        ]);
    }
}