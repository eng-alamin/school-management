<?php

namespace App\Livewire\Frontend;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\Institution;
use App\Models\Employee;
use App\Models\User;

class TeacherRegistrationComponent extends Component
{
    use WithFileUploads;

    // ── Wizard State ──
    public int $currentStep = 1;

    private const TOTAL_STEPS = 5;

    // ── Step 1: Institution (search-based selection, same pattern as OnlineAdmissionComponent) ──
    public $institution;
    public $institution_id;
    public string $institutionSearch = '';

    // ── Step 2: Job Details ──
    public $joining_date;
    public $qualification;
    public $experience_detail;
    public $total_experience;

    // ── Step 3: Personal Details ──
    public $name;
    public $gender;
    public $blood_group;
    public $dob;
    public $religion;
    public $mobile;
    public $email;
    public $present_address;
    public $permanent_address;
    public $photo_upload;

    // ── Step 4: Login Details ──
    public $username;
    public $password;

    // ── Step 5: Bank Details ──
    public $bank_name;
    public $holder_name;
    public $bank_branch;
    public $bank_address;
    public $ifsc_code;
    public $account_no;

    // ── Anti-spam Honeypot (invisible field, bot fill korle catch hobe) ──
    public $website = '';

    // ── Submission Result ──
    public bool $submitted = false;
    public $employeeIdRef;

    public function mount()
    {
        $this->dispatch('date-updated', dob: $this->dob, joining_date: $this->joining_date);
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

        $this->institution = $institution;
        $this->institution_id = $institution->id;
        $this->institutionSearch = $institution->name;
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
            'joining_date'   => 'required|date',
        ]);
    }

    protected function stepThreeValidation(): void
    {
        $this->validate([
            'name'               => 'required|string|max:255',
            'gender'             => 'nullable|in:male,female,other',
            'blood_group'        => 'nullable|string|max:5',
            'dob'                => 'nullable|date',
            'religion'           => 'nullable|string|max:50',
            'mobile'             => 'nullable|string|max:20',
            'email'              => 'required|email|unique:users,email',
            'present_address'    => 'nullable|string',
            'permanent_address'  => 'nullable|string',
            'photo_upload'       => 'nullable|image|max:2048',
        ], [
            'email.unique' => 'This email is already registered. Please use a different email.',
        ]);
    }

    protected function stepFourValidation(): void
    {
        $this->validate([
            'username' => 'required|string|unique:users,username',
            'password' => 'nullable|min:4',
        ]);
    }

    public function nextStep(): void
    {
        match ($this->currentStep) {
            1 => $this->stepOneValidation(),
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
     */
    public function rules(): array
    {
        return [
            'institution_id'  => 'required|exists:institutions,id',

            'joining_date'    => 'required|date',
            'qualification'   => 'nullable|string',
            'experience_detail' => 'nullable|string',
            'total_experience'  => 'nullable|string|max:50',

            'name'               => 'required|string|max:255',
            'gender'             => 'nullable|in:male,female,other',
            'blood_group'        => 'nullable|string|max:5',
            'dob'                => 'nullable|date',
            'religion'           => 'nullable|string|max:50',
            'mobile'             => 'nullable|string|max:20',
            'email'              => 'nullable|email|unique:users,email',
            'present_address'    => 'nullable|string',
            'permanent_address'  => 'nullable|string',
            'photo_upload'       => 'nullable|image|max:2048',

            'username' => 'required|string|unique:users,username',
            'password' => 'nullable|min:4',

            'bank_name'    => 'nullable|string|max:255',
            'holder_name'  => 'nullable|string|max:255',
            'bank_branch'  => 'nullable|string|max:255',
            'bank_address' => 'nullable|string',
            'ifsc_code'    => 'nullable|string|max:50',
            'account_no'   => 'nullable|string|max:50',
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique' => 'This email is already registered. Please use a different email.',
        ];
    }

    protected function failedValidation($validator)
    {
        $this->dispatch('validation-failed');
    }

    public function updated($propertyName)
    {
        if (in_array($propertyName, ['website', 'institutionSearch'], true)) {
            return;
        }

        $this->validateOnly($propertyName, $this->rules(), $this->messages());
    }

    public function submit()
    {
        // ── Honeypot: bot field fill korle silently "success" dekhabo, save korbo na ──
        if (!empty($this->website)) {
            $this->submitted = true;
            $this->employeeIdRef = null;
            return;
        }

        $this->validate($this->rules(), $this->messages());

        $photoPath = null;

        DB::beginTransaction();

        try {
            $photoPath = $this->photo_upload
                ? $this->photo_upload->store('employees', 'public')
                : null;

            $user = User::create([
                'institution_id' => $this->institution_id,
                'role'     => 'teacher',
                'name'     => $this->name,
                'username' => $this->username,
                'email'    => $this->email,
                'password' => !empty($this->password) ? $this->password : '1234',
            ]);

            $inst      = $this->institution;
            $digit     = (int) ($inst->employee_id_digit_length ?? 6);
            $startFrom = (int) ($inst->employee_id_start_from ?? 1);

            $prefix = ($inst->enable_employee_id_prefix && $inst->employee_id_code_prefix)
                ? $inst->employee_id_code_prefix
                : 'EMP' . str_pad($this->institution_id, 2, '0', STR_PAD_LEFT);

            $year = now()->format('y');

            $lastEmployeeForId = Employee::where('institution_id', $this->institution_id)
                ->whereNotNull('employee_id')
                ->lockForUpdate()
                ->orderByDesc('id')
                ->first();

            $serial = $lastEmployeeForId
                ? ((int) substr($lastEmployeeForId->employee_id, -$digit)) + 1
                : $startFrom;

            $employeeId = $prefix . $year . str_pad($serial, $digit, '0', STR_PAD_LEFT);

            $employee = Employee::create([
                'institution_id'    => $this->institution_id,
                'user_id'           => $user->id,
                'employee_id'       => $employeeId,
                'joining_date'      => $this->joining_date,
                'qualification'     => $this->qualification,
                'experience_detail' => $this->experience_detail,
                'total_experience'  => $this->total_experience,
                'name'              => $this->name,
                'gender'            => $this->gender,
                'blood_group'       => $this->blood_group,
                'dob'               => $this->dob,
                'religion'          => $this->religion,
                'mobile'            => $this->mobile,
                'email'             => $this->email,
                'present_address'   => $this->present_address,
                'permanent_address' => $this->permanent_address,
                'photo'             => $photoPath,
                'bank_name'         => $this->bank_name,
                'holder_name'       => $this->holder_name,
                'bank_branch'       => $this->bank_branch,
                'bank_address'      => $this->bank_address,
                'ifsc_code'         => $this->ifsc_code,
                'account_no'        => $this->account_no,
                'status'            => 'inactive',
            ]);

            activity()
                ->performedOn($employee)
                ->withProperties([
                    'institution_id' => $this->institution_id,
                    'icon' => 'person_add',
                    'type' => 'teacher_registration',
                ])
                ->log('Public teacher registration submitted: ' . $employee->name);

            DB::commit();

            $this->employeeIdRef = $employeeId;
            $this->submitted = true;

            $this->dispatch('toast', type: 'success', message: 'Registration submitted successfully!');
            $this->dispatch('scroll-top');

        } catch (\Throwable $e) {

            DB::rollBack();

            // Rollback e DB row gulo revert hoy kintu uploaded file gulo thake jay — tai manually delete
            if ($photoPath) {
                Storage::disk('public')->delete($photoPath);
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
            'joining_date', 'qualification',
            'experience_detail', 'total_experience',
            'name', 'gender', 'blood_group', 'dob', 'religion', 'mobile', 'email',
            'present_address', 'permanent_address', 'photo_upload',
            'username', 'password',
            'bank_name', 'holder_name', 'bank_branch', 'bank_address', 'ifsc_code', 'account_no',
            'website', 'submitted', 'employeeIdRef',
        ]);

        $this->resetValidation();
        $this->dispatch('date-updated', dob: $this->dob, joining_date: $this->joining_date);
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


        return view('livewire.frontend.teacher-registration-component')
            ->with('institutionResults', $institutionResults)
            ->layout('layouts.frontend.app', [
                'title' => 'Teacher Registration',
            ]);
    }
}