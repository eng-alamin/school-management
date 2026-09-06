<?php

namespace App\Livewire\ITSupport\Student;

use Livewire\Component;
use App\Models\Branch;
use App\Models\User;
use App\Models\Student;
use App\Models\Guardian;
use App\Models\AcademicSession;
use App\Models\AcademicClass;
use App\Models\AcademicSection;
use App\Models\AcademicGroup;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

use Illuminate\Validation\ValidationException;
use Illuminate\Database\QueryException;
use Illuminate\Validation\Rule;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;

class StudentEditComponent extends Component
{
    use WithFileUploads;

    public $studentId;
    public $userId;
    public $student;
    public $guardian;

    public $session_id;
    public $registration_no;
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

    public $student_photo;
    public $student_photo_upload;

    public $username;
    public $password;

    public bool $usernameManuallyEdited = true;

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

    public bool $guardianUsernameManuallyEdited = true;

    public $previous_institution;
    public $qualification;
    public $remarks;

    public bool $guardian_exists = false;

    public array $availableSections = [];
    public bool $selectedClassHasSection = true;

    private const DEFAULT_GUARDIAN_PASSWORD = '12345678';

    private function resolveActiveBranchId(): ?int
    {
        $user = auth()->user();

        return $user->branch_id
            ?? Branch::resolveMainBranchId($user->institution_id);
    }

    public function mount($id)
    {
        $institutionId = auth()->user()->institution_id;

        $this->student = Student::with('user', 'guardians')
            ->where('institution_id', $institutionId)
            ->findOrFail($id);

        $this->studentId = $this->student->id;

        $this->userId = $this->student->user_id;

        // Academic
        $this->session_id     = $this->student->session_id;
        $this->registration_no    = $this->student->registration_no;
        $this->roll_no        = $this->student->roll_no;
        $this->admission_date = $this->student->admission_date;
        $this->class_id       = $this->student->class_id;
        $this->section_id     = $this->student->section_id;
        $this->group_id       = $this->student->group_id;

        $this->loadSectionsForClass($this->class_id);

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
            $this->guardian_photo  = $this->guardian->photo;
        }

        // Previous Institution
        $this->previous_institution = $this->student->previous_institution;
        $this->qualification   = $this->student->qualification;
        $this->remarks         = $this->student->remarks;

        $this->dispatch('date-updated', field: 'admission_date', date: $this->admission_date);
        $this->dispatch('date-updated', field: 'dob', date: $this->dob);
    }

    private function loadSectionsForClass($classId): void
    {
        $this->availableSections = [];
        $this->selectedClassHasSection = true;

        if (!$classId) {
            return;
        }

        $institutionId = auth()->user()->institution_id;

        $class = AcademicClass::with('sections')
            ->where('institution_id', $institutionId)
            ->find($classId);

        if ($class) {
            $this->selectedClassHasSection = (bool) $class->has_section;

            if ($this->selectedClassHasSection) {
                $this->availableSections = $class->sections
                    ->map(fn($s) => ['id' => $s->id, 'name' => $s->name])
                    ->values()
                    ->toArray();
            }
        }
    }

    public function updatedClassId($value): void
    {
        $this->section_id = null;
        $this->loadSectionsForClass($value);
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

    public function updatedGuardianName($value): void
    {
        if (!$this->guardian_exists && !$this->guardianUsernameManuallyEdited) {
            $this->guardian_username = $this->generateUniqueUsername($value);
        }
    }

    public function updatedGuardianUsername(): void
    {
        $this->guardianUsernameManuallyEdited = true;
    }

    public function enableGuardianUsernameAutoSuggest(): void
    {
        $this->guardianUsernameManuallyEdited = false;
        $this->guardian_username = $this->generateUniqueUsername($this->guardian_name);
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

    public function rules()
    {
        $institutionId = auth()->user()->institution_id;

        $branchId = $this->student->branch_id
            ?? Branch::resolveMainBranchId($institutionId);

        return [
            'session_id'  => 'required',

            'registration_no' => [
                'nullable',
                Rule::unique('students', 'registration_no')
                    ->ignore($this->studentId)
                    ->where(fn($q) => $q
                        ->where('institution_id', $institutionId)
                        ->where('branch_id', $branchId)
                    ),
            ],

            'class_id' => [
                'required',
                Rule::exists('academic_classes', 'id')
                    ->where(fn($q) => $q->where('institution_id', $institutionId)),
            ],

            'section_id' => [
                Rule::requiredIf($this->selectedClassHasSection),
                'nullable',
                Rule::exists('academic_sections', 'id')
                    ->where(fn($q) => $q->where('institution_id', $institutionId)),
            ],

            'name'        => 'required',
            'gender'      => 'nullable|in:male,female,other',
            'blood_group' => 'nullable|in:A+,A-,B+,B-,AB+,AB-,O+,O-',
            'dob'         => 'nullable|date|before:today',
            'religion'    => 'nullable|in:muslim,hindu,christian,buddhist',
            'mobile'      => 'nullable|digits_between:10,15',
            'email'       => ['nullable', 'email', Rule::unique('users', 'email')->ignore($this->userId)],

            'admission_date' => 'required|date',

            'student_photo_upload'       => 'nullable|image|mimes:jpeg,png,jpg|max:2048',

            'username'    => ['required', Rule::unique('users', 'username')->ignore($this->userId)],
            'password'    => 'nullable',

            'guardian_id' => $this->guardian_exists
                ? [
                    'required',
                    Rule::exists('guardians', 'id')->where(function ($q) use ($institutionId) {
                        $q->whereIn(
                            'user_id',
                            User::where('institution_id', $institutionId)->pluck('id')
                        );
                    }),
                ]
                : 'nullable',

            'guardian_name'     => !$this->guardian_exists ? 'required' : 'nullable',
            'guardian_relation' => !$this->guardian_exists ? 'required' : 'nullable',
            'guardian_mobile'   => !$this->guardian_exists ? 'required|digits_between:10,15' : 'nullable|digits_between:10,15',
            'guardian_email'    => !$this->guardian_exists ? 'required|email|unique:users,email' : 'nullable|email',

            'guardian_username' => !$this->guardian_exists ? ['required', Rule::unique('users', 'username')->ignore($this->userId)] : 'nullable',

            'guardian_photo_upload'       => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ];
    }

    public function messages()
    {
        return [
            'section_id.required' => 'Please select a section for this class.',
        ];
    }

    protected function failedValidation($validator)
    {
        $this->dispatch('validation-failed');

        throw (new ValidationException($validator))
            ->errorBag($this->getErrorBag());
    }

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName, $this->rules(), $this->messages());
    }

    public function update()
    {
        DB::beginTransaction();

        $oldStudentPhotoToDelete  = null;
        $oldGuardianPhotoToDelete = null;

        try {

            $this->validate($this->rules(), $this->messages());

            $institutionId = auth()->user()->institution_id;

            $user = User::where('institution_id', $institutionId)
                ->findOrFail($this->userId);

            $userData = [
                'name'     => $this->name,
                'username' => $this->username,
                'email'    => $this->email,
            ];

            if (!empty($this->password)) {
                $userData['password'] = $this->password;
            }

            $user->update($userData);

            $sectionId = $this->selectedClassHasSection ? ($this->section_id ?: null) : null;

            $studentData = [
                'user_id'          => $user->id,

                'session_id'       => $this->session_id,
                'registration_no'  => $this->registration_no,
                'roll_no'          => $this->roll_no,
                'admission_date'   => $this->admission_date,
                'class_id'         => $this->class_id,
                'section_id'       => $sectionId,
                'group_id'         => $this->group_id,

                'name'             => $this->name,
                'gender'           => $this->gender,
                'blood_group'      => $this->blood_group,
                'dob'              => $this->dob,
                'religion'         => $this->religion,
                'mobile'           => $this->mobile,
                'email'            => $this->email,
                'present_address'  => $this->present_address,
                'permanent_address'=> $this->permanent_address,

                'previous_institution'  => $this->previous_institution,
                'qualification'    => $this->qualification,
                'remarks'          => $this->remarks,
            ];

            if ($this->student_photo_upload) {
                $oldStudentPhotoToDelete = $this->student->photo;
                $studentData['photo'] = $this->student_photo_upload->store('students', 'public');
            }

            $this->student->update($studentData);

            activity()
                ->performedOn($this->student)
                ->causedBy(auth()->user())
                ->withProperties(['institution_id' => $institutionId])
                ->log('Student updated');

            if ($this->guardian_exists) {

                $this->student->guardians()->sync([
                    $this->guardian_id => [
                        'institution_id' => $institutionId,
                    ]
                ]);

            } else {

                $guardianPassword = !empty($this->guardian_password)
                    ? $this->guardian_password
                    : self::DEFAULT_GUARDIAN_PASSWORD;

                $userGuardian = User::create([
                    'institution_id' => $institutionId,
                    'branch_id'      => $this->resolveActiveBranchId(),
                    'role'     => 'parent',
                    'name'     => $this->guardian_name,
                    'username' => $this->guardian_username,
                    'email'    => $this->guardian_email,
                    'password' => $guardianPassword,
                    'is_verified' => true,
                ]);

                $guardianData = [
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
                ];

                if ($this->guardian_photo_upload) {
                    $oldGuardianPhotoToDelete = $this->guardian?->photo;
                    $guardianData['photo'] = $this->guardian_photo_upload->store('guardians', 'public');
                }

                $guardian = Guardian::create($guardianData);

                activity()
                    ->performedOn($guardian)
                    ->causedBy(auth()->user())
                    ->withProperties(['institution_id' => $institutionId])
                    ->log('Guardian created (from student edit)');

                $this->student->guardians()->sync([
                    $guardian->id => [
                        'institution_id' => $institutionId,
                    ]
                ]);
            }

            DB::commit();

            if ($oldStudentPhotoToDelete) {
                Storage::disk('public')->delete($oldStudentPhotoToDelete);
            }
            if ($oldGuardianPhotoToDelete) {
                Storage::disk('public')->delete($oldGuardianPhotoToDelete);
            }

            $this->dispatch('date-updated', field: 'admission_date', date: $this->admission_date);
            $this->dispatch('date-updated', field: 'dob', date: $this->dob);

            $this->dispatch('toast', type: 'success', message: 'Student updated successfully!');

        } catch (ValidationException $e) {

            DB::rollBack();
            throw $e;

        } catch (QueryException $e) {

            DB::rollBack();

            if ((int) $e->getCode() === 23000 || str_contains($e->getMessage(), '1062')) {

                if (str_contains($e->getMessage(), 'users_username_unique')) {
                    $this->addError('username', 'This username is already taken. Please choose a different one.');
                    $this->addError('guardian_username', 'This username is already taken. Please choose a different one.');
                } elseif (str_contains($e->getMessage(), 'users_email_unique')) {
                    $this->addError('email', 'This email is already taken. Please choose a different one.');
                    $this->addError('guardian_email', 'This email is already taken. Please choose a different one.');
                } elseif (str_contains($e->getMessage(), 'students_registration_no')) {
                    $this->addError('registration_no', 'This registration number is already used. Please refresh and try again.');
                } else {
                    $this->addError('name', 'A duplicate entry was found. Please check your input and try again.');
                }

                $this->dispatch('validation-failed');
                $this->dispatch('toast', type: 'error', message: 'Duplicate data found. Please check the highlighted fields.');

                return;
            }

            $this->dispatch('toast', type: 'error', message: 'Something went wrong!');
            throw $e;

        } catch (\Throwable $e) {

            DB::rollBack();

            $this->dispatch('toast', type: 'error', message: 'Something went wrong!');
            throw $e;
        }
    }

    public function render()
    {
        $institutionId = auth()->user()->institution_id;

        $sessions   = AcademicSession::orderBy('name')->get();

        $classes    = AcademicClass::where('institution_id', $institutionId)
            ->orderBy('id')
            ->get();

        $groups     = AcademicGroup::where('institution_id', $institutionId)
            ->where('is_status', true)
            ->orderBy('name')
            ->get();

        $guardians  = Guardian::whereHas('user', fn($q) => $q->where('institution_id', $institutionId))
            ->orderBy('name')
            ->get();


        return view('livewire.admin.student.student-edit-component', [
            'sessions'      => $sessions,
            'classes'      => $classes,
            'groups'      => $groups,
            'guardians'      => $guardians,
        ])->layout('layouts.itsupport.app', [
            'title' => 'Edit Student | ' . institution()->name,
        ]);
    }
}