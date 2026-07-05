<?php

namespace App\Livewire\Admin\Student;

use Livewire\Component;
use App\Models\Student;
use App\Models\FeePayment;
use App\Models\OfficeAccount;
use App\Models\FeeInvoice;

class StudentPaymentComponent extends Component
{
    public $student;

    // Form
    public $fee_invoice_id     = null;
    public $payment_date       = '';
    public $amount             = '0';
    public $payment_method     = 'cash';
    public $office_account_id  = null;
    public $remarks            = '';

    protected function rules(): array
    {
        return [
            'fee_invoice_id'    => 'required|exists:fee_invoices,id',
            'payment_date'      => 'required|date',
            'amount'            => 'required|numeric|min:0.01',
            'payment_method'    => 'required|in:cash,bank,cheque,online,other',
            'office_account_id' => 'nullable|exists:office_accounts,id',
            'remarks'           => 'nullable|string',
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

    public function updatedFeeInvoiceId(): void
    {
        if ($this->fee_invoice_id) {
            $invoice      = FeeInvoice::find($this->fee_invoice_id);
            $this->amount = $invoice?->remaining ?? '0';
        } else {
            $this->amount = '0';
        }
    }

    public function save(): void
    {
        $this->validate();

        // security check: এই invoice টা এই student-এরই কিনা যাচাই
        $invoice = FeeInvoice::where('student_id', $this->student->id)
            ->findOrFail($this->fee_invoice_id);

        // amount যেন invoice-এর due-এর চেয়ে বেশি না হয়
        if ((float) $this->amount > $invoice->remaining) {
            $this->addError('amount', 'Amount cannot exceed the due amount (৳' . number_format($invoice->remaining, 2) . ').');
            return;
        }

        FeePayment::create([
            'student_id'        => $this->student->id,
            'fee_invoice_id'    => $this->fee_invoice_id,
            'office_account_id' => $this->office_account_id,
            'payment_date'      => $this->payment_date,
            'amount'            => $this->amount,
            'payment_method'    => $this->payment_method,
            'remarks'           => $this->remarks ?: null,
        ]);

        // Invoice totals recalculate
        $invoice->recalculate();

        $this->dispatch('toast', type: 'success', message: 'Payment saved successfully!');
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->reset([
            'fee_invoice_id', 'payment_method',
            'office_account_id', 'remarks',
        ]);
        $this->amount       = '0';
        $this->payment_date = now()->format('Y-m-d');
        $this->resetValidation();
    }

    public function render()
    {
        // পুরো paid না হওয়া invoice গুলো দেখাবে
        $invoices = FeeInvoice::where('student_id', $this->student->id)
            ->where('payment_status', '!=', 'paid')
            ->with(['items.feeSetup.feeType'])
            ->orderBy('invoice_date')
            ->get();

        $officeAccounts = OfficeAccount::orderBy('name')->get();

        return view('livewire.admin.student.student-payment-component')
            ->with(['invoices' => $invoices, 'officeAccounts' => $officeAccounts])
            ->layout('layouts.admin.app', [
                'title' => 'Payment Add | ' . institution()->name,
            ]);
    }
}