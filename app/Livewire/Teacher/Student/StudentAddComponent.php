<?php

namespace App\Livewire\Teacher\Student;

use Livewire\Component;
use App\Models\User;
use App\Models\Student;
use App\Models\Guardian;
use App\Models\AcademicSession;
use App\Models\AcademicClass;
use App\Models\AcademicSection;
use App\Models\AcademicGroup;
use Illuminate\Support\Facades\DB;

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
    public $group_id;

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

    public $guardian_id;
    public $guardian_name, $guardian_relation;
    public $guardian_father_name, $guardian_mother_name;
    public $guardian_occupation, $guardian_income, $guardian_education;
    public $guardian_mobile, $guardian_email;
    public $guardian_address;
    public $guardian_username, $guardian_password;

    public $guardian_photo_upload;

    public $previous_institution;
    public $qualification;
    public $remarks;

    public $studentId;

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
            'session_id'  => 'required',
            'register_no' => 'nullable|unique:students,register_no',
            'class_id'    => 'required',

            'name' => 'required',

            'student_photo_upload'       => 'nullable',

            'username' => 'required|unique:users,username',
            'password' => 'nullable',

            'guardian_id' => $this->guardian_exists ? 'required' : 'nullable',

            'guardian_name'     => !$this->guardian_exists ? 'required' : 'nullable',
            'guardian_relation' => !$this->guardian_exists ? 'required' : 'nullable',
            'guardian_mobile'   => !$this->guardian_exists ? 'required' : 'nullable',
            
            'guardian_username' => !$this->guardian_exists ? 'required|unique:users,username' : 'nullable',

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
        DB::beginTransaction();

        try {

            $this->validate($this->rules());

            // ── Default password
            $userPassword = $this->password ?: '1234';

            // ── Create Student User
            $user = User::create([
                'institution_id' => auth()->user()->institution_id,
                'role'     => 'student',
                'name'     => $this->name,
                'username' => $this->username,
                'email'    => $this->email,
                'password' => $userPassword,
                'is_verified' => TRUE,
            ]);

            // ── Upload Photos
            $studentPhotoPath = $this->student_photo_upload
                ? $this->student_photo_upload->store('students', 'public')
                : null;

            $guardianPhotoPath = $this->guardian_photo_upload
                ? $this->guardian_photo_upload->store('guardians', 'public')
                : null;

            // ── Generate Student ID (SAFE - avoid duplicate)
            $institutionId = auth()->user()->institution_id;
            $institutionCode = 'SCH' . str_pad($institutionId, 2, '0', STR_PAD_LEFT);
            $year = now()->format('y');

            $lastStudent = Student::where('institution_id', $institutionId)
                ->lockForUpdate()
                ->orderByDesc('id')
                ->first();

            $serial = $lastStudent
                ? ((int) substr($lastStudent->student_id, -6)) + 1
                : 1;

            $studentId = $institutionCode . $year . str_pad($serial, 6, '0', STR_PAD_LEFT);

            // ── Create Student
            $student = Student::create([
                'user_id'         => $user->id,

                'session_id'      => $this->session_id,
                'student_id'      => $studentId,
                'register_no'     => $this->register_no,
                'roll_no'         => $this->roll_no,
                'admission_date'  => $this->admission_date,
                'class_id'        => $this->class_id,
                'section_id'      => $this->section_id,
                'group_id'        => $this->group_id,

                'name'            => $this->name,
                'gender'          => $this->gender,
                'blood_group'     => $this->blood_group,
                'dob'             => $this->dob,
                'religion'        => $this->religion,
                'mobile'          => $this->mobile,
                'email'           => $this->email,
                'present_address' => $this->present_address,
                'permanent_address' => $this->permanent_address,
                'photo'           => $studentPhotoPath,

                'previous_institution'  => $this->previous_institution,
                'qualification'    => $this->qualification,
                'remarks'          => $this->remarks,
            ]);

            // ── Guardian Logic
            if ($this->guardian_exists) {

                $student->guardians()->syncWithoutDetaching([
                    $this->guardian_id => [
                        'institution_id' => auth()->user()->institution_id
                    ]
                ]);

            } else {

                $guardianPassword = $this->guardian_password ?: '1234';

                $userGuardian = User::create([
                    'institution_id' => auth()->user()->institution_id,
                    'role'     => 'parent',
                    'name'     => $this->guardian_name,
                    'username' => $this->guardian_username,
                    'email'    => $this->guardian_email,
                    'password' => $guardianPassword,
                    'is_verified' => TRUE,
                ]);

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
                    'photo'       => $guardianPhotoPath,
                ]);

                $student->guardians()->attach($guardian->id, [
                    'institution_id' => auth()->user()->institution_id
                ]);
            }

            DB::commit();

            $this->resetForm();

            $this->dispatch('date-updated', date: $this->admission_date);
            $this->dispatch('date-updated', date: $this->dob);

            $this->dispatch('toast', type: 'success', message: 'Student created successfully!');

        } catch (\Throwable $e) {

            DB::rollBack();

            $this->dispatch('toast', type: 'error', message: 'Something went wrong!');
            throw $e;
        }
    }
    
    public function render()
    {
        $sessions = AcademicSession::orderBy('name')->get();
        $classes = AcademicClass::orderBy('id')->get();
        $sections = AcademicSection::orderBy('name')->get();
        $groups = AcademicGroup::orderBy('name')->get();
        $guardians = Guardian::all();

        return view('livewire.teacher.student.student-add-component')
        ->with('sessions', $sessions)
        ->with('classes', $classes)
        ->with('sections', $sections)
        ->with('groups', $groups)
        ->with('guardians', $guardians)
        ->layout('layouts.teacher.app', [
            'title' => 'Create Admission | ' . institution()->name,
        ]);
    }
}