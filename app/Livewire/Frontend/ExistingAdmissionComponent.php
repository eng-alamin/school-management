<?php

namespace App\Livewire\Frontend;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\Admission;
use App\Models\AcademicSession;
use App\Models\AcademicClass;
use App\Models\AcademicClassAssign;
use App\Models\AcademicGroup;

class ExistingAdmissionComponent extends Component
{
    use WithFileUploads;

    public Admission $admission;

    public int $currentStep = 1;

    private const TOTAL_STEPS = 4;

    // ── Blocked when application already approved (student/user account already created) ──
    public bool $alreadyApproved = false;

    // ── Academic Details ──
    public $session_id;
    public $class_id;
    public $group_id;

    // ── Student Details ──
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
    public $existing_photo_path;

    // ── Guardian Details ──
    public $guardian_name;
    public $guardian_relation;
    public $guardian_father_name;
    public $guardian_mother_name;
    public $guardian_occupation;
    public $guardian_mobile;
    public $guardian_email;
    public $guardian_address;

    // ── Previous Institution ──
    public $previous_institution;
    public $qualification;

    // ── Result ──
    public bool $updated_successfully = false;

    public function mount(Admission $admission): void
    {
        $this->admission = $admission;

        if ($admission->status === 'approved') {
            $this->alreadyApproved = true;
            return;
        }

        $this->session_id = $admission->applied_session_id;
        $this->class_id   = $admission->applied_class_id;

        $this->name               = $admission->applicant_name;
        $this->gender              = $admission->gender ?? 'male';
        $this->blood_group         = $admission->blood_group;
        $this->dob                 = $admission->dob;
        $this->religion            = $admission->religion;
        $this->mobile              = $admission->mobile;
        $this->email               = $admission->email;
        $this->present_address     = $admission->present_address;
        $this->permanent_address   = $admission->permanent_address;
        $this->existing_photo_path = $admission->photo;

        $this->guardian_name        = $admission->guardian_name;
        $this->guardian_relation    = $admission->guardian_relation;
        $this->guardian_father_name = $admission->father_name;
        $this->guardian_mother_name = $admission->mother_name;
        $this->guardian_occupation  = $admission->guardian_occupation;
        $this->guardian_mobile      = $admission->guardian_mobile;
        $this->guardian_email       = $admission->guardian_email;
        $this->guardian_address     = $admission->guardian_address;

        $this->previous_institution = $admission->previous_institution;
        $this->qualification        = $admission->qualification;

        $this->dispatch('date-updated', date: $this->dob);
    }

    protected function stepOneValidation(): void
    {
        $this->validate([
            'session_id' => 'required|exists:academic_sessions,id',
            'class_id'   => 'required|exists:academic_classes,id',
        ]);
    }

    protected function stepTwoValidation(): void
    {
        $this->validate([
            'name'                  => 'required|string|max:255',
            'gender'                => 'nullable|in:male,female,other',
            'blood_group'           => 'nullable|string|max:5',
            'dob'                   => 'nullable|date',
            'religion'              => 'nullable|string|max:50',
            'mobile'                => ['nullable', 'regex:/^01[3-9][0-9]{8}$/'],
            'email'                 => 'nullable|email',
            'present_address'       => 'nullable|string',
            'permanent_address'     => 'nullable|string',
            'student_photo_upload'  => 'nullable|image|max:2048',
        ]);
    }

    protected function stepThreeValidation(): void
    {
        $this->validate([
            'guardian_name'        => 'required|string|max:255',
            'guardian_relation'    => 'required|string|max:100',
            'guardian_father_name' => 'nullable|string|max:255',
            'guardian_mother_name' => 'nullable|string|max:255',
            'guardian_occupation'  => 'nullable|string|max:255',
            'guardian_mobile'      => 'required|regex:/^01[3-9][0-9]{8}$/',
            'guardian_email'       => 'required|email',
            'guardian_address'     => 'nullable|string',
        ]);
    }

    public function nextStep(): void
    {
        match ($this->currentStep) {
            1 => $this->stepOneValidation(),
            2 => $this->stepTwoValidation(),
            3 => $this->stepThreeValidation(),
            default => null,
        };

        if ($this->currentStep < self::TOTAL_STEPS) {
            $this->currentStep++;
        }
    }

    public function previousStep(): void
    {
        if ($this->currentStep > 1) {
            $this->currentStep--;
        }
    }

    public function rules(): array
    {
        return [
            'session_id' => 'required|exists:academic_sessions,id',
            'class_id'   => 'required|exists:academic_classes,id',

            'name'       => 'required|string|max:255',
            'gender'     => 'nullable|in:male,female,other',
            'blood_group' => 'nullable|string|max:5',
            'dob'        => 'nullable|date',
            'religion'   => 'nullable|string|max:50',
            'mobile'     => ['nullable', 'regex:/^01[3-9][0-9]{8}$/'],
            'email'              => 'nullable|email',
            'present_address'    => 'nullable|string',
            'permanent_address'  => 'nullable|string',
            'student_photo_upload' => 'nullable|image|max:2048',

            'guardian_name'     => 'required|string|max:255',
            'guardian_relation' => 'required|string|max:100',
            'guardian_father_name' => 'nullable|string|max:255',
            'guardian_mother_name' => 'nullable|string|max:255',
            'guardian_occupation'  => 'nullable|string|max:255',
            'guardian_mobile'      => 'nullable|regex:/^01[3-9][0-9]{8}$/',
            'guardian_email'       => 'nullable|email',
            'guardian_address'     => 'nullable|string',

            'previous_institution' => 'nullable|string',
            'qualification'        => 'nullable|string',
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

    public function update(): void
    {
        $this->validate($this->rules());

        $photoPath = $this->existing_photo_path;

        DB::beginTransaction();

        try {
            if ($this->student_photo_upload) {
                $photoPath = $this->student_photo_upload->store('admissions/students', 'public');

                // Notun photo upload hoyeche — purono ta storage theke delete kore dicchi
                if ($this->existing_photo_path) {
                    Storage::disk('public')->delete($this->existing_photo_path);
                }
            }

            $this->admission->update([
                'applicant_name'       => $this->name,
                'gender'               => $this->gender,
                'blood_group'          => $this->blood_group,
                'dob'                  => $this->dob,
                'religion'             => $this->religion,
                'mobile'               => $this->mobile,
                'email'                => $this->email,
                'present_address'      => $this->present_address,
                'permanent_address'    => $this->permanent_address,
                'photo'                => $photoPath,
                'previous_institution' => $this->previous_institution,
                'qualification'        => $this->qualification,

                'applied_session_id' => $this->session_id,
                'applied_class_id'   => $this->class_id,

                'guardian_name'       => $this->guardian_name,
                'guardian_relation'   => $this->guardian_relation,
                'father_name'         => $this->guardian_father_name,
                'mother_name'         => $this->guardian_mother_name,
                'guardian_occupation' => $this->guardian_occupation,
                'guardian_mobile'     => $this->guardian_mobile,
                'guardian_email'      => $this->guardian_email,
                'guardian_address'    => $this->guardian_address,

                // Update-er por abar review queue e — pending kore dicchi, purono reject reason clear
                'status'           => 'pending',
                'rejection_reason' => null,
            ]);

            activity()
                ->performedOn($this->admission)
                ->withProperties([
                    'institution_id' => $this->admission->institution_id,
                    'icon' => 'edit_note',
                    'type' => 'admission',
                ])
                ->log('Admission application updated and resubmitted: ' . $this->admission->applicant_name);

            DB::commit();

            $this->existing_photo_path = $photoPath;
            $this->updated_successfully = true;

            $this->dispatch('toast', type: 'success', message: 'Application updated and resubmitted for review!');

        } catch (\Throwable $e) {
            DB::rollBack();

            report($e);

            $this->dispatch('toast', type: 'error', message: 'Something went wrong. Please try again.');
        }
    }

    public function render()
    {
        $classes = collect();
        $groups = collect();
        $sessions = collect();

        if (!$this->alreadyApproved) {
            $institutionId = $this->admission->institution_id;

            $assignedClassIds = AcademicClassAssign::where('institution_id', $institutionId)
                ->pluck('class_id')
                ->unique();

            $classes = AcademicClass::whereIn('id', $assignedClassIds)
                ->orderBy('id')
                ->get();

            $groups = AcademicGroup::where('institution_id', $institutionId)
                ->orderBy('name')
                ->get();

            $sessions = AcademicSession::where('institution_id', $institutionId)
                ->orderBy('name')
                ->get();
        }

        return view('livewire.frontend.existing-admission-component')
            ->with('classes', $classes)
            ->with('groups', $groups)
            ->with('sessions', $sessions)
            ->layout('layouts.frontend.app', [
                'title' => 'Update Admission Application',
            ]);
    }
}