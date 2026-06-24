<?php

namespace App\Livewire\Admin\StudentAccounting;

use Livewire\Component;
use App\Models\FeeAllocation;
use App\Models\FeeGroup;
use App\Models\AcademicClass;
use App\Models\Student;

class FeeInvoiceComponent extends Component
{
    // Filter
    public $class_id      = null;
    public $section_id    = null;

    // Dynamic
    public $sections        = [];
    public $students;
    public $selectedStudents = [];
    public $selectAll       = false;
    public $hasFiltered     = false;

    public $confirmDelete = false;
    public $deleteId = null;

    protected function rules()
    {
        return [
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
            ? $this->students->pluck('id')->toArray()
            : [];
    }

    public function updatedSelectedStudents(): void
    {
        $this->selectAll = $this->students && count($this->students) > 0
            && count($this->selectedStudents) === count($this->students);
    }

    public function filter(): void
    {
        $this->validate([
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

        $this->students = Student::with([
                'class',
                'section',
                'feeAllocations.feeGroup',
                'feeInvoices.items',
            ])
            ->where('class_id', $this->class_id)
            ->when(
                $this->section_id && $this->section_id !== 'all',
                fn($q) => $q->where('section_id', $this->section_id)
            )
            ->whereHas('feeAllocations')
            ->orderBy('roll_no')
            ->get();
    }

    private function resolvedSectionId(): ?int
    {
        if (!$this->section_id || $this->section_id === 'all') {
            return null;
        }
        return (int) $this->section_id;
    }

    public function confirmDeleteRecord(int $id): void
    {
        $this->deleteId      = $id;
        $this->confirmDelete = true;
    }

    public function deleteRecord(): void
    {
        // Student এর সব FeeAllocation delete (cascade হলে Invoice ও যাবে)
        FeeAllocation::where('student_id', $this->deleteId)->delete();

        // Invoice cascade না হলে manually delete
        \App\Models\FeeInvoice::where('student_id', $this->deleteId)->delete();

        $this->confirmDelete = false;
        $this->deleteId      = null;
        $this->loadStudents();

        $this->dispatch('toast', type: 'success', message: 'All invoices deleted successfully!');
    }

    public function render()
    {
        $feeGroups = FeeGroup::where('status', true)->orderBy('name')->get();
        $classes   = AcademicClass::orderBy('name')->get();

        return view('livewire.admin.student-accounting.fee-invoice-component')
            ->with(['feeGroups' => $feeGroups, 'classes' => $classes])
            ->layout('layouts.admin.app', [
                'title' => 'Fee Invoices | ' . institution()->name,
            ]);
    }
}