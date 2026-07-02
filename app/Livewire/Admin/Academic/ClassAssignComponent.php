<?php

namespace App\Livewire\Admin\Academic;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\AcademicClassAssign;
use App\Models\AcademicClassAssignDetail;
use App\Models\AcademicClass;
use App\Models\AcademicSubject;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ClassAssignComponent extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

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

    protected function rules(): array
    {
        return [
            'class_id'          => 'required',
            'section_id'        => 'nullable',
            'subject_array'     => 'nullable|array',
            'subject_array.*'   => 'required|integer|exists:academic_subjects,id',
            'teacher_array'     => 'nullable|array',
            'teacher_array.*'   => 'nullable|integer|exists:users,id',
        ];
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatedClassId(string $value): void
    {
        $this->section_id;
        $this->availableSections = [];

        if ($value) {
            $class = AcademicClass::with('sections')->find($value);
            if ($class) {
                $this->availableSections = $class->sections
                    ->map(fn($s) => ['id' => $s->id, 'name' => $s->name])
                    ->toArray();
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
        $record = AcademicClassAssign::with('details')->findOrFail($id);

        $this->editId        = $id;
        $this->class_id      = $record->class_id;
        $this->section_id    = $record->section_id;

        $this->subject_array = $record->details->pluck('subject_id')->toArray();

        $this->teacher_array = $record->details
            ->mapWithKeys(fn($d) => [$d->subject_id => $d->teacher_id])
            ->toArray();

        // Load sections via belongsToMany
        $class = AcademicClass::with('sections')->find($record->class_id);
        $this->availableSections = $class
            ? $class->sections->map(fn($s) => ['id' => $s->id, 'name' => $s->name])->toArray()
            : [];

        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        DB::transaction(function () {
            $data = [
                'institution_id' => institution()->id,
                'class_id'       => $this->class_id,
                'section_id'     => $this->section_id ?: null,
            ];

            if ($this->editId) {
                $assign = AcademicClassAssign::findOrFail($this->editId);
                $assign->update($data);
            } else {
                $assign = AcademicClassAssign::create($data);
            }
            // purano details mucche notun kore boshao (simple & safe approach)
            $assign->details()->delete();

            foreach ($this->subject_array as $subjectId) {
                AcademicClassAssignDetail::create([
                    'academic_class_assign_id' => $assign->id,
                    'subject_id'                => $subjectId,
                    'teacher_id'                => $this->teacher_array[$subjectId] ?? null,
                ]);
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
        AcademicClassAssign::findOrFail($this->deleteId)->delete();
        $this->confirmDelete = false;
        $this->deleteId      = null;
        $this->dispatch('toast', type: 'success', message: 'Assignment deleted successfully!');
    }

    private function resetForm(): void
    {
        $this->reset(['class_id', 'section_id', 'subject_array', 'teacher_array', 'editId', 'availableSections']);
        $this->resetValidation();
    }

    public function render()
    {
        $assigns = AcademicClassAssign::with(['class', 'section', 'details.subject', 'details.teacher'])
            ->when($this->search, fn($q) => $q
                ->whereHas('class', fn($q) => $q->where('name', 'like', "%{$this->search}%"))
                ->orWhereHas('section', fn($q) => $q->where('name', 'like', "%{$this->search}%")))
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        $classes  = AcademicClass::orderBy('id')->get();
        $subjects = AcademicSubject::orderBy('name')->pluck('name', 'id');
        $teachers = User::where('role', User::ROLE_TEACHER)
            ->where('institution_id', institution()->id)
            ->orderBy('name')
            ->pluck('name', 'id');

        return view('livewire.admin.academic.class-assign-component')
            ->with('assigns', $assigns)
            ->with('classes', $classes)
            ->with('subjects', $subjects)
            ->with('teachers', $teachers)
            ->layout('layouts.admin.app', [
                'title' => 'Class Assignments | ' . institution()->name,
            ]);
    }
}