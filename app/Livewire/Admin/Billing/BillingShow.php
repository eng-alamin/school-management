<?php

namespace App\Livewire\Admin\Billing;

use App\Models\Invoice;
use Livewire\Component;
use Livewire\WithPagination;

class BillingShow extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    public string $filterStatus = '';
    public string $filterYear   = '';
    public int    $perPage      = 12;

    public bool     $showDetailModal = false;
    public ?Invoice $viewInvoice     = null;

    public function updatingFilterStatus(): void { $this->resetPage(); }
    public function updatingFilterYear(): void   { $this->resetPage(); }

    public function openDetail(int $id): void
    {
        $this->viewInvoice = Invoice::with('items')
            ->where('school_id', auth()->user()->school_id)
            ->findOrFail($id);

        $this->showDetailModal = true;
    }

    public function render()
    {
        $schoolId = auth()->user()->school_id;

        $invoices = Invoice::where('school_id', $schoolId)
            ->when($this->filterStatus, fn ($q) => $q->where('status', $this->filterStatus))
            ->when($this->filterYear, fn ($q) => $q->where('year', $this->filterYear))
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->paginate($this->perPage);

        $availableYears = Invoice::where('school_id', $schoolId)
            ->select('year')->distinct()->orderByDesc('year')->pluck('year');

        // ── Active Student Count (users table) ──
        $activeStudentCount = \App\Models\User::where('school_id', $schoolId)
            ->where('role', 'student')
            ->where('is_active', true)
            ->count();

        $rate = \App\Models\PricingRate::where('type', 'student')->value('rate') ?? 1.00;
        $estimatedBill = $activeStudentCount * $rate;

        return view('livewire.admin.billing.billing-show')
            ->with([
                'invoices'           => $invoices,
                'availableYears'     => $availableYears,
                'activeStudentCount' => $activeStudentCount,
                'rate'               => $rate,
                'estimatedBill'      => $estimatedBill,
            ])
            ->layout('layouts.admin.app', [
                'title' => "Billing | School SaaS",
            ]);
    }
}