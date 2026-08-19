<?php

namespace App\Livewire\Branch\Employee;

use Livewire\Component;
use App\Models\Employee;
use App\Models\SalaryPayment;
use App\Models\OfficeAccount;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class EmployeeInvoiceComponent extends Component
{
    public $employee;
    public $salaryPayments; // Flat collection — checkbox/payment logic এর জন্য
    public array $paymentsByYear = []; // Year অনুযায়ী Group করা (Accordion Display এর জন্য)

    public array $selectedIds = []; // Selected Salary Payment IDs
    public bool  $selectAll   = false;

    // ---- Payment Modal ----
    public bool   $showPaymentModal = false;
    public array  $paymentRows      = []; // [salary_payment_id => ['month_label'=>, 'net_salary'=>]]
    public string $paymentMethod    = 'cash';
    public string $paymentDate;
    public ?int    $accountId       = null;
    public ?string $transactionId   = null;
    public ?string $note            = null;

    public $officeAccounts = [];

    public string $routePrefix = '';

    public function mount(int $id)
    {
        $this->employee = Employee::with([
            'designation',
            'department',
            'user',
        ])->findOrFail($id);

        $this->paymentDate    = now()->format('Y-m-d');
        $this->officeAccounts = OfficeAccount::orderBy('name')->get();

        $this->loadSalaryPayments();
        $this->routePrefix = $this->resolveRoutePrefix();
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

    private function loadSalaryPayments(): void
    {
        $this->salaryPayments = SalaryPayment::where('institution_id', institution()->id)
            ->where('employee_id', $this->employee->id)
            ->orderByDesc('month')
            ->get();

        $this->groupPaymentsByYear();
    }

    // ---------------------------------------------------------------
    // Year (month column) অনুযায়ী Salary Payment গুলোকে Group করা
    // ---------------------------------------------------------------
    private function groupPaymentsByYear(): void
    {
        $grouped = [];

        $years = $this->salaryPayments
            ->map(fn ($payment) => Carbon::parse($payment->month)->format('Y'))
            ->unique()
            ->sortDesc()
            ->values();

        foreach ($years as $year) {
            $yearPayments = $this->salaryPayments->filter(
                fn ($payment) => Carbon::parse($payment->month)->format('Y') === $year
            )->values();

            if ($yearPayments->isNotEmpty()) {
                $grouped[] = [
                    'year'     => $year,
                    'payments' => $yearPayments,
                ];
            }
        }

        $this->paymentsByYear = $grouped;
    }

    public function updatedSelectAll(bool $value): void
    {
        $this->selectedIds = $value
            ? $this->salaryPayments->pluck('id')->toArray()
            : [];
    }

    public function updatedSelectedIds(): void
    {
        $this->selectAll = $this->salaryPayments->count() > 0
            && count($this->selectedIds) === $this->salaryPayments->count();
    }

    // ---------------------------------------------------------------
    // Modal Open — সিলেক্ট করা Salary Payment গুলো থেকে Payment Row তৈরি হয়
    // শুধু unpaid/partial status-এর record গুলো নেওয়া হয়
    // ---------------------------------------------------------------
    public function collectSelected(): void
    {
        if (empty($this->selectedIds)) {
            $this->dispatch('toast', type: 'error', message: 'কোনো Salary Record সিলেক্ট করা হয়নি।');
            return;
        }

        $this->paymentRows = [];

        foreach ($this->salaryPayments as $payment) {
            if (!in_array($payment->id, $this->selectedIds)) {
                continue;
            }

            if ($payment->status === 'paid') {
                continue;
            }

            $this->paymentRows[$payment->id] = [
                'month_label' => Carbon::parse($payment->month)->format('F, Y'),
                'net_salary'  => (float) $payment->net_salary,
            ];
        }

        if (empty($this->paymentRows)) {
            $this->dispatch('toast', type: 'error', message: 'সিলেক্ট করা Record গুলো ইতিমধ্যে Paid অথবা কোনো Net Salary নেই।');
            return;
        }

        $this->paymentMethod  = 'cash';
        $this->paymentDate    = now()->format('Y-m-d');
        $this->accountId      = null;
        $this->transactionId  = null;
        $this->note           = null;

        $this->showPaymentModal = true;
    }

    public function closePaymentModal(): void
    {
        $this->showPaymentModal = false;
        $this->paymentRows      = [];
        $this->resetErrorBag();
    }

    public function getTotalPayAmountProperty(): float
    {
        return collect($this->paymentRows)->sum(fn ($row) => (float) $row['net_salary']);
    }

    // ---------------------------------------------------------------
    // Payment Save — প্রতিটা Selected Salary Payment রেকর্ডকে Full Paid হিসেবে Mark করা হয়
    // (Schema-তে paid_amount/due_amount কলাম না থাকায় Partial Amount Track করা সম্ভব না,
    //  তাই এখানে শুধু Full Payment সাপোর্ট করা হচ্ছে)
    // ---------------------------------------------------------------
    public function savePayment(): void
    {
        $this->validate([
            'paymentMethod' => 'required|string|in:cash,card,bank,cheque,mobile_banking',
            'paymentDate'   => 'required|date',
            'accountId'     => 'nullable|exists:office_accounts,id',
            'transactionId' => 'nullable|string|max:100',
            'note'          => 'nullable|string|max:255',
        ]);

        if (empty($this->paymentRows)) {
            $this->dispatch('toast', type: 'error', message: 'Payment করার মতো কোনো Record নেই।');
            return;
        }

        DB::transaction(function () {
            foreach ($this->paymentRows as $paymentId => $row) {
                $payment = SalaryPayment::lockForUpdate()->findOrFail($paymentId);

                $payment->update([
                    'payment_date'   => $this->paymentDate,
                    'payment_method' => $this->paymentMethod,
                    'account_id'     => $this->accountId,
                    'transaction_id' => $this->transactionId,
                    'note'           => $this->note,
                    'status'         => 'paid',
                    'paid_by'        => auth()->id(),
                ]);

                activity()
                    ->causedBy(auth()->user())
                    ->performedOn($payment)
                    ->withProperties(['icon' => 'payments', 'type' => 'salary_payment'])
                    ->log("Salary Payment ({$row['month_label']}) এ {$row['net_salary']} টাকা Payment Collect করা হয়েছে।");
            }
        });

        $this->dispatch('toast', type: 'success', message: 'Salary Payment সফলভাবে Collect হয়েছে।');

        $this->closePaymentModal();
        $this->selectedIds = [];
        $this->selectAll   = false;
        $this->loadSalaryPayments();
    }

    public function render()
    {
        return view('livewire.admin.employee.employee-invoice-component')
            ->layout('layouts.branch.app', [
                'title' => 'Employee Salary Invoice | ' . institution()->name,
            ]);
    }
}