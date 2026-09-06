<?php

namespace App\Livewire\ITSupport\StudentAccounting;

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

    public string $routePrefix = '';

    public function mount(): void
    {
        $this->routePrefix = $this->resolveRoutePrefix();
    }

    protected function resolveRoutePrefix(): string
    {
        $routeName = request()->route()?->getName();

        if ($routeName && str_contains($routeName, '.')) {
            return explode('.', $routeName)[0] . '.';
        }

        $segment = request()->segment(1);

        return $segment ? $segment . '.' : '';
    }

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

    public function render()
    {
        $classes = AcademicClass::whereIn('id', AcademicClassAssign::distinct()->pluck('class_id'))
            ->orderBy('name')
            ->get();

        $students = Student::with(['class', 'section', 'feeInvoices.items.feeSetup.feeType'])
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

        return view('livewire.admin.student-accounting.fee-invoice-component')
            ->with([
                'classes'  => $classes,
                'students' => $students,
            ])
            ->layout('layouts.itsupport.app', [
                'title' => 'Fee Invoices | ' . institution()->name,
            ]);
    }
}