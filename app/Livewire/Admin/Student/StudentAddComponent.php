<?php

namespace App\Livewire\Admin\Student;

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

class StudentAddComponent extends Component
{
    use WithFileUploads;

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
    public $guardian_username, $guardian_password, $guardian_password_confirmation;

    public $guardian_photo_upload;

    public $previous_school;
    public $qualification;
    public $remarks;


    public bool $guardian_exists = false;

    public function mount()
    {
        $session = AcademicSession::where('is_current', true)->first();
        $this->session_id = $session?->id;

        $this->admission_date = now()->format('Y-m-d');
        $this->gender = 'male';

        $this->dispatch('date-updated', date: $this->admission_date);
        $this->dispatch('date-updated', date: $this->dob);
    }


    public function rules()
    {
        return [
            'session_id' => 'required',
            'register_no' => 'required|unique:students,register_no',
            'name' => 'required',

            'student_photo_upload'       => 'nullable',

            'username' => 'required|unique:users,username',
            'password' => 'nullable|confirmed|min:4',

            'guardian_id' => $this->guardian_exists ? 'required' : 'nullable',

            'guardian_name' => !$this->guardian_exists ? 'required' : 'nullable',
            'guardian_relation' => !$this->guardian_exists ? 'required' : 'nullable',
            'guardian_mobile' => !$this->guardian_exists ? 'required' : 'nullable',
            'guardian_email' => !$this->guardian_exists ? 'required|email' : 'nullable',

            'guardian_photo_upload'       => 'nullable',
        ];
    }

    public function resetForm()
    {
        $this->reset();
    }

    protected function failedValidation($validator)
    {
        $this->dispatch('validation-failed');
    }

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName, $this->rules());
    }

    public function save()
    {
        try {

            $this->validate($this->rules());

            $userPassword = !empty($this->password)
                ? $this->password
                : '1234';

            // ── Student user update
            $userData = [
                'role'     => 'student',
                'name'     => $this->name,
                'username' => $this->username,
                'email'    => $this->email,
                'password' => $userPassword,
            ];

            $user = User::create($userData);

            // Upload photo
            $studentPhotoPath = $this->student_photo_upload
                ? $this->student_photo_upload->store('students', 'public')
                : null;

            $guardianPhotoPath = $this->guardian_photo_upload
                ? $this->guardian_photo_upload->store('students', 'public')
                : null;

            $student = Student::create([
                'user_id'     => $user->id,

                'session_id' => $this->session_id,
                'register_no' => $this->register_no,
                'roll_no' => $this->roll_no,
                'admission_date' => $this->admission_date,
                'class_id' => $this->class_id,
                'section_id' => $this->section_id,
                'category_id' => $this->category_id,

                'name' => $this->name,
                'gender' => $this->gender,
                'blood_group' => $this->blood_group,
                'dob' => $this->dob,
                'religion' => $this->religion,
                'mobile' => $this->mobile,
                'email' => $this->email,
                'present_address' => $this->present_address,
                'permanent_address' => $this->permanent_address,
                'photo' => $studentPhotoPath,
            ]);

            // Guardian
            if ($this->guardian_exists) {
                $student->guardians()->syncWithoutDetaching([
                    $this->guardian_id => ['school_id' => auth()->user()->school_id]
                ]);
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

                $guardian = Guardian::create([
                    'user_id'     => $userGuardian->id,
                    'name' => $this->guardian_name,
                    'relation' => $this->guardian_relation,
                    'father_name' => $this->guardian_father_name,
                    'mother_name' => $this->guardian_mother_name,
                    'occupation' => $this->guardian_occupation,
                    'income' => $this->guardian_income,
                    'education' => $this->guardian_education,
                    'mobile' => $this->guardian_mobile,
                    'email' => $this->guardian_email,
                    'address' => $this->guardian_address,
                    'photo' => $guardianPhotoPath,
                ]);

                $student->guardians()->attach($guardian->id, [
                    'school_id' => auth()->user()->school_id
                ]);
            }

            $this->resetForm();

            $this->dispatch('date-updated', date: $this->admission_date);
            $this->dispatch('date-updated', date: $this->dob);
            $this->dispatch('toast', type: 'success', message: 'Student created successfully!');

        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->dispatch('validation-failed');
            $this->dispatch('toast', type: 'error', message: 'An error occurred while creating the parent.');
            throw $e;
        }
    }
    
    public function render()
    {
        $sessions = AcademicSession::orderBy('name')->get();
        $classes = AcademicClass::orderBy('id')->get();
        $sections = AcademicSection::orderBy('name')->get();
        $categories = AcademicCategory::orderBy('name')->get();
        $guardians = Guardian::all();

        return view('livewire.admin.student.student-add-component')
        ->with('sessions', $sessions)
        ->with('classes', $classes)
        ->with('sections', $sections)
        ->with('categories', $categories)
        ->with('guardians', $guardians)
        ->layout('layouts.admin.app', [
            'title' => "Create Admission | Monarchy School",
        ]);
    }
}