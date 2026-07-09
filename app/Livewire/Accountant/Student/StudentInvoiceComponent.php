<?php

namespace App\Livewire\Accountant\Student;

use Livewire\Component;
use App\Models\Student;
use App\Models\FeeInvoice;
use App\Models\FeePayment;
use App\Models\OfficeAccount;
use Illuminate\Support\Facades\DB;

class StudentInvoiceComponent extends Component
{
    public $student;
    public $invoices;         // Flat collection of FeeInvoice with items.feeSetup.feeType
    public $invoicesBySession; // Session-wise grouped view data: [['session' => .., 'invoices' => ..], ...]

    public array $selectedIds = []; // Selected Invoice IDs
    public bool  $selectAll   = false;

    // ---- Payment Modal ----
    public bool   $showPaymentModal = false;
    public array  $paymentRows      = []; // [invoice_id => ['invoice_no'=>, 'due'=>, 'pay_amount'=>]]
    public string $paymentMethod    = 'cash';
    public string $paymentDate;
    public ?int    $officeAccountId = null;
    public ?string $remarks         = null;

    public $officeAccounts = [];

    public function mount(int $id)
    {
        $this->student = Student::with([
            'session',
            'class',
            'section',
            'group',
            'guardians',
            'user',
        ])->findOrFail($id);

        $this->paymentDate    = now()->format('Y-m-d');
        $this->officeAccounts = OfficeAccount::orderBy('name')->get();

        $this->loadInvoices();
    }

    private function loadInvoices(): void
    {
        $this->invoices = FeeInvoice::with([
                'items.feeSetup.feeType',
            ])
            ->where('student_id', $this->student->id)
            ->orderBy('invoice_date')
            ->latest()
            ->get();

        // fee_invoices টেবিলে নিজস্ব session_id নেই, তাই Student-এর বর্তমান
        // Session দিয়েই সব Invoice-কে একটা গ্রুপে দেখানো হচ্ছে। ভবিষ্যতে
        // fee_invoices-এ session_id যোগ হলে এই লজিক groupBy('session_id') দিয়ে
        // সহজেই multi-session-এ upgrade করা যাবে।
        $this->invoicesBySession = collect([
            [
                'session'  => $this->student->session,
                'invoices' => $this->invoices,
            ],
        ]);
    }

    public function updatedSelectAll(bool $value): void
    {
        $this->selectedIds = $value
            ? $this->invoices->pluck('id')->toArray()
            : [];
    }

    public function updatedSelectedIds(): void
    {
        $this->selectAll = $this->invoices->count() > 0
            && count($this->selectedIds) === $this->invoices->count();
    }

    // ---------------------------------------------------------------
    // Modal Open — সিলেক্ট করা Invoice গুলো থেকে Payment Row তৈরি হয়
    // ---------------------------------------------------------------
    public function collectSelected(): void
    {
        if (empty($this->selectedIds)) {
            $this->dispatch('toast', type: 'error', message: 'কোনো Invoice সিলেক্ট করা হয়নি।');
            return;
        }

        $this->paymentRows = [];

        foreach ($this->invoices as $invoice) {
            if (!in_array($invoice->id, $this->selectedIds)) {
                continue;
            }

            $due = (float) $invoice->due_amount;

            if ($due <= 0) {
                continue; // যেই Invoice-এ Due নেই সেটা বাদ
            }

            $this->paymentRows[$invoice->id] = [
                'invoice_no' => $invoice->invoice_no,
                'due'        => $due,
                'pay_amount' => $due, // ডিফল্ট ফুল Due Amount, চাইলে কমানো যাবে (Partial)
            ];
        }

        if (empty($this->paymentRows)) {
            $this->dispatch('toast', type: 'error', message: 'সিলেক্ট করা Invoice গুলোর কোনো Due নেই।');
            return;
        }

        $this->paymentMethod    = 'cash';
        $this->paymentDate      = now()->format('Y-m-d');
        $this->officeAccountId  = null;
        $this->remarks          = null;

        $this->showPaymentModal = true;
    }

    public function closePaymentModal(): void
    {
        $this->showPaymentModal = false;
        $this->paymentRows      = [];
        $this->resetErrorBag();
    }

    // Modal-এ Total দেখানোর জন্য Computed Property
    public function getTotalPayAmountProperty(): float
    {
        return collect($this->paymentRows)->sum(fn ($row) => (float) $row['pay_amount']);
    }

    // ---------------------------------------------------------------
    // Payment Save — DB::transaction() এর মধ্যে সব Invoice এ Payment বসবে
    // ---------------------------------------------------------------
    public function savePayment(): void
    {
        $this->validate([
            'paymentMethod'             => 'required|string|max:50',
            'paymentDate'               => 'required|date',
            'officeAccountId'           => 'nullable|exists:office_accounts,id',
            'remarks'                   => 'nullable|string|max:255',
            'paymentRows.*.pay_amount'  => 'required|numeric|min:0',
        ]);

        foreach ($this->paymentRows as $invoiceId => $row) {
            if ((float) $row['pay_amount'] > (float) $row['due']) {
                $this->addError("paymentRows.$invoiceId.pay_amount", 'Pay Amount, Due Amount এর চেয়ে বেশি হতে পারবে না।');
                return;
            }
        }

        if ($this->totalPayAmount <= 0) {
            $this->dispatch('toast', type: 'error', message: 'অন্তত একটা Invoice-এ Payment Amount দিন।');
            return;
        }

        DB::transaction(function () {
            foreach ($this->paymentRows as $invoiceId => $row) {
                $payAmount = (float) $row['pay_amount'];

                if ($payAmount <= 0) {
                    continue;
                }

                $invoice = FeeInvoice::lockForUpdate()->findOrFail($invoiceId);

                // Payment Record তৈরি (fee_payments schema অনুযায়ী)
                $payment = FeePayment::create([
                    'student_id'        => $this->student->id,
                    'fee_invoice_id'    => $invoice->id,
                    'office_account_id' => $this->officeAccountId,
                    'payment_date'      => $this->paymentDate,
                    'amount'            => $payAmount,
                    'payment_method'    => $this->paymentMethod,
                    'remarks'           => $this->remarks,
                ]);

                // Invoice এর paid/due/status — FeeInvoice::recalculate() নিজেই করে দেয়
                $invoice->recalculate();

                // Activity Log
                activity()
                    ->causedBy(auth()->user())
                    ->performedOn($invoice)
                    ->withProperties(['icon' => 'payments', 'type' => 'payment'])
                    ->log("Invoice #{$invoice->invoice_no} এ {$payAmount} টাকা Payment Collect করা হয়েছে।");
            }
        });

        $this->dispatch('toast', type: 'success', message: 'Payment সফলভাবে Collect হয়েছে।');

        $this->closePaymentModal();
        $this->selectedIds = [];
        $this->selectAll   = false;
        $this->loadInvoices();
    }

    public function render()
    {
        return view('livewire.accountant.student.student-invoice-component')
            ->layout('layouts.accountant.app', [
                'title' => 'Student Invoice | ' . institution()->name,
            ]);
    }
}