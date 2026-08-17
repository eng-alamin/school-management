<?php

namespace App\Livewire\Branch\Student;

use Livewire\Component;
use App\Models\Student;
use App\Models\FeeInvoice;
use App\Models\FeePayment;
use App\Models\OfficeAccount;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class InvoiceComponent extends Component
{
    public $student;
    public $invoices; // Flat collection — checkbox/payment logic এর জন্য
    public array $invoicesBySession = []; // Session Year অনুযায়ী Group করা (Accordion Display এর জন্য)

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
            ->orderByDesc('invoice_date')
            ->get();

        $this->groupInvoicesBySession();
    }

    // ---------------------------------------------------------------
    // Session Year (academic_sessions) অনুযায়ী Invoice গুলোকে Group করা
    // ---------------------------------------------------------------
    private function groupInvoicesBySession(): void
    {
        $sessions = DB::table('academic_sessions')
            ->where('institution_id', institution()->id)
            ->orderByDesc('start_date')
            ->get();

        $grouped = [];

        foreach ($sessions as $session) {
            $sessionInvoices = $this->invoices->filter(function ($invoice) use ($session) {
                if (!$session->start_date || !$session->end_date) {
                    return false;
                }

                $invoiceDate = Carbon::parse($invoice->invoice_date);

                return $invoiceDate->betweenIncluded(
                    Carbon::parse($session->start_date),
                    Carbon::parse($session->end_date)
                );
            })->values();

            if ($sessionInvoices->isNotEmpty()) {
                $grouped[] = [
                    'session'  => $session,
                    'invoices' => $sessionInvoices,
                ];
            }
        }

        // Session-এর বাইরে পড়া কোনো Invoice থাকলে (Session Range Miss হলে) সেগুলো আলাদা "Others" গ্রুপে রাখা
        $matchedIds = collect($grouped)->flatMap(fn ($g) => $g['invoices']->pluck('id'));
        $unmatched  = $this->invoices->whereNotIn('id', $matchedIds)->values();

        if ($unmatched->isNotEmpty()) {
            $grouped[] = [
                'session'  => (object) ['id' => 0, 'name' => 'Others'],
                'invoices' => $unmatched,
            ];
        }

        $this->invoicesBySession = $grouped;
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
                continue;
            }

            $this->paymentRows[$invoice->id] = [
                'invoice_no' => $invoice->invoice_no,
                'due'        => $due,
                'pay_amount' => $due,
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

    public function getTotalPayAmountProperty(): float
    {
        return collect($this->paymentRows)->sum(fn ($row) => (float) $row['pay_amount']);
    }

    // ---------------------------------------------------------------
    // Payment Save
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

                FeePayment::create([
                    'student_id'        => $this->student->id,
                    'fee_invoice_id'    => $invoice->id,
                    'office_account_id' => $this->officeAccountId,
                    'payment_date'      => $this->paymentDate,
                    'amount'            => $payAmount,
                    'payment_method'    => $this->paymentMethod,
                    'remarks'           => $this->remarks,
                ]);

                $invoice->recalculate();

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
        return view('livewire.branch.student.invoice-component')
            ->layout('layouts.branch.app', [
                'title' => 'Student Invoice | ' . institution()->name,
            ]);
    }
}