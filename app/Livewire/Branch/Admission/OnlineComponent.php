<?php

namespace App\Livewire\Branch\Admission;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Admission;
use App\Models\AcademicClass;
use App\Models\AcademicSession;
use App\Models\FeeInvoice;
use App\Models\FeePayment;
use App\Models\OfficeAccount;
use App\Services\AdmissionService;
use App\Mail\AdmissionApprovedMail;
use App\Mail\AdmissionRejectedMail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class OnlineComponent extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    public $search    = '';
    public $perPage   = 10;
    public $sortField = 'created_at';
    public $sortDir   = 'desc';

    public $filterClass   = '';
    public $filterSession = '';
    public $filterStatus  = '';

    // View modal
    public bool $showViewModal = false;
    public ?Admission $viewAdmission = null;

    // Reject confirm
    public bool $confirmReject = false;
    public ?int $rejectId      = null;
    public string $rejectReason = '';

    // ── Currently being approved (fee -> payment flow-er target admission) ──
    public ?int $approveId = null;

    // ── STEP 1: Fee Confirmation Modal (only for is_new = true admissions) ──
    public bool $showFeeModal = false;
    public array $feeItems = [];
    public array $selectedFees = [
        'admission_fee'    => true,
        'registration_fee' => true,
        'monthly_fee'      => true,
    ];

    // ── STEP 2: Payment Collect Modal (invoice toiri howar por) ──
    public bool $showPaymentModal = false;
    public $createdInvoiceId;
    public $createdInvoiceNo;
    public $createdInvoiceDue = 0;
    public $payAmount = 0;
    public string $paymentMethod = 'cash';
    public $paymentDate;
    public $officeAccountId;
    public $paymentRemarks;

    public function mount(): void
    {
        $this->paymentDate = now()->format('Y-m-d');
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedFilterClass(): void
    {
        $this->resetPage();
    }

    public function updatedFilterSession(): void
    {
        $this->resetPage();
    }

    public function updatedFilterStatus(): void
    {
        $this->resetPage();
    }

    public function sortBy($field): void
    {
        if ($this->sortField === $field) {
            $this->sortDir = $this->sortDir === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDir   = 'asc';
        }
    }

    /**
     * View button click korle full admission data modal e load hobe —
     * Approve korar age full review korar jonno.
     */
    public function viewRecord(int $id): void
    {
        $this->viewAdmission = Admission::with(['appliedClass', 'appliedSession'])
            ->findOrFail($id);

        $this->showViewModal = true;
    }

    public function closeViewModal(): void
    {
        $this->showViewModal = false;
        $this->viewAdmission = null;
    }

    /**
     * Approve button click korle: age ekta "Approve Admission?" confirm
     * modal dekhano hoto — ekhon r shetake dekhano hobe na.
     *
     * - is_new = true (New Student) hole: sorasori Fee Confirmation Modal
     *   dekhabe (FeeSetup theke load kora fee item gulo diye).
     * - is_new = false (Existing Student) hole: kono modal chara i
     *   sorasori approve hoye jabe (fee/invoice lagbe na).
     */
    public function confirmApproveRecord(int $id, AdmissionService $admissionService): void
    {
        $this->showViewModal = false;

        $admission = Admission::findOrFail($id);

        if ((bool) $admission->is_new) {
            $this->approveId = $id;
            $this->feeItems  = $admissionService->loadFeeItems($admission->institution_id, $admission->applied_class_id);

            foreach (['admission_fee', 'registration_fee', 'monthly_fee'] as $key) {
                if (!isset($this->feeItems[$key])) {
                    $this->selectedFees[$key] = false;
                }
            }

            $this->showFeeModal = true;
        } else {
            $this->approveExistingStudent($admission, $admissionService);
        }
    }

    /**
     * "Existing Student" admission — direct approve, kono fee/invoice/modal lagbe na.
     */
    private function approveExistingStudent(Admission $admission, AdmissionService $admissionService): void
    {
        try {
            $result = $admissionService->approveWithoutInvoice($admission, auth()->id());

            $this->sendApprovalEmail($admission->fresh(), $result['credentials']);

            $this->dispatch('toast', type: 'success', message: 'Admission approved & student account created!');
        } catch (\Throwable $e) {
            report($e);
            $this->dispatch('toast', type: 'error', message: 'Approve failed: ' . $e->getMessage());
        }
    }

    public function getFeeModalTotalProperty()
    {
        $total = 0;

        foreach ($this->feeItems as $key => $item) {
            if ($this->selectedFees[$key] ?? false) {
                $total += $item['amount'];
            }
        }

        return $total;
    }

    /**
     * Fee Confirmation Modal theke "Cancel" — approve process ekdom bad
     * hoye jabe, kono Student/Invoice toiri hobe na.
     */
    public function closeFeeModal(): void
    {
        $this->resetApprovalFlow();
    }

    /**
     * STEP 1 Confirm & Generate Invoice — Student + User + Guardian +
     * Invoice sob ekshathe create hobe (AdmissionService::approveWithInvoice()
     * er moddhe ekta i transaction-e), tarpor STEP 2 Payment Modal open hobe.
     */
    public function generateInvoiceAndApprove(AdmissionService $admissionService): void
    {
        try {
            $admission = Admission::findOrFail($this->approveId);

            $selectedItems = [];
            foreach ($this->feeItems as $key => $item) {
                if ($this->selectedFees[$key] ?? false) {
                    $selectedItems[] = $item;
                }
            }

            $result  = $admissionService->approveWithInvoice($admission, auth()->id(), $selectedItems);
            $invoice = $result['invoice'];

            $this->sendApprovalEmail($admission->fresh(), $result['credentials']);

            $this->showFeeModal = false;

            if ($invoice) {
                $this->createdInvoiceId  = $invoice->id;
                $this->createdInvoiceNo  = $invoice->invoice_no;
                $this->createdInvoiceDue = (float) $invoice->due_amount;
                $this->payAmount         = (float) $invoice->due_amount;
                $this->paymentDate       = now()->format('Y-m-d');

                $this->showPaymentModal = true;

                $this->dispatch('toast', type: 'success', message: 'Admission approved & student account created!');
            } else {
                $this->resetApprovalFlow();
                $this->dispatch('toast', type: 'success', message: 'Admission approved & student account created!');
            }

        } catch (\Throwable $e) {
            report($e);
            $this->dispatch('toast', type: 'error', message: 'Approve failed: ' . $e->getMessage());
        }
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

    /**
     * STEP 2 theke "Skip / Later" — Student already create hoye geche,
     * shudhu payment collect na kore flow bondho kore dao.
     */
    public function skipPayment(): void
    {
        $this->resetApprovalFlow();
    }

    public function closePaymentModal(): void
    {
        $this->resetApprovalFlow();
    }

    /**
     * STEP 2 "Confirm Payment" — FeePayment record hobe, Invoice er
     * paid_amount/due_amount/payment_status update hobe, tarpor print
     * dispatch hobe (StudentAddComponent er identical pattern).
     */
    public function confirmPayment(): void
    {
        $this->validate($this->paymentRules());

        DB::beginTransaction();

        try {
            $invoice = FeeInvoice::findOrFail($this->createdInvoiceId);

            FeePayment::create([
                'institution_id'    => $invoice->institution_id,
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

            $invoice->load('items.feeSetup.feeType', 'student');

            $this->dispatch('open-invoice-print', [
                'invoiceNo'   => $invoice->invoice_no,
                'studentName' => $invoice->student?->name,
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

            $this->resetApprovalFlow();

        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            $this->dispatch('toast', type: 'error', message: 'Payment failed!');
        }
    }

    /**
     * Fee Modal + Payment Modal + approve-flow-er sob temporary state ekshathe
     * reset kore dey. Student/Invoice already create hoye thakle seta touch
     * kore na — shudhu component-er UI state clear kore.
     */
    private function resetApprovalFlow(): void
    {
        $this->approveId = null;

        $this->showFeeModal = false;
        $this->feeItems = [];
        $this->selectedFees = [
            'admission_fee'    => true,
            'registration_fee' => true,
            'monthly_fee'      => true,
        ];

        $this->showPaymentModal = false;
        $this->createdInvoiceId = null;
        $this->createdInvoiceNo = null;
        $this->createdInvoiceDue = 0;
        $this->payAmount = 0;
        $this->paymentMethod = 'cash';
        $this->paymentDate = now()->format('Y-m-d');
        $this->officeAccountId = null;
        $this->paymentRemarks = null;
    }

    public function confirmRejectRecord(int $id): void
    {
        $this->showViewModal = false;
        $this->rejectId      = $id;
        $this->rejectReason  = '';
        $this->resetValidation('rejectReason');
        $this->confirmReject = true;
    }

    /**
     * Reject: reason save hobe, ebong guardian_email + student email (jodi thake)
     * dutotei notification mail jabe.
     */
    public function rejectRecord(): void
    {
        $this->validate([
            'rejectReason' => 'required|string|min:5|max:1000',
        ], [
            'rejectReason.required' => 'Reject korar karon likhun.',
            'rejectReason.min'      => 'Karon ta ektu bistarito likhun (minimum 5 character).',
        ]);

        try {
            $admission = Admission::findOrFail($this->rejectId);

            $admission->update([
                'status'            => 'rejected',
                'rejection_reason'  => $this->rejectReason,
                'reviewed_by'       => auth()->id(),
                'reviewed_at'       => now(),
            ]);

            $this->sendRejectionEmails($admission);

            $this->confirmReject = false;
            $this->rejectId      = null;
            $this->rejectReason  = '';

            $this->dispatch('toast', type: 'success', message: 'Admission rejected & notification email sent.');
        } catch (\Throwable $e) {
            report($e);
            $this->dispatch('toast', type: 'error', message: 'Reject failed: ' . $e->getMessage());
        }
    }

    /**
     * Approve mail: Guardian ebong Student — dujonke ALADA ALADA mail
     * pathano hoy (sendRejectionEmails()-er identical pattern), jate
     * ekjoner login credential/email onnojoner kache expose na hoy.
     *
     * FIX (Bug): Age eta shudhu EKJON-ke (student email thakle student,
     * nahole guardian) mail pathacchilo — mane Student email thakle
     * Guardian ekdomi mail PETO NA, jeta bhul chilo (Guardian-er nijer
     * account thakleo credential mail pawa uchit).
     *
     * Ekhon:
     * - Guardian: SOBSHOMOY mail jay (AdmissionService-e guardian email
     *   required enforce kora ache — tai eta guaranteed thake). Guardian
     *   nijer credentials-i shudhu dekhbe, Student-er na.
     * - Student: SHUDHU tokhoni mail jay jokhon Admission form-e Student-er
     *   nijer email dewa chilo ($credentials['student']['has_email'] === true,
     *   AdmissionService-e set kora hoy). Student nijer credentials-i shudhu
     *   dekhbe, Guardian-er na.
     *
     * NOTE: AdmissionApprovedMail constructor-ke ekhon
     * (Admission $admission, array $credentials, string $recipientRole)
     * — $recipientRole = 'guardian' | 'student' — accept korte hobe, jate
     * mail template bujhte pare shudhu kar credentials show korte hobe.
     * AdmissionApprovedMail.php file-ta share korle seta-o update kore
     * dite pari.
     */
    private function sendApprovalEmail(Admission $admission, array $credentials): void
    {
        $guardianEmail = $credentials['guardian']['email'] ?? null;

        if ($guardianEmail) {
            Mail::to($guardianEmail)->send(
                new AdmissionApprovedMail($admission, $credentials, 'guardian')
            );
        }

        $studentHasEmail = $credentials['student']['has_email'] ?? false;
        $studentEmail    = $credentials['student']['email'] ?? null;

        if ($studentHasEmail && $studentEmail) {
            Mail::to($studentEmail)->send(
                new AdmissionApprovedMail($admission, $credentials, 'student')
            );
        }
    }

    /**
     * Guardian ebong (jodi thake) student — dutake alada alada mail pathano hoy,
     * jate ekjoner email onnojoner kache expose na hoy.
     */
    private function sendRejectionEmails(Admission $admission): void
    {
        $recipients = collect([$admission->guardian_email, $admission->email])
            ->filter()
            ->unique();

        foreach ($recipients as $recipientEmail) {
            Mail::to($recipientEmail)->send(new AdmissionRejectedMail($admission));
        }
    }

    public function render()
    {
        $classes  = AcademicClass::orderBy('name')->get();
        $sessions = AcademicSession::orderBy('name')->get();
        $officeAccounts = OfficeAccount::orderBy('name')->get();

        $admissions = Admission::with(['appliedClass', 'appliedSession'])
            ->when($this->filterClass, fn($q) =>
                $q->where('applied_class_id', $this->filterClass)
            )
            ->when($this->filterSession, fn($q) =>
                $q->where('applied_session_id', $this->filterSession)
            )
            ->when($this->filterStatus, fn($q) =>
                $q->where('status', $this->filterStatus)
            )
            ->when($this->search, fn($q) => $q->where(fn($q) => $q
                ->where('applicant_name', 'like', "%{$this->search}%")
                ->orWhere('application_no', 'like', "%{$this->search}%")
                ->orWhere('mobile', 'like', "%{$this->search}%")
                ->orWhere('guardian_name', 'like', "%{$this->search}%")))
            ->orderBy($this->sortField, $this->sortDir)
            ->paginate($this->perPage);

        return view('livewire.admin.admission.online-component')
            ->with('admissions', $admissions)
            ->with('classes', $classes)
            ->with('sessions', $sessions)
            ->with('officeAccounts', $officeAccounts)
            ->layout('layouts.branch.app', [
                'title' => 'Online Admissions | ' . institution()->name,
            ]);
    }
}