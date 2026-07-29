<?php

namespace App\Livewire\Teacher\Homework;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Storage;
use App\Models\Homework;
use App\Models\AcademicClass;
use App\Models\AcademicClassAssign;
use App\Models\AcademicClassAssignDetail;

class HomeworkListComponent extends Component
{
    use WithPagination;

    public $search    = '';
    public $perPage   = 10;
    public $sortField = 'created_at';
    public $sortDir   = 'desc';

    public $filterClass   = '';
    public $filterSection = '';

    public array $availableSections = [];

    // Fields sortBy() is allowed to touch — keeps the wire:click payload
    // from being used to sort by an arbitrary/unindexed or unrelated column.
    private const SORTABLE_FIELDS = ['title', 'homework_date', 'submission_date', 'created_at'];

    public $confirmDelete = false;
    public $deleteId;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedFilterClass($value): void
    {
        $this->filterSection     = '';
        $this->availableSections = [];
        $this->resetPage();

        if (!$value) return;

        // Only show sections this teacher actually teaches for the selected
        // class — matches the same scoping used on the Add/Edit forms.
        $myAssignIds = AcademicClassAssignDetail::where('teacher_id', auth()->id())
            ->pluck('academic_class_assign_id');

        $assigns = AcademicClassAssign::with('section')
            ->where('class_id', $value)
            ->whereNotNull('section_id')
            ->whereIn('id', $myAssignIds)
            ->get();

        $this->availableSections = $assigns
            ->filter(fn($a) => $a->section)
            ->map(fn($a) => ['id' => $a->section->id, 'name' => $a->section->name])
            ->unique('id')
            ->values()
            ->toArray();
    }

    public function updatedFilterSection(): void
    {
        $this->resetPage();
    }

    public function sortBy($field): void
    {
        if (!in_array($field, self::SORTABLE_FIELDS, true)) {
            return;
        }

        if ($this->sortField === $field) {
            $this->sortDir = $this->sortDir === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDir   = 'asc';
        }
    }

    public function confirmDeleteRecord($id): void
    {
        $this->deleteId      = $id;
        $this->confirmDelete = true;
    }

    public function deleteRecord(): void
    {
        // Ownership check: without filtering by teacher_id, any teacher could
        // delete another teacher's homework just by knowing its id (IDOR).
        $homework = Homework::where('id', $this->deleteId)
            ->where('teacher_id', auth()->id())
            ->first();

        if ($homework) {
            if ($homework->attachment) {
                Storage::disk('public')->delete($homework->attachment);
            }
            $homework->delete();
            $this->dispatch('toast', type: 'success', message: 'Homework deleted successfully.');
        } else {
            $this->dispatch('toast', type: 'error', message: 'Homework not found or not allowed.');
        }

        $this->confirmDelete = false;
        $this->deleteId      = null;
    }

    public function render()
    {
        // Filter dropdown only offers classes this teacher is assigned to —
        // previously it listed every class in the institution, so picking one
        // the teacher doesn't teach silently returned an empty (confusing) list.
        $myAssignIds = AcademicClassAssignDetail::where('teacher_id', auth()->id())
            ->pluck('academic_class_assign_id');

        $classIds = AcademicClassAssign::whereIn('id', $myAssignIds)
            ->distinct()
            ->pluck('class_id');

        $classes = AcademicClass::whereIn('id', $classIds)
            ->orderBy('name')
            ->get();

        $homeworks = Homework::with(['class', 'section', 'subject'])->where('teacher_id', auth()->id())
            ->when($this->search, fn($q) =>
                $q->where('title', 'like', '%' . $this->search . '%')
            )
            ->when($this->filterClass, fn($q) =>
                $q->where('class_id', $this->filterClass)
            )
            ->when($this->filterSection && $this->filterSection !== 'all', fn($q) =>
                $q->where('section_id', $this->filterSection)
            )
            ->orderBy($this->sortField, $this->sortDir)
            ->paginate($this->perPage);

        return view('livewire.teacher.homework.homework-list-component')
            ->with('classes', $classes)
            ->with('homeworks', $homeworks)
            ->layout('layouts.teacher.app', [
                'title' => 'Homework List | ' . institution()->name,
            ]);
    }
}