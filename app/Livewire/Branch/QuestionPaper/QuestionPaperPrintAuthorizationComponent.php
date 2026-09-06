<?php

namespace App\Livewire\Branch\QuestionPaper;

use App\Models\Branch;
use App\Models\AcademicSession;
use App\Models\ExamSetup;
use App\Models\ExamSetupDetail;
use App\Models\QuestionPaperPrintAuthorization;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class QuestionPaperPrintAuthorizationComponent extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    public string $search = '';
    public int $perPage = 15;

    // ---------- "Assign Printer" modal — same cascading picker as the
    // question-paper create flow: Exam name -> Class (ExamSetup) -> Subject.
    public bool $showAssignModal = false;
    public ?string $newExamName = null;
    public ?int $newExamSetupId = null;
    public ?int $newSubjectId = null;
    public ?int $newUserId = null;
    public ?string $validFrom = null;
    public ?string $validUntil = null;

    public function mount(): void
    {
        $this->validFrom = now()->format('Y-m-d\TH:i');
        $this->validUntil = now()->addHours(2)->format('Y-m-d\TH:i');
    }

    private function activeBranchId(): ?int
    {
        return Auth::user()->branch_id
            ?? Branch::resolveMainBranchId(institution()->id);
    }

    private function resolveCurrentSessionId(): ?int
    {
        return AcademicSession::query()
            ->where('institution_id', institution()->id)
            ->where('branch_id', $this->activeBranchId())
            ->active()
            ->value('id');
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function openAssignModal(): void
    {
        $this->reset(['newExamName', 'newExamSetupId', 'newSubjectId', 'newUserId']);
        $this->validFrom = now()->format('Y-m-d\TH:i');
        $this->validUntil = now()->addHours(2)->format('Y-m-d\TH:i');
        $this->showAssignModal = true;
    }

    public function closeAssignModal(): void
    {
        $this->showAssignModal = false;
    }

    public function updatedNewExamName(): void
    {
        $this->newExamSetupId = null;
        $this->newSubjectId = null;
    }

    public function updatedNewExamSetupId(): void
    {
        $this->newSubjectId = null;
    }

    /** STEP 1 — distinct exam names for this institution/branch/session. */
    public function getExamNameOptionsProperty()
    {
        return ExamSetup::query()
            ->where('institution_id', Auth::user()->institution_id)
            ->where('branch_id', $this->activeBranchId())
            ->where('academic_session_id', $this->resolveCurrentSessionId())
            ->orderBy('name')
            ->distinct()
            ->pluck('name');
    }

    /** STEP 2 — every class (ExamSetup row) this exam name covers. */
    public function getClassOptionsProperty()
    {
        if (!$this->newExamName) {
            return collect();
        }

        return ExamSetup::query()
            ->where('institution_id', Auth::user()->institution_id)
            ->where('branch_id', $this->activeBranchId())
            ->where('academic_session_id', $this->resolveCurrentSessionId())
            ->where('name', $this->newExamName)
            ->with('classAssign.academicClass')
            ->get()
            ->map(fn (ExamSetup $setup) => [
                'id' => $setup->id,
                'label' => trim(($setup->classAssign?->academicClass?->name ?? 'Class')
                    . ($setup->classAssign?->section ? ' - ' . $setup->classAssign->section : '')),
            ])
            ->sortBy('label')
            ->values();
    }

    /** STEP 3 — subjects entered under the chosen ExamSetup. */
    public function getSubjectOptionsProperty()
    {
        if (!$this->newExamSetupId) {
            return collect();
        }

        return ExamSetupDetail::query()
            ->where('institution_id', Auth::user()->institution_id)
            ->where('branch_id', $this->activeBranchId())
            ->where('exam_setup_id', $this->newExamSetupId)
            ->with('classAssignDetail.subject')
            ->orderBy('serial')
            ->get()
            ->map(fn (ExamSetupDetail $detail) => [
                'id' => $detail->classAssignDetail?->subject?->id,
                'label' => $detail->classAssignDetail?->subject?->name,
            ])
            ->filter(fn ($row) => filled($row['id']))
            ->unique('id')
            ->values();
    }

    /** Employees in this branch who can be assigned as the printer. */
    public function getUserOptionsProperty()
    {
        return User::query()
            ->where('institution_id', Auth::user()->institution_id)
            ->where('branch_id', $this->activeBranchId())
            ->whereIn('role', ['admin', 'branch', 'staff', 'teacher', 'accountant'])
            ->orderBy('name')
            ->get(['id', 'name', 'role']);
    }

    public function assign(): void
    {
        $this->validate([
            'newExamSetupId' => 'required|integer',
            'newSubjectId' => 'required|integer',
            'newUserId' => 'required|integer',
            'validFrom' => 'required|date',
            'validUntil' => 'required|date|after:validFrom',
        ], [], [
            'newExamSetupId' => 'Class',
            'newSubjectId' => 'Subject',
            'newUserId' => 'Employee',
            'validFrom' => 'Valid from',
            'validUntil' => 'Valid until',
        ]);

        $institutionId = Auth::user()->institution_id;
        $branchId = $this->activeBranchId();

        // Defense-in-depth: re-verify the cascade server-side, exactly like
        // the question-paper create flow — never trust the payload just
        // because client-side validation passed.
        $examSetup = ExamSetup::query()
            ->where('institution_id', $institutionId)
            ->where('branch_id', $branchId)
            ->find($this->newExamSetupId);
        abort_unless($examSetup, 403);

        $subjectBelongsToExam = ExamSetupDetail::query()
            ->where('institution_id', $institutionId)
            ->where('branch_id', $branchId)
            ->where('exam_setup_id', $examSetup->id)
            ->whereHas('classAssignDetail.subject', fn ($q) => $q->where('academic_subjects.id', $this->newSubjectId))
            ->exists();
        abort_unless($subjectBelongsToExam, 403);

        $employeeBelongsHere = User::query()
            ->where('institution_id', $institutionId)
            ->where('branch_id', $branchId)
            ->where('id', $this->newUserId)
            ->exists();
        abort_unless($employeeBelongsHere, 403);

        DB::transaction(function () use ($institutionId, $branchId, $examSetup) {
            $auth = QuestionPaperPrintAuthorization::create([
                'institution_id' => $institutionId,
                'branch_id' => $branchId,
                'user_id' => $this->newUserId,
                'exam_id' => $examSetup->id,
                'subject_id' => $this->newSubjectId,
                'valid_from' => $this->validFrom,
                'valid_until' => $this->validUntil,
                'is_revoked' => false,
            ]);

            activity()
                ->tap(fn ($a) => $a->institution_id = $institutionId)
                ->causedBy(Auth::id())
                ->performedOn($auth)
                ->withProperties([
                    'icon' => 'admin_panel_settings',
                    'type' => 'question_paper_print_authorized',
                    'user_id' => $this->newUserId,
                    'exam_id' => $examSetup->id,
                    'subject_id' => $this->newSubjectId,
                ])
                ->log('Print authorization granted');
        });

        $this->showAssignModal = false;
        $this->dispatch('toast', type: 'success', message: 'Print authorization granted.');
    }

    public function revoke(int $authorizationId): void
    {
        $institutionId = Auth::user()->institution_id;

        $auth = QuestionPaperPrintAuthorization::query()
            ->where('institution_id', $institutionId)
            ->findOrFail($authorizationId);

        $auth->update(['is_revoked' => true]);

        activity()
            ->tap(fn ($a) => $a->institution_id = $institutionId)
            ->causedBy(Auth::id())
            ->performedOn($auth)
            ->withProperties(['icon' => 'block', 'type' => 'question_paper_print_revoked'])
            ->log('Print authorization revoked');

        $this->dispatch('toast', type: 'success', message: 'Authorization revoked.');
    }

    public function getAuthorizationsProperty()
    {
        $institutionId = Auth::user()->institution_id;
        $branchId = $this->activeBranchId();

        return QuestionPaperPrintAuthorization::query()
            ->where('institution_id', $institutionId)
            ->where('branch_id', $branchId)
            ->with(['user', 'examSetup.classAssign.academicClass', 'subject'])
            ->when($this->search, function ($q) {
                $term = '%' . $this->search . '%';
                $q->where(function ($inner) use ($term) {
                    $inner->whereHas('user', fn ($u) => $u->where('name', 'like', $term))
                        ->orWhereHas('subject', fn ($s) => $s->where('name', 'like', $term));
                });
            })
            ->orderByDesc('created_at')
            ->paginate($this->perPage);
    }

    public function render()
    {
        return view('livewire.admin.question-paper.question-paper-print-authorization-component', [
            'authorizations' => $this->authorizations,
        ])->layout('layouts.branch.app', [
            'title' => 'Print Authorizations | ' . institution()->name,
        ]);
    }
}