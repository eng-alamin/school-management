<?php

namespace App\Livewire\Admin\Student;

use Livewire\Component;
use App\Models\User;
use App\Models\Student;
use App\Models\Guardian;
use App\Models\AcademicSession;
use App\Models\AcademicClass;
use App\Models\AcademicClassAssign;
use App\Models\AcademicGroup;
use App\Models\FeeSetup;
use App\Models\FeeInvoice;
use App\Models\FeeInvoiceItem;
use Illuminate\Support\Facades\DB;

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

    // ── New / Existing student flag ──
    public bool $is_new_student = true;

    // ── Class -> Section dependent dropdown (academic_class_assigns theke) ──
    public array $availableSections = [];

    // ── STEP 1: Fee Confirmation Modal ──
    public bool $showFeeModal = false;
    public array $feeItems = [];
    public array $selectedFees = [
        'admission_fee'    => true,
        'registration_fee' => true,
        'monthly_fee'      => true,
    ];

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

        $lastStudent = Student::where('institution_id', $institutionId)
            ->whereNotNull('registration_no')
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
            : 'SCH' . str_pad($institutionId, 2, '0', STR_PAD_LEFT);

        $year = now()->format('y');

        $lastStudent = Student::where('institution_id', $institutionId)
            ->whereNotNull('student_id')
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

        $institutionId = auth()->user()->institution_id;

        $count = Student::where('institution_id', $institutionId)
            ->where('class_id', $classId)
            ->count();

        $this->roll_no = str_pad($count + 1, 2, '0', STR_PAD_LEFT);
    }

    public function updatedClassId($value): void
    {
        $this->section_id = null;
        $this->availableSections = [];

        if (!$value) {
            $this->roll_no = null;
            return;
        }

        $institutionId = auth()->user()->institution_id;

        $assigns = AcademicClassAssign::with('section')
            ->where('institution_id', $institutionId)
            ->where('class_id', $value)
            ->get();

        $this->availableSections = $assigns
            ->pluck('section')
            ->filter()
            ->unique('id')
            ->map(fn($s) => ['id' => $s->id, 'name' => $s->name])
            ->values()
            ->toArray();

        $this->generateRollNo($value);
    }

    public function rules()
    {
        return [
            'session_id'  => 'required',
            'registration_no' => 'nullable|unique:students,registration_no',
            'student_id'  => 'nullable|unique:students,student_id',
            'class_id'    => 'required',

            'name' => 'required',

            'student_photo_upload' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',

            'username' => 'required|unique:users,username',
            'password' => 'nullable',

            'guardian_id' => $this->guardian_exists ? 'required' : 'nullable',

            'guardian_name'     => !$this->guardian_exists ? 'required' : 'nullable',
            'guardian_relation' => !$this->guardian_exists ? 'required' : 'nullable',

            'guardian_username' => !$this->guardian_exists ? 'required|unique:users,username' : 'nullable',

            'guardian_photo_upload' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',

            'is_new_student' => 'boolean',
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
    }

    public function updated($propertyName)
    {
        if (str_starts_with($propertyName, 'selectedFees')) {
            return;
        }
        $this->validateOnly($propertyName, $this->rules());
    }

    /**
     * STEP 1: "Save" button e click korle - direct save na kore
     * age form validate kore, fee_setups theke fee load kore Fee Confirm Modal dekhabe.
     */
    public function openFeeConfirmModal()
    {
        $this->validate($this->rules());

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

    /**
     * STEP 1 Confirm & Save -> Student + Invoice create.
     * Invoice toiri hole (new student + fee selected) StudentPaymentCollectComponent
     * page-e redirect kora hoy (invoice id shoho). Invoice na thakle form reset kore dey.
     */
    public function save()
    {
        DB::beginTransaction();

        try {

            $this->validate($this->rules());

            $institutionId = auth()->user()->institution_id;

            $userPassword = $this->password ?: '12345678';

            $user = User::create([
                'institution_id' => $institutionId,
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

            // ── Race-condition safety: lock kore last student dhore
            // student_id ke re-verify/re-generate kora hocche, preview-e generate
            // kora value use na kore ekhane lock-safe kora holo ──
            $inst = institution();
            $stdDigit     = (int) ($inst->student_id_digit_length ?? 6);
            $stdStartFrom = (int) ($inst->student_id_start_from ?? 1);

            $stdPrefix = ($inst->enable_student_id_prefix && $inst->student_id_code_prefix)
                ? $inst->student_id_code_prefix
                : 'SCH' . str_pad($institutionId, 2, '0', STR_PAD_LEFT);

            $year = now()->format('y');

            $lastStudentForId = Student::where('institution_id', $institutionId)
                ->whereNotNull('student_id')
                ->lockForUpdate()
                ->orderByDesc('id')
                ->first();

            $stdSerial = $lastStudentForId
                ? ((int) substr($lastStudentForId->student_id, -$stdDigit)) + 1
                : $stdStartFrom;

            $studentId = $stdPrefix . $year . str_pad($stdSerial, $stdDigit, '0', STR_PAD_LEFT);

            // ── Race-condition safety: registration_no-o ekhon ekivabei
            // lock kore re-verify/re-generate kora hocche, preview-e generate
            // kora value use na kore ──
            $regDigit     = (int) ($inst->registration_digit_length ?? 6);
            $regStartFrom = (int) ($inst->registration_start_from ?? 1);

            $regPrefix = ($inst->enable_registration_prefix && $inst->registration_code_prefix)
                ? $inst->registration_code_prefix
                : 'RG' . str_pad($institutionId, 2, '0', STR_PAD_LEFT);

            $lastStudentForReg = Student::where('institution_id', $institutionId)
                ->whereNotNull('registration_no')
                ->lockForUpdate()
                ->orderByDesc('id')
                ->first();

            $regSerial = $lastStudentForReg
                ? ((int) substr($lastStudentForReg->registration_no, -$regDigit)) + 1
                : $regStartFrom;

            $registrationNo = $regPrefix . $year . str_pad($regSerial, $regDigit, '0', STR_PAD_LEFT);

            $rollSerial = Student::where('institution_id', $institutionId)
                ->where('class_id', $this->class_id)
                ->lockForUpdate()
                ->count();

            $rollNo = $this->roll_no ?: str_pad($rollSerial + 1, 2, '0', STR_PAD_LEFT);

            $student = Student::create([
                'user_id' => $user->id,

                'session_id'     => $this->session_id,
                'student_id'     => $studentId,
                'registration_no'    => $registrationNo,
                'roll_no'        => $rollNo,
                'admission_date' => $this->admission_date,
                'class_id'       => $this->class_id,
                'section_id'     => $this->section_id,
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
                // ── Invoice toiri hoyeche - Payment Collect page e redirect koro ──
                $this->dispatch('toast', type: 'success', message: 'Student created successfully!');

                return redirect()->route('admin.students.payment-collect', ['invoice' => $invoice->id]);
            }

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

        $groups = AcademicGroup::orderBy('name')->get();
        $guardians = Guardian::all();

        return view('livewire.admin.student.student-add-component')
            ->with('sessions', $sessions)
            ->with('classes', $classes)
            ->with('groups', $groups)
            ->with('guardians', $guardians)
            ->layout('layouts.admin.app', [
                'title' => 'Create Admission | ' . institution()->name,
            ]);
    }
}