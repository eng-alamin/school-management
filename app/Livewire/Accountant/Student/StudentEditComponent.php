<?php

namespace App\Livewire\Tenant\Accountant\Student;

use Livewire\Component;
use App\Models\User;
use App\Models\Student;
use App\Models\Guardian;
use App\Models\AcademicSession;
use App\Models\AcademicClass;
use App\Models\AcademicSection;
use App\Models\AcademicCategory;

use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;
use Livewire\WithFileUploads;

class StudentEditComponent extends Component
{
    use WithFileUploads;

    public $studentId;
    public $userId;
    public $student;
    public $guardian;

    public $session_id;
    public $register_no;
    public $roll_no;
    public $admission_date;
    public $class_id;
    public $section_id;
    public $category_id;

    public $name;
    public $gender;
    public $blood_group;
    public $dob;
    public $religion;
    public $mobile;
    public $email;
    public $present_address;
    public $permanent_address;

    public $student_photo;
    public $student_photo_upload;

    public $username;
    public $password;
    public $password_confirmation;

    public $guardian_id;
    public $guardian_name, $guardian_relation;
    public $guardian_father_name, $guardian_mother_name;
    public $guardian_occupation, $guardian_income, $guardian_education;
    public $guardian_mobile, $guardian_email;
    public $guardian_address;

    public $guardian_photo;
    public $guardian_photo_upload;

    public $guardian_username;
    public $guardian_password;
    public $guardian_password_confirmation;

    public $previous_school;
    public $qualification;
    public $remarks;

    public bool $guardian_exists = false;

    public function mount($id)
    {
        $this->studentId = $id;
        $this->student   = Student::with('user','guardians')->findOrFail($id);

        $this->userId = $this->student->user_id;

        // Academic
        $this->session_id     = $this->student->session_id;
        $this->register_no    = $this->student->register_no;
        $this->roll_no        = $this->student->roll_no;
        $this->admission_date = $this->student->admission_date;
        $this->class_id       = $this->student->class_id;
        $this->section_id     = $this->student->section_id;
        $this->category_id    = $this->student->category_id;

        // Personal
        $this->name              = $this->student->name;
        $this->gender            = $this->student->gender;
        $this->blood_group       = $this->student->blood_group;
        $this->dob               = $this->student->dob;
        $this->religion          = $this->student->religion;
        $this->mobile            = $this->student->mobile;
        $this->email             = $this->student->email;
        $this->present_address   = $this->student->present_address;
        $this->permanent_address = $this->student->permanent_address;

        $this->student_photo = $this->student->photo;

        // Login
        $this->username = $this->student->user->username;

        // Guardian — existing guardian থাকলে pre-fill
        $this->guardian = $this->student->guardians->first();
        if ($this->guardian) {
            $this->guardian_exists = true;
            $this->guardian_id     = $this->guardian->id;
             $this->guardian_photo = $this->guardian->photo;
        }

        // Previous school
        $this->previous_school = $this->student->previous_school;
        $this->qualification   = $this->student->qualification;
        $this->remarks         = $this->student->remarks;

        $this->dispatch('date-updated', date: $this->admission_date);
        $this->dispatch('date-updated', date: $this->dob);
    }

    public function rules()
    {
        return [
            'session_id'  => 'required',
            'register_no' => ['required', Rule::unique('students', 'register_no')->ignore($this->studentId)],
            'name'        => 'required',

            'student_photo_upload'       => 'nullable',

            'username'    => ['required', Rule::unique('users', 'username')->ignore($this->userId)],
            'password'    => 'nullable|confirmed|min:4',

            'guardian_id'       => $this->guardian_exists ? 'required' : 'nullable',
            'guardian_name'     => !$this->guardian_exists ? 'required' : 'nullable',
            'guardian_relation' => !$this->guardian_exists ? 'required' : 'nullable',
            'guardian_mobile'   => !$this->guardian_exists ? 'required' : 'nullable',
            'guardian_email'    => !$this->guardian_exists ? 'required|email' : 'nullable',

            'guardian_photo_upload'       => 'nullable',
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

    public function safePreviewUrl($upload): ?string
    {
        if (!$upload) return null;
        try {
            return $upload->temporaryUrl();
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function deleteOldFile($path): void
    {
        if (!$path) {
            return;
        }

        $fullPath = public_path($path);

        if (file_exists($fullPath)) {
            unlink($fullPath);
        }
    }

    public function update()
    {
        try {
            $this->validate($this->rules());

            // ── Student user update ──────────────────────────────
            $userData = [
                'name'     => $this->name,
                'username' => $this->username,
                'email'    => $this->email,
            ];

            if (!empty($this->password)) {
                $userData['password'] = $this->password;
            }

            $user = User::with('student')->findOrFail($this->userId);
            $user->update($userData);

            // ── Student record update ────────────────────────────
            $studentData = [
                'user_id'          => $user->id,

                'session_id'       => $this->session_id,
                'register_no'      => $this->register_no,
                'roll_no'          => $this->roll_no,
                'admission_date'   => $this->admission_date,
                'class_id'         => $this->class_id,
                'section_id'       => $this->section_id,
                'category_id'      => $this->category_id,

                'name'             => $this->name,
                'gender'           => $this->gender,
                'blood_group'      => $this->blood_group,
                'dob'              => $this->dob,
                'religion'         => $this->religion,
                'mobile'           => $this->mobile,
                'email'            => $this->email,
                'present_address'  => $this->present_address,
                'permanent_address'=> $this->permanent_address,

                'previous_school'  => $this->previous_school,
                'qualification'    => $this->qualification,
                'remarks'          => $this->remarks,
            ];

            if ($this->student_photo_upload) {
                $this->deleteOldFile($this->student->photo);
                $studentData['photo'] = \App\Helpers\TenantFileHelper::store($this->student_photo_upload, 'students');
            }

            $this->student->update($studentData);

            // ── Guardian ─────────────────────────────────────────
            if ($this->guardian_exists) {
                $this->student->guardians()->sync([$this->guardian_id]);
            } else {
                $guardianPassword = !empty($this->guardian_password)
                    ? $this->guardian_password
                    : '1234';

                $userGuardian = User::create([
                    'role'     => 'parent',
                    'name'     => $this->guardian_name,
                    'username' => $this->guardian_username,
                    'email'    => $this->guardian_email,
                    'password' => $guardianPassword,
                ]);

                if ($this->student_photo_upload) {
                    $guardian['photo'] = \App\Helpers\TenantFileHelper::store($this->guardian_photo_upload, 'guardians');
                }

                $guardian = Guardian::create([
                    'user_id'     => $userGuardian->id,
                    'name'        => $this->guardian_name,
                    'relation'    => $this->guardian_relation,
                    'father_name' => $this->guardian_father_name,
                    'mother_name' => $this->guardian_mother_name,
                    'occupation'  => $this->guardian_occupation,
                    'income'      => $this->guardian_income,
                    'education'   => $this->guardian_education,
                    'mobile'      => $this->guardian_mobile,
                    'email'       => $this->guardian_email,
                    'address'     => $this->guardian_address,
                ]);

                $this->student->guardians()->sync([$guardian->id]);
            }

            $this->dispatch('date-updated', date: $this->admission_date);
            $this->dispatch('date-updated', date: $this->dob);
            $this->dispatch('toast', type: 'success', message: 'Student updated successfully!');

        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->dispatch('validation-failed');
            $this->dispatch('toast', type: 'error', message: 'An error occurred while creating the parent.');
            throw $e;
        }
    }

    public function render()
    {
        $sessions   = AcademicSession::orderBy('name')->get();
        $classes    = AcademicClass::orderBy('id')->get();
        $sections   = AcademicSection::orderBy('name')->get();
        $categories = AcademicCategory::orderBy('name')->get();
        $guardians  = Guardian::orderBy('name')->get();

        return view('livewire.tenant.accountant.student.student-edit-component')
            ->with('sessions', $sessions)
            ->with('classes', $classes)
            ->with('sections', $sections)
            ->with('categories', $categories)
            ->with('guardians', $guardians)
            ->layout('layouts.accountant.app', [
                'title' => "Edit Student | Monarchy School",
            ]);
    }
}