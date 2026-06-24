<?php

namespace App\Livewire\Admin\Salary;

use Livewire\Component;
use App\Models\Employee;
use App\Models\SalaryPayment;
use App\Models\SalaryAssign;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class InvoicePaymentComponent extends Component
{
    public int    $employeeId;
    public string $month;           // Y-m  e.g. 2026-05

    public ?array $employee    = null;
    public ?array $payment     = null;
    public ?array $assign      = null;
    public array  $allowances  = [];
    public array  $deductions  = [];

    // School / tenant info
    public string $schoolName    = '';
    public string $schoolAddress = '';
    public string $schoolPhone   = '';
    public string $schoolEmail   = '';
    public string $schoolLogo    = '';

    public function mount(int $id, string $month): void
    {
        if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
            abort(400, 'Invalid month format. Expected YYYY-MM.');
        }

        $this->employeeId = $id;
        $this->month      = $month;

        $this->loadEmployee();
        $this->loadPayment();
        $this->loadAssignDetails();
        $this->loadSchoolInfo();
    }

    private function loadEmployee(): void
    {
        $emp = Employee::with(['designation', 'department'])
            ->findOrFail($this->employeeId);

        $data = $emp->toArray();
        $data['joining_date'] = $emp->joining_date
            ? Carbon::parse($emp->joining_date)->format('Y-m-d')
            : null;

        $this->employee = $data;
    }

    private function loadPayment(): void
    {
        $monthDate = Carbon::createFromFormat('Y-m', $this->month)
            ->startOfMonth()->toDateString();

        $pay = SalaryPayment::where('employee_id', $this->employeeId)
            ->where('month', $monthDate)
            ->first();

        if (!$pay) {
            abort(404, 'Payment record not found for this month.');
        }

        $data = $pay->toArray();
        $data['month']        = $pay->month?->format('Y-m-d') ?? $monthDate;
        $data['payment_date'] = $pay->payment_date instanceof \Carbon\Carbon
            ? $pay->payment_date->format('Y-m-d')
            : ($pay->payment_date ?? null);

        $this->payment = $data;
    }

    private function loadAssignDetails(): void
    {
        $assign = SalaryAssign::with([
            'salaryTemplate.allowances',
            'salaryTemplate.deductions',
        ])->where('employee_id', $this->employeeId)->first();

        if (!$assign) return;

        $this->assign = $assign->toArray();

        // Overtime row synthesized from payment data
        $overtimeAmount = (float) ($this->payment['overtime_amount'] ?? 0);
        $overtimeHour   = (float) ($this->payment['overtime_hour']   ?? 0);

        if ($assign->salaryTemplate?->allowances) {
            $this->allowances = $assign->salaryTemplate->allowances
                ->map(fn($a) => ['name' => $a->name, 'amount' => (float) $a->amount])
                ->toArray();
        }

        // Append overtime as an allowance row if applicable
        if ($overtimeAmount > 0) {
            $this->allowances[] = [
                'name'   => 'Overtime Salary (' . (int) $overtimeHour . ' Hour)',
                'amount' => $overtimeAmount,
            ];
        }

        if ($assign->salaryTemplate?->deductions) {
            $this->deductions = $assign->salaryTemplate->deductions
                ->map(fn($d) => ['name' => $d->name, 'amount' => (float) $d->amount])
                ->toArray();
        }
    }

    private function loadSchoolInfo(): void
    {
        $setting = DB::table('settings')->first();
        if ($setting) {
            $this->schoolName    = $setting->school_name    ?? $setting->name    ?? '';
            $this->schoolAddress = $setting->address        ?? '';
            $this->schoolPhone   = $setting->phone          ?? '';
            $this->schoolEmail   = $setting->email          ?? '';
            $this->schoolLogo    = $setting->logo           ?? '';
        }
    }

    // Convert number to words (simple, up to millions)
    public function numberToWords(float $number): string
    {
        $number  = (int) round($number);
        $ones    = ['', 'ONE', 'TWO', 'THREE', 'FOUR', 'FIVE', 'SIX', 'SEVEN', 'EIGHT', 'NINE',
                    'TEN', 'ELEVEN', 'TWELVE', 'THIRTEEN', 'FOURTEEN', 'FIFTEEN', 'SIXTEEN',
                    'SEVENTEEN', 'EIGHTEEN', 'NINETEEN'];
        $tens    = ['', '', 'TWENTY', 'THIRTY', 'FORTY', 'FIFTY', 'SIXTY', 'SEVENTY', 'EIGHTY', 'NINETY'];

        if ($number === 0) return 'ZERO';

        $convert = function (int $n) use (&$convert, $ones, $tens): string {
            if ($n < 20)     return $ones[$n];
            if ($n < 100)    return $tens[(int)($n / 10)] . ($n % 10 ? ' ' . $ones[$n % 10] : '');
            if ($n < 1000)   return $ones[(int)($n / 100)] . ' HUNDRED' . ($n % 100 ? ' ' . $convert($n % 100) : '');
            if ($n < 100000) return $convert((int)($n / 1000)) . ' THOUSAND' . ($n % 1000 ? ' ' . $convert($n % 1000) : '');
            if ($n < 10000000) return $convert((int)($n / 100000)) . ' LAKH' . ($n % 100000 ? ' ' . $convert($n % 100000) : '');
            return $convert((int)($n / 10000000)) . ' CRORE' . ($n % 10000000 ? ' ' . $convert($n % 10000000) : '');
        };

        return '(' . $convert($number) . ')';
    }

    public function render()
    {
        return view('livewire.admin.salary.invoice-payment-component')
            ->layout('layouts.admin.app', [
                'title' => 'Salary Payslip | ' . institution()->name,
            ]);
    }
}