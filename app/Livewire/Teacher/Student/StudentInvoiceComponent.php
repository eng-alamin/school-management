<?php

namespace App\Livewire\Teacher\Student;

use Livewire\Component;
use App\Models\Student;
use App\Models\FeeInvoice;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StudentInvoiceComponent extends Component
{
    public $student;

    /** @var \Illuminate\Support\Collection Flat list of the student's invoices — used for checkbox/select-all logic */
    public $invoices;

    /** @var array Invoices grouped by academic session, for the accordion-style display */
    public array $invoicesBySession = [];

    public array $selectedIds = [];
    public bool  $selectAll   = false;

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

        $this->loadInvoices();
    }

    private function loadInvoices(): void
    {
        // BUG FIX (critical): this used to be FeeAllocation::with('feeGroup.items.feeType')
        // — the fee_allocations / fee_group_items tables no longer exist. The
        // fee module now works at Invoice + Invoice Item level (fee_setups →
        // fee_invoice_items), same as the already-working Admin panel.
        $this->invoices = FeeInvoice::with(['items.feeSetup.feeType'])
            ->where('student_id', $this->student->id)
            ->orderByDesc('invoice_date')
            ->get();

        $this->groupInvoicesBySession();
    }

    /**
     * Groups invoices by academic_sessions (matched by invoice_date falling
     * inside a session's start_date/end_date range), so the blade can show
     * them under session-year headers instead of one long flat table.
     */
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

    /**
     * Redirects to the Payment Add page for this student. If exactly one
     * unpaid invoice was selected, its id is passed along via query string
     * so the payment form can preselect it.
     */
    public function collectSelected()
    {
        if (empty($this->selectedIds)) {
            $this->dispatch('toast', type: 'error', message: 'No invoice selected.');
            return;
        }

        $selectedWithDue = $this->invoices
            ->whereIn('id', $this->selectedIds)
            ->filter(fn ($invoice) => (float) $invoice->due_amount > 0)
            ->values();

        if ($selectedWithDue->isEmpty()) {
            $this->dispatch('toast', type: 'error', message: 'Selected invoice(s) have no due amount.');
            return;
        }

        $params = ['id' => $this->student->id];

        if ($selectedWithDue->count() === 1) {
            $params['invoice_id'] = $selectedWithDue->first()->id;
        }

        return $this->redirect(route('teacher.student.payment.add', $params));
    }

    public function render()
    {
        return view('livewire.teacher.student.student-invoice-component')
            ->layout('layouts.teacher.app', [
                'title' => 'Student Invoice | ' . institution()->name,
            ]);
    }
}