<?php

namespace App\Livewire\ITSupport\Attendance;

use Livewire\Component;
use App\Models\Attendance;
use App\Models\Student;
use App\Models\AcademicClass;
use App\Models\AcademicSection;
use App\Models\AcademicSubject;
use App\Models\AcademicClassAssign;
use App\Models\AcademicClassAssignDetail;
use App\Models\ExamSetup;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ExamComponent extends Component
{
    public $filterExam = '';
    public $filterClass = '';
    public $filterSection = '';
    public $filterSubject = '';

    public bool $selectedClassHasSection = true;
    public array $availableSections = [];

    public $subjects = [];

    public $data = [];
    public $hasAttendance = false;

    // Exam
    public function getExams()
    {
        $institutionId = institution()->id;

        return ExamSetup::with('classAssign.academicClass', 'classAssign.academicSection')
            ->where('institution_id', $institutionId)
            ->whereHas('details')
            ->where('is_published', 1)
            ->orderBy('name')
            ->get();
    }

    // Classes
    public function getAvailableClasses()
    {
        $institutionId = institution()->id;

        return AcademicClass::where('institution_id', $institutionId)
            ->whereIn('id', AcademicClassAssign::where('institution_id', $institutionId)->distinct()->pluck('class_id'))
            ->orderBy('id')
            ->get();
    }

    /**
     * Class_id (ar optionally section_id) diye academic_class_assign_details
     * theke unique subject list ber kore — age JSON 'subjects' column theke ashto
     */
    protected function loadSubjects(): void
    {
        $institutionId = institution()->id;

        $query = AcademicClassAssignDetail::where('institution_id', $institutionId)
            ->whereHas('classAssign', function ($q) use ($institutionId) {
                $q->where('institution_id', $institutionId)
                    ->where('class_id', $this->filterClass);

                if ($this->filterSection && $this->filterSection !== 'all') {
                    $q->where('section_id', $this->filterSection);
                }
            });

        $subjectIds = $query->pluck('subject_id')->unique()->values();

        $this->subjects = $subjectIds->isNotEmpty()
            ? AcademicSubject::where('institution_id', $institutionId)
                ->whereIn('id', $subjectIds)
                ->orderBy('name')
                ->get()
            : [];
    }

    public function updatedFilterClass()
    {
        $this->filterSection     = '';
        $this->filterSubject     = '';
        $this->availableSections = [];
        $this->subjects          = [];
        $this->data               = [];
        $this->hasAttendance      = false;

        if (!$this->filterClass) {
            $this->selectedClassHasSection = true;
            return;
        }

        $institutionId = institution()->id;

        $class = AcademicClass::where('institution_id', $institutionId)
            ->find($this->filterClass);

        $this->selectedClassHasSection = $class ? (bool) $class->has_section : true;

        if ($this->selectedClassHasSection) {
            $this->availableSections = AcademicSection::where('institution_id', $institutionId)
                ->whereIn('id',
                    AcademicClassAssign::where('institution_id', $institutionId)
                        ->where('class_id', $this->filterClass)
                        ->whereNotNull('section_id')
                        ->pluck('section_id')
                )
                ->orderBy('name')
                ->get()
                ->map(fn ($s) => ['id' => $s->id, 'name' => $s->name])
                ->toArray();
        }

        // Section na thakle sub-class-e sathe sathe subject load kora hocche,
        // section thakle updatedFilterSection() e load hobe (section select korar por)
        if (!$this->selectedClassHasSection) {
            $this->loadSubjects();
        }
    }

    public function updatedFilterSection()
    {
        $this->filterSubject = '';
        $this->data          = [];
        $this->hasAttendance = false;

        if (!$this->filterClass || !$this->filterSection) return;

        $this->loadSubjects();
    }

    public function filter()
    {
        if (!$this->filterExam) {
            $this->dispatch('toast', type: 'error', message: 'Please select a exam.');
            return;
        }
        if (!$this->filterClass) {
            $this->dispatch('toast', type: 'error', message: 'Please select a class.');
            return;
        }

        if ($this->selectedClassHasSection && !$this->filterSection) {
            $this->dispatch('toast', type: 'error', message: 'Please select a section.');
            return;
        }

        if (!$this->filterSubject) {
            $this->dispatch('toast', type: 'error', message: 'Please select a subject.');
            return;
        }

        $institutionId = institution()->id;

        // ✅ Fix: cross-tampering guard — filterExam client theke set kora, kintu
        // eta selected filterClass er sathe milche kina check kora hocche na thakle
        // ager version e (Position module er moto)
        $examSetup = ExamSetup::where('institution_id', $institutionId)
            ->with('classAssign')
            ->find($this->filterExam);

        if (!$examSetup || !$examSetup->classAssign || (int) $examSetup->classAssign->class_id !== (int) $this->filterClass) {
            $this->dispatch('toast', type: 'error', message: 'নির্বাচিত Exam টি এই Class এর সাথে মিলছে না।');
            $this->hasAttendance = false;
            return;
        }

        // ✅ Fix: filterSubject আসলেই এই class/section এর assigned subject কিনা যাচাই
        $subjectValid = AcademicClassAssignDetail::where('institution_id', $institutionId)
            ->where('subject_id', $this->filterSubject)
            ->whereHas('classAssign', function ($q) use ($institutionId) {
                $q->where('institution_id', $institutionId)
                    ->where('class_id', $this->filterClass);

                if ($this->filterSection && $this->filterSection !== 'all') {
                    $q->where('section_id', $this->filterSection);
                }
            })
            ->exists();

        if (!$subjectValid) {
            $this->dispatch('toast', type: 'error', message: 'নির্বাচিত Subject টি এই Class এর সাথে মিলছে না।');
            $this->hasAttendance = false;
            return;
        }

        $studentsQuery = Student::where('institution_id', $institutionId)
            ->where('class_id', $this->filterClass)
            ->orderBy('section_id')
            ->orderBy('roll_no');

        if ($this->filterSection && $this->filterSection !== 'all') {
            $studentsQuery->where('section_id', $this->filterSection);
        }

        $students = $studentsQuery->get();

        if ($students->isEmpty()) {
            $this->dispatch('toast', type: 'error', message: 'No Exam found.');
            $this->hasAttendance = false;
            return;
        }

        $sectionNames = AcademicSection::where('institution_id', $institutionId)
            ->whereIn('id', $students->pluck('section_id')->unique())
            ->pluck('name', 'id');

        $existingQuery = Attendance::where('institution_id', $institutionId)
            ->where('type', 'exam')
            ->where('exam_id', $this->filterExam)
            ->where('class_id', $this->filterClass)
            ->where('subject_id', $this->filterSubject);

        if ($this->filterSection && $this->filterSection !== 'all') {
            $existingQuery->where('section_id', $this->filterSection);
        }

        $existing = $existingQuery->get()->keyBy('attendable_id');

        $this->data = $students->map(function ($student) use ($existing, $sectionNames) {

            $att = $existing[$student->id] ?? null;

            return [
                'student_id'   => $student->id,
                'section_id'   => $student->section_id,
                'section_name' => $sectionNames[$student->section_id] ?? '',
                'name'         => $student->name,
                'roll_no'      => $student->roll_no,
                'registration_no'  => $student->registration_no,

                'status'       => $att->status ?? 'present',
                'remarks'      => $att->remarks ?? '',
            ];
        })->toArray();

        $this->hasAttendance = true;
    }

    public function save()
    {
        $this->validate([
            'filterExam'    => 'required',
            'filterClass'   => 'required',
            'filterSubject' => 'required',
        ]);

        $institutionId = institution()->id;

        DB::beginTransaction();
        try {
            foreach ($this->data as $item) {

                Attendance::updateOrCreate(
                    [
                        'attendable_id'   => $item['student_id'],
                        'attendable_type' => Student::class,
                        'type'            => 'exam',
                        'exam_id'         => $this->filterExam,
                        'class_id'        => $this->filterClass,
                        'section_id'      => $item['section_id'],
                        'subject_id'      => $this->filterSubject,
                    ],
                    [
                        'institution_id' => $institutionId,
                        'status'         => $item['status'],
                        'remarks'        => $item['remarks'],
                    ]
                );
            }

            DB::commit();
            $this->dispatch('toast', type: 'success', message: 'Attendance saved successfully!');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Exam attendance save failed: ' . $e->getMessage());
            $this->dispatch('toast', type: 'error', message: 'Something went wrong!');
        }
    }

    public function resetForm()
    {
        $this->filterExam    = '';
        $this->filterClass   = '';
        $this->filterSection = '';
        $this->filterSubject = '';

        $this->selectedClassHasSection = true;
        $this->availableSections       = [];
        $this->subjects                 = [];

        $this->data          = [];
        $this->hasAttendance = false;

        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.admin.attendance.exam-component')
            ->with('exams', $this->getExams())
            ->with('classes', $this->getAvailableClasses())
            ->with('subjects', $this->subjects)
            ->layout('layouts.itsupport.app', [
                'title' => 'Exam Attendance | ' . institution()->name,
            ]);
    }
}