<?php

namespace App\Livewire\Teacher\Student;

use Livewire\Component;
use App\Models\Student;
use App\Models\FeeInvoice;
use App\Models\FeePayment;
use App\Models\OfficeAccount;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PaymentAddComponent extends Component
{
    public $student;

    /** @var \Illuminate\Support\Collection Student's unpaid/partial invoices, for the dropdown */
    public $invoices;

    // Form
    public $invoice_id      = null;
    public $due_amount      = 0;
    public $payment_date    = '';
    public $amount          = 0;
    public $payment_method  = 'cash';
    public $office_account_id = null;
    public $remarks         = '';

    public function mount(int $id): void
    {
        $this->student = Student::with(['class', 'section', 'guardians'])->findOrFail($id);

        $this->payment_date = now()->format('Y-m-d');

        $this->loadInvoices();

        // Optional preselect coming from the Invoice page ("Selected Fees
        // Collect"). This is a query-string value (?invoice_id=), not a
        // route segment, so it's read via request() rather than a mount()
        // parameter.
        $preselectedInvoiceId = (int) request()->query('invoice_id', 0);

        if ($preselectedInvoiceId && $this->invoices->firstWhere('id', $preselectedInvoiceId)) {
            $this->invoice_id = $preselectedInvoiceId;
            $this->updatedInvoiceId();
        }

        $this->dispatch('date-updated', date: $this->payment_date);
    }

    private function loadInvoices(): void
    {
        // BUG FIX (critical): FeeInvoiceItem / fee_invoice_item_id-based
        // lookup removed. Payments are collected against a FeeInvoice as a
        // whole (fee_payments has no per-item column), same as the Admin panel.
        $this->invoices = FeeInvoice::where('student_id', $this->student->id)
            ->where('payment_status', '!=', 'paid')
            ->orderByDesc('invoice_date')
            ->get();
    }

    public function updatedInvoiceId(): void
    {
        $invoice = $this->invoice_id
            ? $this->invoices->firstWhere('id', $this->invoice_id)
            : null;

        $this->due_amount = $invoice ? round((float) $invoice->due_amount, 2) : 0;
        $this->amount     = $this->due_amount;
    }

    protected function rules(): array
    {
        return [
            'invoice_id'        => 'required|exists:fee_invoices,id',
            'payment_date'      => 'required|date',
            'amount'            => 'required|numeric|min:0.01|max:' . max($this->due_amount, 0.01),
            'payment_method'    => 'required|in:cash,bkash,nagad,bank,cheque',
            'office_account_id' => 'nullable|exists:office_accounts,id',
            'remarks'           => 'nullable|string|max:255',
        ];
    }

    public function updated($propertyName): void
    {
        $this->validateOnly($propertyName, $this->rules());
    }

    protected function failedValidation($validator)
    {
        $this->dispatch('validation-failed');
    }

    public function save(): void
    {
        $this->validate();

        DB::beginTransaction();

        try {
            $institutionId = auth()->user()->institution_id;

            // ── Re-fetch with lock so two simultaneous submits for the same
            // invoice can't both collect payment past the actual due amount.
            $invoice = FeeInvoice::where('id', $this->invoice_id)
                ->lockForUpdate()
                ->firstOrFail();

            abort_if(
                $invoice->institution_id !== $institutionId || $invoice->student_id !== $this->student->id,
                403,
                'Unauthorized access to this invoice.'
            );

            if ((float) $this->amount > (float) $invoice->due_amount) {
                throw ValidationException::withMessages([
                    'amount' => 'Amount cannot be greater than the current due amount ('
                        . number_format($invoice->due_amount, 2) . ').',
                ]);
            }

            FeePayment::create([
                'student_id'        => $this->student->id,
                'fee_invoice_id'    => $invoice->id,
                'office_account_id' => $this->office_account_id ?: null,
                'payment_date'      => $this->payment_date,
                'amount'            => $this->amount,
                'payment_method'    => $this->payment_method,
                'remarks'           => $this->remarks ?: null,
            ]);

            // Recalculates paid_amount / due_amount / payment_status from the sum of payments.
            $invoice->recalculate();

            DB::commit();

            $this->dispatch('toast', type: 'success', message: 'Payment saved successfully!');
            $this->resetForm();

        } catch (ValidationException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->dispatch('toast', type: 'error', message: 'Something went wrong!');
            throw $e;
        }
    }

    private function resetForm(): void
    {
        $this->reset(['invoice_id', 'due_amount', 'office_account_id', 'remarks']);
        $this->amount         = 0;
        $this->payment_method = 'cash';
        $this->payment_date   = now()->format('Y-m-d');
        $this->resetValidation();
        $this->loadInvoices();
    }

    public function render()
    {
        $officeAccounts = OfficeAccount::orderBy('name')->get();

        return view('livewire.teacher.student.payment-add-component')
            ->with('officeAccounts', $officeAccounts)
            ->layout('layouts.teacher.app', [
                'title' => 'Payment Add | ' . institution()->name,
            ]);
    }
}