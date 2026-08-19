<?php

namespace App\Livewire\Branch\Academic;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\AcademicClassAssign;
use App\Models\AcademicClassAssignDetail;
use App\Models\AcademicClass;
use App\Models\AcademicSubject;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ClassAssignComponent extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    // ── Sort/Search allowlist (security: raw column injection thekano jonno) ──
    private const SORTABLE_FIELDS = ['id', 'class_name', 'section_name'];

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

    protected function rules(): array
    {
        $institutionId = institution()->id;

        return [
            'class_id' => [
                'required',
                Rule::exists('academic_classes', 'id')
                    ->where(fn ($q) => $q->where('institution_id', $institutionId)),
                Rule::unique('academic_class_assigns', 'class_id')
                    ->where(fn ($query) => $query
                        ->where('institution_id', $institutionId)
                        ->where('section_id', $this->section_id ?: null))
                    ->ignore($this->editId),
            ],

            // Class-e section thakle (selectedClassHasSection = true) section_id required
            'section_id' => [
                Rule::requiredIf($this->selectedClassHasSection),
                'nullable',
                Rule::exists('academic_sections', 'id')
                    ->where(fn ($q) => $q->where('institution_id', $institutionId)),
            ],

            // Comeconpokkhe 1ta subject select kora lagbe
            'subject_array'   => 'required|array|min:1',
            'subject_array.*' => [
                'required',
                'integer',
                Rule::exists('academic_subjects', 'id')
                    ->where(fn ($q) => $q->where('institution_id', $institutionId)),
                // Prottek selected subject-er jonno teacher select kora required —
                // Livewire checkbox check korle teacher_array-te key tairi nao hote pare,
                // tai eikhane manually check kora hocche.
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
                        ->where('role', User::ROLE_TEACHER)),
            ],
        ];
    }

    protected function messages(): array
    {
        return [
            'class_id.unique'     => 'This class and section combination has already been assigned.',
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

    // Checkbox theke subject uncheck korle, oi subject er teacher_array entry o clean kore dao
    public function updatedSubjectArray(): void
    {
        $this->teacher_array = collect($this->teacher_array)
            ->only($this->subject_array)
            ->toArray();
    }

    public function sortBy(string $field): void
    {
        // ── Allowlist check: sortable field na hole silently ignore ──
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
        $this->resetForm();
        $this->editId = null;
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $institutionId = institution()->id;

        // Defense-in-depth: institution_id explicitly check kora holo (IDOR protection)
        $record = AcademicClassAssign::with('details')
            ->where('institution_id', $institutionId)
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
        $this->validate();

        $institutionId = institution()->id;

        // Class-e section na thakle, kokhono section_id set kora jabe na (data integrity)
        $sectionId = $this->selectedClassHasSection ? ($this->section_id ?: null) : null;

        DB::transaction(function () use ($institutionId, $sectionId) {
            $data = [
                'institution_id' => $institutionId,
                'class_id'       => $this->class_id,
                'section_id'     => $sectionId,
            ];

            if ($this->editId) {
                $assign = AcademicClassAssign::where('institution_id', $institutionId)
                    ->findOrFail($this->editId);
                $assign->update($data);
            } else {
                $assign = AcademicClassAssign::create($data);
            }

            $assign->details()->delete();

            foreach ($this->subject_array as $subjectId) {
                AcademicClassAssignDetail::create([
                    'institution_id'            => $institutionId,
                    'academic_class_assign_id'  => $assign->id,
                    'subject_id'                => $subjectId,
                    'teacher_id'                => $this->teacher_array[$subjectId] ?? null,
                ]);
            }

            if (function_exists('activity')) {
                activity()
                    ->causedBy(auth()->user())
                    ->performedOn($assign)
                    ->withProperties(['class_id' => $assign->class_id, 'section_id' => $assign->section_id])
                    ->log($this->editId ? 'Updated class assignment' : 'Created class assignment');
            }
        });

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

        // Defense-in-depth: institution_id explicitly check kora holo (IDOR protection)
        $assign = AcademicClassAssign::where('institution_id', $institutionId)
            ->findOrFail($this->deleteId);

        DB::transaction(function () use ($assign) {
            $assign->details()->delete();

            $className   = $assign->academicClass?->name;
            $sectionName = $assign->academicSection?->name;

            $assign->delete();

            if (function_exists('activity')) {
                activity()
                    ->causedBy(auth()->user())
                    ->withProperties(['class_name' => $className, 'section_name' => $sectionName])
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

        // ── Sort field -> actual qualified column mapping ──
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
            ->with(['academicClass', 'academicSection', 'details.subject', 'details.teacher'])
            ->where('academic_class_assigns.institution_id', $institutionId)
            ->when($this->search, fn ($q) => $q->where(function ($q) {
                // ── OR condition always wrapped in closure (institution scope leak thekano) ──
                $q->where('ac.name', 'like', "%{$this->search}%")
                  ->orWhere('asec.name', 'like', "%{$this->search}%");
            }))
            ->orderBy($sortColumn, $this->sortDirection)
            ->paginate($this->perPage);

        $classes  = AcademicClass::where('institution_id', $institutionId)->orderBy('id')->get();
        $subjects = AcademicSubject::where('institution_id', $institutionId)->orderBy('name')->pluck('name', 'id');
        $teachers = User::where('role', User::ROLE_TEACHER)
            ->where('institution_id', $institutionId)
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