<?php

namespace App\Livewire\Teacher\Attendance;

use Livewire\Component;
use App\Models\Attendance;
use App\Models\Student;
use App\Models\AcademicSection;
use App\Models\AttendanceClassAssign;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StudentComponent extends Component
{
    public $filterClass   = '';
    public $filterSection = '';
    public $filterDate;

    // Selected class-e amake (teacher) assign kora section thakle true hobe
    public bool $selectedClassHasSection = false;

    public $data          = [];
    public $hasAttendance = false;

    // ── Ekbar query kore memoize kora hocche, ekই request-e barbar DB hit thekano jonno ──
    private ?Collection $cachedMyAssigns = null;

    public function mount()
    {
        $this->filterDate = now()->format('Y-m-d');
    }

    /**
     * Ei logged-in teacher-ke attendance duty hisebe je je class+section
     * assign kora hoyeche, shudhu shegulor AcademicClassAssign record return kore.
     *
     * Security note: ei method-i single source of truth. Dropdown, filter(),
     * ebong save() — shob jaygay ei method diye e authorization check kora hocche,
     * jate teacher tar assign kora chara kono class/section-er attendance
     * dekhte ba save korte na pare (IDOR protection).
     */
    private function myAssignedClassAssigns(): Collection
    {
        if ($this->cachedMyAssigns !== null) {
            return $this->cachedMyAssigns;
        }

        $institutionId = institution()->id;

        return $this->cachedMyAssigns = AttendanceClassAssign::with([
                'classAssign.academicClass',
                'classAssign.academicSection',
            ])
            ->where('institution_id', $institutionId)
            ->where('teacher_id', auth()->id())
            ->get()
            ->pluck('classAssign')
            ->filter();
    }

    public function getAvailableClasses()
    {
        return $this->myAssignedClassAssigns()
            ->pluck('academicClass')
            ->filter()
            ->unique('id')
            ->sortBy('name')
            ->values();
    }

    public function getAvailableSections()
    {
        if (! $this->filterClass) {
            return collect();
        }

        return $this->myAssignedClassAssigns()
            ->where('class_id', (int) $this->filterClass)
            ->pluck('academicSection')
            ->filter()
            ->unique('id')
            ->sortBy('name')
            ->values();
    }

    // Ei class-er jonno amake (teacher) assign kora section_id gulo return kore
    // (no-section class hole array-te null thakte pare)
    private function myAssignedSectionIds(string $classId): array
    {
        return $this->myAssignedClassAssigns()
            ->where('class_id', (int) $classId)
            ->pluck('section_id')
            ->toArray();
    }

    /**
     * Defense-in-depth: dropdown-e shudhu assigned class/section dekhano hoyeo,
     * front-end theke wire:model tamper kore onno class/section pathale
     * eikhane dhora porbe (filter() ebong save() duitatei call kora hocche).
     */
    private function isAssignedToMe(?string $classId, ?string $sectionId = null): bool
    {
        if (! $classId) {
            return false;
        }

        $institutionId = institution()->id;

        return AttendanceClassAssign::where('institution_id', $institutionId)
            ->where('teacher_id', auth()->id())
            ->whereHas('classAssign', function ($q) use ($classId, $sectionId) {
                $q->where('class_id', $classId);

                if ($sectionId && $sectionId !== 'all') {
                    $q->where('section_id', $sectionId);
                }
            })
            ->exists();
    }

    public function updatedFilterClass(): void
    {
        $this->filterSection           = '';
        $this->data                    = [];
        $this->hasAttendance           = false;
        $this->selectedClassHasSection = false;

        if (! $this->filterClass) {
            return;
        }

        // Ei class-e amar assigned section_id gulor modhye kono ekta-o non-null hole
        // section dropdown dekhabo (ClassAssign-e has_section thakar shomotul)
        $sectionIds = $this->myAssignedSectionIds($this->filterClass);
        $this->selectedClassHasSection = collect($sectionIds)->filter()->isNotEmpty();
    }

    public function updatedFilterSection(): void
    {
        $this->data          = [];
        $this->hasAttendance = false;
    }

    public function filter()
    {
        if (! $this->filterClass) {
            $this->dispatch('toast', type: 'error', message: 'Please select a class.');
            return;
        }

        // ── Authorization check: shudhu amake assign kora class/section-e-i allow ──
        if (! $this->isAssignedToMe($this->filterClass, $this->filterSection)) {
            $this->dispatch('toast', type: 'error', message: 'You are not assigned to take attendance for this class/section.');
            return;
        }

        $institutionId = institution()->id;

        // Amar (teacher) ei class-e assigned section_id gulo — "All" select korle
        // eigulor moddheই attendance limit thakbe, pura class-e na (data leak thekano)
        $mySectionIds = collect($this->myAssignedSectionIds($this->filterClass))->filter()->values();

        $studentsQuery = Student::where('institution_id', $institutionId)
            ->where('class_id', $this->filterClass)
            ->orderBy('section_id')
            ->orderBy('roll_no');

        if ($this->filterSection && $this->filterSection !== 'all') {
            $studentsQuery->where('section_id', $this->filterSection);
        } elseif ($mySectionIds->isNotEmpty()) {
            $studentsQuery->whereIn('section_id', $mySectionIds);
        }

        $students = $studentsQuery->get();

        if ($students->isEmpty()) {
            $this->dispatch('toast', type: 'error', message: 'No students found.');
            $this->hasAttendance = false;
            return;
        }

        $sectionNames = AcademicSection::where('institution_id', $institutionId)
            ->whereIn('id', $students->pluck('section_id')->unique())
            ->pluck('name', 'id');

        $existingQuery = Attendance::where('institution_id', $institutionId)
            ->where('type', 'student')
            ->where('date', $this->filterDate)
            ->where('class_id', $this->filterClass);

        if ($this->filterSection && $this->filterSection !== 'all') {
            $existingQuery->where('section_id', $this->filterSection);
        } elseif ($mySectionIds->isNotEmpty()) {
            $existingQuery->whereIn('section_id', $mySectionIds);
        }

        // ── FIX (carried over): attendable_id (students.id, integer PK) diye key kora hocche ──
        $existing = $existingQuery->get()->keyBy('attendable_id');

        $this->data = $students->map(function ($student) use ($existing, $sectionNames) {
            $att = $existing[$student->id] ?? null;

            return [
                'id'           => $student->id,
                'section_id'   => $student->section_id,
                'section_name' => $sectionNames[$student->section_id] ?? '',
                'name'         => $student->name,
                'roll_no'      => $student->roll_no,
                'register_no'  => $student->register_no,
                'status'       => $att->status ?? 'present',
                'remarks'      => $att->remarks ?? '',
            ];
        })->toArray();

        $this->hasAttendance = true;
    }

    public function save()
    {
        $this->validate([
            'filterClass' => 'required',
            'filterDate'  => 'required|date',
        ]);

        // ── Authorization check: save-er somoyo abar verify kora hocche (defense-in-depth) ──
        if (! $this->isAssignedToMe($this->filterClass, $this->filterSection)) {
            $this->dispatch('toast', type: 'error', message: 'You are not assigned to take attendance for this class/section.');
            return;
        }

        $institutionId = institution()->id;

        DB::beginTransaction();
        try {
            foreach ($this->data as $item) {
                Attendance::updateOrCreate(
                    [
                        'attendable_id'   => $item['id'],
                        'attendable_type' => Student::class,
                        'date'            => $this->filterDate,
                        'type'            => 'student',
                        'class_id'        => $this->filterClass,
                        'section_id'      => $item['section_id'],
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
            Log::error('Teacher student attendance save failed: ' . $e->getMessage());
            $this->dispatch('toast', type: 'error', message: 'Something went wrong while saving attendance.');
        }
    }

    public function resetForm()
    {
        $this->filterClass             = '';
        $this->filterSection           = '';
        $this->filterDate              = now()->format('Y-m-d');
        $this->selectedClassHasSection = false;
        $this->data                    = [];
        $this->hasAttendance           = false;
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.teacher.attendance.student-component')
            ->with('classes', $this->getAvailableClasses())
            ->with('sections', $this->getAvailableSections())
            ->layout('layouts.teacher.app', [
                'title' => 'Student Attendance | ' . institution()->name,
            ]);
    }
}