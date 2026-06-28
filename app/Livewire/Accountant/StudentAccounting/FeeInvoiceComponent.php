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

class FeeInvoiceComponent extends Component
{
    // ── Filter ──
    public $filterClass   = '';
    public $filterSection = '';

    // ── State ──
    public $students         = null;
    public array $selectedStudents = [];
    public bool  $selectAll        = false;
    public bool  $hasFiltered      = false;

    // ── Delete Confirm ──
    public bool $confirmDelete = false;
    public ?int $deleteId      = null;

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
        $this->students        = null;
        $this->selectedStudents = [];
        $this->selectAll       = false;
        $this->hasFiltered     = false;
    }

    // ── Section changed ──
    public function updatedFilterSection()
    {
        $this->students        = null;
        $this->selectedStudents = [];
        $this->selectAll       = false;
        $this->hasFiltered     = false;
    }

    // ── Select All toggle ──
    public function updatedSelectAll(bool $value): void
    {
        $this->selectedStudents = $value
            ? $this->students->pluck('id')->toArray()
            : [];
    }

    // ── Individual checkbox ──
    public function updatedSelectedStudents(): void
    {
        $this->selectAll = $this->students && $this->students->count() > 0
            && count($this->selectedStudents) === $this->students->count();
    }

    // ── Filter ──
    public function filter(): void
    {
        if (!$this->filterClass) {
            $this->dispatch('toast', type: 'error', message: 'Please select a class.');
            return;
        }

        $query = Student::with([
                'class',
                'section',
                'feeAllocations.feeGroup',
                'feeInvoices.items',
            ])
            ->where('class_id', $this->filterClass)
            ->whereHas('feeAllocations')
            ->orderBy('section_id')
            ->orderBy('roll_no');

        if ($this->filterSection && $this->filterSection !== 'all') {
            $query->where('section_id', $this->filterSection);
        }

        $students = $query->get();

        if ($students->isEmpty()) {
            $this->dispatch('toast', type: 'error', message: 'No students with fee allocations found.');
            $this->hasFiltered = false;
            return;
        }

        $this->students        = $students;
        $this->selectedStudents = [];
        $this->selectAll       = false;
        $this->hasFiltered     = true;
    }

    // ── Delete confirm ──
    public function confirmDeleteRecord(int $id): void
    {
        $this->deleteId      = $id;
        $this->confirmDelete = true;
    }

    // ── Delete student's allocations + invoices ──
    public function deleteRecord(): void
    {
        FeeAllocation::where('student_id', $this->deleteId)->delete();
        FeeInvoice::where('student_id', $this->deleteId)->delete();

        $this->confirmDelete = false;
        $this->deleteId      = null;

        // Reload list
        $this->filter();

        $this->dispatch('toast', type: 'success', message: 'All invoices deleted successfully!');
    }

    // ── Reset ──
    public function resetForm(): void
    {
        $this->filterClass     = '';
        $this->filterSection   = '';
        $this->students        = null;
        $this->selectedStudents = [];
        $this->selectAll       = false;
        $this->hasFiltered     = false;
    }

    public function render()
    {
        return view('livewire.accountant.student-accounting.fee-invoice-component')
            ->with([
                'classes'  => $this->getAvailableClasses(),
                'sections' => $this->getAvailableSections(),
            ])
            ->layout('layouts.accountant.app', [
                'title' => 'Fee Invoices | ' . institution()->name,
            ]);
    }
}