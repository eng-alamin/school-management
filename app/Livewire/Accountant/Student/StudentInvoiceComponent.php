<?php

namespace App\Livewire\Accountant\Student;

use Livewire\Component;
use App\Models\Student;
use App\Models\FeeAllocation;
use App\Models\FeeInvoice;
use App\Models\FeeInvoiceItem;

class StudentInvoiceComponent extends Component
{
    public $student;
    public $feeAllocations;
    public $invoiceItemsMap = []; // fee_group_item_id => FeeInvoiceItem

    public array $selectedIds = [];
    public bool  $selectAll   = false;

    public function mount(int $id)
    {
        $this->student = Student::with([
            'session',
            'class',
            'section',
            'category',
            'guardians',
            'user',
        ])->findOrFail($id);

        $this->loadAllocations();
    }

    private function loadAllocations(): void
    {
        $this->feeAllocations = FeeAllocation::with([
                'feeGroup.items.feeType',
            ])
            ->where('student_id', $this->student->id)
            ->get();

        // FeeInvoiceItem গুলো fee_group_item_id দিয়ে map করো
        $allGroupItemIds = $this->feeAllocations
            ->flatMap(fn($a) => $a->feeGroup->items->pluck('id'))
            ->toArray();

        $invoiceItems = FeeInvoiceItem::with('itemPayments')
            ->whereIn('fee_group_item_id', $allGroupItemIds)
            ->whereHas('invoice', fn($q) =>
                $q->where('student_id', $this->student->id)
            )
            ->get()
            ->keyBy('fee_group_item_id');

        $this->invoiceItemsMap = $invoiceItems;
    }

    public function updatedSelectAll(bool $value): void
    {
        if ($value) {
            $ids = [];
            foreach ($this->feeAllocations as $allocation) {
                foreach ($allocation->feeGroup->items as $item) {
                    $ids[] = $item->id;
                }
            }
            $this->selectedIds = $ids;
        } else {
            $this->selectedIds = [];
        }
    }

    public function updatedSelectedIds(): void
    {
        $total = $this->feeAllocations->sum(
            fn($a) => $a->feeGroup->items->count()
        );
        $this->selectAll = count($this->selectedIds) === $total && $total > 0;
    }

    public function collectSelected(): void
    {
        if (empty($this->selectedIds)) {
            $this->dispatch('toast', type: 'error', message: 'No fee selected.');
            return;
        }

        // Payment page এ redirect
        $this->redirect(route('accountant.student.payment.add', $this->student->id));
    }

    public function render()
    {
        return view('livewire.accountant.student.student-invoice-component')
            ->layout('layouts.accountant.app', [
                'title' => "Student Invoice | School SaaS",
            ]);
    }
}