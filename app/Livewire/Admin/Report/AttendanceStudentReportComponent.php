<?php

namespace App\Livewire\Admin\Report;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\AcademicClass;
use App\Models\AcademicSection;
use App\Models\Attendance;
use App\Models\Student;
use Illuminate\Support\Facades\DB;

class AttendanceStudentReportComponent extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    public const SORTABLE_FIELDS = ['date', 'check_in', 'check_out', 'status', 'created_at'];

    protected array $sortColumnMap = [
        'date'       => 'attendances.date',
        'check_in'   => 'attendances.check_in',
        'check_out'  => 'attendances.check_out',
        'status'     => 'attendances.status',
        'created_at' => 'attendances.created_at',
    ];

    public string $sortField     = 'date';
    public string $sortDirection = 'desc';

    // ---------- Filters ----------
    public string $dateFrom;
    public string $dateTo;
    public string $classId   = 'all';
    public string $sectionId = 'all';
    public string $status    = 'all';
    public string $search    = '';
    public int    $perPage   = 15;

    public function mount(): void
    {
        $this->dateFrom = now()->startOfMonth()->toDateString();
        $this->dateTo   = now()->toDateString();
    }

    public function updatingClassId(): void
    {
        $this->sectionId = 'all';
        $this->resetPage();
    }

    public function updatingDateFrom(): void  { $this->resetPage(); }
    public function updatingDateTo(): void    { $this->resetPage(); }
    public function updatingSectionId(): void { $this->resetPage(); }
    public function updatingStatus(): void    { $this->resetPage(); }
    public function updatingSearch(): void    { $this->resetPage(); }

    public function sortBy(string $field): void
    {
        if (! in_array($field, self::SORTABLE_FIELDS, true)) {
            return;
        }

        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField     = $field;
            $this->sortDirection = 'asc';
        }
    }

    /**
     * Common filters (date range, class/section, search) ছাড়া status filter।
     * Summary count-এর জন্য status বাদে বাকি সব filter দরকার হয়, তাই আলাদা রাখা হলো।
     */
    protected function baseQueryWithoutStatus()
    {
        return Attendance::query()
            ->select('attendances.*')
            ->where('institution_id', institution()->id)
            ->where('type', 'student')
            ->where('attendable_type', Student::class)
            ->whereBetween('date', [$this->dateFrom, $this->dateTo])
            ->when($this->classId !== 'all', fn ($q) => $q->where('class_id', $this->classId))
            ->when($this->sectionId !== 'all', fn ($q) => $q->where('section_id', $this->sectionId))
            ->when($this->search !== '', function ($q) {
                $q->whereHasMorph('attendable', [Student::class], function ($sub) {
                    $sub->where(function ($inner) {
                        $inner->where('name', 'like', "%{$this->search}%")
                              ->orWhere('student_id', 'like', "%{$this->search}%")
                              ->orWhere('roll_no', 'like', "%{$this->search}%");
                    });
                });
            });
    }

    protected function baseQuery()
    {
        return $this->baseQueryWithoutStatus()
            ->when($this->status !== 'all', fn ($q) => $q->where('status', $this->status));
    }

    public function render()
    {
        $sortColumn = $this->sortColumnMap[$this->sortField] ?? 'attendances.date';

        $records = $this->baseQuery()
            ->with(['attendable' => function ($morphTo) {
                $morphTo->morphWith([
                    Student::class => ['academicClass:id,name', 'academicSection:id,name'],
                ]);
            }])
            ->orderBy($sortColumn, $this->sortDirection)
            ->paginate($this->perPage);

        // Summary counts — একই ফিল্টার (তারিখ, class/section, search) দিয়ে, শুধু status বাদে
        $summary = $this->baseQueryWithoutStatus()
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $classes = AcademicClass::where('institution_id', institution()->id)
            ->orderBy('name')
            ->get(['id', 'name']);

        $sections = $this->classId !== 'all'
            ? AcademicSection::whereIn('id', function ($q) {
                $q->select('section_id')
                    ->from('academic_class_sections')
                    ->where('class_id', $this->classId);
            })
                ->orderBy('name')
                ->get(['id', 'name'])
            : collect();

        return view('livewire.admin.report.attendance-student-report-component', [
            'records'  => $records,
            'summary'  => $summary,
            'classes'  => $classes,
            'sections' => $sections,
        ])
        ->layout('layouts.admin.app', [
            'title' => 'Reports | ' . institution()->name,
            'breadcrumbs' => [
                ['name' => 'Student Attendance Report', 'url' => route('admin.reports.attendances.student')],
            ],
        ]);
    }
}