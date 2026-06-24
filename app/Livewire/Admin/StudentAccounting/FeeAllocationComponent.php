<?php

namespace App\Livewire\Admin\StudentAccounting;

use Livewire\Component;
use App\Models\FeeAllocation;
use App\Models\FeeGroup;
use App\Models\AcademicClass;
use App\Models\Student;
use App\Models\FeeInvoice;
use App\Models\FeeInvoiceItem;

class FeeAllocationComponent extends Component
{
    // Filter
    public $class_id      = null;
    public $section_id    = null;
    public $fee_group_id  = null;

    // Dynamic
    public array $sections        = [];
    public array $students        = [];
    public array $selectedStudents = [];
    public bool  $selectAll       = false;
    public bool  $hasFiltered     = false;

    protected function rules(): array
    {
        return [
            'fee_group_id'       => 'required|exists:fee_groups,id',
            'class_id'           => 'required|exists:academic_classes,id',
            'selectedStudents'   => 'required|array|min:1',
            'selectedStudents.*' => 'exists:students,id',
        ];
    }

    public function updatedClassId(): void
    {
        $this->section_id       = null;
        $this->sections         = [];
        $this->students         = [];
        $this->selectedStudents = [];
        $this->selectAll        = false;
        $this->hasFiltered      = false;

        if ($this->class_id) {
            $class          = AcademicClass::with('sections')->find($this->class_id);
            $this->sections = $class?->sections->toArray() ?? [];
        }
    }

    public function updatedSectionId(): void
    {
        $this->students         = [];
        $this->selectedStudents = [];
        $this->selectAll        = false;
        $this->hasFiltered      = false;
    }

    public function updatedSelectAll(bool $value): void
    {
        $this->selectedStudents = $value
            ? array_column($this->students, 'id')
            : [];
    }

    public function updatedSelectedStudents(): void
    {
        $this->selectAll = count($this->students) > 0
            && count($this->selectedStudents) === count($this->students);
    }

    public function filter(): void
    {
        $this->validate([
            'fee_group_id' => 'required|exists:fee_groups,id',
            'class_id'     => 'required|exists:academic_classes,id',
            'section_id'     => 'required',
        ]);

        $this->loadStudents();
        $this->selectedStudents = [];
        $this->selectAll        = false;
        $this->hasFiltered      = true;
    }

    private function loadStudents(): void
    {
        if (!$this->class_id) return;

        $this->students = Student::with('section','guardians')
            ->where('class_id', $this->class_id)
            ->when(
                $this->section_id && $this->section_id !== 'all',
                fn($q) => $q->where('section_id', $this->section_id)
            )
            ->orderBy('roll_no')
            ->get()
            ->toArray();
    }

    private function resolvedSectionId(): ?int
    {
        if (!$this->section_id || $this->section_id === 'all') {
            return null;
        }
        return (int) $this->section_id;
    }

    public function save(): void
    {
        if (!$this->selectedStudents) {
            $this->dispatch('toast', type: 'error', message: "Select a students");
            return;
        }

        $this->validate();

        $resolvedSection = $this->resolvedSectionId();
        $feeGroup        = FeeGroup::with('items.feeType')->findOrFail($this->fee_group_id);

        $data = [
            'fee_group_id' => $this->fee_group_id,
            'class_id'     => $this->class_id,
            'section_id'   => $resolvedSection,
            'status'       => true,
        ];

        $count   = 0;
        $skipped = 0;

        foreach ($this->selectedStudents as $studentId) {

            // ── FeeAllocation — existing check or create ──
            $allocation = FeeAllocation::firstOrCreate(
                [
                    'student_id'   => $studentId,
                    'fee_group_id' => $this->fee_group_id,
                    'class_id'     => $this->class_id,
                    'section_id'   => $resolvedSection,
                ],
                $data + ['student_id' => $studentId]
            );

            // ── FeeInvoice — existing check ──
            $invoice = \App\Models\FeeInvoice::where('student_id', $studentId)
                ->where('fee_allocation_id', $allocation->id)
                ->first();

            if ($invoice) {
                // Invoice already exists — skip
                $skipped++;
                continue;
            }

            // ── Invoice amounts calculate ──
            $subtotal = $feeGroup->items->sum('amount');

            // ── FeeInvoice create ──
            $invoice = \App\Models\FeeInvoice::create([
                'invoice_no'        => $this->generateInvoiceNo(),
                'student_id'        => $studentId,
                'fee_allocation_id' => $allocation->id,
                'class_id'          => $this->class_id,
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

            // ── FeeInvoiceItems create ──
            foreach ($feeGroup->items as $item) {
                \App\Models\FeeInvoiceItem::create([
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

        // ── Toast ──
        if ($count > 0 && $skipped > 0) {
            $this->dispatch('toast', type: 'success', message: "{$count} invoice(s) created. {$skipped} already existed (skipped).");
        } elseif ($count > 0) {
            $this->dispatch('toast', type: 'success', message: "{$count} allocation(s) & invoice(s) created successfully!");
        } else {
            $this->dispatch('toast', type: 'error', message: "All selected students already have invoices.");
        }

        $this->selectedStudents = [];
        $this->selectAll        = false;
    }

    private function generateInvoiceNo(): string
    {
        $last = \App\Models\FeeInvoice::lockForUpdate()->latest('id')->first();
        $next = $last ? ((int) ltrim(substr($last->invoice_no, 4), '0') + 1) : 1;

        return 'INV-' . str_pad($next, 6, '0', STR_PAD_LEFT);
    }

    public function resetForm(): void
    {
        $this->reset([
            'fee_group_id', 'class_id', 'section_id',
            'sections', 'students', 'selectedStudents', 'hasFiltered',
        ]);
        $this->selectAll = false;
        $this->resetValidation();
    }

    public function render()
    {
        $feeGroups = FeeGroup::where('status', true)->orderBy('name')->get();
        $classes   = AcademicClass::orderBy('name')->get();

        return view('livewire.admin.student-accounting.fee-allocation-component')
            ->with(['feeGroups' => $feeGroups, 'classes' => $classes])
            ->layout('layouts.admin.app', [
                'title' => 'Fee Allocation | ' . institution()->name,
            ]);
    }
}