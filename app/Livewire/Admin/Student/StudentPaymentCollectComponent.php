<?php

namespace App\Livewire\Admin\Student;

use Livewire\Component;
use App\Models\FeeInvoice;
use App\Models\FeePayment;
use App\Models\OfficeAccount;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StudentPaymentCollectComponent extends Component
{
    // ── Route model binding diye invoice ashbe, mount()-e institution ownership verify kora hoy ──
    public FeeInvoice $invoice;

    public $createdInvoiceId;
    public $createdInvoiceNo;
    public $createdInvoiceDue = 0;
    public $payAmount = 0;
    public string $paymentMethod = 'cash';
    public $paymentDate;
    public $officeAccountId;
    public $paymentRemarks;

    public function mount(FeeInvoice $invoice)
    {
        // ── Security: Invoice-ti je user login kore ache tar institution-er kina check kora hocche.
        // Na hole onno institution-er invoice URL diye access korte parbe na (IDOR protection) ──
        abort_if(
            $invoice->institution_id !== auth()->user()->institution_id,
            403,
            'Unauthorized access to this invoice.'
        );

        // ── Already fully paid invoice re-open kora thekeo protect kora hocche ──
        abort_if(
            $invoice->payment_status === 'paid',
            403,
            'This invoice is already fully paid.'
        );

        // ── BUG FIX (N+1 prevention): print dispatch-er jonno dorkari relations
        // age theke load kore rakha hocche, jate confirmPayment()-e alada query na lage ──
        $invoice->loadMissing(['student', 'items.feeSetup.feeType']);

        $this->invoice = $invoice;

        $this->createdInvoiceId  = $invoice->id;
        $this->createdInvoiceNo  = $invoice->invoice_no;

        // ── BUG FIX (floating point precision): round kore rakha hocche,
        // jate validation rule-e 'max:' concatenation-e vul float na jai ──
        $this->createdInvoiceDue = round((float) $invoice->due_amount, 2);
        $this->payAmount         = round((float) $invoice->due_amount, 2);

        $this->paymentDate = now()->format('Y-m-d');

        $this->dispatch('date-updated', date: $this->paymentDate);
    }

    private function paymentRules(): array
    {
        return [
            'payAmount'       => 'required|numeric|min:0.01|max:' . $this->createdInvoiceDue,
            'paymentMethod'   => 'required|in:cash,bkash,nagad,bank,cheque',
            'paymentDate'     => 'required|date',
            'officeAccountId' => 'nullable',
        ];
    }

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName, $this->paymentRules());
    }

    protected function failedValidation($validator)
    {
        $this->dispatch('validation-failed');
    }

    /**
     * "Confirm Payment" -> fee_payments e record hobe,
     * invoice paid_amount/due_amount/payment_status update hobe, tarpor print dispatch hobe.
     */
    public function confirmPayment()
    {
        $this->validate($this->paymentRules());

        DB::beginTransaction();

        try {

            // ── Re-fetch with lock, jate ekta simultaneous request theke double payment na hoy ──
            $invoice = FeeInvoice::where('id', $this->createdInvoiceId)
                ->lockForUpdate()
                ->firstOrFail();

            $institutionId = auth()->user()->institution_id;

            abort_if($invoice->institution_id !== $institutionId, 403, 'Unauthorized access to this invoice.');

            // ── BUG FIX (race-condition safety): lock nrewar por abar fresh due_amount
            // er biporite payAmount re-check kora hocche, jate concurrent/double-submit
            // request-e over-payment na hoy (age eta check hoto na) ──
            if ($this->payAmount > (float) $invoice->due_amount) {
                throw ValidationException::withMessages([
                    'payAmount' => 'Pay amount cannot be greater than the current due amount ('
                        . number_format($invoice->due_amount, 2) . ').',
                ]);
            }

            FeePayment::create([
                'institution_id'    => $institutionId,
                'fee_invoice_id'    => $invoice->id,
                'student_id'        => $invoice->student_id,
                'amount'            => $this->payAmount,
                'payment_method'    => $this->paymentMethod,
                'payment_date'      => $this->paymentDate,
                'office_account_id' => $this->officeAccountId ?: null,
                'remarks'           => $this->paymentRemarks,
            ]);

            $newPaid = (float) $invoice->paid_amount + (float) $this->payAmount;
            $newDue  = (float) $invoice->total_amount - $newPaid;

            $invoice->update([
                'paid_amount'    => $newPaid,
                'due_amount'     => max($newDue, 0),
                'payment_status' => $newDue <= 0 ? 'paid' : 'partial',
            ]);

            DB::commit();

            $invoice->refresh();
            $invoice->loadMissing(['student', 'items.feeSetup.feeType']);

            // ── BUG FIX (main bug): age $this->invoice kokhono refresh/sync hoto na,
            // shudhu local $invoice variable refresh hoto. Fole payment successful howar
            // porew blade-er table-e Paid Amount column stale/purono value dekhato.
            // Ekhon component property-o notun kore sync kora hocche ──
            $this->invoice = $invoice;

            // ── Print korar jonno dorkari data pathai ──
            $this->dispatch('open-invoice-print', [
                'invoiceNo'   => $invoice->invoice_no,
                'studentName' => $invoice->student->name,
                'paymentDate' => \Carbon\Carbon::parse($this->paymentDate)->format('d.M.Y'),
                'totalAmount' => number_format($invoice->total_amount, 0),
                'paidAmount'  => number_format($invoice->paid_amount, 0),
                'dueAmount'   => number_format($invoice->due_amount, 0),
                'items'       => $invoice->items->map(function ($item) use ($invoice) {
                    $isMonthly  = $item->feeSetup?->frequency === 'monthly';
                    $monthLabel = $isMonthly
                        ? \Carbon\Carbon::parse($invoice->invoice_date)->format('F')
                        : null;

                    return [
                        'feeTypeName' => $item->feeSetup?->feeType?->name ?? '—',
                        'monthLabel'  => $monthLabel,
                        'amount'      => number_format($item->base_amount, 0),
                        'discount'    => number_format($item->discount_amount, 0),
                        'fine'        => number_format($item->fine_amount, 0),
                    ];
                })->toArray(),
            ]);

            $this->dispatch('toast', type: 'success', message: 'Payment collected successfully!');

            // ── Payment collect howar por refreshed invoice tothyo diye state update kora hocche,
            // jate page-e thakle o due/paid amount thik dekhay ──
            $this->createdInvoiceDue = round((float) $invoice->due_amount, 2);
            $this->payAmount         = round((float) $invoice->due_amount, 2);
            $this->paymentRemarks    = null;

        } catch (ValidationException $e) {

            DB::rollBack();
            throw $e;

        } catch (\Throwable $e) {

            DB::rollBack();
            $this->dispatch('toast', type: 'error', message: 'Payment failed!');
            throw $e;
        }
    }

    /**
     * "Skip / Later" -> Payment na kore Student List page-e ferot jao.
     * Student ta already save hoye geche, invoice o toiri hoye ache — sudhu payment ekhon collect kora hocche na.
     */
    public function skipPayment()
    {
        $this->dispatch('toast', type: 'success', message: 'You can collect payment later from Fee Invoices.');

        return redirect()->route('admin.student.add');
    }

    public function render()
    {
        $officeAccounts = OfficeAccount::orderBy('name')->get();

        return view('livewire.admin.student.student-payment-collect-component')
            ->with('officeAccounts', $officeAccounts)
            ->layout('layouts.admin.app', [
                'title' => 'Collect Payment | ' . institution()->name,
            ]);
    }
}