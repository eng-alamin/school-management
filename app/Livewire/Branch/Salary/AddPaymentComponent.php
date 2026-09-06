<?php

namespace App\Livewire\Branch\Salary;

use Livewire\Component;
use App\Models\Employee;
use App\Models\OfficeAccount;
use App\Models\SalaryAssign;
use App\Models\SalaryPayment;
use App\Models\SalaryAdvance;
use App\Models\SalaryAdvanceRepayment;
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

    public string $salaryGrade = '';

    // ── Salary Advance (auto-deduction) ────────────────────────────
    public ?int  $activeAdvanceId  = null;
    public float $advanceDeduction = 0;
    public float $advanceRemaining = 0;

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
        if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
            abort(400, 'Invalid month format. Expected YYYY-MM.');
        }

        $this->employeeId  = $id;
        $this->month       = $month;
        $this->paymentDate = now()->format('Y-m-d');

        $this->loadEmployee();
        $this->loadSalaryAssign();
        $this->loadActiveAdvance();
        $this->checkExistingPayment();
        $this->recalculate();
    }

    private function loadEmployee(): void
    {
        $emp = Employee::with(['designation', 'department'])
            ->findOrFail($this->employeeId);

        $data = $emp->toArray();
        $data['joining_date'] = $emp->joining_date
            ? \Carbon\Carbon::parse($emp->joining_date)->format('Y-m-d')
            : null;

        $this->employee = $data;
    }

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
        $this->salaryGrade    = (string) ($assign->salary_grade ?? '');

        if ($assign->salaryTemplate?->allowances) {
            $this->allowances = $assign->salaryTemplate->allowances
                ->map(fn ($a) => ['name' => $a->name, 'amount' => (float) $a->amount])
                ->toArray();
        }

        if ($assign->salaryTemplate?->deductions) {
            $this->deductions = $assign->salaryTemplate->deductions
                ->map(fn ($d) => ['name' => $d->name, 'amount' => (float) $d->amount])
                ->toArray();
        }
    }

    /**
     * Finds the employee's single active (unsettled) advance, if any, and
     * works out how much should be auto-deducted from THIS payment.
     */
    private function loadActiveAdvance(): void
    {
        $advance = SalaryAdvance::where('employee_id', $this->employeeId)
            ->active()
            ->first();

        if (!$advance) return;

        $this->activeAdvanceId  = $advance->id;
        $this->advanceDeduction = $advance->nextDeductionAmount();
        $this->advanceRemaining = (float) $advance->remaining_amount;
    }

    private function checkExistingPayment(): void
    {
        $monthDate = Carbon::createFromFormat('Y-m', $this->month)->startOfMonth()->toDateString();

        $payment = SalaryPayment::where('employee_id', $this->employeeId)
            ->where('month', $monthDate)
            ->first();

        if ($payment) {
            $this->alreadyPaid = ($payment->status === 'paid');

            $data = $payment->toArray();
            $data['month']        = $payment->month?->format('Y-m-d');
            $data['payment_date'] = $payment->payment_date?->format('Y-m-d');

            $this->existingPayment = $data;

            // If already paid, show the advance amount that WAS deducted
            // (snapshot), not a freshly recalculated one.
            if ($this->alreadyPaid) {
                $this->advanceDeduction = (float) ($payment->advance_deduction ?? 0);
            }
        }
    }

    private function recalculate(): void
    {
        $this->overtimeAmount = $this->overtimeHour * $this->overtimeRate;
        $this->netSalary      = $this->grossSalary + $this->overtimeAmount
                                 - $this->totalDeduction - $this->advanceDeduction;
    }

    public function updatedOvertimeHour(): void
    {
        $this->overtimeHour = max(0, (float) $this->overtimeHour);
        $this->recalculate();
    }

    public function processPayment(): mixed
    {
        $this->validate([
            'paymentDate'  => 'required|date',
            'payVia'       => 'required|in:cash,bank,cheque,mobile_banking',
            'accountId'    => 'required_if:payVia,bank,cheque,mobile_banking|nullable|exists:office_accounts,id',
            'overtimeHour' => 'nullable|numeric|min:0',
        ], [
            'payVia.required'       => 'Please select a payment method.',
            'payVia.in'             => 'Invalid payment method selected.',
            'accountId.required_if' => 'Please select an account for this payment method.',
        ]);

        $monthDate = Carbon::createFromFormat('Y-m', $this->month)->startOfMonth()->toDateString();

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
            $payment = SalaryPayment::updateOrCreate(
                [
                    'institution_id' => institution()->id,
                    'employee_id'    => $this->employeeId,
                    'month'          => $monthDate,
                ],
                [
                    'salary_assign_id'  => $assign?->id,
                    'basic_salary'      => $this->basicSalary,
                    'total_allowance'   => $this->totalAllowance,
                    'total_deduction'   => $this->totalDeduction,
                    'advance_deduction' => $this->advanceDeduction,
                    'gross_salary'      => $this->grossSalary,
                    'overtime_hour'     => $this->overtimeHour,
                    'overtime_rate'     => $this->overtimeRate,
                    'overtime_amount'   => $this->overtimeAmount,
                    'net_salary'        => $this->netSalary,
                    'payment_date'      => $this->paymentDate,
                    'payment_method'    => $this->payVia,
                    'account_id'        => $this->accountId ?: null,
                    'note'              => $this->remarks    ?: null,
                    'status'            => 'paid',
                    'paid_by'           => Auth::id(),
                ]
            );

            // FIX: record the advance repayment (if any) so SalaryAdvance's
            // remaining_amount auto-syncs via SalaryAdvanceRepaymentObserver.
            if ($this->activeAdvanceId && $this->advanceDeduction > 0) {
                SalaryAdvanceRepayment::create([
                    'institution_id'    => institution()->id,
                    'salary_advance_id' => $this->activeAdvanceId,
                    'salary_payment_id' => $payment->id,
                    'amount'            => $this->advanceDeduction,
                    'deducted_date'     => $this->paymentDate,
                ]);
            }
        });

        $this->alreadyPaid = true;
        $this->dispatch('notify', type: 'success', message: 'Salary paid successfully.');

        return redirect()->route('branch.salary.invoice-payment', [
            'id'    => $this->employeeId,
            'month' => $this->month,
        ]);
    }

    public function render()
    {
        $officeAccounts = OfficeAccount::query()
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return view('livewire.admin.salary.add-payment-component', [
            'officeAccounts' => $officeAccounts,
        ])->layout('layouts.branch.app', [
            'title' => 'Salary | ' . institution()->name,
        ]);
    }
}