<?php

namespace App\Livewire\Branch\Academic;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\AcademicClassAssign;
use App\Models\AcademicClassAssignDetail;
use App\Models\AcademicClass;
use App\Models\AcademicSession;
use App\Models\AcademicSubject;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use Illuminate\Validation\Rule;

class ClassAssignComponent extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    // ── Sort/Search allowlist (security: raw column injection thekano jonno) ──
    private const SORTABLE_FIELDS = ['id', 'class_name', 'section_name'];

    // Pagination whitelist (security: DoS thekano jonno, arbitrary perPage allow kora jabe na)
    private const PER_PAGE_OPTIONS = [10, 25, 50];

    // List
    public string $search = '';
    public int $perPage = 10;
    public string $sortField = 'id';
    public string $sortDirection = 'asc';

    // Modal
    public bool $showModal = false;
    public bool $confirmDelete = false;
    public ?int $deleteId = null;

    // Form
    public ?int $editId = null;
    public string $class_id = '';
    public $section_id;

    // subject_array = checkbox diye select kora subject_id array
    public array $subject_array = [];

    // teacher_array = [subject_id => teacher_id]
    public array $teacher_array = [];

    // Dependent dropdown
    public array $availableSections = [];

    // Selected class-er has_section flag, blade e section dropdown show/hide korar jonno
    public bool $selectedClassHasSection = true;

    // Current active academic session — cache kore rakha holo jate baar baar
    // query na chole. Eita mount() e resolve kora hoy.
    public ?int $currentSessionId = null;

    public function mount(): void
    {
        $this->currentSessionId = $this->resolveCurrentSessionId();
    }

    private function resolveCurrentSessionId(): ?int
    {
        return AcademicSession::query()
            ->where('institution_id', institution()->id)
            ->where('branch_id', $this->activeBranchId())
            ->active() // scopeActive() -> is_current = true
            ->value('id');
    }

    private function activeBranchId(): ?int
    {
        return auth()->user()->branch_id
            ?? Branch::resolveMainBranchId(institution()->id);
    }

    protected function rules(): array
    {
        $institutionId = institution()->id;
        $branchId      = $this->activeBranchId();
        $sessionId     = $this->currentSessionId;

        return [
            'class_id' => [
                'required',
                Rule::exists('academic_classes', 'id')
                    ->where(fn ($q) => $q->where('institution_id', $institutionId)),
                Rule::unique('academic_class_assigns', 'class_id')
                    ->where(fn ($query) => $query
                        ->where('institution_id', $institutionId)
                        ->where('branch_id', $branchId)
                        ->where('session_id', $sessionId)
                        ->where('section_id', $this->section_id ?: null))
                    ->ignore($this->editId),
            ],

            'section_id' => [
                Rule::requiredIf($this->selectedClassHasSection),
                'nullable',
                Rule::exists('academic_sections', 'id')
                    ->where(fn ($q) => $q->where('institution_id', $institutionId)),
            ],

            'subject_array'   => 'required|array|min:1',
            'subject_array.*' => [
                'required',
                'integer',
                Rule::exists('academic_subjects', 'id')
                    ->where(fn ($q) => $q->where('institution_id', $institutionId)),
                function ($attribute, $value, $fail) {
                    if (empty($this->teacher_array[$value])) {
                        $fail('Please assign a teacher for the selected subject.');
                    }
                },
            ],

            'teacher_array'   => 'nullable|array',
            'teacher_array.*' => [
                'nullable',
                'integer',
                Rule::exists('users', 'id')
                    ->where(fn ($q) => $q->where('institution_id', $institutionId)
                        ->where('branch_id', $branchId)
                        ->where('role', User::ROLE_TEACHER)),
            ],
        ];
    }

    protected function messages(): array
    {
        return [
            'class_id.unique'     => 'This class and section combination has already been assigned for the current session.',
            'class_id.exists'     => 'Selected class is invalid.',
            'section_id.required' => 'This class has sections. Please select a section.',
            'section_id.exists'   => 'Selected section is invalid.',
            'subject_array.required' => 'Please select at least one subject.',
            'subject_array.min'      => 'Please select at least one subject.',
            'subject_array.*.required' => 'Selected subject is invalid.',
            'subject_array.*.exists'   => 'Selected subject is invalid.',
            'teacher_array.*.exists'   => 'Selected teacher is invalid.',
        ];
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatedPerPage($value): void
    {
        if (! in_array((int) $value, self::PER_PAGE_OPTIONS, true)) {
            $this->perPage = 10;
        }

        $this->resetPage();
    }

    public function updatedClassId(string $value): void
    {
        $this->section_id = null;
        $this->availableSections = [];
        $this->selectedClassHasSection = true;

        if ($value) {
            $class = AcademicClass::with('sections')
                ->where('institution_id', institution()->id)
                ->find($value);

            if ($class) {
                $this->selectedClassHasSection = (bool) $class->has_section;

                if ($this->selectedClassHasSection) {
                    $this->availableSections = $class->sections
                        ->map(fn ($s) => ['id' => $s->id, 'name' => $s->name])
                        ->toArray();
                }
            }
        }
    }

    public function updatedSubjectArray(): void
    {
        $this->teacher_array = collect($this->teacher_array)
            ->only($this->subject_array)
            ->toArray();
    }

    public function sortBy(string $field): void
    {
        if (! in_array($field, self::SORTABLE_FIELDS, true)) {
            return;
        }

        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
    }

    public function openCreate(): void
    {
        abort_unless((bool) $this->currentSessionId, 422, 'No active academic session found. Please set a current session first.');

        $this->resetForm();
        $this->editId = null;
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $institutionId = institution()->id;
        $branchId      = $this->activeBranchId();

        $record = AcademicClassAssign::with('details')
            ->where('institution_id', $institutionId)
            ->where('branch_id', $branchId)
            ->where('session_id', $this->currentSessionId)
            ->findOrFail($id);

        $this->editId     = $id;
        $this->class_id   = (string) $record->class_id;
        $this->section_id = $record->section_id;

        $this->subject_array = $record->details->pluck('subject_id')->toArray();

        $this->teacher_array = $record->details
            ->mapWithKeys(fn ($d) => [$d->subject_id => $d->teacher_id])
            ->toArray();

        $class = AcademicClass::with('sections')
            ->where('institution_id', $institutionId)
            ->find($record->class_id);

        $this->selectedClassHasSection = $class ? (bool) $class->has_section : true;

        $this->availableSections = ($class && $this->selectedClassHasSection)
            ? $class->sections->map(fn ($s) => ['id' => $s->id, 'name' => $s->name])->toArray()
            : [];

        $this->showModal = true;
    }

    public function save(): void
    {
        abort_unless((bool) $this->currentSessionId, 422, 'No active academic session found. Please set a current session first.');

        $this->validate();

        $institutionId = institution()->id;
        $branchId      = $this->activeBranchId();
        $sessionId     = $this->currentSessionId;

        $sectionId = $this->selectedClassHasSection ? ($this->section_id ?: null) : null;

        try {
            DB::transaction(function () use ($institutionId, $branchId, $sessionId, $sectionId) {
                $data = [
                    'institution_id' => $institutionId,
                    'branch_id'      => $branchId,
                    'session_id'     => $sessionId,
                    'class_id'       => $this->class_id,
                    'section_id'     => $sectionId,
                ];

                if ($this->editId) {
                    $assign = AcademicClassAssign::where('institution_id', $institutionId)
                        ->where('branch_id', $branchId)
                        ->where('session_id', $sessionId)
                        ->findOrFail($this->editId);
                    $assign->update($data);
                } else {
                    $assign = AcademicClassAssign::create($data);
                }

                $assign->details()->delete();

                foreach ($this->subject_array as $subjectId) {
                    AcademicClassAssignDetail::create([
                        'institution_id'            => $institutionId,
                        'branch_id'                 => $branchId,
                        'academic_class_assign_id'  => $assign->id,
                        'subject_id'                => $subjectId,
                        'teacher_id'                => $this->teacher_array[$subjectId] ?? null,
                    ]);
                }

                if (function_exists('activity')) {
                    activity()
                        ->causedBy(auth()->user())
                        ->performedOn($assign)
                        ->tap(fn ($a) => $a->institution_id = $institutionId)
                        ->withProperties([
                            'icon'       => 'assignment_ind',
                            'type'       => $this->editId ? 'class_assign_updated' : 'class_assign_created',
                            'class_id'   => $assign->class_id,
                            'section_id' => $assign->section_id,
                            'session_id' => $assign->session_id,
                        ])
                        ->log($this->editId ? 'Updated class assignment' : 'Created class assignment');
                }
            });
        } catch (QueryException $e) {
            if ((int) $e->getCode() === 23000) {
                $this->dispatch(
                    'toast',
                    type: 'error',
                    message: 'This class and section combination was just assigned by someone else. Please refresh and try again.'
                );

                return;
            }

            throw $e;
        }

        $this->dispatch(
            'toast',
            type: 'success',
            message: $this->editId ? 'Assignment updated successfully!' : 'Class assigned successfully!'
        );

        $this->showModal = false;
        $this->resetForm();
    }

    public function confirmDeleteRecord(int $id): void
    {
        $this->deleteId      = $id;
        $this->confirmDelete = true;
    }

    public function deleteRecord(): void
    {
        $institutionId = institution()->id;
        $branchId      = $this->activeBranchId();

        $assign = AcademicClassAssign::where('institution_id', $institutionId)
            ->where('branch_id', $branchId)
            ->where('session_id', $this->currentSessionId)
            ->findOrFail($this->deleteId);

        DB::transaction(function () use ($assign, $institutionId) {
            $assign->details()->delete();

            $className   = $assign->academicClass?->name;
            $sectionName = $assign->academicSection?->name;

            $assign->delete();

            if (function_exists('activity')) {
                activity()
                    ->causedBy(auth()->user())
                    ->tap(fn ($a) => $a->institution_id = $institutionId)
                    ->withProperties([
                        'icon'         => 'assignment_late',
                        'type'         => 'class_assign_deleted',
                        'class_name'   => $className,
                        'section_name' => $sectionName,
                    ])
                    ->log('Deleted class assignment');
            }
        });

        $this->confirmDelete = false;
        $this->deleteId      = null;
        $this->dispatch('toast', type: 'success', message: 'Assignment deleted successfully!');
    }

    private function resetForm(): void
    {
        $this->reset(['class_id', 'section_id', 'subject_array', 'teacher_array', 'editId', 'availableSections']);
        $this->selectedClassHasSection = true;
        $this->resetValidation();
    }

    public function render()
    {
        $institutionId = institution()->id;
        $branchId      = $this->activeBranchId();

        $sortColumnMap = [
            'id'           => 'academic_class_assigns.id',
            'class_name'   => 'ac.name',
            'section_name' => 'asec.name',
        ];
        $sortColumn = $sortColumnMap[$this->sortField] ?? 'ac.name';

        $assigns = AcademicClassAssign::query()
            ->select('academic_class_assigns.*')
            ->leftJoin('academic_classes as ac', 'ac.id', '=', 'academic_class_assigns.class_id')
            ->leftJoin('academic_sections as asec', 'asec.id', '=', 'academic_class_assigns.section_id')
            ->with(['academicClass', 'academicSection', 'session', 'details.subject', 'details.teacher'])
            ->where('academic_class_assigns.institution_id', $institutionId)
            ->where('academic_class_assigns.branch_id', $branchId)
            ->where('academic_class_assigns.session_id', $this->currentSessionId)
            ->when($this->search, fn ($q) => $q->where(function ($q) {
                $q->where('ac.name', 'like', "%{$this->search}%")
                  ->orWhere('asec.name', 'like', "%{$this->search}%");
            }))
            ->orderBy($sortColumn, $this->sortDirection)
            ->paginate($this->perPage);

        $classes  = AcademicClass::where('institution_id', $institutionId)->orderBy('id')->get();
        $subjects = AcademicSubject::where('institution_id', $institutionId)->orderBy('name')->pluck('name', 'id');

        $teachers = User::where('role', User::ROLE_TEACHER)
            ->where('institution_id', $institutionId)
            ->where('branch_id', $branchId)
            ->orderBy('name')
            ->pluck('name', 'id');

        return view('livewire.admin.academic.class-assign-component')
            ->with('assigns', $assigns)
            ->with('classes', $classes)
            ->with('subjects', $subjects)
            ->with('teachers', $teachers)
            ->layout('layouts.branch.app', [
                'title' => 'Class Assignments | ' . institution()->name,
            ]);
    }
}