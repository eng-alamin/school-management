<?php

namespace App\Livewire\Teacher\Homework;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
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

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    public function updatedFilterClass($value): void
    {
        $this->filterSection     = '';
        $this->availableSections = [];
        $this->resetPage();

        if (!$value) return;

        $institutionId = institution()->id;

        // Only show sections this teacher actually teaches for the selected
        // class — matches the same scoping used on the Add/Edit forms.
        $myAssignIds = AcademicClassAssignDetail::where('institution_id', $institutionId)
            ->where('teacher_id', auth()->id())
            ->pluck('academic_class_assign_id');

        $assigns = AcademicClassAssign::with('section')
            ->where('institution_id', $institutionId)
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
        // Ownership + institution check: without this, any teacher could
        // delete another teacher's (or another institution's) homework just
        // by knowing its id (IDOR).
        $homework = Homework::where('id', $this->deleteId)
            ->where('institution_id', institution()->id)
            ->where('teacher_id', auth()->id())
            ->first();

        if (!$homework) {
            $this->confirmDelete = false;
            $this->deleteId      = null;
            $this->dispatch('toast', type: 'error', message: 'Homework not found or not allowed.');
            return;
        }

        $attachmentPath = $homework->attachment;
        $title          = $homework->title;

        try {
            DB::transaction(function () use ($homework, $title) {
                // Activity log delete-এর আগে, পরে না
                activity()
                    ->performedOn($homework)
                    ->causedBy(auth()->user())
                    ->tap(fn($a) => $a->institution_id = institution()->id)
                    ->log('Homework "' . $title . '" deleted');

                $homework->delete();
            });

            // DB delete committed successfully → এখন attachment মুছে ফেলা নিরাপদ
            if ($attachmentPath) {
                Storage::disk('public')->delete($attachmentPath);
            }

            $this->dispatch('toast', type: 'success', message: 'Homework deleted successfully.');

        } catch (\Throwable $e) {
            $this->dispatch('toast', type: 'error', message: 'Delete failed: ' . $e->getMessage());
            report($e);
        }

        $this->confirmDelete = false;
        $this->deleteId      = null;
    }

    public function render()
    {
        $institutionId = institution()->id;

        // Filter dropdown শুধু এই teacher যেসব class পড়ায় সেগুলাই দেখাবে
        $myAssignIds = AcademicClassAssignDetail::where('institution_id', $institutionId)
            ->where('teacher_id', auth()->id())
            ->pluck('academic_class_assign_id');

        $classIds = AcademicClassAssign::where('institution_id', $institutionId)
            ->whereIn('id', $myAssignIds)
            ->distinct()
            ->pluck('class_id');

        $classes = AcademicClass::where('institution_id', $institutionId)
            ->whereIn('id', $classIds)
            ->orderBy('name')
            ->get();

        $homeworks = Homework::with(['class', 'section', 'subject'])
            ->where('institution_id', $institutionId)
            ->where('teacher_id', auth()->id())
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