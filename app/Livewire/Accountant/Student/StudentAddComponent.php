<?php

namespace App\Livewire\Accountant\Student;

use Livewire\Component;
use App\Models\Branch;
use App\Models\User;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\Guardian;
use App\Models\AcademicSession;
use App\Models\AcademicClass;
use App\Models\AcademicClassAssign;
use App\Models\AcademicGroup;
use App\Models\FeeSetup;
use App\Models\FeeInvoice;
use App\Models\FeeInvoiceItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;

use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;
use Livewire\WithFileUploads;

class StudentAddComponent extends Component
{
    use WithFileUploads;

    public $session_id;
    public $registration_no;
    public $student_id;
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

    public bool $guardian_exists = false;
    public bool $is_new_student = true;

    public array $availableSections = [];

    public bool $selectedClassHasSection = true;

    public bool $showFeeModal = false;
    public array $feeItems = [];
    public array $selectedFees = [
        'admission_fee'    => true,
        'registration_fee' => true,
        'monthly_fee'      => true,
    ];

    // User model এ এখনো BelongsToBranch trait নেই, তাই branch_id auto-fill
    // হয় না — এই resolved id সব User::create() কলে explicit পাঠাতে হবে।
    private function resolveActiveBranchId(): ?int
    {
        $user = auth()->user();

        return $user->branch_id
            ?? Branch::resolveMainBranchId($user->institution_id);
    }

    private function generateRegisterNo(): void
    {
        $institutionId = auth()->user()->institution_id;
        $inst = institution();

        $digit     = (int) ($inst->registration_digit_length ?? 6);
        $startFrom = (int) ($inst->registration_start_from ?? 1);

        $prefix = ($inst->enable_registration_prefix && $inst->registration_code_prefix)
            ? $inst->registration_code_prefix
            : 'RG' . str_pad($institutionId, 2, '0', STR_PAD_LEFT);

        $year = now()->format('y');

        // BranchScope automatically narrows this to the current branch
        // for branch-scoped roles — no manual branch_id filter needed.
        $lastStudent = Student::whereNotNull('registration_no')
            ->orderByDesc('id')
            ->first();

        $serial = $lastStudent
            ? ((int) substr($lastStudent->registration_no, -$digit)) + 1
            : $startFrom;

        $this->registration_no = $prefix . $year . str_pad($serial, $digit, '0', STR_PAD_LEFT);
    }

    private function generateStudentId(): void
    {
        $institutionId = auth()->user()->institution_id;
        $inst = institution();

        $digit     = (int) ($inst->student_id_digit_length ?? 6);
        $startFrom = (int) ($inst->student_id_start_from ?? 1);

        $prefix = ($inst->enable_student_id_prefix && $inst->student_id_code_prefix)
            ? $inst->student_id_code_prefix
            : 'STD' . str_pad($institutionId, 2, '0', STR_PAD_LEFT);

        $year = now()->format('y');

        $lastStudent = Student::whereNotNull('student_id')
            ->orderByDesc('id')
            ->first();

        $serial = $lastStudent
            ? ((int) substr($lastStudent->student_id, -$digit)) + 1
            : $startFrom;

        $this->student_id = $prefix . $year . str_pad($serial, $digit, '0', STR_PAD_LEFT);
    }

    private function generateRollNo($classId): void
    {
        if (!$classId) {
            $this->roll_no = null;
            return;
        }

        $count = Student::where('class_id', $classId)->count();

        $this->roll_no = str_pad($count + 1, 2, '0', STR_PAD_LEFT);
    }

    public function mount()
    {
        $session = AcademicSession::where('is_current', true)->first();
        $this->session_id = $session?->id;

        $group = AcademicGroup::where('is_current', true)->first();
        $this->group_id = $group?->id;

        $this->generateRegisterNo();
        $this->generateStudentId();

        $this->admission_date = now()->format('Y-m-d');
        $this->gender = 'male';

        $this->dispatch('date-updated', date: $this->admission_date);
        $this->dispatch('date-updated', date: $this->dob);
    }

    public function updatedClassId($value): void
    {
        $this->section_id = null;
        $this->availableSections = [];
        $this->selectedClassHasSection = true;

        if (!$value) {
            $this->roll_no = null;
            return;
        }

        $institutionId = auth()->user()->institution_id;

        $class = AcademicClass::with('sections')
            ->where('institution_id', $institutionId)
            ->find($value);

        if ($class) {
            $this->selectedClassHasSection = (bool) $class->has_section;

            if ($this->selectedClassHasSection) {
                $this->availableSections = $class->sections
                    ->map(fn($s) => ['id' => $s->id, 'name' => $s->name])
                    ->values()
                    ->toArray();
            }
        }

        $this->generateRollNo($value);
    }

    public function updatedGuardianExists($value): void
    {
        if ($value) {
            $this->reset([
                'guardian_name',
                'guardian_relation',
                'guardian_father_name',
                'guardian_mother_name',
                'guardian_occupation',
                'guardian_income',
                'guardian_education',
                'guardian_mobile',
                'guardian_email',
                'guardian_address',
                'guardian_username',
                'guardian_password',
                'guardian_photo_upload',
            ]);
        } else {
            $this->reset(['guardian_id']);
        }

        $this->resetValidation([
            'guardian_id',
            'guardian_name',
            'guardian_relation',
            'guardian_username',
        ]);
    }

    public function rules()
    {
        $institutionId = auth()->user()->institution_id;
        $branchId      = $this->resolveActiveBranchId();

        return [
            'session_id'  => 'required',

            'registration_no' => [
                'nullable',
                Rule::unique('students', 'registration_no')
                    ->where(fn($q) => $q
                        ->where('institution_id', $institutionId)
                        ->where('branch_id', $branchId)
                    )
                    ->whereNull('deleted_at'),
            ],

            'student_id' => [
                'nullable',
                Rule::unique('students', 'student_id')
                    ->where(fn($q) => $q
                        ->where('institution_id', $institutionId)
                        ->where('branch_id', $branchId)
                    )
                    ->whereNull('deleted_at'),
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

            'name' => 'required',

            'student_photo_upload' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',

            'username' => 'required|unique:users,username',
            'password' => 'nullable',

            'guardian_id' => $this->guardian_exists ? 'required' : 'nullable',

            'guardian_name'     => !$this->guardian_exists ? 'required' : 'nullable',
            'guardian_relation' => !$this->guardian_exists ? 'required' : 'nullable',

            'guardian_username' => !$this->guardian_exists
                ? ['required', 'unique:users,username', 'different:username']
                : 'nullable',

            'guardian_photo_upload' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',

            'is_new_student' => 'boolean',
        ];
    }

    public function messages()
    {
        return [
            'guardian_username.unique'   => 'This guardian username is already taken. Please choose a different one.',
            'guardian_username.different'=> 'Guardian username must be different from student username.',
            'username.unique'            => 'This student username is already taken. Please choose a different one.',
            'section_id.required'        => 'Please select a section for this class.',
        ];
    }

    public function resetForm()
    {
        $this->reset();

        $session = AcademicSession::where('is_current', true)->first();
        $this->session_id = $session?->id;

        $this->generateRegisterNo();
        $this->generateStudentId();

        $this->admission_date = now()->format('Y-m-d');
        $this->gender = 'male';
        $this->is_new_student = true;

        $this->availableSections = [];
        $this->selectedClassHasSection = true;

        $this->showFeeModal = false;
        $this->feeItems = [];
        $this->selectedFees = [
            'admission_fee'    => true,
            'registration_fee' => true,
            'monthly_fee'      => true,
        ];

        $this->dispatch('date-updated', date: $this->admission_date);
        $this->dispatch('date-updated', date: $this->dob);
    }

    protected function failedValidation($validator)
    {
        $this->dispatch('validation-failed');

        throw (new ValidationException($validator))
            ->errorBag($this->getErrorBag());
    }

    public function updated($propertyName)
    {
        if (str_starts_with($propertyName, 'selectedFees')) {
            return;
        }
        $this->validateOnly($propertyName, $this->rules(), $this->messages());
    }

    public function openFeeConfirmModal()
    {
        $this->validate($this->rules(), $this->messages());

        if (!$this->is_new_student) {
            $this->save();
            return;
        }

        $this->loadFeeItems();
        $this->showFeeModal = true;
    }

    private function loadFeeItems(): void
    {
        $institutionId = auth()->user()->institution_id;

        $this->feeItems = [];

        $admissionFee = FeeSetup::with('feeType')
            ->where('institution_id', $institutionId)
            ->where('class_id', $this->class_id)
            ->where('frequency', 'yearly')
            ->where('status', true)
            ->whereHas('feeType', fn($q) => $q->where('name', 'like', '%admission%'))
            ->first();

        if ($admissionFee) {
            $this->feeItems['admission_fee'] = [
                'fee_setup_id' => $admissionFee->id,
                'label'        => $admissionFee->feeType->name,
                'amount'       => (float) $admissionFee->amount,
            ];
        }

        $registrationFee = FeeSetup::with('feeType')
            ->where('institution_id', $institutionId)
            ->where('class_id', $this->class_id)
            ->where('frequency', 'one_time')
            ->where('status', true)
            ->whereHas('feeType', fn($q) => $q->where('name', 'like', '%registration%'))
            ->first();

        if ($registrationFee) {
            $this->feeItems['registration_fee'] = [
                'fee_setup_id' => $registrationFee->id,
                'label'        => $registrationFee->feeType->name,
                'amount'       => (float) $registrationFee->amount,
            ];
        }

        $monthlyFee = FeeSetup::with('feeType')
            ->where('institution_id', $institutionId)
            ->where('class_id', $this->class_id)
            ->where('frequency', 'monthly')
            ->where('status', true)
            ->first();

        if ($monthlyFee) {
            $this->feeItems['monthly_fee'] = [
                'fee_setup_id' => $monthlyFee->id,
                'label'        => $monthlyFee->feeType->name . ' (' . now()->format('F, Y') . ')',
                'amount'       => (float) $monthlyFee->amount,
            ];
        }

        foreach (['admission_fee', 'registration_fee', 'monthly_fee'] as $key) {
            if (!isset($this->feeItems[$key])) {
                $this->selectedFees[$key] = false;
            }
        }
    }

    public function getFeeModalTotalProperty()
    {
        $total = 0;

        foreach ($this->feeItems as $key => $item) {
            if ($this->selectedFees[$key] ?? false) {
                $total += $item['amount'];
            }
        }

        return $total;
    }

    public function closeFeeModal()
    {
        $this->showFeeModal = false;
    }

    public function save()
    {
        DB::beginTransaction();

        try {
            $this->validate($this->rules(), $this->messages());

            $institutionId = auth()->user()->institution_id;
            $branchId      = $this->resolveActiveBranchId();

            $userPassword = $this->password ?: '12345678';

            // User model এ এখনো BelongsToBranch trait নেই, তাই auto-fill হয় না।
            $user = User::create([
                'institution_id' => $institutionId,
                'branch_id'      => $branchId,
                'role'     => 'student',
                'name'     => $this->name,
                'username' => $this->username,
                'email'    => $this->email,
                'password' => $userPassword,
                'is_verified' => true,
            ]);

            $studentPhotoPath = $this->student_photo_upload
                ? $this->student_photo_upload->store('students', 'public')
                : null;

            $guardianPhotoPath = $this->guardian_photo_upload
                ? $this->guardian_photo_upload->store('guardians', 'public')
                : null;

            $inst = institution();
            $stdDigit     = (int) ($inst->student_id_digit_length ?? 6);
            $stdStartFrom = (int) ($inst->student_id_start_from ?? 1);

            $stdPrefix = ($inst->enable_student_id_prefix && $inst->student_id_code_prefix)
                ? $inst->student_id_code_prefix
                : 'STD' . str_pad($institutionId, 2, '0', STR_PAD_LEFT);

            $year = now()->format('y');

            // BranchScope narrows this automatically for branch-scoped roles.
            $lastStudentForId = Student::whereNotNull('student_id')
                ->lockForUpdate()
                ->orderByDesc('id')
                ->first();

            $stdSerial = $lastStudentForId
                ? ((int) substr($lastStudentForId->student_id, -$stdDigit)) + 1
                : $stdStartFrom;

            $studentId = $stdPrefix . $year . str_pad($stdSerial, $stdDigit, '0', STR_PAD_LEFT);

            $regDigit     = (int) ($inst->registration_digit_length ?? 6);
            $regStartFrom = (int) ($inst->registration_start_from ?? 1);

            $regPrefix = ($inst->enable_registration_prefix && $inst->registration_code_prefix)
                ? $inst->registration_code_prefix
                : 'RG' . str_pad($institutionId, 2, '0', STR_PAD_LEFT);

            $lastStudentForReg = Student::whereNotNull('registration_no')
                ->lockForUpdate()
                ->orderByDesc('id')
                ->first();

            $regSerial = $lastStudentForReg
                ? ((int) substr($lastStudentForReg->registration_no, -$regDigit)) + 1
                : $regStartFrom;

            $registrationNo = $regPrefix . $year . str_pad($regSerial, $regDigit, '0', STR_PAD_LEFT);

            $rollSerial = Student::where('class_id', $this->class_id)
                ->lockForUpdate()
                ->count();

            $rollNo = $this->roll_no ?: str_pad($rollSerial + 1, 2, '0', STR_PAD_LEFT);

            $sectionId = $this->selectedClassHasSection ? ($this->section_id ?: null) : null;

            $student = Student::create([
                'user_id' => $user->id,

                'session_id'     => $this->session_id,
                'student_id'     => $studentId,
                'registration_no'=> $registrationNo,
                'roll_no'        => $rollNo,
                'admission_date' => $this->admission_date,
                'class_id'       => $this->class_id,
                'section_id'     => $sectionId,
                'group_id'       => $this->group_id,

                'name'              => $this->name,
                'gender'            => $this->gender,
                'blood_group'       => $this->blood_group,
                'dob'               => $this->dob,
                'religion'          => $this->religion,
                'mobile'            => $this->mobile,
                'email'             => $this->email,
                'present_address'   => $this->present_address,
                'permanent_address' => $this->permanent_address,
                'photo'             => $studentPhotoPath,

                'previous_institution' => $this->previous_institution,
                'qualification'        => $this->qualification,
                'remarks'              => $this->remarks,
            ]);

            StudentEnrollment::create([
                'institution_id'      => $institutionId,
                'student_id'          => $student->id,
                'class_id'            => $this->class_id,
                'section_id'          => $sectionId,
                'group_id'            => $this->group_id,
                'roll_no'             => $rollNo,
                'status'              => 'running',
                'carry_forward_due'   => false,
            ]);

            if ($this->guardian_exists) {

                $student->guardians()->syncWithoutDetaching([
                    $this->guardian_id => [
                        'institution_id' => $institutionId,
                    ]
                ]);

            } else {

                $guardianPassword = $this->guardian_password ?: '12345678';

                $userGuardian = User::create([
                    'institution_id' => $institutionId,
                    'branch_id'      => $branchId,
                    'role'     => 'parent',
                    'name'     => $this->guardian_name,
                    'username' => $this->guardian_username,
                    'email'    => $this->guardian_email,
                    'password' => $guardianPassword,
                    'is_verified' => true,
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
                    'institution_id' => $institutionId,
                ]);
            }

            $invoice = null;

            if ($this->is_new_student && !empty($this->feeItems)) {
                $invoice = $this->generateFeeInvoice($student, $institutionId);
            }

            DB::commit();

            $this->showFeeModal = false;

            if ($invoice) {
                $this->dispatch('toast', type: 'success', message: 'Student created successfully!');

                return redirect()->route('accountant.students.payment-collect', ['invoice' => $invoice->id]);
            }

            $this->resetForm();
            $this->dispatch('date-updated', date: $this->admission_date);
            $this->dispatch('date-updated', date: $this->dob);
            $this->dispatch('toast', type: 'success', message: 'Student created successfully!');

        } catch (ValidationException $e) {

            DB::rollBack();
            $this->showFeeModal = false;
            throw $e;

        } catch (QueryException $e) {

            DB::rollBack();

            if ((int) $e->getCode() === 23000 || str_contains($e->getMessage(), '1062')) {

                $this->showFeeModal = false;

                if (str_contains($e->getMessage(), 'users_username_unique')) {
                    $this->addError('username', 'This username is already taken. Please choose a different one.');
                    $this->addError('guardian_username', 'This username is already taken. Please choose a different one.');
                } elseif (str_contains($e->getMessage(), 'students_registration_no')) {
                    $this->addError('registration_no', 'This registration number is already used. Please refresh and try again.');
                } elseif (str_contains($e->getMessage(), 'students_student_id')) {
                    $this->addError('student_id', 'This student ID is already used. Please refresh and try again.');
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

    private function generateFeeInvoice(Student $student, int $institutionId): ?FeeInvoice
    {
        $selectedItems = [];

        foreach ($this->feeItems as $key => $item) {
            if ($this->selectedFees[$key] ?? false) {
                $selectedItems[] = $item;
            }
        }

        if (empty($selectedItems)) {
            return null;
        }

        $subtotal = collect($selectedItems)->sum('amount');

        $invoiceNo = $this->generateInvoiceNo($institutionId);

        $invoice = FeeInvoice::create([
            'institution_id'  => $institutionId,
            'student_id'      => $student->id,
            'invoice_no'      => $invoiceNo,
            'subtotal'        => $subtotal,
            'discount_amount' => 0,
            'fine_amount'     => 0,
            'total_amount'    => $subtotal,
            'paid_amount'     => 0,
            'due_amount'      => $subtotal,
            'invoice_date'    => $this->admission_date,
            'due_date'        => now()->parse($this->admission_date)->addDays(7)->format('Y-m-d'),
            'payment_status'  => 'unpaid',
            'status'          => true,
        ]);

        foreach ($selectedItems as $item) {
            FeeInvoiceItem::create([
                'institution_id'  => $institutionId,
                'fee_invoice_id'  => $invoice->id,
                'fee_setup_id'    => $item['fee_setup_id'],
                'base_amount'     => $item['amount'],
                'fine_amount'     => 0,
                'discount_amount' => 0,
                'total_amount'    => $item['amount'],
            ]);
        }

        return $invoice;
    }

    private function generateInvoiceNo(int $institutionId): string
    {
        $prefix = 'INV' . str_pad($institutionId, 2, '0', STR_PAD_LEFT) . now()->format('ym');

        $lastInvoice = FeeInvoice::where('institution_id', $institutionId)
            ->where('invoice_no', 'like', $prefix . '%')
            ->lockForUpdate()
            ->orderByDesc('id')
            ->first();

        $serial = $lastInvoice
            ? ((int) substr($lastInvoice->invoice_no, -5)) + 1
            : 1;

        return $prefix . str_pad($serial, 5, '0', STR_PAD_LEFT);
    }

    public function render()
    {
        $institutionId = auth()->user()->institution_id;

        $sessions = AcademicSession::orderBy('name')->get();

        $assignedClassIds = AcademicClassAssign::where('institution_id', $institutionId)
            ->pluck('class_id')
            ->unique();

        $classes = AcademicClass::whereIn('id', $assignedClassIds)
            ->orderBy('id')
            ->get();

        $groups = AcademicGroup::orderBy('name')
            ->where('is_status', true)
            ->get();

        $guardians = Guardian::all();

        return view('livewire.admin.student.student-add-component', [
            'sessions'  => $sessions,
            'classes'   => $classes,
            'groups'    => $groups,
            'guardians' => $guardians,
        ])->layout('layouts.accountant.app', [
            'title' => 'Create Admission | ' . institution()->name,
        ]);
    }
}