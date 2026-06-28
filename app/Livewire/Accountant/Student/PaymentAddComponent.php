<?php

namespace App\Livewire\Accountant\Student;

use Livewire\Component;
use App\Models\Student;
use App\Models\FeePayment;
use App\Models\FeeAllocation;
use App\Models\OfficeAccount;
use App\Models\FeeInvoice;
use App\Models\FeeInvoiceItem;
use App\Services\NotificationService;

class PaymentAddComponent extends Component
{
    public $student;

    // Form
    public $fee_invoice_item_id = null;
    public $payment_date        = '';
    public $amount              = '0';
    public $discount            = '0';
    public $fine                = '0.00';
    public $payment_method      = 'cash';
    public $office_account_id   = null;
    public $remarks             = '';

    // Dynamic
    public $invoiceItems = [];

    protected function rules(): array
    {
        return [
            'fee_invoice_item_id' => 'required|exists:fee_invoice_items,id',
            'payment_date'        => 'required|date',
            'amount'              => 'required|numeric|min:0',
            'discount'            => 'nullable|numeric|min:0',
            'fine'                => 'nullable|numeric|min:0',
            'payment_method'      => 'required|in:cash,bank,cheque,online,other',
            'office_account_id'   => 'required|exists:office_accounts,id',
            'remarks'             => 'nullable|string',
        ];
    }

    public function mount($id): void
    {
        $this->payment_date = now()->format('Y-m-d');

        $this->student = Student::with([
            'class',
            'section',
            'guardians',
        ])->findOrFail($id);
    }

    public function updatedFeeInvoiceItemId(): void
    {
        if ($this->fee_invoice_item_id) {
            $invoiceItem  = FeeInvoiceItem::with('itemPayments')->find($this->fee_invoice_item_id);
            $this->amount = $invoiceItem?->remaining ?? '0';
        } else {
            $this->amount = '0';
        }
    }

    public function getPaidAmountProperty(): float
    {
        return (float) $this->amount
             - (float) $this->discount
             + (float) $this->fine;
    }

    public function save(): void
    {
        $this->validate();

        $invoiceItem = FeeInvoiceItem::with([
            'invoice',
            'feeGroupItem',
            'itemPayments',
        ])->findOrFail($this->fee_invoice_item_id);

        $net = (float) $invoiceItem->amount
             - (float) $invoiceItem->discount_amount
             + (float) $invoiceItem->fine_amount;

        $paidAmount = $this->getPaidAmountProperty();

        $status = match(true) {
            $paidAmount <= 0    => 'unpaid',
            $paidAmount >= $net => 'paid',
            default             => 'partial',
        };

        FeePayment::create([
            'student_id'          => $this->student->id,
            'fee_allocation_id'   => $invoiceItem->invoice->fee_allocation_id,
            'fee_invoice_item_id' => $this->fee_invoice_item_id,
            'fee_group_item_id'   => $invoiceItem->fee_group_item_id,
            'office_account_id'   => $this->office_account_id,
            'payment_date'        => $this->payment_date,
            'amount'              => $this->amount,
            'discount'            => $this->discount ?: 0,
            'fine'                => $this->fine ?: 0,
            'paid_amount'         => $paidAmount,
            'payment_method'      => $this->payment_method,
            'payment_status'      => $status,
            'remarks'             => $this->remarks ?: null,
        ]);

        // Invoice totals recalculate
        $invoiceItem->invoice->recalculate();

        // NotificationService::feePaid($user, $paidAmount);

        $this->dispatch('toast', type: 'success', message: 'Payment saved successfully!');
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->reset([
            'fee_invoice_item_id', 'discount',
            'fine', 'payment_method',
            'office_account_id', 'remarks',
        ]);
        $this->amount       = '0';
        $this->payment_date = now()->format('Y-m-d');
        $this->resetValidation();
    }

    public function render()
    {
        $invoices = FeeInvoice::where('student_id', $this->student->id)
            ->with([
                'items.feeGroupItem.feeType',
                'items.itemPayments',
            ])
            ->get();

        // Fully paid items বাদ দাও
        $this->invoiceItems = $invoices
            ->pluck('items')
            ->flatten()
            ->filter(fn($item) => !$item->is_paid)
            ->values();

        $officeAccounts = OfficeAccount::orderBy('name')->get();

        return view('livewire.accountant.student.payment-add-component')
            ->with(['invoices' => $invoices, 'officeAccounts' => $officeAccounts])
            ->layout('layouts.accountant.app', [
                'title' => 'Payment Add | ' . institution()->name,
            ]);
    }
}