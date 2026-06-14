<?php

namespace App\Livewire\Accountant\Employee;

use Livewire\Component;
use App\Models\Employee;
use App\Models\Department;
use App\Models\Designation;
use App\Models\User;
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
            'designation_id' => 'required|exists:designations,id',
            'department_id'  => 'required|exists:departments,id',
            'name'           => 'required',
            'mobile'         => 'nullable|string|max:20',
            'email'          => 'nullable|email',
            'photo_upload' => 'nullable|image|max:2048',
            'username'       => 'required|unique:users,username',
            'password'       => 'nullable|min:4',
        ];
    }

    public function resetForm(): void
    {
        $this->reset();
    }

    public function save(): void
    {
        try {
            $this->validate($this->rules());

            $user = User::create([
                'role'     => $this->role,
                'name'     => $this->name,
                'username' => $this->username,
                'email'    => $this->email,
                'password' => !empty($this->password) ? $this->password : '1234',
            ]);

           $photoPath = $this->photo_upload
            ? $this->photo_upload->store('employees', 'public')
            : null;

            Employee::create([
                'user_id'           => $user->id,
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

            $this->dispatch('toast', type: 'success', message: 'Employee created successfully!');
            $this->resetForm();

        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            $this->dispatch('toast', type: 'error', message: 'An error occurred: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.accountant.employee.employee-add-component', [
            'departments'  => Department::all(),
            'designations' => Designation::all(),
        ])->layout('layouts.accountant.app', [
            'title' => 'Create Employee | ' . school()->name,
        ]);
    }
}