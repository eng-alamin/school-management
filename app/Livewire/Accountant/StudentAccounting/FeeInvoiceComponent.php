<?php

namespace App\Livewire\Accountant\StudentAccounting;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\FeeInvoice;
use App\Models\AcademicClass;
use App\Models\AcademicClassAssign;
use App\Models\Student;
use Illuminate\Support\Facades\DB;

class FeeInvoiceComponent extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    // ── List / Filter (Homework pattern) ──
    public string $search        = '';
    public int    $perPage       = 10;
    public string $sortField     = 'roll_no';
    public string $sortDir       = 'asc';

    public $filterClass   = '';
    public $filterSection = '';

    public array $availableSections = [];

    // ── Delete Confirm ──
    public bool $confirmDelete = false;
    public ?int $deleteId      = null;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatedFilterClass($value): void
    {
        $this->filterSection     = '';
        $this->availableSections = [];
        $this->resetPage();

        if (! $value) return;

        $assigns = AcademicClassAssign::with('section')
            ->where('class_id', $value)
            ->whereNotNull('section_id')
            ->get();

        $this->availableSections = $assigns
            ->filter(fn ($a) => $a->section)
            ->map(fn ($a) => ['id' => $a->section->id, 'name' => $a->section->name])
            ->unique('id')
            ->values()
            ->toArray();
    }

    public function updatedFilterSection(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDir = $this->sortDir === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDir   = 'asc';
        }
    }

    public function confirmDeleteRecord(int $id): void
    {
        $this->deleteId      = $id;
        $this->confirmDelete = true;
    }

    // ── শুধু Invoice ডিলিট হবে — Items cascadeOnDelete দিয়ে অটো মুছবে ──
    public function deleteRecord(): void
    {
        DB::beginTransaction();

        try {
            $invoices = FeeInvoice::where('student_id', $this->deleteId)->get();

            if ($invoices->isEmpty()) {
                DB::rollBack();
                $this->confirmDelete = false;
                $this->deleteId = null;
                $this->dispatch('toast', type: 'warning', message: 'No invoice found.');
                return;
            }

            $student = Student::find($this->deleteId);

            FeeInvoice::where('student_id', $this->deleteId)->delete();

            activity()
                ->withProperties([
                    'icon'           => 'delete',
                    'type'           => 'delete',
                    'institution_id' => institution()->id,
                ])
                ->log('Deleted ' . $invoices->count() . ' Fee Invoice(s) for Student: ' . ($student->name ?? $this->deleteId));

            DB::commit();

            $this->confirmDelete = false;
            $this->deleteId      = null;

            $this->dispatch('toast', type: 'success', message: 'All invoices deleted successfully!');

        } catch (\Throwable $e) {
            DB::rollBack();
            $this->dispatch('toast', type: 'error', message: 'Something went wrong!');
        }
    }

    public function render()
    {
        $classes = AcademicClass::whereIn('id', AcademicClassAssign::distinct()->pluck('class_id'))
            ->orderBy('name')
            ->get();

        $students = Student::with(['class', 'section', 'feeInvoices.items.feeGroupItem.feeType'])
            ->whereHas('feeInvoices')
            ->when($this->search, fn ($q) => $q->where(function ($q2) {
                $q2->where('name', 'like', '%' . $this->search . '%')
                   ->orWhere('student_id', 'like', '%' . $this->search . '%')
                   ->orWhere('roll_no', 'like', '%' . $this->search . '%');
            }))
            ->when($this->filterClass, fn ($q) =>
                $q->where('class_id', $this->filterClass)
            )
            ->when($this->filterSection && $this->filterSection !== 'all', fn ($q) =>
                $q->where('section_id', $this->filterSection)
            )
            ->orderBy($this->sortField, $this->sortDir)
            ->paginate($this->perPage);

        return view('livewire.accountant.student-accounting.fee-invoice-component')
            ->with([
                'classes'  => $classes,
                'students' => $students,
            ])
            ->layout('layouts.accountant.app', [
                'title' => 'Fee Invoices | ' . institution()->name,
            ]);
    }
}