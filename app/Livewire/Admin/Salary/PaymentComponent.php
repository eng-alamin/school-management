<?php

namespace App\Livewire\Admin\Salary;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Employee;
use App\Models\SalaryPayment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PaymentComponent extends Component
{
    use WithPagination;

    // ── Filters ───────────────────────────────────────────────────
    public string $role        = '';
    public string $month       = '';
    public bool   $hasFiltered = false;

    // ── Table controls ────────────────────────────────────────────
    public string $search  = '';
    public int    $perPage = 25;

    // ── Roles map ─────────────────────────────────────────────────
    public array $roles = [
        'teacher'    => 'Teacher',
        'accountant' => 'Accountant',
        'staff'      => 'Staff',
    ];

    // ─────────────────────────────────────────────────────────────

    public string $routePrefix = '';

    public function mount(): void
    {
        $this->routePrefix = $this->resolveRoutePrefix();

        $this->month = now()->format('Y-m');
    }

    protected function resolveRoutePrefix(): string
    {
        $routeName = request()->route()?->getName();

        if ($routeName && str_contains($routeName, '.')) {
            return explode('.', $routeName)[0] . '.';
        }

        $segment = request()->segment(1);

        return $segment ? $segment . '.' : '';
    }

    public function updatedSearch(): void  { $this->resetPage(); }
    public function updatedPerPage(): void { $this->resetPage(); }
    public function updatedRole(): void
    {
        $this->hasFiltered = false;
        $this->resetPage();
    }

    public function filter(): void
    {
        $this->validate([
            'role'  => 'required',
            'month' => 'required|date_format:Y-m',
        ], [
            'role.required'     => 'Please select a role.',
            'month.required'    => 'Please select a month.',
            'month.date_format' => 'Month must be in YYYY-MM format.',
        ]);

        $this->hasFiltered = true;
        $this->resetPage();
    }

    public function resetForm(): void
    {
        $this->role        = '';
        $this->month       = now()->format('Y-m');
        $this->search      = '';
        $this->hasFiltered = false;
        $this->resetPage();
        $this->resetValidation();
    }

    // ---------------------------------------------------------------
    // Selected Month বর্তমান মাসের চেয়ে আগে (Past) নাকি বর্তমান/ভবিষ্যৎ
    // (Current/Future) সেটা নির্ধারণ করা হয় — Badge/Action Logic-এ ব্যবহার হবে
    // ---------------------------------------------------------------
    private function isSelectedMonthInPast(): bool
    {
        if (!$this->month) {
            return false;
        }

        $selected = Carbon::createFromFormat('Y-m', $this->month)->startOfMonth();
        $current  = now()->startOfMonth();

        return $selected->lessThan($current);
    }

    private function employeeQuery()
    {
        $monthDate     = Carbon::createFromFormat('Y-m', $this->month)->startOfMonth()->toDateString();
        $institutionId = institution()->id;

        return Employee::with(['user', 'designation', 'department'])
            ->whereHas('user', fn ($q) => $q->where('role', $this->role))
            ->when($this->search, function ($q) {
                $s = '%' . $this->search . '%';
                $q->where(function ($qq) use ($s) {
                    $qq->whereHas('user', fn ($uq) => $uq->where('name', 'like', $s)
                            ->orWhere('phone', 'like', $s)
                            ->orWhere('email', 'like', $s))
                       ->orWhere('employees.name', 'like', $s)
                       ->orWhere('employees.mobile', 'like', $s);
                });
            })
            ->addSelect([
                'employees.*',

                // NOTE: DB::table() raw query bypasses Eloquent global scopes entirely,
                // so institution_id must always be filtered explicitly here.
                'sa_grade' => DB::table('salary_assigns')
                    ->select('salary_grade')
                    ->whereColumn('employee_id', 'employees.id')
                    ->where('institution_id', $institutionId)
                    ->limit(1),

                'sa_basic' => DB::table('salary_assigns')
                    ->select('basic_salary')
                    ->whereColumn('employee_id', 'employees.id')
                    ->where('institution_id', $institutionId)
                    ->limit(1),

                'sa_id' => DB::table('salary_assigns')
                    ->select('id')
                    ->whereColumn('employee_id', 'employees.id')
                    ->where('institution_id', $institutionId)
                    ->limit(1),

                // FIX (Critical): Passing an Eloquent Builder directly into addSelect()
                // does NOT automatically apply the model's global scope (BelongsToInstitution).
                // Global scopes are only applied via applyScopes(), which is triggered by
                // terminal methods like get()/first()/toBase() — not when the builder is
                // simply converted to a raw subquery via getQuery(). Relying on the global
                // scope here was an incorrect assumption and a multi-tenant data leak risk.
                // institution_id is now filtered explicitly, matching the sa_grade/sa_basic
                // pattern above.

                // FIX (Bug): We need to know whether a salary_payments ROW EXISTS for the
                // selected month at all — not just its status — because a NULL status could
                // mean either "no record" or a genuinely null status. salary_payment_id is
                // the reliable existence flag used by the view to decide between
                // "No Data Found" (past month, never processed) vs.
                // "Invoice Not Generated Yet" (current/future month, payroll not run yet).
                'salary_payment_id' => SalaryPayment::select('id')
                    ->whereColumn('employee_id', 'employees.id')
                    ->where('institution_id', $institutionId)
                    ->where('month', $monthDate)
                    ->limit(1),

                'salary_status' => SalaryPayment::select('status')
                    ->whereColumn('employee_id', 'employees.id')
                    ->where('institution_id', $institutionId)
                    ->where('month', $monthDate)
                    ->limit(1),

                'salary_basic' => SalaryPayment::select('basic_salary')
                    ->whereColumn('employee_id', 'employees.id')
                    ->where('institution_id', $institutionId)
                    ->where('month', $monthDate)
                    ->limit(1),
            ]);
    }

    public function render()
    {
        $employees = $this->hasFiltered
            ? $this->employeeQuery()->paginate($this->perPage)
            : null;

        return view('livewire.admin.salary.payment-component', [
            'employees'          => $employees,
            'isSelectedMonthPast' => $this->isSelectedMonthInPast(),
        ])->layout('layouts.admin.app', [
            'title' => 'Payroll | ' . institution()->name,
        ]);
    }
}