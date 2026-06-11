<?php

namespace App\Livewire\Tenant\Student\Profile;

use Livewire\Component;
use App\Models\Student;
use App\Models\AcademicSession;
use App\Models\AcademicClass;
use App\Models\AcademicSection;
use App\Models\AcademicCategory;
use Illuminate\Validation\Rule;
use Livewire\WithFileUploads;

class EditComponent extends Component
{
    use WithFileUploads;

    public $student;
    public $studentId;

    // Academic
    public $session_id;
    public $register_no;
    public $roll_no;
    public $admission_date;
    public $class_id;
    public $section_id;
    public $category_id;

    // Personal
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

    // Previous school
    public $previous_school;
    public $qualification;
    public $remarks;

    public function mount(): void
    {
        $this->student = Student::with(['session', 'class', 'section', 'category', 'user'])
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $this->studentId = $this->student->id;

        $this->session_id     = $this->student->session_id;
        $this->register_no    = $this->student->register_no;
        $this->roll_no        = $this->student->roll_no;
        $this->admission_date = $this->student->admission_date;
        $this->class_id       = $this->student->class_id;
        $this->section_id     = $this->student->section_id;
        $this->category_id    = $this->student->category_id;

        $this->name              = $this->student->name;
        $this->gender            = $this->student->gender;
        $this->blood_group       = $this->student->blood_group;
        $this->dob               = $this->student->dob;
        $this->religion          = $this->student->religion;
        $this->mobile            = $this->student->mobile;
        $this->email             = $this->student->email;
        $this->present_address   = $this->student->present_address;
        $this->permanent_address = $this->student->permanent_address;
        $this->student_photo     = $this->student->photo;

        $this->previous_school = $this->student->previous_school;
        $this->qualification   = $this->student->qualification;
        $this->remarks         = $this->student->remarks;

        $this->dispatch('date-updated', date: $this->admission_date);
        $this->dispatch('date-updated', date: $this->dob);
    }

    protected function rules(): array
    {
        return [
            'name'                 => 'required|string',
            'gender'               => 'nullable',
            'blood_group'          => 'nullable',
            'dob'                  => 'nullable|date',
            'religion'             => 'nullable',
            'mobile'               => 'nullable',
            'email'                => 'nullable|email',
            'present_address'      => 'nullable|string',
            'permanent_address'    => 'nullable|string',
            'student_photo_upload' => 'nullable|image|max:2048',
            'previous_school'      => 'nullable|string',
            'qualification'        => 'nullable|string',
            'remarks'              => 'nullable|string',
        ];
    }

    public function safePreviewUrl($upload): ?string
    {
        if (!$upload) return null;
        try {
            return $upload->temporaryUrl();
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function deleteOldFile($path): void
    {
        if (!$path) return;
        $fullPath = public_path($path);
        if (file_exists($fullPath)) {
            unlink($fullPath);
        }
    }

    public function update(): void
    {
        $this->validate();

        $studentData = [
            'name'             => $this->name,
            'gender'           => $this->gender,
            'blood_group'      => $this->blood_group,
            'dob'              => $this->dob,
            'religion'         => $this->religion,
            'mobile'           => $this->mobile,
            'email'            => $this->email,
            'present_address'  => $this->present_address,
            'permanent_address'=> $this->permanent_address,
            'previous_school'  => $this->previous_school,
            'qualification'    => $this->qualification,
            'remarks'          => $this->remarks,
        ];

        if ($this->student_photo_upload) {
            $this->deleteOldFile($this->student->photo);
            $studentData['photo'] = \App\Helpers\TenantFileHelper::store($this->student_photo_upload, 'students');
        }

        $this->student->update($studentData);

        $this->dispatch('date-updated', date: $this->admission_date);
        $this->dispatch('date-updated', date: $this->dob);
        $this->dispatch('toast', type: 'success', message: 'Profile updated successfully!');
    }

    public function render()
    {
        $sessions   = AcademicSession::orderBy('name')->get();
        $classes    = AcademicClass::orderBy('id')->get();
        $sections   = AcademicSection::orderBy('name')->get();
        $categories = AcademicCategory::orderBy('name')->get();

        return view('livewire.tenant.student.profile.edit-component', compact('sessions', 'classes', 'sections', 'categories'))
            ->layout('layouts.student.app', [
                'title' => "Profile Edit | Monarchy School",
            ]);
    }
}