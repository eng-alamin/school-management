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

    public $role;
    public $joining_date;
    public $designation_id;
    public $department_id;
    public $qualification;
    public $experience_detail;
    public $total_experience;
    public $comments;

    public $name;
    public $dob;
    public $religion;
    public $mobile;
    public $email;
    public $present_address;
    public $permanent_address;

    public $photo;
    public $photo_upload;

    public $username;
    public $password;

    public bool $usernameManuallyEdited = true;

    public $bank_name;
    public $holder_name;
    public $bank_branch;
    public $bank_address;
    public $ifsc_code;
    public $account_no;

    private const ASSIGNABLE_ROLES = [
        User::ROLE_TEACHER,
        User::ROLE_STAFF,
        User::ROLE_ACCOUNTANT,
    ];

    public function mount($id)
    {
        $institutionId = auth()->user()->institution_id;

        $this->employee = Employee::with('user')
            ->where('institution_id', $institutionId)
            ->findOrFail($id);

        $this->employeeId = $this->employee->id;
        $this->userId     = $this->employee->user_id;

        $this->role               = $this->employee->user->role;
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

    public function rules(): array
    {
        $institutionId = auth()->user()->institution_id;

        return [
            'role' => ['required', Rule::in(self::ASSIGNABLE_ROLES)],
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

            'name' => 'required',
            'mobile' => 'nullable|string|max:20',
            'email'    => ['nullable', Rule::unique('users', 'email')->ignore($this->userId)],
            'photo_upload' => 'nullable|image|max:2048',

            'username'    => ['required', Rule::unique('users', 'username')->ignore($this->userId)],
            'password'    => 'nullable|min:8',
        ];
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

    public function update()
    {
        $this->validate($this->rules());

        $institutionId = auth()->user()->institution_id;

        $oldPhoto = null;
        $newPhotoPath = null;

        try {
            DB::transaction(function () use ($institutionId, &$oldPhoto, &$newPhotoPath) {

                abort_unless($this->employee->institution_id === $institutionId, 403);

                $userData = [
                    'role'     => $this->role,
                    'name'     => $this->name,
                    'username' => $this->username,
                    'email'    => $this->email,
                ];

                if (!empty($this->password)) {
                    $userData['password'] = $this->password;
                }

                $user = User::where('institution_id', $institutionId)->findOrFail($this->userId);
                $user->update($userData);

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
            });

            if ($oldPhoto) {
                Storage::disk('public')->delete($oldPhoto);
            }

            activity()
                ->performedOn($this->employee)
                ->causedBy(auth()->user())
                ->withProperties([
                    'institution_id' => $institutionId,
                    'icon' => 'edit',
                    'type' => 'employee_updated',
                ])
                ->log('Employee updated: ' . $this->employee->name);

            $this->dispatch('toast', type: 'success', message: 'Employee updated successfully!');

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

        return view('livewire.admin.employee.employee-edit-component', [
            'departments'  => EmployeeDepartment::where('institution_id', $institutionId)->get(),
            'designations' => EmployeeDesignation::where('institution_id', $institutionId)->get(),
        ])->layout('layouts.admin.app', [
            'title' => 'Edit Employee | ' . institution()->name,
        ]);
    }
}