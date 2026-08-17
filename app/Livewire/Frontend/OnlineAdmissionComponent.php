<?php

namespace App\Livewire\Frontend;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\Institution;
use App\Models\AcademicSession;
use App\Models\AcademicClass;
use App\Models\AcademicClassAssign;
use App\Models\AcademicGroup;
use App\Models\Admission;
use App\Models\Guardian;

class OnlineAdmissionComponent extends Component
{
    use WithFileUploads;

    // ── Wizard State ──
    public int $currentStep = 1;

    private const TOTAL_STEPS = 5;

    // ── Step 1: Institution (search-based selection) ──
    public $institution_id;
    public string $institutionSearch = '';

    // ── Admission Type Modal (Step 1 -> Step 2 transition) ──
    public bool $showAdmissionTypeModal = false;
    public ?bool $is_new = true;

    // ── Step 2: Academic Details ──
    public $session_id;
    public $class_id;
    public $group_id;

    // ── Step 3: Student Details ──
    public $name;
    public $gender = 'male';
    public $blood_group;
    public $dob;
    public $religion;
    public $mobile;
    public $email;
    public $present_address;
    public $permanent_address;
    public $student_photo_upload;

    // ── Step 4: Guardian Details ──
    // "Guardian Already Exist" — ekjon Guardian-er sontanra ALADA ALADA
    // Institution-e porte pare, tai eta GLOBAL search (kono institution_id
    // filter chara). guardian_user_id holo shei guardian-er GLOBAL login
    // identity (users.id), Guardian::id na — karon Guardian row prottek
    // Institution-e alada thakte pare (multi-tenant scoping).
    public bool $guardian_exists = false;
    public $guardian_user_id;
    public string $guardianSearch = '';

    public $guardian_name;
    public $guardian_relation;
    public $guardian_father_name;
    public $guardian_mother_name;
    public $guardian_occupation;
    public $guardian_mobile;
    public $guardian_email;
    public $guardian_address;

    // ── Step 5: Previous Institution ──
    public $previous_institution;
    public $qualification;

    // ── Anti-spam Honeypot (invisible field, bot fill korle catch hobe) ──
    public $website = '';

    // ── Submission Result ──
    public bool $submitted = false;
    public $applicationNo;

    public function mount()
    {
        $this->dispatch('date-updated', date: $this->dob);
    }

    /**
     * Livewire auto-calls this jokhon $institution_id property change hoy —
     * chai seta wire:model theke hok ba selectInstitution() theke programmatically.
     */
    public function updatedInstitutionId(): void
    {
        $this->session_id = null;
        $this->class_id = null;
        $this->group_id = null;

        if ($this->institution_id) {
            $currentSession = AcademicSession::where('institution_id', $this->institution_id)
                ->where('is_current', true)
                ->first();
            $this->session_id = $currentSession?->id;

            $currentGroup = AcademicGroup::where('institution_id', $this->institution_id)
                ->where('is_current', true)
                ->first();
            $this->group_id = $currentGroup?->id;
        }
    }

    /**
     * Search result theke user ekta institution click korle eta call hobe.
     */
    public function selectInstitution($institutionId): void
    {
        $institution = Institution::find($institutionId);

        if (!$institution) {
            return;
        }

        $this->institution_id = $institution->id;
        $this->institutionSearch = $institution->name;

        $this->updatedInstitutionId();
    }

    /**
     * "Change" button click korle abar search box dekhabe.
     */
    public function changeInstitution(): void
    {
        $this->institution_id = null;
        $this->institutionSearch = '';
    }

    /**
     * "Guardian Already Exist" toggle off korle search state o clear kore dey,
     * jate purono selection thake na jay.
     */
    public function updatedGuardianExists(): void
    {
        $this->guardian_user_id = null;
        $this->guardianSearch = '';
    }

    /**
     * Guardian search result theke user ekta guardian click korle eta call hobe.
     * $userId holo shei guardian-er GLOBAL login identity (users.id) — eta
     * diye AdmissionService approve-er shomoy current Institution-e
     * proyojon mote notun Guardian row toiri/reuse korbe, kintu login
     * account notun banabe na.
     */
    public function selectGuardian($userId): void
    {
        // withoutGlobalScopes() — cross-institution search, tai
        // InstitutionScope bypass kora hocche.
        $guardian = Guardian::withoutGlobalScopes()
            ->where('user_id', $userId)
            ->with('institution')
            ->first();

        if (!$guardian) {
            return;
        }

        $this->guardian_user_id = $userId;

        $contact = $guardian->mobile
            ? substr($guardian->mobile, 0, 4) . '******' . substr($guardian->mobile, -3)
            : ($guardian->email ?? '');

        $this->guardianSearch = $guardian->name . ' (' . $contact . ')';


        // ── Display/summary-er jonno guardian-er details form field-eo
        // bhorat kore rakhi, jate Step 5 review ebong Admission row-e
        // (readable snapshot) guardian info save thake ──
        $this->guardian_name = $guardian->name;
        $this->guardian_relation = $guardian->relation;
        $this->guardian_father_name = $guardian->father_name;
        $this->guardian_mother_name = $guardian->mother_name;
        $this->guardian_occupation = $guardian->occupation;
        $this->guardian_mobile = $guardian->mobile;
        $this->guardian_email = $guardian->email;
        $this->guardian_address = $guardian->address;
    }

    /**
     * "Change" button click korle abar guardian search box dekhabe.
     */
    public function changeGuardian(): void
    {
        $this->guardian_user_id = null;
        $this->guardianSearch = '';
    }

    /**
     * Admission Type modal theke "Existing Student" / "New Student" select korle
     * eta call hobe, then Step 2 e move kore.
     */
    public function selectAdmissionType(bool $isNew): void
    {
        $this->is_new = $isNew;
        $this->showAdmissionTypeModal = false;
        $this->currentStep = 2;

        $this->dispatch('scroll-top');
    }

    public function closeAdmissionTypeModal(): void
    {
        $this->showAdmissionTypeModal = false;
    }

    /**
     * Step-wise validation — Continue button ei gulo trigger kore.
     */
    protected function stepOneValidation(): void
    {
        $this->validate([
            'institution_id' => 'required|exists:institutions,id',
        ], [
            'institution_id.required' => 'Please select your institution first.',
        ]);
    }

    protected function stepTwoValidation(): void
    {
        $this->validate([
            'session_id' => 'required|exists:academic_sessions,id',
            'class_id'   => 'required|exists:academic_classes,id',
        ]);
    }

    protected function stepThreeValidation(): void
    {
        $this->validate([
            'name'                  => 'required|string|max:255',
            'gender'                => 'nullable|in:male,female,other',
            'blood_group'           => 'nullable|string|max:5',
            'dob'                   => 'nullable|date',
            'religion'              => 'nullable|string|max:50',
            'mobile'                => ['nullable', 'regex:/^01[3-9][0-9]{8}$/'],
            'email'                 => 'nullable|email|unique:users,email',
            'present_address'       => 'nullable|string',
            'permanent_address'     => 'nullable|string',
            'student_photo_upload'  => 'nullable|image|max:2048',
        ], [
            'email.unique' => 'This email is already registered. Please use a different email.',
        ]);
    }

    protected function stepFourValidation(): void
    {
        $this->validate($this->guardianRules(), $this->guardianValidationMessages());
    }

    /**
     * Guardian_exists flag onujayi rules dynamically toggle hoy — existing
     * (global) guardian select korle shudhu guardian_user_id lagbe, notun
     * guardian dile age-er moto shob field required thakbe.
     */
    private function guardianRules(): array
    {
        return [
            'guardian_user_id' => $this->guardian_exists ? 'required|exists:users,id' : 'nullable',

            'guardian_name'        => !$this->guardian_exists ? 'required|string|max:255' : 'nullable',
            'guardian_relation'    => !$this->guardian_exists ? 'required|string|max:100' : 'nullable',
            'guardian_father_name' => 'nullable|string|max:255',
            'guardian_mother_name' => 'nullable|string|max:255',
            'guardian_occupation'  => 'nullable|string|max:255',
            'guardian_mobile'      => !$this->guardian_exists ? 'required|regex:/^01[3-9][0-9]{8}$/' : 'nullable',
            'guardian_email'       => !$this->guardian_exists
                ? 'required|email|unique:users,email'
                : 'nullable|email',
            'guardian_address'     => 'nullable|string',
        ];
    }

    /**
     * guardianRules() e jothajotho unique error message dekhanor jonno.
     */
    private function guardianValidationMessages(): array
    {
        return [
            'guardian_email.unique' => 'This email is already registered. Please use a different email.',
        ];
    }

    public function nextStep(): void
    {
        // ── Step 1 e Continue click korle sorasori step barbe na —
        // Institution validate hobar por Admission Type modal dekhabe.
        if ($this->currentStep === 1) {
            $this->stepOneValidation();
            $this->showAdmissionTypeModal = true;
            return;
        }

        match ($this->currentStep) {
            2 => $this->stepTwoValidation(),
            3 => $this->stepThreeValidation(),
            4 => $this->stepFourValidation(),
            default => null,
        };

        if ($this->currentStep < self::TOTAL_STEPS) {
            $this->currentStep++;
            $this->dispatch('scroll-top');
        }
    }

    public function previousStep(): void
    {
        if ($this->currentStep > 1) {
            $this->currentStep--;
            $this->dispatch('scroll-top');
        }
    }

    /**
     * Full validation rules — final submit e safety-net hisebe use hoy,
     * ebong real-time (per-field) validation e o use hoy.
     *
     * Note: 'application_no' ekhane rakha hoy nai — eta system-generated
     * (formatApplicationNo() theke), user input na, tai validate korar
     * dorkar nai.
     */
    public function rules(): array
    {
        return array_merge([
            'institution_id' => 'required|exists:institutions,id',
            'is_new'    => 'required|boolean',
            'session_id' => 'required|exists:academic_sessions,id',
            'class_id'   => 'required|exists:academic_classes,id',
            // 'group_id'   => 'nullable|exists:academic_groups,id',

            'name'       => 'required|string|max:255',
            'gender'     => 'nullable|in:male,female,other',
            'blood_group' => 'nullable|string|max:5',
            'dob'        => 'nullable|date',
            'religion'   => 'nullable|string|max:50',
            // Note: এখানে আর 'unique:users,phone' চেক করা হচ্ছে না, কারণ Admission
            // পর্যায়ে এখনো কোনো User তৈরি হয় না — Approve হওয়ার পরেই হবে।
            'mobile'     => ['nullable', 'regex:/^01[3-9][0-9]{8}$/'],
            'email'              => 'nullable|email|unique:users,email',
            'present_address'    => 'nullable|string',
            'permanent_address'  => 'nullable|string',
            'student_photo_upload' => 'nullable|image|max:2048',

            'previous_institution' => 'nullable|string',
            'qualification'        => 'nullable|string',
        ], $this->guardianRules());
    }

    /**
     * rules() er shathe match kore validation messages — student o guardian
     * duitar unique email message ekshathe merge kora hocche.
     */
    public function messages(): array
    {
        return array_merge([
            'email.unique' => 'This email is already registered. Please use a different email.',
        ], $this->guardianValidationMessages());
    }

    protected function failedValidation($validator)
    {
        $this->dispatch('validation-failed');
    }

    public function updated($propertyName)
    {
        if (in_array($propertyName, ['website', 'institutionSearch', 'guardianSearch'], true)) {
            return;
        }

        $this->validateOnly($propertyName, $this->rules(), $this->messages());
    }

    /**
     * APP + institutionId(2) + year(2) + admission id (6, zero-padded) — শুধু
     * ট্র্যাকিং-এর জন্য পঠনযোগ্য একটা রেফারেন্স নম্বর, ইউনিকনেস আসে admission->id থেকেই।
     *
     * NOTE: এটা $admission->id (auto-increment) ব্যবহার করে, তাই Admission
     * row create howar POR e শুধু call kora jay — create() call-er age na.
     */
    private function formatApplicationNo(Admission $admission): string
    {
        return 'APP-'
            . str_pad((string) $admission->institution_id, 2, '0', STR_PAD_LEFT)
            . now()->format('y')
            . '-' . str_pad((string) $admission->id, 6, '0', STR_PAD_LEFT);
    }

    public function submit()
    {
        // ── Honeypot: bot field fill korle silently "success" dekhabo, save korbo na ──
        if (!empty($this->website)) {
            $this->submitted = true;
            $this->applicationNo = null;
            return;
        }

        $this->validate($this->rules(), $this->messages());

        $studentPhotoPath = null;

        DB::beginTransaction();

        try {
            $studentPhotoPath = $this->student_photo_upload
                ? $this->student_photo_upload->store('admissions/students', 'public')
                : null;

            $admission = Admission::create([
                'institution_id' => $this->institution_id,
                // ── Global guardian identity (users.id) — Institution-independent.
                // Approve howar shomoy AdmissionService ei id diye current
                // Institution-e Guardian row reuse/create korbe. ──
                'guardian_user_id' => $this->guardian_exists ? $this->guardian_user_id : null,
                'is_new'    => $this->is_new,

                'applicant_name' => $this->name,
                'gender' => $this->gender,
                'blood_group' => $this->blood_group,
                'dob' => $this->dob,
                'religion' => $this->religion,
                'mobile' => $this->mobile,
                'email' => $this->email,
                'present_address' => $this->present_address,
                'permanent_address' => $this->permanent_address,
                'photo' => $studentPhotoPath,
                'previous_institution' => $this->previous_institution,
                'qualification' => $this->qualification,

                'applied_session_id' => $this->session_id,
                'applied_class_id' => $this->class_id,
                // 'applied_group_id' => $this->group_id,

                'guardian_name' => $this->guardian_name,
                'guardian_relation' => $this->guardian_relation,
                'father_name' => $this->guardian_father_name,
                'mother_name' => $this->guardian_mother_name,
                'guardian_occupation' => $this->guardian_occupation,
                'guardian_mobile' => $this->guardian_mobile,
                'guardian_email' => $this->guardian_email,
                'guardian_address' => $this->guardian_address,

                'status' => 'pending',
            ]);

            // ── application_no admission->id nirvor kore, tai create() howar
            // por e generate kore alada update() diye DB-te insert kora hocche ──
            $admission->update([
                'application_no' => $this->formatApplicationNo($admission),
            ]);

            activity()
                ->performedOn($admission)
                ->withProperties([
                    'institution_id' => $this->institution_id,
                    'icon' => 'how_to_reg',
                    'type' => 'admission',
                ])
                ->log('Online admission application submitted: ' . $admission->applicant_name);

            DB::commit();

            $this->applicationNo = $admission->application_no;
            $this->submitted = true;

            $this->dispatch('toast', type: 'success', message: 'Application submitted successfully!');
            $this->dispatch('scroll-top');

        } catch (\Throwable $e) {

            DB::rollBack();

            // Rollback e DB row gulo revert hoy kintu uploaded file gulo thake jay — tai manually delete
            if ($studentPhotoPath) {
                Storage::disk('public')->delete($studentPhotoPath);
            }

            report($e);

            $this->dispatch('toast', type: 'error', message: 'Something went wrong. Please try again.');
        }
    }

    public function resetForm()
    {
        $this->reset([
            'currentStep',
            'institution_id', 'institutionSearch',
            'showAdmissionTypeModal', 'is_new',
            'session_id', 'class_id', 'group_id',
            'name', 'gender', 'blood_group', 'dob', 'religion', 'mobile', 'email',
            'present_address', 'permanent_address', 'student_photo_upload',
            'guardian_exists', 'guardian_user_id', 'guardianSearch',
            'guardian_name', 'guardian_relation', 'guardian_father_name', 'guardian_mother_name',
            'guardian_occupation', 'guardian_mobile',
            'guardian_email', 'guardian_address',
            'previous_institution', 'qualification',
            'website', 'submitted', 'applicationNo',
        ]);

        $this->gender = 'male';

        $this->resetValidation();
        $this->dispatch('date-updated', date: $this->dob);
        $this->dispatch('scroll-top');
    }

    public function render()
    {
        $institutionResults = collect();

        if (!$this->institution_id) {
            $query = Institution::query()->orderBy('name');

            if (strlen($this->institutionSearch) > 0) {
                $query->where('name', 'like', '%' . $this->institutionSearch . '%');
            }

            $institutionResults = $query->limit(10)->get();
        }

        $classes = collect();
        $groups = collect();
        $sessions = collect();

        if ($this->institution_id) {
            $assignedClassIds = AcademicClassAssign::where('institution_id', $this->institution_id)
                ->pluck('class_id')
                ->unique();

            $classes = AcademicClass::whereIn('id', $assignedClassIds)
                ->orderBy('id')
                ->get();

            $groups = AcademicGroup::where('institution_id', $this->institution_id)
                ->where('is_status', true)
                ->orderBy('name')
                ->get();

            $sessions = AcademicSession::where('institution_id', $this->institution_id)
                ->where('is_current', true)
                ->orderBy('name')
                ->get();
        }

        $guardianResults = collect();


        if ($this->guardian_exists && !$this->guardian_user_id && strlen($this->guardianSearch) > 0) {
            $guardianResults = Guardian::withoutGlobalScopes()
                ->with('institution')
                ->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->guardianSearch . '%')
                        ->orWhere('email', 'like', '%' . $this->guardianSearch . '%');
                })
                ->orderBy('name')
                ->limit(30)
                ->get()
                ->unique('user_id')
                ->take(10)
                ->values();
        }

        return view('livewire.frontend.online-admission-component')
            ->with('institutionResults', $institutionResults)
            ->with('classes', $classes)
            ->with('groups', $groups)
            ->with('sessions', $sessions)
            ->with('guardianResults', $guardianResults)
            ->layout('layouts.frontend.app', [
                'title' => 'Online Admission',
            ]);
    }
}