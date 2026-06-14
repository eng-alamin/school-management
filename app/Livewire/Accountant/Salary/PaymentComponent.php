<?php

namespace App\Livewire\Accountant\Salary;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Employee;
use App\Models\SalaryAssign;
use App\Models\SalaryPayment;
use App\Models\SalaryTemplate;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
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

    // ── Pay Now modal ─────────────────────────────────────────────
    public bool   $showPayModal   = false;
    public ?int   $payEmployeeId  = null;
    public string $paymentDate    = '';
    public string $paymentMethod  = 'cash';
    public ?int   $accountId      = null;
    public string $transactionId  = '';
    public string $note           = '';

    // ── Payslip modal ─────────────────────────────────────────────
    public bool   $showPayslip = false;
    public ?array $payslipData = null;

    // ── Roles map ─────────────────────────────────────────────────
    public array $roles = [
        'accountant'        => 'Accountant',
        'teacher'      => 'Teacher',
        'accountant'   => 'Accountant',
        'librarian'    => 'Librarian',
        'receptionist' => 'Receptionist',
    ];

    // ─────────────────────────────────────────────────────────────

    public function mount(): void
    {
        $this->month       = now()->format('Y-m');
        $this->paymentDate = now()->format('Y-m-d');
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

    public function openPayModal(int $employeeId): void
    {
        $monthDate   = Carbon::createFromFormat('Y-m', $this->month)->startOfMonth()->toDateString();
        $alreadyPaid = SalaryPayment::where('employee_id', $employeeId)
            ->where('month', $monthDate)
            ->where('status', 'paid')
            ->exists();

        if ($alreadyPaid) {
            $this->dispatch('notify', type: 'error', message: 'Salary already paid for this employee this month.');
            return;
        }

        $this->payEmployeeId = $employeeId;
        $this->paymentDate   = now()->format('Y-m-d');
        $this->paymentMethod = 'cash';
        $this->accountId     = null;
        $this->transactionId = '';
        $this->note          = '';
        $this->showPayModal  = true;
    }

    public function processPayment(): void
    {
        $this->validate([
            'paymentDate'   => 'required|date',
            'paymentMethod' => 'required|in:cash,bank,cheque,mobile_banking',
        ]);

        $monthDate   = Carbon::createFromFormat('Y-m', $this->month)->startOfMonth()->toDateString();
        $alreadyPaid = SalaryPayment::where('employee_id', $this->payEmployeeId)
            ->where('month', $monthDate)
            ->where('status', 'paid')
            ->exists();

        if ($alreadyPaid) {
            $this->showPayModal = false;
            $this->dispatch('notify', type: 'error', message: 'Salary already paid for this employee this month.');
            return;
        }

        $assign    = SalaryAssign::where('employee_id', $this->payEmployeeId)->first();
        $basic     = (float) ($assign?->basic_salary    ?? 0);
        $allowance = (float) ($assign?->total_allowance ?? 0);
        $deduction = (float) ($assign?->total_deduction ?? 0);
        $gross     = (float) ($assign?->gross_salary    ?? ($basic + $allowance));
        $net       = (float) ($assign?->net_salary      ?? ($gross - $deduction));

        DB::transaction(function () use ($monthDate, $assign, $basic, $allowance, $deduction, $gross, $net) {
            SalaryPayment::updateOrCreate(
                [
                    'employee_id' => $this->payEmployeeId,
                    'month'       => $monthDate,
                ],
                [
                    'salary_assign_id' => $assign?->id,
                    'basic_salary'     => $basic,
                    'total_allowance'  => $allowance,
                    'total_deduction'  => $deduction,
                    'gross_salary'     => $gross,
                    'net_salary'       => $net,
                    'payment_date'     => $this->paymentDate,
                    'payment_method'   => $this->paymentMethod,
                    'account_id'       => $this->accountId     ?: null,
                    'transaction_id'   => $this->transactionId ?: null,
                    'note'             => $this->note           ?: null,
                    'status'           => 'paid',
                    'paid_by'          => Auth::id(),
                ]
            );
        });

        $this->showPayModal = false;
        $this->dispatch('notify', type: 'success', message: 'Salary paid successfully.');
    }

    public function openPayslip(int $employeeId): void
    {
        $monthDate = Carbon::createFromFormat('Y-m', $this->month)->startOfMonth()->toDateString();

        $payment = SalaryPayment::with(['employee.user', 'employee.designation', 'employee.department'])
            ->where('employee_id', $employeeId)
            ->where('month', $monthDate)
            ->first();

        if (!$payment) {
            $this->dispatch('notify', type: 'error', message: 'No payslip found for this employee.');
            return;
        }

        $data                 = $payment->toArray();
        $data['month']        = $payment->month?->format('Y-m-d');
        $data['payment_date'] = $payment->payment_date?->format('Y-m-d');

        $this->payslipData = $data;
        $this->showPayslip = true;
    }

    private function employeeQuery()
    {
        $monthDate = Carbon::createFromFormat('Y-m', $this->month)->startOfMonth()->toDateString();

        return Employee::with(['user', 'designation', 'department'])
            ->whereHas('user', fn($q) => $q->where('role', $this->role))
            ->when($this->search, function ($q) {
                $s = '%' . $this->search . '%';
                $q->where(function ($qq) use ($s) {
                    $qq->whereHas('user', fn($uq) => $uq->where('name', 'like', $s)
                            ->orWhere('phone', 'like', $s)
                            ->orWhere('email', 'like', $s))
                       ->orWhere('employees.name', 'like', $s)
                       ->orWhere('employees.mobile', 'like', $s);
                });
            })
            ->addSelect([
                'employees.*',

                'sa_grade' => DB::table('salary_assigns')
                    ->select('salary_grade')
                    ->whereColumn('employee_id', 'employees.id')
                    ->limit(1),

                'sa_basic' => DB::table('salary_assigns')
                    ->select('basic_salary')
                    ->whereColumn('employee_id', 'employees.id')
                    ->limit(1),

                'sa_id' => DB::table('salary_assigns')
                    ->select('id')
                    ->whereColumn('employee_id', 'employees.id')
                    ->limit(1),

                'salary_status' => SalaryPayment::select('status')
                    ->whereColumn('employee_id', 'employees.id')
                    ->where('month', $monthDate)
                    ->limit(1),

                'salary_basic' => SalaryPayment::select('basic_salary')
                    ->whereColumn('employee_id', 'employees.id')
                    ->where('month', $monthDate)
                    ->limit(1),
            ]);
    }

    public function render()
    {
        $employees = $this->hasFiltered
            ? $this->employeeQuery()->paginate($this->perPage)
            : null;

        $officeAccounts = DB::table('office_accounts')->get(['id', 'name']);

        return view('livewire.accountant.salary.payment-component', [
            'employees'      => $employees,
            'officeAccounts' => $officeAccounts,
        ])->layout('layouts.accountant.app', [
            'title' => 'Payroll | Salary Payments',
        ]);
    }
}