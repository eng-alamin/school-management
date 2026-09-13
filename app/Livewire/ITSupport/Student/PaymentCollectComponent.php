<?php

namespace App\Livewire\ITSupport\Student;

use Livewire\Component;
use App\Models\FeeInvoice;
use App\Models\FeePayment;
use App\Models\OfficeAccount;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

class PaymentCollectComponent extends Component
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

        $invoice->loadMissing(['student', 'items.feeSetup.feeType']);

        $this->invoice = $invoice;

        $this->createdInvoiceId  = $invoice->id;
        $this->createdInvoiceNo  = $invoice->invoice_no;

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

            if ($this->payAmount > (float) $invoice->due_amount) {
                throw ValidationException::withMessages([
                    'payAmount' => 'Pay amount cannot be greater than the current due amount ('
                        . number_format($invoice->due_amount, 2) . ').',
                ]);
            }

            $itemAmounts = $this->buildItemAllocations($invoice, (float) $this->payAmount);

            FeePayment::createWithAllocations([
                'institution_id'    => $institutionId,
                'fee_invoice_id'    => $invoice->id,
                'student_id'        => $invoice->student_id,
                'payment_method'    => $this->paymentMethod,
                'payment_date'      => $this->paymentDate,
                'office_account_id' => $this->officeAccountId ?: null,
                'remarks'           => $this->paymentRemarks,
            ], $itemAmounts);

            $invoice->recalculate();

            DB::commit();

            $invoice->refresh();
            $invoice->loadMissing(['student', 'items.feeSetup.feeType']);
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

        } catch (InvalidArgumentException $e) {

            DB::rollBack();
            throw ValidationException::withMessages([
                'payAmount' => $e->getMessage(),
            ]);

        } catch (\Throwable $e) {

            DB::rollBack();
            $this->dispatch('toast', type: 'error', message: 'Payment failed!');
            throw $e;
        }
    }

    /**
     * Ekta lump-sum pay amount-ke invoice-er item-gulor moddhe FIFO order-e
     * (item id ascending) bhag kore [fee_invoice_item_id => allocated_amount] return kore.
     * Prottekta item-e tar nijer remaining balance porjonto e allocate kora hoy.
     *
     * @throws \InvalidArgumentException jodi total amount item-gulor total remaining er cheye beshi hoy
     */
    private function buildItemAllocations(FeeInvoice $invoice, float $amount): array
    {
        $itemAmounts         = [];
        $remainingToAllocate = round($amount, 2);

        $items = $invoice->items()->orderBy('id')->get();

        foreach ($items as $item) {
            if ($remainingToAllocate <= 0) {
                break;
            }

            $itemRemaining = round((float) $item->remaining, 2);

            if ($itemRemaining <= 0) {
                continue;
            }

            $allocate = min($itemRemaining, $remainingToAllocate);

            $itemAmounts[$item->id] = $allocate;
            $remainingToAllocate    = round($remainingToAllocate - $allocate, 2);
        }

        if ($remainingToAllocate > 0.01) {
            throw new InvalidArgumentException(
                'Pay amount is greater than the total remaining due across this invoice\'s items.'
            );
        }

        return $itemAmounts;
    }

    /**
     * "Skip / Later" -> Payment na kore Student List page-e ferot jao.
     * Student ta already save hoye geche, invoice o toiri hoye ache — sudhu payment ekhon collect kora hocche na.
     */
    public function skipPayment()
    {
        $this->dispatch('toast', type: 'success', message: 'You can collect payment later from Fee Invoices.');

        return redirect()->route('itsupport.student.add');
    }

    public function render()
    {
        $officeAccounts = OfficeAccount::orderBy('name')->get();

        return view('livewire.admin.student.payment-collect-component')
            ->with('officeAccounts', $officeAccounts)
            ->layout('layouts.itsupport.app', [
                'title' => 'Collect Payment | ' . institution()->name,
            ]);
    }
}