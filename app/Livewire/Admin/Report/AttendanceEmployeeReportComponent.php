<?php

namespace App\Livewire\Admin\Report;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\EmployeeDesignation;
use Illuminate\Support\Facades\DB;

class AttendanceEmployeeReportComponent extends Component
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
    public string $designationId = 'all';
    public string $status        = 'all';
    public string $search        = '';
    public int    $perPage       = 15;

    public function mount(): void
    {
        $this->dateFrom = now()->startOfMonth()->toDateString();
        $this->dateTo   = now()->toDateString();
    }

    public function updatingDateFrom(): void      { $this->resetPage(); }
    public function updatingDateTo(): void        { $this->resetPage(); }
    public function updatingDesignationId(): void { $this->resetPage(); }
    public function updatingStatus(): void        { $this->resetPage(); }
    public function updatingSearch(): void        { $this->resetPage(); }

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
     * Common filters (date range, designation, search) ছাড়া status filter।
     * Summary count-এর জন্য status বাদে বাকি সব filter দরকার হয়, তাই আলাদা রাখা হলো।
     */
    protected function baseQueryWithoutStatus()
    {
        return Attendance::query()
            ->select('attendances.*')
            ->where('institution_id', institution()->id)
            ->where('type', 'employee')
            ->where('attendable_type', Employee::class)
            ->whereBetween('date', [$this->dateFrom, $this->dateTo])
            ->when($this->designationId !== 'all', function ($q) {
                $q->whereHasMorph('attendable', [Employee::class], function ($sub) {
                    $sub->where('designation_id', $this->designationId);
                });
            })
            ->when($this->search !== '', function ($q) {
                $q->whereHasMorph('attendable', [Employee::class], function ($sub) {
                    $sub->where(function ($inner) {
                        $inner->where('name', 'like', "%{$this->search}%")
                              ->orWhere('employee_id', 'like', "%{$this->search}%");
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
                    Employee::class => ['designation:id,name'],
                ]);
            }])
            ->orderBy($sortColumn, $this->sortDirection)
            ->paginate($this->perPage);

        // Summary counts — একই ফিল্টার (তারিখ, designation, search) দিয়ে, শুধু status বাদে
        $summary = $this->baseQueryWithoutStatus()
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $designations = EmployeeDesignation::where('institution_id', institution()->id)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('livewire.admin.report.attendance-employee-report-component', [
            'records'      => $records,
            'summary'      => $summary,
            'designations' => $designations,
        ])
        ->layout('layouts.admin.app', [
            'title' => 'Reports | ' . institution()->name,
            'breadcrumbs' => [
                ['name' => 'Employee Attendance Report', 'url' => route('admin.reports.attendances.employee')],
            ],
        ]);
    }
}