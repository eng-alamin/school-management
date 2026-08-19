<?php

namespace App\Livewire\Branch\Report;

use Livewire\Component;
use App\Models\AcademicClass;
use App\Models\AcademicSection;
use App\Models\Attendance;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;

class AttendanceReportComponent extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    public const SORTABLE_FIELDS = ['date', 'check_in', 'check_out', 'status', 'created_at'];

    protected array $sortColumnMap = [
        'date' => 'attendances.date',
        'check_in' => 'attendances.check_in',
        'check_out' => 'attendances.check_out',
        'status' => 'attendances.status',
        'created_at' => 'attendances.created_at',
    ];

    public string $sortField = 'date';
    public string $sortDirection = 'desc';

    // ---------- Filters ----------
    public string $type = 'student'; // 'student' | 'employee'
    public string $dateFrom;
    public string $dateTo;
    public string $classId = 'all';
    public string $sectionId = 'all';
    public string $status = 'all';
    public string $search = '';
    public int $perPage = 15;

    public function mount(): void
    {
        $this->dateFrom = now()->startOfMonth()->toDateString();
        $this->dateTo = now()->toDateString();
    }

    public function updatingType(): void
    {
        $this->classId = 'all';
        $this->sectionId = 'all';
        $this->resetPage();
    }

    public function updatingClassId(): void
    {
        $this->sectionId = 'all';
        $this->resetPage();
    }

    public function updatingDateFrom(): void
    {
        $this->resetPage();
    }

    public function updatingDateTo(): void
    {
        $this->resetPage();
    }

    public function updatingSectionId(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if (! in_array($field, self::SORTABLE_FIELDS, true)) {
            return;
        }

        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    /**
     * Common filters (date range, type, class/section, search) ছাড়া status filter।
     * Summary count-এর জন্য status বাদে বাকি সব filter দরকার হয়, তাই আলাদা রাখা হলো।
     */
    protected function baseQueryWithoutStatus()
    {
        $attendableClass = $this->type === 'employee'
            ? \App\Models\Employee::class
            : \App\Models\Student::class;

        return Attendance::query()
            ->select('attendances.*')
            ->where('institution_id', institution()->id)
            ->where('type', $this->type)
            ->where('attendable_type', $attendableClass)
            ->whereBetween('date', [$this->dateFrom, $this->dateTo])
            ->when($this->type === 'student' && $this->classId !== 'all', fn ($q) => $q->where('class_id', $this->classId))
            ->when($this->type === 'student' && $this->sectionId !== 'all', fn ($q) => $q->where('section_id', $this->sectionId))
            ->when($this->search !== '', function ($q) use ($attendableClass) {
                $q->whereHasMorph('attendable', [$attendableClass], function ($sub) {
                    $sub->where(function ($inner) {
                        $inner->where('name', 'like', "%{$this->search}%");
                        if ($this->type === 'student') {
                            $inner->orWhere('student_id', 'like', "%{$this->search}%")
                                ->orWhere('roll_no', 'like', "%{$this->search}%");
                        } else {
                            $inner->orWhere('employee_id', 'like', "%{$this->search}%");
                        }
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
            ->with([
                'attendable' => function ($morphTo) {
                    $morphTo->morphWith([
                        \App\Models\Student::class => ['academicClass:id,name', 'academicSection:id,name'],
                        \App\Models\Employee::class => ['designation:id,name'],
                    ]);
                },
            ])
            ->orderBy($sortColumn, $this->sortDirection)
            ->paginate($this->perPage);

        // Summary counts — একই ফিল্টার (তারিখ, type, class/section, search) দিয়ে, শুধু status বাদে
        $summary = $this->baseQueryWithoutStatus()
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $classes = $this->type === 'student'
            ? AcademicClass::where('institution_id', institution()->id)->orderBy('name')->get(['id', 'name'])
            : collect();

        $sections = ($this->type === 'student' && $this->classId !== 'all')
            ? AcademicSection::whereIn('id', function ($q) {
                $q->select('section_id')
                    ->from('academic_class_sections')
                    ->where('class_id', $this->classId);
            })
                ->orderBy('name')
                ->get(['id', 'name'])
            : collect();

        return view('livewire.admin.report.attendance-report-component', [
            'records' => $records,
            'summary' => $summary,
            'classes' => $classes,
            'sections' => $sections,
        ])
        ->layout('layouts.branch.app', [
            'title' => 'Reports | ' . institution()->name,
            'breadcrumbs' => [
                ['name' => 'Attendance Report', 'url' => route('branch.reports.attendances')],   
            ],
        ]);
    }
}
