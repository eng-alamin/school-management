<?php

namespace App\Livewire\Accountant\Salary;

use Livewire\Component;
use App\Models\Employee;
use App\Models\SalaryAssign;
use App\Models\SalaryPayment;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AddPaymentComponent extends Component
{
    // ── Route params ──────────────────────────────────────────────
    public int    $employeeId;
    public string $month;           // format: Y-m  e.g. 2026-05

    // ── Employee / salary data (read-only display) ─────────────────
    public ?array $employee     = null;
    public ?array $salaryAssign = null;
    public array  $allowances   = [];
    public array  $deductions   = [];

    // ── Computed salary fields ────────────────────────────────────
    public float  $basicSalary    = 0;
    public float  $totalAllowance = 0;
    public float  $totalDeduction = 0;
    public float  $grossSalary    = 0;
    public float  $overtimeRate   = 0;

    // FIX 3: salary_grade is a string ('Grade A', 'Grade B'), not a float.
    //         Declaring it as float caused (float)'Grade A' = 0 — always blank.
    public string $salaryGrade = '';

    // ── Editable payment fields ───────────────────────────────────
    public float  $overtimeHour   = 0;
    public float  $overtimeAmount = 0;
    public float  $netSalary      = 0;

    public string $payVia      = '';
    public ?int   $accountId   = null;
    public string $remarks     = '';
    public string $paymentDate = '';

    // ── UI state ──────────────────────────────────────────────────
    public bool   $alreadyPaid     = false;
    public ?array $existingPayment = null;

    // ─────────────────────────────────────────────────────────────

    public function mount(int $id, string $month): void
    {
        // FIX 6: Validate month format on mount so a malformed URL param
        //         does not cause a silent Carbon exception later in the page.
        if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
            abort(400, 'Invalid month format. Expected YYYY-MM.');
        }

        $this->employeeId  = $id;
        $this->month       = $month;
        $this->paymentDate = now()->format('Y-m-d');

        $this->loadEmployee();
        $this->loadSalaryAssign();
        $this->checkExistingPayment();
        $this->recalculate();
    }

    // ── Load employee ─────────────────────────────────────────────
    private function loadEmployee(): void
    {
        $emp = Employee::with(['designation', 'department'])
            ->findOrFail($this->employeeId);

        // Employee model has no date cast on joining_date — it comes from
        // the DB as a raw string, not a Carbon object. Using ?->format() on
        // a string causes "Call to a member function format() on string".
        // Use Carbon::parse() instead, which accepts both strings and objects.
        $data = $emp->toArray();
        $data['joining_date'] = $emp->joining_date
            ? \Carbon\Carbon::parse($emp->joining_date)->format('Y-m-d')
            : null;

        $this->employee = $data;
    }

    // ── Load salary assign + template allowances/deductions ────────
    private function loadSalaryAssign(): void
    {
        $assign = SalaryAssign::with([
            'salaryTemplate.allowances',
            'salaryTemplate.deductions',
        ])->where('employee_id', $this->employeeId)->first();

        if (!$assign) return;

        $this->salaryAssign   = $assign->toArray();
        $this->basicSalary    = (float) ($assign->basic_salary    ?? 0);
        $this->totalAllowance = (float) ($assign->total_allowance ?? 0);
        $this->totalDeduction = (float) ($assign->total_deduction ?? 0);
        $this->grossSalary    = (float) ($assign->gross_salary    ?? ($this->basicSalary + $this->totalAllowance));
        $this->overtimeRate   = (float) ($assign->overtime_rate   ?? 0);

        // FIX 3: salary_grade is a string — cast to string, not float.
        $this->salaryGrade = (string) ($assign->salary_grade ?? '');

        if ($assign->salaryTemplate?->allowances) {
            $this->allowances = $assign->salaryTemplate->allowances
                ->map(fn($a) => ['name' => $a->name, 'amount' => (float) $a->amount])
                ->toArray();
        }

        if ($assign->salaryTemplate?->deductions) {
            $this->deductions = $assign->salaryTemplate->deductions
                ->map(fn($d) => ['name' => $d->name, 'amount' => (float) $d->amount])
                ->toArray();
        }
    }

    // ── Check if already paid this month ──────────────────────────
    private function checkExistingPayment(): void
    {
        $monthDate = Carbon::createFromFormat('Y-m', $this->month)->startOfMonth()->toDateString();

        $payment = SalaryPayment::where('employee_id', $this->employeeId)
            ->where('month', $monthDate)
            ->first();

        if ($payment) {
            $this->alreadyPaid = ($payment->status === 'paid');

            // FIX 4: toArray() on a model with date casts returns Carbon objects.
            //         Serialize date fields to strings so existingPayment is
            //         safe to use in Blade or pass around as plain array data.
            $data = $payment->toArray();
            $data['month']        = $payment->month?->format('Y-m-d');
            $data['payment_date'] = $payment->payment_date?->format('Y-m-d');

            $this->existingPayment = $data;
        }
    }

    // ── Recalculate net salary ─────────────────────────────────────
    private function recalculate(): void
    {
        $this->overtimeAmount = $this->overtimeHour * $this->overtimeRate;
        $this->netSalary      = $this->grossSalary + $this->overtimeAmount - $this->totalDeduction;
    }

    // ── Livewire: overtime hour changed ───────────────────────────
    public function updatedOvertimeHour(): void
    {
        $this->overtimeHour = max(0, (float) $this->overtimeHour);
        $this->recalculate();
    }

    // ── Process payment ───────────────────────────────────────────
    // FIX 7: Return type changed from void to mixed.
    //         void functions cannot return a value — PHP throws a TypeError.
    //         This method returns redirect() on success and null on early exit.
    public function processPayment(): mixed
    {
        $this->validate([
            'paymentDate' => 'required|date',
            'payVia'      => 'required|in:cash,bank,cheque,mobile_banking',
        ], [
            'payVia.required' => 'Please select a payment method.',
            'payVia.in'       => 'Invalid payment method selected.',
        ]);

        $monthDate = Carbon::createFromFormat('Y-m', $this->month)->startOfMonth()->toDateString();

        // FIX 2: Server-side already-paid guard was missing.
        //         Anyone can POST to this action on a paid employee via direct URL.
        $isPaid = SalaryPayment::where('employee_id', $this->employeeId)
            ->where('month', $monthDate)
            ->where('status', 'paid')
            ->exists();

        if ($isPaid) {
            $this->alreadyPaid = true;
            $this->dispatch('notify', type: 'error', message: 'Salary already paid for this month.');
            return null;
        }

        $assign = SalaryAssign::where('employee_id', $this->employeeId)->first();

        DB::transaction(function () use ($monthDate, $assign) {
            SalaryPayment::updateOrCreate(
                [
                    'employee_id' => $this->employeeId,
                    'month'       => $monthDate,
                ],
                [
                    'salary_assign_id' => $assign?->id,
                    'basic_salary'     => $this->basicSalary,
                    'total_allowance'  => $this->totalAllowance,
                    'total_deduction'  => $this->totalDeduction,
                    'gross_salary'     => $this->grossSalary,
                    'overtime_hour'    => $this->overtimeHour,
                    'overtime_rate'    => $this->overtimeRate,
                    'overtime_amount'  => $this->overtimeAmount,
                    'net_salary'       => $this->netSalary,
                    'payment_date'     => $this->paymentDate,
                    'payment_method'   => $this->payVia,
                    'account_id'       => $this->accountId ?: null,
                    'note'             => $this->remarks    ?: null,
                    'status'           => 'paid',
                    'paid_by'          => Auth::id(),
                ]
            );
        });

        $this->alreadyPaid = true;
        $this->dispatch('notify', type: 'success', message: 'Salary paid successfully.');

        return redirect()->route('accountant.salary.payment');
    }

    // ── Render ────────────────────────────────────────────────────
    public function render()
    {
        $officeAccounts = DB::table('office_accounts')->get(['id', 'name']);

        return view('livewire.accountant.salary.add-payment-component', [
            'officeAccounts' => $officeAccounts,
        ])->layout('layouts.accountant.app', [
            'title' => 'Salary | ' . institution()->name,
        ]);
    }
}