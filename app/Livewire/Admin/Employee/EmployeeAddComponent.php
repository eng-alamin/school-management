<?php

namespace App\Livewire\Admin\Employee;

use Livewire\Component;
use App\Models\Employee;
use App\Models\EmployeeDepartment;
use App\Models\EmployeeDesignation;
use App\Models\User;
use Illuminate\Support\Facades\DB;

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

    // Employee Details
    public $name;
    public $dob;
    public $religion;
    public $mobile;
    public $email;
    public $present_address;
    public $permanent_address;
    public $photo_upload;

    // Login Details
    public $username;
    public $password;

    // Bank Info
    public $bank_name;
    public $holder_name;
    public $bank_branch;
    public $bank_address;
    public $ifsc_code;
    public $account_no;

    public function rules(): array
    {
        return [
            'role'           => 'required',
            'designation_id' => 'required|exists:employee_designations,id',
            'department_id'  => 'required|exists:employee_departments,id',
            'name'           => 'required',
            'mobile'         => 'nullable|string|max:20',
            'email'          => 'nullable|unique:users,email',
            'photo_upload'   => 'nullable|image|max:2048',
            'username'       => 'required|unique:users,username',
            'password'       => 'nullable|min:4',
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
                'password' => !empty($this->password) ? $this->password : '1234',
            ]);

            $photoPath = $this->photo_upload
                ? $this->photo_upload->store('employees', 'public')
                : null;

            // ── Generate Employee ID (SAFE - avoid duplicate)
            $institutionCode = 'SCH' . str_pad($institutionId, 2, '0', STR_PAD_LEFT);
            $year = now()->format('y');

            $lastEmployee = Employee::where('institution_id', $institutionId)
                ->lockForUpdate()
                ->orderByDesc('id')
                ->first();

            $serial = $lastEmployee
                ? ((int) substr($lastEmployee->employee_id, -4)) + 1
                : 1;

            $employeeId = $institutionCode . $year . str_pad($serial, 4, '0', STR_PAD_LEFT);

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