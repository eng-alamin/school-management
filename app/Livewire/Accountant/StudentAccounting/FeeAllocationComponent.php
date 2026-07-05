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
use Illuminate\Support\Facades\DB;

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

    // ── Available Classes (established pattern) ──
    public function getAvailableClasses()
    {
        return AcademicClass::whereIn('id', AcademicClassAssign::distinct()->pluck('class_id'))
            ->orderBy('name')
            ->get();
    }

    // ── Available Sections (established pattern) ──
    public function getAvailableSections()
    {
        if (!$this->filterClass) return [];

        return AcademicSection::whereIn('id',
            AcademicClassAssign::where('class_id', $this->filterClass)->pluck('section_id')
        )->orderBy('name')->get();
    }

    public function updatedFilterClass()
    {
        $this->filterSection    = '';
        $this->students         = [];
        $this->selectedStudents = [];
        $this->selectAll        = false;
        $this->hasFiltered      = false;
    }

    public function updatedFilterSection()
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
            $this->dispatch('toast', type: 'error', message: 'নির্বাচিত Class/Section-এ কোনো Student পাওয়া যায়নি।');
            $this->hasFiltered = false;
            return;
        }

        $this->students          = $students->toArray();
        $this->selectedStudents  = [];
        $this->selectAll         = false;
        $this->hasFiltered       = true;
    }

    private function resolvedSectionId(): ?int
    {
        if (!$this->filterSection || $this->filterSection === 'all') {
            return null;
        }
        return (int) $this->filterSection;
    }

    // ── Save allocation (class/section-level) + invoice (student-level) ──
    public function save(): void
    {
        if (empty($this->selectedStudents)) {
            $this->dispatch('toast', type: 'error', message: 'অন্তত একজন Student সিলেক্ট করুন।');
            return;
        }

        $this->validate();

        $resolvedSection = $this->resolvedSectionId();
        $feeGroup = FeeGroup::with('items.feeType')->findOrFail($this->fee_group_id);

        if ($feeGroup->items->isEmpty()) {
            $this->dispatch('toast', type: 'error', message: 'এই Fee Group-এ কোনো Fee Item যোগ করা নেই।');
            return;
        }

        [$count, $skipped] = DB::transaction(function () use ($feeGroup, $resolvedSection) {

            // ── ধাপ ১: Class/Section-level Allocation — প্রতিটা Item-এর জন্য একবারই বসবে ──
            foreach ($feeGroup->items as $item) {
                FeeAllocation::firstOrCreate(
                    [
                        'academic_class_id'   => $this->filterClass,
                        'academic_section_id' => $resolvedSection,
                        'fee_group_item_id'   => $item->id,
                    ],
                    ['status' => true]
                );
            }

            $itemIds  = $feeGroup->items->pluck('id')->toArray();
            $subtotal = $feeGroup->items->sum('amount');
            $count    = 0;
            $skipped  = 0;

            // ── ধাপ ২: প্রতি Student-এর জন্য Invoice তৈরি ──
            foreach ($this->selectedStudents as $studentId) {

                // এই Fee Group-এর Item দিয়ে আগে Invoice হয়েছে কিনা চেক
                $alreadyInvoiced = FeeInvoiceItem::whereIn('fee_group_item_id', $itemIds)
                    ->whereHas('feeInvoice', fn($q) => $q->where('student_id', $studentId))
                    ->exists();

                if ($alreadyInvoiced) {
                    $skipped++;
                    continue;
                }

                $invoice = FeeInvoice::create([
                    'invoice_no'      => $this->generateInvoiceNo(),
                    'student_id'      => $studentId,
                    'subtotal'        => $subtotal,
                    'discount_amount' => 0,
                    'fine_amount'     => 0,
                    'total_amount'    => $subtotal,
                    'paid_amount'     => 0,
                    'due_amount'      => $subtotal,
                    'invoice_date'    => now()->toDateString(),
                    'due_date'        => null,
                    'payment_status'  => 'unpaid',
                    'status'          => true,
                ]);

                foreach ($feeGroup->items as $item) {
                    FeeInvoiceItem::create([
                        'fee_invoice_id'    => $invoice->id,
                        'fee_group_item_id' => $item->id,
                        'base_amount'       => $item->amount,
                        'fine_amount'       => 0,
                        'discount_amount'   => 0,
                        'total_amount'      => $item->amount,
                    ]);
                }

                $count++;
            }

            return [$count, $skipped];
        });

        if ($count > 0 && $skipped > 0) {
            $this->dispatch('toast', type: 'success', message: "{$count} টি Invoice তৈরি হয়েছে। {$skipped} জনের আগে থেকেই Invoice ছিল (Skip হয়েছে)।");
        } elseif ($count > 0) {
            $this->dispatch('toast', type: 'success', message: "{$count} টি Invoice সফলভাবে তৈরি হয়েছে!");
        } else {
            $this->dispatch('toast', type: 'warning', message: 'নির্বাচিত সব Student-এর আগে থেকেই এই Fee Group-এর Invoice আছে।');
        }

        $this->selectedStudents = [];
        $this->selectAll        = false;
    }

    // ── Invoice number generator — Institution scoped ──
    private function generateInvoiceNo(): string
    {
        $last = FeeInvoice::where('institution_id', institution()->id)
            ->lockForUpdate()
            ->latest('id')
            ->first();

        $next = $last ? ((int) ltrim(substr($last->invoice_no, 4), '0') + 1) : 1;

        return 'INV-' . str_pad($next, 6, '0', STR_PAD_LEFT);
    }

    public function resetForm(): void
    {
        $this->filterClass      = '';
        $this->filterSection    = '';
        $this->fee_group_id     = null;
        $this->students         = [];
        $this->selectedStudents = [];
        $this->selectAll        = false;
        $this->hasFiltered      = false;
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