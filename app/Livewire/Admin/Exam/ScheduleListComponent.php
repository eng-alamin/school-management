<?php

namespace App\Livewire\Admin\Exam;

use Livewire\Component;
use App\Models\ExamSetup;
use App\Models\ExamSchedule;
use Livewire\WithPagination;

class ScheduleListComponent extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    // Sort column allowlist — direct Livewire method call দিয়ে arbitrary column
    // পাঠিয়ে orderBy() manipulate করা ঠেকানোর জন্য (established project pattern)
    private const SORTABLE_FIELDS = ['id', 'name'];

    // List
    public string $search        = '';
    public int    $perPage       = 10;
    public string $sortField     = 'name';
    public string $sortDirection = 'asc';

    // Modal
    public bool       $showViewModal = false;
    public bool       $confirmDelete = false;
    public ?int        $deleteId      = null;
    public ?ExamSetup $viewRecord    = null;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        // Allowlist check — না থাকলে silently ignore করা হচ্ছে, যাতে সরাসরি
        // Livewire component call করে arbitrary column দিয়ে orderBy() manipulate
        // করা না যায়।
        if (!in_array($field, self::SORTABLE_FIELDS, true)) {
            return;
        }

        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField     = $field;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
    }

    public function openView(int $examSetupId): void
    {
        // FIX (N+1): আগে 'details.classAssignDetail.subject' eager-load করা
        // হচ্ছিল, কিন্তু blade আসলে $viewRecord->schedules ব্যবহার করে —
        // সেই chain (schedules.examSetupDetail.classAssignDetail.subject)
        // eager-load-ই হচ্ছিল না, ফলে প্রতিটা schedule row-এর জন্য আলাদা
        // query চলত (N+1)। এখন সঠিক relation eager-load করে, এবং
        // eager-load-এর ভেতরেই ordering করে ম্যানুয়াল sortBy() বাদ দেওয়া হলো।
        $this->viewRecord = ExamSetup::with([
            'classAssign.academicClass',
            'classAssign.academicSection',
            'schedules' => fn($q) => $q->orderBy('exam_date')->orderBy('start_time'),
            'schedules.examSetupDetail.classAssignDetail.subject',
        ])->findOrFail($examSetupId);

        $this->showViewModal = true;
    }

    public function confirmDeleteRecord(int $examSetupId): void
    {
        $this->deleteId      = $examSetupId;
        $this->confirmDelete = true;
    }

    // ── পুরো Exam এর সব subject-schedule একসাথে মুছে ফেলা হবে ──
    public function deleteRecord(): void
    {
        // FIX: আগে সরাসরি ExamSchedule::where(...)->delete() চালানো হতো,
        // ownership/institution scope explicit check ছাড়াই (defense-in-depth
        // গ্যাপ)। এখন আগে ExamSetup::findOrFail() দিয়ে নিশ্চিত হওয়া হচ্ছে যে
        // এই id বর্তমান institution-এর, নাহলে 404 হবে।
        $examSetup = ExamSetup::findOrFail($this->deleteId);

        ExamSchedule::where('exam_setup_id', $examSetup->id)->delete();

        // FIX: delete-এর কোনো activity log ছিল না — established pattern
        // অনুযায়ী সব delete action log হওয়া উচিত।
        activity()
            ->causedBy(auth()->user())
            ->performedOn($examSetup)
            ->withProperties(['icon' => 'event_note', 'type' => 'exam_schedule'])
            ->tap(fn($a) => $a->institution_id = $examSetup->institution_id)
            ->log('Exam schedule deleted for: ' . $examSetup->name);

        $this->confirmDelete = false;
        $this->deleteId      = null;
        $this->dispatch('toast', type: 'success', message: 'Schedule deleted successfully!');
    }

    public function render()
    {
        // শুধু সেই Exam Setup গুলো দেখাবো যাদের অন্তত একটা schedule আছে
        $setups = ExamSetup::with(['classAssign.academicClass', 'classAssign.academicSection'])
            ->withCount([
                'schedules as total_subjects',
                'schedules as published_count' => fn($q) => $q->where('is_published', true),
            ])
            ->whereHas('schedules')
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.admin.exam.schedule-list-component')
            ->with('schedules', $setups)
            ->layout('layouts.admin.app', [
                'title' => 'Exam Schedule | ' . institution()->name,
            ]);
    }
}