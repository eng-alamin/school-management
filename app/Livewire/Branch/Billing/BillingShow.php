<?php

namespace App\Livewire\Admin\Billing;

use App\Models\Invoice;
use App\Models\PricingRate;
use App\Models\SmsLog;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class BillingShow extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    private const ALLOWED_PER_PAGE = [12, 24, 50];

    public string $filterStatus = '';
    public string $filterYear   = '';
    public int    $perPage      = 12;

    public bool     $showDetailModal = false;
    public ?Invoice $viewInvoice     = null;

    public function updatingFilterStatus($value): void
    {
        if ($value !== '' && ! in_array($value, Invoice::STATUSES, true)) {
            $this->filterStatus = '';
        }

        $this->resetPage();
    }

    public function updatingFilterYear(): void
    {
        $this->resetPage();
    }

    public function updatingPerPage($value): void
    {
        if (! in_array((int) $value, self::ALLOWED_PER_PAGE, true)) {
            $this->perPage = 12;
        }

        $this->resetPage();
    }

    public function openDetail(int $id): void
    {
        $this->viewInvoice = Invoice::with('items')
            ->where('institution_id', auth()->user()->institution_id)
            ->findOrFail($id);

        $this->showDetailModal = true;
    }

    public function render()
    {
        $institutionId = auth()->user()->institution_id;
        
        $invoices = Invoice::where('institution_id', $institutionId)
            ->when($this->filterStatus, fn ($q) => $q->where('status', $this->filterStatus))
            ->when($this->filterYear, fn ($q) => $q->where('year', (int) $this->filterYear))
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->paginate($this->perPage);

        $availableYears = Invoice::where('institution_id', $institutionId)
            ->select('year')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year');

        // ── Active Student Count (users table) ──
        $activeStudentCount = User::where('institution_id', $institutionId)
            ->where('role', 'student')
            ->where('is_active', true)
            ->count();

        // ── এই মাসে পাঠানো SMS সংখ্যা (sms_logs table) ──
        $smsCount = SmsLog::where('institution_id', $institutionId)
            ->where('status', 'sent')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        $studentRate = PricingRate::where('type', 'student')->value('rate') ?? 1.00;
        $smsRate     = PricingRate::where('type', 'sms')->value('rate') ?? 0;

        $studentAmount = $activeStudentCount * $studentRate;
        $smsAmount     = $smsCount * $smsRate;
        $estimatedBill = $studentAmount + $smsAmount;

        return view('livewire.admin.billing.billing-show')
            ->with([
                'invoices'           => $invoices,
                'availableYears'     => $availableYears,
                'statusOptions'      => Invoice::STATUSES,
                'activeStudentCount' => $activeStudentCount,
                'smsCount'           => $smsCount,
                'rate'               => $studentRate,   // backward compatible name (student rate)
                'studentRate'        => $studentRate,
                'smsRate'            => $smsRate,
                'studentAmount'      => $studentAmount,
                'smsAmount'          => $smsAmount,
                'estimatedBill'      => $estimatedBill,
            ])
            ->layout('layouts.admin.app', [
                'title' => 'Billing | ' . institution()->name,
            ]);
    }
}