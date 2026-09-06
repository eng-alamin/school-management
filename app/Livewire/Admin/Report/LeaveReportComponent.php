<?php

namespace App\Livewire\Admin\Report;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\LeaveApplication;
use App\Models\LeaveCategory;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class LeaveReportComponent extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    public const SORTABLE_FIELDS = ['start_date', 'end_date', 'total_days', 'status', 'created_at'];

    protected array $sortColumnMap = [
        'start_date'  => 'leave_applications.start_date',
        'end_date'    => 'leave_applications.end_date',
        'total_days'  => 'leave_applications.total_days',
        'status'      => 'leave_applications.status',
        'created_at'  => 'leave_applications.created_at',
    ];

    public string $sortField     = 'start_date';
    public string $sortDirection = 'desc';

    // ---------- Filters ----------
    public string $dateFrom;
    public string $dateTo;
    public string $role            = 'all';
    public string $leaveCategoryId = 'all';
    public string $status          = 'all';
    public string $search          = '';
    public int    $perPage         = 15;

    // Role → Model class map (ApplicationComponent এর সাথে সামঞ্জস্যপূর্ণ)
    protected array $roleModelMap = [
        'teacher'    => User::class,
        'accountant' => User::class,
        'staff'      => User::class,
        'student'    => User::class,
    ];

    public function mount(): void
    {
        $this->dateFrom = now()->startOfMonth()->toDateString();
        $this->dateTo   = now()->toDateString();
    }

    private function activeBranchId(): ?int
    {
        return auth()->user()->branch_id
            ?? Branch::resolveMainBranchId(institution()->id);
    }

    public function updatingRole(): void            { $this->resetPage(); }
    public function updatingLeaveCategoryId(): void { $this->resetPage(); }
    public function updatingDateFrom(): void        { $this->resetPage(); }
    public function updatingDateTo(): void          { $this->resetPage(); }
    public function updatingStatus(): void          { $this->resetPage(); }
    public function updatingSearch(): void          { $this->resetPage(); }

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
     * Common filters (date range, role, category, search) ছাড়া status filter।
     * Summary count-এর জন্য status বাদে বাকি সব filter দরকার হয়, তাই আলাদা রাখা হলো।
     */
    protected function baseQueryWithoutStatus()
    {
        $institutionId = institution()->id;
        $branchId      = $this->activeBranchId();

        return LeaveApplication::query()
            ->select('leave_applications.*')
            ->where('institution_id', $institutionId)
            ->where('branch_id', $branchId)
            ->where(function ($q) {
                // Leave period overlap: start_date <= dateTo AND end_date >= dateFrom
                $q->where('start_date', '<=', $this->dateTo)
                  ->where('end_date', '>=', $this->dateFrom);
            })
            ->when($this->role !== 'all', function ($q) {
                $model = $this->roleModelMap[$this->role] ?? null;
                if ($model) {
                    $q->where('applicable_type', $model)
                      ->whereHasMorph('applicable', $model, function ($sub) {
                          $sub->where('role', $this->role);
                      });
                }
            })
            ->when($this->leaveCategoryId !== 'all', fn ($q) => $q->where('leave_category_id', $this->leaveCategoryId))
            ->when($this->search !== '', function ($q) {
                // applicable_type এখানে সবসময় User::class (roleModelMap দেখুন)
                $q->whereHasMorph('applicable', User::class, function ($sub) {
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
        $sortColumn = $this->sortColumnMap[$this->sortField] ?? 'leave_applications.start_date';

        $records = $this->baseQuery()
            ->with(['applicable:id,name,role', 'leaveCategory:id,name', 'approvedByUser:id,name'])
            ->orderBy($sortColumn, $this->sortDirection)
            ->paginate($this->perPage);

        // Summary counts — একই ফিল্টার (তারিখ, role, category, search) দিয়ে, শুধু status বাদে
        $summary = $this->baseQueryWithoutStatus()
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $categories = LeaveCategory::where('institution_id', institution()->id)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('livewire.admin.report.leave-report-component', [
            'records'    => $records,
            'summary'    => $summary,
            'categories' => $categories,
        ])
        ->layout('layouts.admin.app', [
            'title' => 'Reports | ' . institution()->name,
            'breadcrumbs' => [
                ['name' => 'Leave Report', 'url' => route('admin.reports.leaves')],
            ],
        ]);
    }
}