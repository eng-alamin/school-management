<?php

namespace App\Livewire\Teacher\Salary;

use Livewire\Component;
use App\Models\Employee;
use App\Models\SalaryPayment;
use Carbon\Carbon;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PayslipComponent extends Component
{
    public int    $id;
    public string $month;

    public function mount(int $id, string $month): void
    {
        $employee = $this->currentEmployee();

        // SECURITY: The {id} in the URL must match the logged-in teacher's
        // own employee record. Without this check, a teacher could change
        // the id in the URL and view another employee's payslip
        // (IDOR / horizontal privilege escalation).
        if ($id !== $employee->id) {
            throw new NotFoundHttpException();
        }

        $this->id    = $id;
        $this->month = $month;
    }

    /**
     * BUG FIX: department/designation were not eager-loaded before, but the
     * payslip view accesses $employee['department']['name'] and
     * $employee['designation']['name'] directly. Without eager loading this
     * either triggers an N+1 query per payslip view or a null-access error
     * if the relation methods aren't defined for lazy loading in this context.
     */
    protected function currentEmployee(): Employee
    {
        return Employee::with(['department', 'designation'])
            ->where('user_id', auth()->id())
            ->where('institution_id', institution()->id)
            ->firstOrFail();
    }

    public function render()
    {
        $employee  = $this->currentEmployee();
        $monthDate = Carbon::createFromFormat('Y-m', $this->month)->startOfMonth()->toDateString();

        $salary = SalaryPayment::where('employee_id', $employee->id)
            ->where('institution_id', institution()->id)
            ->where('month', $monthDate)
            ->where('status', 'paid')
            ->firstOrFail();

        // BUG FIX: the view expects a $payment array with keys like
        // basic_salary, total_allowance, overtime_amount, total_deduction,
        // advance_deduction, net_salary, payment_date, id — but only
        // $salary (a raw model) was being passed before. Missing keys
        // default safely to 0 instead of throwing undefined-index errors.
        $payment = [
            'id'                => $salary->id,
            'payment_date'      => $salary->payment_date ?? null,
            'basic_salary'      => $salary->basic_salary ?? 0,
            'total_allowance'   => $salary->total_allowance ?? 0,
            'overtime_amount'   => $salary->overtime_amount ?? 0,
            'total_deduction'   => $salary->total_deduction ?? 0,
            'advance_deduction' => $salary->advance_deduction ?? 0,
            'net_salary'        => $salary->net_salary ?? $salary->basic_salary ?? 0,
        ];

        // BUG FIX: no allowance/deduction breakdown table is confirmed in the
        // current schema, so these default to empty collections. The blade
        // already handles this gracefully via @forelse ... @empty
        // ("No Information Available"). If a breakdown table/relation exists
        // (e.g. SalaryAllowance / SalaryDeduction), tell me and I will wire
        // it in here instead of the empty defaults.
        $allowances = [];
        $deductions = [];

        // BUG FIX: $schoolName / $schoolAddress / $schoolPhone / $schoolEmail
        // were referenced in the view but never passed at all.
        $schoolName    = institution()->name ?? null;
        $schoolAddress = institution()->address ?? null;
        $schoolPhone   = institution()->phone ?? null;
        $schoolEmail   = institution()->email ?? null;

        return view('livewire.teacher.salary.payslip-component', [
            'employee'      => $employee,
            'payment'       => $payment,
            'allowances'    => $allowances,
            'deductions'    => $deductions,
            'schoolName'    => $schoolName,
            'schoolAddress' => $schoolAddress,
            'schoolPhone'   => $schoolPhone,
            'schoolEmail'   => $schoolEmail,
        ])->layout('layouts.teacher.app', [
            'title' => 'Payslip | ' . institution()->name,
        ]);
    }

    /**
     * BUG FIX: the view calls $this->numberToWords() but this method did
     * not exist on the component at all, which would throw a
     * BadMethodCallException on every payslip render.
     *
     * Converts a Taka amount into English words, e.g. 45000 => "Taka Forty
     * Five Thousand Only".
     */
    public function numberToWords(float $amount): string
    {
        $amount = (int) round($amount);

        if ($amount === 0) {
            return 'Taka Zero Only';
        }

        $ones = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine',
                 'Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen',
                 'Seventeen', 'Eighteen', 'Nineteen'];
        $tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];

        $convertBelowThousand = function (int $n) use ($ones, $tens): string {
            $words = '';

            if ($n >= 100) {
                $words .= $ones[intdiv($n, 100)] . ' Hundred ';
                $n %= 100;
            }

            if ($n >= 20) {
                $words .= $tens[intdiv($n, 10)] . ' ';
                $n %= 10;
            }

            if ($n > 0) {
                $words .= $ones[$n] . ' ';
            }

            return trim($words);
        };

        $crore    = intdiv($amount, 10000000);
        $lakh     = intdiv($amount % 10000000, 100000);
        $thousand = intdiv($amount % 100000, 1000);
        $rest     = $amount % 1000;

        $parts = [];

        if ($crore > 0) {
            $parts[] = $convertBelowThousand($crore) . ' Crore';
        }

        if ($lakh > 0) {
            $parts[] = $convertBelowThousand($lakh) . ' Lakh';
        }

        if ($thousand > 0) {
            $parts[] = $convertBelowThousand($thousand) . ' Thousand';
        }

        if ($rest > 0) {
            $parts[] = $convertBelowThousand($rest);
        }

        return 'Taka ' . implode(' ', $parts) . ' Only';
    }
}