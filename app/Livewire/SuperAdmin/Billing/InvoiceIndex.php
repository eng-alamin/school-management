<?php

namespace App\Livewire\SuperAdmin\Billing;

use App\Models\Invoice;
use Livewire\Component;
use Livewire\WithPagination;

class InvoiceIndex extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    // Filters
    public string $search       = '';
    public string $filterStatus = '';
    public string $filterMonth  = '';
    public string $filterYear   = '';
    public int    $perPage      = 15;

    // View Modal
    public bool      $showDetailModal = false;
    public ?Invoice  $viewInvoice     = null;

    // Mark Paid Modal
    public bool $showPaidModal = false;
    public ?int $payId         = null;

    // Discount Modal
    public bool   $showDiscountModal = false;
    public ?int   $discountId        = null;
    public string $discount          = '';
    public ?Invoice $discountInvoice = null;

    public function updatingSearch(): void       { $this->resetPage(); }
    public function updatingFilterStatus(): void { $this->resetPage(); }
    public function updatingFilterMonth(): void  { $this->resetPage(); }
    public function updatingFilterYear(): void   { $this->resetPage(); }

    public function openDetail(int $id): void
    {
        $this->viewInvoice = Invoice::with(['items', 'school'])->findOrFail($id);
        $this->showDetailModal = true;
    }

    public function confirmMarkPaid(int $id): void
    {
        $this->payId        = $id;
        $this->showPaidModal = true;
    }

    public function markPaid(): void
    {
        $invoice = Invoice::findOrFail($this->payId);

        $invoice->update([
            'status'  => 'paid',
            'paid_at' => now(),
        ]);

        // ── Activity Log ───────────────────────────────────────────
        activity()
            ->causedBy(auth()->user())
            ->performedOn($invoice)
            ->withProperties(['icon' => 'payments', 'type' => 'invoice'])
            ->log('Invoice marked as paid: ' . $invoice->invoice_no);

        $this->showPaidModal = false;
        $this->payId         = null;

        $this->dispatch('toast', type: 'success', message: 'Invoice marked as paid successfully!');
    }

    public function openDiscount(int $id): void
    {
        $invoice = Invoice::findOrFail($id);

        $this->discountId      = $id;
        $this->discountInvoice = $invoice;
        $this->discount        = (string) $invoice->discount;
        $this->showDiscountModal = true;
    }

    public function applyDiscount(): void
    {
        $this->validate([
            'discount' => 'required|numeric|min:0',
        ]);

        $invoice = Invoice::findOrFail($this->discountId);

        if ((float) $this->discount > (float) $invoice->total_amount) {
            $this->addError('discount', 'Discount cannot be greater than total amount.');
            return;
        }

        $payable = $invoice->total_amount - (float) $this->discount;

        $invoice->update([
            'discount'       => $this->discount,
            'payable_amount' => max(0, $payable),
        ]);

        // ── Activity Log ───────────────────────────────────────────
        activity()
            ->causedBy(auth()->user())
            ->performedOn($invoice)
            ->withProperties(['icon' => 'discount', 'type' => 'invoice'])
            ->log('Discount of ৳' . number_format($this->discount, 2) . ' applied to invoice: ' . $invoice->invoice_no);

        $this->showDiscountModal = false;
        $this->reset(['discountId', 'discount', 'discountInvoice']);

        $this->dispatch('toast', type: 'success', message: 'Discount applied successfully!');
    }

    public function render()
    {
        $invoices = Invoice::with('school')
            ->when($this->search, fn ($q) =>
                $q->where(fn ($q2) =>
                    $q2->where('invoice_no', 'like', "%{$this->search}%")
                       ->orWhereHas('school', fn ($q3) =>
                           $q3->where('name', 'like', "%{$this->search}%")
                       )
                )
            )
            ->when($this->filterStatus, fn ($q) => $q->where('status', $this->filterStatus))
            ->when($this->filterMonth, fn ($q) => $q->where('month', $this->filterMonth))
            ->when($this->filterYear, fn ($q) => $q->where('year', $this->filterYear))
            ->orderByDesc('created_at')
            ->paginate($this->perPage);

        $availableYears = Invoice::select('year')->distinct()->orderByDesc('year')->pluck('year');

        // Quick stats
        $totalPaid    = Invoice::where('status', 'paid')->sum('payable_amount');
        $totalPending = Invoice::where('status', 'pending')->sum('payable_amount');
        $totalOverdue = Invoice::where('status', 'overdue')->count();

        return view('livewire.super-admin.billing.invoice-index')
            ->with([
                'invoices'       => $invoices,
                'availableYears' => $availableYears,
                'totalPaid'      => $totalPaid,
                'totalPending'   => $totalPending,
                'totalOverdue'   => $totalOverdue,
            ])
            ->layout('layouts.superadmin.app', [
                'title' => "Manage Invoices | School SaaS",
            ]);
    }
}
