<?php

namespace App\Services;

use App\Models\Admission;
use App\Models\FeeInvoice;
use App\Models\FeeInvoiceItem;
use App\Models\FeeSetup;
use App\Models\Guardian;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Single responsibility: convert an Admission (intake/application data)
 * into permanent Student + Guardian records once approved, and (for new
 * students) generate a Fee Invoice from admin-selected fee items.
 *
 * Kept out of the Livewire component on purpose (SOLID - SRP), so the
 * component only orchestrates UI state while this service owns the
 * business logic and can be unit-tested independently.
 */
class AdmissionService
{
    /**
     * StudentAddComponent::loadFeeItems()-er identical logic — admission_fee
     * (yearly), registration_fee (one_time), monthly_fee (monthly) — jeta
     * jeta active FeeSetup pawa jay sob gulo array-e return kore, jate
     * component ta Fee Confirmation Modal-e checkbox hisebe dekhate pare.
     *
     * @return array<string, array{fee_setup_id: int, label: string, amount: float}>
     */
    public function loadFeeItems(int $institutionId, ?int $classId): array
    {
        if (!$classId) {
            return [];
        }

        $items = [];

        $admissionFee = FeeSetup::with('feeType')
            ->where('institution_id', $institutionId)
            ->where('class_id', $classId)
            ->where('frequency', 'yearly')
            ->where('status', true)
            ->whereHas('feeType', fn($q) => $q->where('name', 'like', '%admission%'))
            ->first();

        if ($admissionFee) {
            $items['admission_fee'] = [
                'fee_setup_id' => $admissionFee->id,
                'label'        => $admissionFee->feeType->name,
                'amount'       => (float) $admissionFee->amount,
            ];
        }

        $registrationFee = FeeSetup::with('feeType')
            ->where('institution_id', $institutionId)
            ->where('class_id', $classId)
            ->where('frequency', 'one_time')
            ->where('status', true)
            ->whereHas('feeType', fn($q) => $q->where('name', 'like', '%registration%'))
            ->first();

        if ($registrationFee) {
            $items['registration_fee'] = [
                'fee_setup_id' => $registrationFee->id,
                'label'        => $registrationFee->feeType->name,
                'amount'       => (float) $registrationFee->amount,
            ];
        }

        $monthlyFee = FeeSetup::with('feeType')
            ->where('institution_id', $institutionId)
            ->where('class_id', $classId)
            ->where('frequency', 'monthly')
            ->where('status', true)
            ->first();

        if ($monthlyFee) {
            $items['monthly_fee'] = [
                'fee_setup_id' => $monthlyFee->id,
                'label'        => $monthlyFee->feeType->name . ' (' . now()->format('F, Y') . ')',
                'amount'       => (float) $monthlyFee->amount,
            ];
        }

        return $items;
    }

    /**
     * "Existing Student" (is_new = false) admission approve — kono fee/invoice
     * lagbe na, tai sorasori Student+Guardian create kore approve kore dey.
     *
     * @return array{student: Student, credentials: array}
     * @throws RuntimeException if the admission is not in a pending state
     */
    public function approveWithoutInvoice(Admission $admission, ?int $reviewerId = null): array
    {
        if ($admission->status !== 'pending') {
            throw new RuntimeException('Only pending admissions can be approved.');
        }

        return DB::transaction(function () use ($admission, $reviewerId) {
            return $this->convertToStudent($admission, $reviewerId);
        });
    }

    /**
     * "New Student" (is_new = true) admission approve — Fee Confirmation
     * Modal theke admin je fee item gulo select korse ($selectedFeeItems),
     * shegulo diye Student create howar shathe shathei ekta Invoice-o
     * generate kore dey (sob ekta shomoi transaction-e).
     *
     * @param array<int, array{fee_setup_id:int, label:string, amount:float}> $selectedFeeItems
     * @return array{student: Student, invoice: ?FeeInvoice, credentials: array}
     * @throws RuntimeException if the admission is not in a pending state
     */
    public function approveWithInvoice(Admission $admission, ?int $reviewerId, array $selectedFeeItems): array
    {
        if ($admission->status !== 'pending') {
            throw new RuntimeException('Only pending admissions can be approved.');
        }

        return DB::transaction(function () use ($admission, $reviewerId, $selectedFeeItems) {
            $result  = $this->convertToStudent($admission, $reviewerId);
            $invoice = $this->generateInvoice($result['student'], $admission, $selectedFeeItems);

            return [
                'student'     => $result['student'],
                'invoice'     => $invoice,
                'credentials' => $result['credentials'],
            ];
        });
    }

    public function reject(Admission $admission, string $reason, ?int $reviewerId = null): Admission
    {
        if ($admission->status !== 'pending') {
            throw new RuntimeException('Only pending admissions can be rejected.');
        }

        $admission->update([
            'status'           => 'rejected',
            'rejection_reason' => $reason,
            'reviewed_by'      => $reviewerId,
            'reviewed_at'      => now(),
        ]);

        activity()
            ->causedBy($reviewerId ? User::find($reviewerId) : null)
            ->performedOn($admission)
            ->withProperties(['icon' => 'block', 'type' => 'admission'])
            ->tap(function ($activity) use ($admission) {
                $activity->institution_id = $admission->institution_id;
            })
            ->log('Admission rejected: ' . $admission->applicant_name);

        return $admission;
    }

    /**
     * Guardian + Student create kore, guardian attach kore, admission status
     * 'approved' e update kore, activity log kore, ebong dujoner (student +
     * guardian) login credentials (username/email/plain-password) return
     * kore — jate approval email-e dekhano jay.
     *
     * @return array{student: Student, credentials: array{
     *     student: array{username: string, email: ?string, password: string},
     *     guardian: array{username: string, email: string, password: ?string, is_new: bool}
     * }}
     */
    private function convertToStudent(Admission $admission, ?int $reviewerId): array
    {
        $guardianResult = $this->findOrCreateGuardian($admission);
        $studentResult  = $this->createStudent($admission, $guardianResult['guardian']);

        $student = $studentResult['student'];

        $student->guardians()->attach($guardianResult['guardian']->id, [
            'institution_id' => $admission->institution_id,
        ]);

        $admission->update([
            'status'      => 'approved',
            'student_id'  => $student->id,
            'reviewed_by' => $reviewerId,
            'reviewed_at' => now(),
        ]);

        activity()
            ->causedBy($reviewerId ? User::find($reviewerId) : null)
            ->performedOn($admission)
            ->withProperties(['icon' => 'how_to_reg', 'type' => 'admission'])
            ->tap(function ($activity) use ($admission) {
                $activity->institution_id = $admission->institution_id;
            })
            ->log('Admission approved and converted to student: ' . $admission->applicant_name);

        return [
            'student' => $student,
            'credentials' => [
                'student' => $studentResult['credentials'],
                'guardian' => [
                    'username' => $guardianResult['username'],
                    'email'    => $guardianResult['email'],
                    'password' => $guardianResult['password'],
                    'is_new'   => $guardianResult['is_new'],
                ],
            ],
        ];
    }

    /**
     * Guardian resolve korar priority — EKJON Guardian-er sontan ALADA
     * ALADA Institution-e porte pare, tai login (User) GLOBAL rakha hoy,
     * kintu prottek Institution-e Guardian-er NIJER ekta row lagbe
     * (InstitutionScope + students relation-er jonno). Priority:
     *
     * 1) Online Admission form-e "Guardian Already Exist" diye parent
     *    jodi kono guardian select kore thake ($admission->guardian_user_id
     *    set thakle — eta ekta GLOBAL users.id, kono nirdishto Institution-e
     *    bound na):
     *      (a) current Institution-e shei user_id diye Guardian row AGE
     *          THEKEI thakle → shudhu shei row-ta reuse kora hoy.
     *      (b) na thakle → NOTUN User na baniye, SHUDHU notun ekta Guardian
     *          row toiri kora hoy ei Institution-er jonno, kintu same
     *          user_id link kore — fole guardian ekই username/password
     *          diye ei notun school-eo dhukte parbe.
     *
     * 2) guardian_user_id na thakle purono behavior: একই email/mobile
     *    দিয়ে guardian (এই Institution-এ) আগে থেকে থাকলে reuse করে —
     *    password `null` return হয়।
     *
     * 3) কোনোটাই না মিললে নতুন User(role=parent) + Guardian তৈরি করে এবং
     *    plain password return করে (mail-এ দেখানোর জন্য)।
     *
     * @return array{guardian: Guardian, username: string, email: string, password: ?string, is_new: bool}
     */
    private function findOrCreateGuardian(Admission $admission): array
    {
        // ── (1) Global guardian identity select kora hoyeche (cross-institution) ──
        if ($admission->guardian_user_id) {
            $guardianUser = User::find($admission->guardian_user_id);

            if ($guardianUser) {
                // (1a) Ei Institution-e already ei user-er Guardian row ache ki na check
                $existingGuardianHere = Guardian::withoutGlobalScopes()
                    ->where('institution_id', $admission->institution_id)
                    ->where('user_id', $guardianUser->id)
                    ->first();

                if ($existingGuardianHere) {
                    return [
                        'guardian' => $existingGuardianHere,
                        'username' => $guardianUser->username,
                        'email'    => $guardianUser->email ?? ($existingGuardianHere->email ?? ''),
                        'password' => null,
                        'is_new'   => false,
                    ];
                }

                // (1b) User globally ache, kintu EI Institution-e Guardian row
                // nai — tai notun User na baniye SHUDHU notun Guardian row
                // toiri kore same user_id link kora hocche. Source data
                // hisebe onno kono Institution-er guardian row (jodi thake)
                // ba admission form-e dewa guardian_* fields use kora hocche.
                $anyGuardianProfile = Guardian::withoutGlobalScopes()
                    ->where('user_id', $guardianUser->id)
                    ->first();

                $newGuardianForThisInstitution = Guardian::create([
                    'institution_id' => $admission->institution_id,
                    'user_id'        => $guardianUser->id,
                    'name'           => $anyGuardianProfile->name ?? $admission->guardian_name ?? $guardianUser->name,
                    'relation'       => $anyGuardianProfile->relation ?? $admission->guardian_relation,
                    'father_name'    => $anyGuardianProfile->father_name ?? $admission->father_name,
                    'mother_name'    => $anyGuardianProfile->mother_name ?? $admission->mother_name,
                    'occupation'     => $anyGuardianProfile->occupation ?? $admission->guardian_occupation,
                    'mobile'         => $anyGuardianProfile->mobile ?? $admission->guardian_mobile,
                    'email'          => $anyGuardianProfile->email ?? $admission->guardian_email ?? $guardianUser->email,
                    'address'        => $anyGuardianProfile->address ?? $admission->guardian_address,
                ]);

                return [
                    'guardian' => $newGuardianForThisInstitution,
                    'username' => $guardianUser->username,
                    'email'    => $guardianUser->email ?? '',
                    'password' => null, // ── Login already exists, notun password lagbe na ──
                    'is_new'   => false,
                ];
            }
            // ── User delete hoye thakle (edge case) — normal email/mobile
            // fallback-e proceed korbe ──
        }

        // ── (2) email/mobile match fallback (shudhu ei Institution-er moddhe) ──
        $guardian = null;

        if ($admission->guardian_email || $admission->guardian_mobile) {
            $guardian = Guardian::where('institution_id', $admission->institution_id)
                ->where(function ($query) use ($admission) {
                    if ($admission->guardian_email) {
                        $query->orWhere('email', $admission->guardian_email);
                    }
                    if ($admission->guardian_mobile) {
                        $query->orWhere('mobile', $admission->guardian_mobile);
                    }
                })
                ->first();
        }

        if ($guardian) {
            $existingUser = User::find($guardian->user_id);

            return [
                'guardian' => $guardian,
                'username' => $existingUser?->username ?? ($guardian->email ?? ''),
                'email'    => $guardian->email ?? '',
                'password' => null,
                'is_new'   => false,
            ];
        }

        if (!$admission->guardian_email) {
            throw new RuntimeException('Guardian email is required to create a new guardian account.');
        }

        // ── (3) Guardian-er PLAIN PASSWORD ekhane generate hocche ──
        $plainPassword = Str::random(10);
        $username      = $admission->guardian_email;

        // NOTE: assumes the default Laravel 12 User model casts 'password' => 'hashed',
        // so the plain value is hashed automatically on save. If your User model does
        // NOT have that cast, wrap this with Hash::make($plainPassword) instead.
        $guardianUser = User::create([
            'institution_id' => $admission->institution_id,
            'name'            => $admission->guardian_name ?? $admission->applicant_name . ' Guardian',
            'username'        => $username,
            'email'           => $admission->guardian_email,
            'password'        => $plainPassword, // ── User table-e (hashed) save hocche ──
            'role'            => 'parent',
            'is_verified'     => true,
        ]);

        $guardian = Guardian::create([
            'institution_id' => $admission->institution_id,
            'user_id'        => $guardianUser->id,
            'name'           => $admission->guardian_name ?? $guardianUser->name,
            'relation'       => $admission->guardian_relation,
            'father_name'    => $admission->father_name,
            'mother_name'    => $admission->mother_name,
            'occupation'     => $admission->guardian_occupation,
            'mobile'         => $admission->guardian_mobile,
            'email'          => $admission->guardian_email,
            'address'        => $admission->guardian_address,
        ]);

        return [
            'guardian' => $guardian,
            'username' => $username,
            'email'    => $admission->guardian_email,
            'password' => $plainPassword, // ── mail e dekhanor jonno return hocche ──
            'is_new'   => true,
        ];
    }

    /**
     * StudentAddComponent::save() er identical numbering pattern follow kora hocche:
     * student_id  = Institution Settings (enable_student_id_prefix, student_id_code_prefix,
     *     student_id_digit_length, student_id_start_from) onujayi dynamically generate hoy.
     * registration_no = Institution Settings (enable_registration_prefix,
     *     registration_code_prefix, registration_digit_length, registration_start_from) onujayi
     *     dynamically generate hoy — student_id theke completely independent serial.
     * roll_no     = class-wise student count + 1, 2-digit zero-padded
     *
     * lockForUpdate() diye race-condition theke bacha hocche, jate ekshathe
     * duita approve request-e duplicate student_id/registration_no toiri na hoy.
     *
     * Student-er User account-e username hisebe student_id use kora hocche
     * (email na thakleo login korte pare), ebong ekta real usable random
     * password generate kore mail-e pathano hoy — age emon random/unusable
     * password chilo jeta diye login kora jeto na, ekhon student nijeo
     * login credential pabe.
     *
     * @return array{student: Student, credentials: array{username: string, email: ?string, password: string}}
     */
    private function createStudent(Admission $admission, Guardian $guardian): array
    {
        $institutionId = $admission->institution_id;

        // ── student_id: Institution Settings onujayi dynamic ───────────────
        $year = now()->format('y');

        $studentId = $this->generateStudentId($institutionId, $year);

        // ── registration_no: Institution Settings onujayi dynamic ──────────
        $registerNo = $this->generateRegisterNo($institutionId, $year);

        $rollSerial = Student::where('institution_id', $institutionId)
            ->where('class_id', $admission->applied_class_id)
            ->lockForUpdate()
            ->count();

        $rollNo = str_pad((string) ($rollSerial + 1), 2, '0', STR_PAD_LEFT);

        $studentPlainPassword = Str::random(8);

        $studentUser = User::create([
            'institution_id' => $institutionId,
            'name'            => $admission->applicant_name,
            'username'        => $studentId,
            'email'           => $admission->email,
            'password'        => $studentPlainPassword,
            'role'            => 'student',
            'is_verified'     => true,
        ]);

        $student = Student::create([
            'institution_id'        => $institutionId,
            'user_id'                => $studentUser->id,
            'session_id'             => $admission->applied_session_id,
            'student_id'             => $studentId,
            'registration_no'        => $registerNo,
            'roll_no'                => $rollNo,
            'admission_date'         => now()->toDateString(),
            'class_id'               => $admission->applied_class_id,
            'name'                   => $admission->applicant_name,
            'gender'                 => $admission->gender,
            'blood_group'            => $admission->blood_group,
            'dob'                    => $admission->dob,
            'religion'               => $admission->religion,
            'mobile'                 => $admission->mobile,
            'email'                  => $admission->email,
            'present_address'        => $admission->present_address,
            'permanent_address'      => $admission->permanent_address,
            'photo'                  => $admission->photo,
            'previous_institution'   => $admission->previous_institution,
            'qualification'          => $admission->qualification,
        ]);

        return [
            'student' => $student,
            'credentials' => [
                'username' => $studentId,
                'email'    => $admission->email,
                'password' => $studentPlainPassword,
            ],
        ];
    }

    /**
     * Institution Settings (enable_student_id_prefix, student_id_code_prefix,
     * student_id_digit_length, student_id_start_from) onujayi student_id generate kore.
     * StudentAddComponent::generateStudentId()-er logic-er sathe consistent.
     * lockForUpdate() diye race-condition theke bacha hoy.
     */
    private function generateStudentId(int $institutionId, string $year): string
    {
        $inst = institution();

        $digit     = (int) ($inst?->student_id_digit_length ?? 6);
        $startFrom = (int) ($inst?->student_id_start_from ?? 1);

        $prefix = ($inst?->enable_student_id_prefix && $inst?->student_id_code_prefix)
            ? $inst->student_id_code_prefix
            : 'SCH' . str_pad((string) $institutionId, 2, '0', STR_PAD_LEFT);

        $lastStudent = Student::where('institution_id', $institutionId)
            ->whereNotNull('student_id')
            ->lockForUpdate()
            ->orderByDesc('id')
            ->first();

        $serial = $lastStudent
            ? ((int) substr($lastStudent->student_id, -$digit)) + 1
            : $startFrom;

        return $prefix . $year . str_pad((string) $serial, $digit, '0', STR_PAD_LEFT);
    }

    /**
     * Institution Settings (enable_registration_prefix, registration_code_prefix,
     * registration_digit_length, registration_start_from) onujayi registration_no generate kore.
     * StudentAddComponent::generateRegisterNo()-er logic-er sathe consistent.
     */
    private function generateRegisterNo(int $institutionId, string $year): string
    {
        $inst = institution();

        $digit     = (int) ($inst?->registration_digit_length ?? 6);
        $startFrom = (int) ($inst?->registration_start_from ?? 1);

        $prefix = ($inst?->enable_registration_prefix && $inst?->registration_code_prefix)
            ? $inst->registration_code_prefix
            : 'RG' . str_pad((string) $institutionId, 2, '0', STR_PAD_LEFT);

        $lastStudent = Student::where('institution_id', $institutionId)
            ->whereNotNull('registration_no')
            ->lockForUpdate()
            ->orderByDesc('id')
            ->first();

        $serial = $lastStudent
            ? ((int) substr($lastStudent->registration_no, -$digit)) + 1
            : $startFrom;

        return $prefix . $year . str_pad((string) $serial, $digit, '0', STR_PAD_LEFT);
    }

    /**
     * Admin je fee item gulo Fee Confirmation Modal-e select korse
     * ($selectedFeeItems), shudhu shegulo diye Invoice + Invoice Items
     * toiri kore. Kono item select na thakle null return kore (invoice
     * toiri hoy na, approve process fail korbe na).
     *
     * @param array<int, array{fee_setup_id:int, label:string, amount:float}> $selectedFeeItems
     */
    private function generateInvoice(Student $student, Admission $admission, array $selectedFeeItems): ?FeeInvoice
    {
        if (empty($selectedFeeItems)) {
            return null;
        }

        $institutionId = $admission->institution_id;
        $subtotal      = collect($selectedFeeItems)->sum('amount');

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
            'invoice_date'    => now()->toDateString(),
            'due_date'        => now()->addDays(7)->format('Y-m-d'),
            'payment_status'  => 'unpaid',
            'status'          => true,
        ]);

        foreach ($selectedFeeItems as $item) {
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

    /**
     * StudentAddComponent::generateInvoiceNo()-er identical pattern:
     * INV + institution_id(2) + year-month(4) + serial(5).
     * lockForUpdate() diye duita approve ekshathe hoile duplicate
     * invoice_no toiri howa theke bacha hocche.
     */
    private function generateInvoiceNo(int $institutionId): string
    {
        $prefix = 'INV' . str_pad((string) $institutionId, 2, '0', STR_PAD_LEFT) . now()->format('ym');

        $lastInvoice = FeeInvoice::where('institution_id', $institutionId)
            ->where('invoice_no', 'like', $prefix . '%')
            ->lockForUpdate()
            ->orderByDesc('id')
            ->first();

        $serial = $lastInvoice
            ? ((int) substr($lastInvoice->invoice_no, -5)) + 1
            : 1;

        return $prefix . str_pad((string) $serial, 5, '0', STR_PAD_LEFT);
    }
}