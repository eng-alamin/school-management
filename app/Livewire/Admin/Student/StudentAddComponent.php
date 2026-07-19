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
use App\Models\FeePayment;
use App\Models\OfficeAccount;
use Illuminate\Support\Facades\DB;

use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;
use Livewire\WithFileUploads;

class StudentAddComponent extends Component
{
    use WithFileUploads;

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

    // ── STEP 2: Payment Collect Modal (invoice toiri howar por) ──
    public bool $showPaymentModal = false;
    public $createdInvoiceId;
    public $createdInvoiceNo;
    public $createdInvoiceDue = 0;
    public $payAmount = 0;
    public string $paymentMethod = 'cash';
    public $paymentDate;
    public $officeAccountId;
    public $paymentRemarks;

    public function mount()
    {
        $session = AcademicSession::where('is_current', true)->first();
        $this->session_id = $session?->id;

        $group = AcademicGroup::where('is_current', true)->first();
        $this->group_id = $group?->id;

        $this->generateRegisterNo();

        $this->admission_date = now()->format('Y-m-d');
        $this->paymentDate = now()->format('Y-m-d');
        $this->gender = 'male';

        $this->dispatch('date-updated', date: $this->admission_date);
        $this->dispatch('date-updated', date: $this->dob);
    }

    private function generateRegisterNo(): void
    {
        $institutionId = auth()->user()->institution_id;
        $institutionCode = 'RG' . str_pad($institutionId, 2, '0', STR_PAD_LEFT);
        $year = now()->format('y');

        $lastStudent = Student::where('institution_id', $institutionId)
            ->lockForUpdate()
            ->orderByDesc('id')
            ->first();

        $serial = $lastStudent
            ? ((int) substr($lastStudent->student_id, -6)) + 1
            : 1;

        $this->registration_no = $institutionCode . $year . str_pad($serial, 6, '0', STR_PAD_LEFT);
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

    private function paymentRules(): array
    {
        return [
            'payAmount'      => 'required|numeric|min:0.01|max:' . $this->createdInvoiceDue,
            'paymentMethod'  => 'required|in:cash,bkash,nagad,bank,cheque',
            'paymentDate'    => 'required|date',
            'officeAccountId' => 'nullable',
        ];
    }

    public function resetForm()
    {
        $this->reset();

        $session = AcademicSession::where('is_current', true)->first();
        $this->session_id = $session?->id;

        $this->generateRegisterNo();

        $this->admission_date = now()->format('Y-m-d');
        $this->paymentDate = now()->format('Y-m-d');
        $this->gender = 'male';
        $this->is_new_student = true;

        $this->showFeeModal = false;
        $this->feeItems = [];
        $this->selectedFees = [
            'admission_fee'    => true,
            'registration_fee' => true,
            'monthly_fee'      => true,
        ];

        $this->showPaymentModal = false;
        $this->createdInvoiceId = null;
        $this->createdInvoiceNo = null;
        $this->createdInvoiceDue = 0;
        $this->payAmount = 0;
        $this->paymentMethod = 'cash';
        $this->officeAccountId = null;
        $this->paymentRemarks = null;

        $this->dispatch('date-updated', date: $this->admission_date);
        $this->dispatch('date-updated', date: $this->dob);
    }

    protected function failedValidation($validator)
    {
        $this->dispatch('validation-failed');
    }

    public function updated($propertyName)
    {
        if (str_starts_with($propertyName, 'selectedFees') || str_starts_with($propertyName, 'paymentRows')) {
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
     * Invoice toiri hole direct form reset na kore, STEP 2 Payment Modal open kore.
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

            $rollSerial = Student::where('institution_id', $institutionId)
                ->where('class_id', $this->class_id)
                ->lockForUpdate()
                ->count();

            $rollNo = $this->roll_no ?: str_pad($rollSerial + 1, 2, '0', STR_PAD_LEFT);

            $student = Student::create([
                'user_id' => $user->id,

                'session_id'     => $this->session_id,
                'student_id'     => $studentId,
                'registration_no'    => $this->registration_no,
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
                // ── Invoice toiri hoyeche - Payment Collect Modal open koro ──
                $this->createdInvoiceId  = $invoice->id;
                $this->createdInvoiceNo  = $invoice->invoice_no;
                $this->createdInvoiceDue = (float) $invoice->due_amount;
                $this->payAmount         = (float) $invoice->due_amount;

                $this->showPaymentModal = true;

                $this->dispatch('toast', type: 'success', message: 'Student created successfully!');
            } else {
                $this->resetForm();
                $this->dispatch('date-updated', date: $this->admission_date);
                $this->dispatch('date-updated', date: $this->dob);
                $this->dispatch('toast', type: 'success', message: 'Student created successfully!');
            }

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

    /**
     * STEP 2: Payment Modal theke "Skip / Later" -> Payment na kore Form reset kore dao.
     */
    public function skipPayment()
    {
        $this->resetForm();
        $this->dispatch('date-updated', date: $this->admission_date);
        $this->dispatch('date-updated', date: $this->dob);
    }

    /**
     * STEP 2: Payment Modal theke "Confirm Payment" -> fee_payments e record hobe,
     * invoice paid_amount/due_amount/payment_status update hobe, tarpor print dispatch hobe.
     */
    public function confirmPayment()
    {
        $this->validate($this->paymentRules());

        DB::beginTransaction();

        try {

            $invoice = FeeInvoice::findOrFail($this->createdInvoiceId);
            $institutionId = auth()->user()->institution_id;

            FeePayment::create([
                'institution_id'   => $institutionId,
                'fee_invoice_id'   => $invoice->id,
                'student_id'       => $invoice->student_id,
                'amount'           => $this->payAmount,
                'payment_method'           => $this->paymentMethod,
                'payment_date'     => $this->paymentDate,
                'office_account_id' => $this->officeAccountId ?: null,
                'remarks'          => $this->paymentRemarks,
            ]);

            $newPaid = (float) $invoice->paid_amount + (float) $this->payAmount;
            $newDue  = (float) $invoice->total_amount - $newPaid;

            $invoice->update([
                'paid_amount'    => $newPaid,
                'due_amount'     => max($newDue, 0),
                'payment_status' => $newDue <= 0 ? 'paid' : 'partial',
            ]);

            DB::commit();

            // ── Print korar jonno dorkari data pathai ──
            $this->dispatch('open-invoice-print', [
            'invoiceNo'    => $invoice->invoice_no,
            'studentName'  => $invoice->student->name,
            'paymentDate'  => \Carbon\Carbon::parse($this->paymentDate)->format('d.M.Y'),
            'totalAmount'  => number_format($invoice->total_amount, 0),
            'paidAmount'   => number_format($invoice->paid_amount, 0),
            'dueAmount'    => number_format($invoice->due_amount, 0),
            'items'        => $invoice->items->map(function ($item) use ($invoice) {
                $isMonthly  = $item->feeSetup?->frequency === 'monthly';
                $monthLabel = $isMonthly
                    ? \Carbon\Carbon::parse($invoice->invoice_date)->format('F')
                    : null;

                return [
                    'feeTypeName' => $item->feeSetup?->feeType?->name ?? '—',
                    'monthLabel'  => $monthLabel,
                    'amount'      => number_format($item->base_amount, 0),
                    'discount'    => number_format($item->discount_amount, 0),
                    'fine'        => number_format($item->fine_amount, 0),
                ];
            })->toArray(),
        ]);

            $this->dispatch('toast', type: 'success', message: 'Payment collected successfully!');

            $this->resetForm();

            $this->dispatch('date-updated', date: $this->admission_date);
            $this->dispatch('date-updated', date: $this->dob);

        } catch (\Throwable $e) {

            DB::rollBack();
            $this->dispatch('toast', type: 'error', message: 'Payment failed!');
            throw $e;
        }
    }

    public function closePaymentModal()
    {
        // Modal bondho korle o student already save hoye geche, tai form reset kore dao
        $this->resetForm();
        $this->dispatch('date-updated', date: $this->admission_date);
        $this->dispatch('date-updated', date: $this->dob);
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
        $officeAccounts = OfficeAccount::orderBy('name')->get();

        return view('livewire.admin.student.student-add-component')
            ->with('sessions', $sessions)
            ->with('classes', $classes)
            ->with('groups', $groups)
            ->with('guardians', $guardians)
            ->with('officeAccounts', $officeAccounts)
            ->layout('layouts.admin.app', [
                'title' => 'Create Admission | ' . institution()->name,
            ]);
    }
}