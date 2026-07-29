<?php

namespace App\Livewire\Teacher\Exam;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\ExamSetup;
use App\Models\AcademicClassAssignDetail;

class ScheduleListComponent extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    // List
    public string $search        = '';
    public int    $perPage       = 10;
    public string $sortField     = 'id';
    public string $sortDirection = 'asc';

    // Modal
    public bool      $showViewModal = false;
    public ?ExamSetup $viewRecord   = null;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField     = $field;
            $this->sortDirection = 'asc';
        }
        $this->resetPage();
    }

    public function openView(int $id): void
    {
        // render()-এ যে scoping আছে (শুধু published + teacher-এর নিজের
        // class/section-এর exam), openView()-এ সেই একই scoping না থাকলে
        // teacher URL/id ম্যানিপুলেট করে অন্য class-এর বা এখনো unpublished
        // exam setup-ও দেখতে পারত। তাই এখানে একই condition বসানো হলো।
        $assignIds = $this->getTeacherClassAssignIds();

        $this->viewRecord = ExamSetup::with([
            'term',
            'type',
            'details.classAssignDetail.subject',
            'details.classAssignDetail.classAssign.class',
            'details.classAssignDetail.classAssign.section',
        ])
            ->where('is_published', true)
            ->whereHas('details.classAssignDetail', function ($q) use ($assignIds) {
                $q->whereIn('academic_class_assign_id', $assignIds);
            })
            ->findOrFail($id);

        $this->showViewModal = true;
    }

    // ── Teacher এর assigned class+section গুলো বের করো ──────────────────
    private function getTeacherClassAssignIds(): array
    {
        return AcademicClassAssignDetail::where('teacher_id', auth()->id())
            ->pluck('academic_class_assign_id')
            ->toArray();
    }

    public function render()
    {
        // Teacher যে class+section এ আছে সেই assign IDs
        $assignIds = $this->getTeacherClassAssignIds();

        // Published ExamSetup যেগুলোতে এই teacher এর class এর subject আছে
        $schedules = ExamSetup::with(['term', 'type', 'details'])
            ->withCount([
                'schedules as total_subjects',
                'schedules as published_count' => fn($q) => $q->where('is_published', true),
            ])
            ->where('is_published', true)
            ->whereHas('details.classAssignDetail', function ($q) use ($assignIds) {
                $q->whereIn('academic_class_assign_id', $assignIds);
            })
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.teacher.exam.schedule-list-component')
            ->with('schedules', $schedules)
            ->layout('layouts.teacher.app', [
                'title' => 'Exam Schedule | ' . institution()->name,
            ]);
    }
}