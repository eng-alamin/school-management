<?php

namespace App\Livewire\Accountant\StudentAccounting;

use Livewire\Component;
use App\Models\FeeAllocation;
use App\Models\FeeGroup;
use App\Models\AcademicClass;
use App\Models\AcademicSection;
use App\Models\AcademicClassAssign;
use App\Models\Student;
use App\Models\FeeInvoice;
use App\Models\FeeInvoiceItem;

class FeeAllocationComponent extends Component
{
    // ── Filter ──
    public $filterClass    = '';
    public $filterSection  = '';
    public $fee_group_id   = null;

    // ── State ──
    public array $students         = [];
    public array $selectedStudents = [];
    public bool  $selectAll        = false;
    public bool  $hasFiltered      = false;

    // ── Validation ──
    protected function rules(): array
    {
        return [
            'fee_group_id'       => 'required|exists:fee_groups,id',
            'filterClass'        => 'required|exists:academic_classes,id',
            'selectedStudents'   => 'required|array|min:1',
            'selectedStudents.*' => 'exists:students,id',
        ];
    }

    // ── Available Classes (StudentComponent pattern) ──
    public function getAvailableClasses()
    {
        return AcademicClass::whereIn('id', AcademicClassAssign::distinct()->pluck('class_id'))
            ->orderBy('name')
            ->get();
    }

    // ── Available Sections (StudentComponent pattern) ──
    public function getAvailableSections()
    {
        if (!$this->filterClass) return [];

        return AcademicSection::whereIn('id',
            AcademicClassAssign::where('class_id', $this->filterClass)->pluck('section_id')
        )->orderBy('name')->get();
    }

    // ── Class changed ──
    public function updatedFilterClass()
    {
        $this->filterSection   = '';
        $this->students        = [];
        $this->selectedStudents = [];
        $this->selectAll       = false;
        $this->hasFiltered     = false;
    }

    // ── Section changed ──
    public function updatedFilterSection()
    {
        $this->students        = [];
        $this->selectedStudents = [];
        $this->selectAll       = false;
        $this->hasFiltered     = false;
    }

    // ── Select All toggle ──
    public function updatedSelectAll(bool $value): void
    {
        $this->selectedStudents = $value
            ? array_column($this->students, 'id')
            : [];
    }

    // ── Individual checkbox changed ──
    public function updatedSelectedStudents(): void
    {
        $this->selectAll = count($this->students) > 0
            && count($this->selectedStudents) === count($this->students);
    }

    // ── Filter students ──
    public function filter(): void
    {
        $this->validate([
            'fee_group_id' => 'required|exists:fee_groups,id',
            'filterClass'  => 'required|exists:academic_classes,id',
        ]);

        $query = Student::with('section', 'guardians')
            ->where('class_id', $this->filterClass)
            ->orderBy('section_id')
            ->orderBy('roll_no');

        if ($this->filterSection && $this->filterSection !== 'all') {
            $query->where('section_id', $this->filterSection);
        }

        $students = $query->get();

        if ($students->isEmpty()) {
            $this->dispatch('toast', type: 'error', message: 'No students found for selected class/section.');
            $this->hasFiltered = false;
            return;
        }

        $this->students        = $students->toArray();
        $this->selectedStudents = [];
        $this->selectAll       = false;
        $this->hasFiltered     = true;
    }

    // ── Resolve section id ──
    private function resolvedSectionId(): ?int
    {
        if (!$this->filterSection || $this->filterSection === 'all') {
            return null;
        }
        return (int) $this->filterSection;
    }

    // ── Save allocation + invoice ──
    public function save(): void
    {
        if (empty($this->selectedStudents)) {
            $this->dispatch('toast', type: 'error', message: 'Please select at least one student.');
            return;
        }

        $this->validate();

        $resolvedSection = $this->resolvedSectionId();
        $feeGroup        = FeeGroup::with('items.feeType')->findOrFail($this->fee_group_id);

        $count   = 0;
        $skipped = 0;

        foreach ($this->selectedStudents as $studentId) {

            // ── FeeAllocation — find or create ──
            $allocation = FeeAllocation::firstOrCreate(
                [
                    'student_id'   => $studentId,
                    'fee_group_id' => $this->fee_group_id,
                    'class_id'     => $this->filterClass,
                    'section_id'   => $resolvedSection,
                ],
                [
                    'student_id'   => $studentId,
                    'fee_group_id' => $this->fee_group_id,
                    'class_id'     => $this->filterClass,
                    'section_id'   => $resolvedSection,
                    'status'       => true,
                ]
            );

            // ── FeeInvoice already exists? Skip ──
            $invoiceExists = FeeInvoice::where('student_id', $studentId)
                ->where('fee_allocation_id', $allocation->id)
                ->exists();

            if ($invoiceExists) {
                $skipped++;
                continue;
            }

            // ── Calculate subtotal ──
            $subtotal = $feeGroup->items->sum('amount');

            // ── Create Invoice ──
            $invoice = FeeInvoice::create([
                'invoice_no'        => $this->generateInvoiceNo(),
                'student_id'        => $studentId,
                'fee_allocation_id' => $allocation->id,
                'class_id'          => $this->filterClass,
                'section_id'        => $resolvedSection,
                'subtotal'          => $subtotal,
                'discount_amount'   => 0,
                'fine_amount'       => 0,
                'total_amount'      => $subtotal,
                'paid_amount'       => 0,
                'due_amount'        => $subtotal,
                'invoice_date'      => now()->toDateString(),
                'due_date'          => null,
                'payment_status'    => 'unpaid',
                'status'            => true,
            ]);

            // ── Create Invoice Items ──
            foreach ($feeGroup->items as $item) {
                FeeInvoiceItem::create([
                    'fee_invoice_id'    => $invoice->id,
                    'fee_group_item_id' => $item->id,
                    'fee_type_name'     => $item->feeType->name ?? $item->fee_type_name ?? 'N/A',
                    'amount'            => $item->amount,
                    'fine_amount'       => 0,
                    'discount_amount'   => 0,
                    'total_amount'      => $item->amount,
                ]);
            }

            $count++;
        }

        // ── Toast message ──
        if ($count > 0 && $skipped > 0) {
            $this->dispatch('toast', type: 'success', message: "{$count} invoice(s) created. {$skipped} already existed (skipped).");
        } elseif ($count > 0) {
            $this->dispatch('toast', type: 'success', message: "{$count} allocation(s) & invoice(s) created successfully!");
        } else {
            $this->dispatch('toast', type: 'warning', message: "All selected students already have invoices for this fee group.");
        }

        $this->selectedStudents = [];
        $this->selectAll        = false;
    }

    // ── Invoice number generator ──
    private function generateInvoiceNo(): string
    {
        $last = FeeInvoice::lockForUpdate()->latest('id')->first();
        $next = $last ? ((int) ltrim(substr($last->invoice_no, 4), '0') + 1) : 1;

        return 'INV-' . str_pad($next, 6, '0', STR_PAD_LEFT);
    }

    // ── Reset ──
    public function resetForm(): void
    {
        $this->filterClass     = '';
        $this->filterSection   = '';
        $this->fee_group_id    = null;
        $this->students        = [];
        $this->selectedStudents = [];
        $this->selectAll       = false;
        $this->hasFiltered     = false;
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.accountant.student-accounting.fee-allocation-component')
            ->with([
                'feeGroups' => FeeGroup::where('status', true)->orderBy('name')->get(),
                'classes'   => $this->getAvailableClasses(),
                'sections'  => $this->getAvailableSections(),
            ])
            ->layout('layouts.accountant.app', [
                'title' => 'Fee Allocation | ' . institution()->name,
            ]);
    }
}