<?php

namespace App\Livewire\Admin\Academic;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\AcademicClass;
use App\Models\AcademicSection;
use App\Models\AcademicClassSection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class ClassComponent extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    private const SORTABLE_FIELDS = ['id', 'name', 'numeric', 'has_section'];

    // List
    public string $search = '';
    public int $perPage = 10;
    public string $sortField = 'id';
    public string $sortDirection = 'asc';

    // Modal
    public bool $showModal = false;
    public bool $confirmDelete = false;
    public ?int $deleteId = null;
    public ?string $deleteBlockedMessage = null;

    // Form
    public ?int $editId = null;
    public string $name = '';
    public $numeric;
    public bool $hasSection = true;
    public array $sectionIds = [];
    public array $previousSectionIds = []; 

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

    protected function rules(): array
    {
        $institutionId = auth()->user()->institution_id;

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('academic_classes', 'name')
                    ->where(fn ($q) => $q->where('institution_id', $institutionId))
                    ->ignore($this->editId),
            ],
            'numeric' => [
                'nullable',
                'integer',
                'min:0',
            ],
            'hasSection'   => 'boolean',
            'sectionIds'   => 'nullable|array',
            'sectionIds.*' => 'exists:academic_sections,id',
        ];
    }

    protected function messages(): array
    {
        return [
            'name.unique'    => 'This class name already exists in your institution.',
        ];
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatedHasSection(): void
    {
        if (! $this->hasSection) {
            // Backup current selection before clearing
            $this->previousSectionIds = $this->sectionIds;
            $this->sectionIds = [];
        } else {
            // Restore previously selected sections when toggled back on
            $this->sectionIds = $this->previousSectionIds;
        }
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
        $this->resetForm();
        $this->editId = null;
        $this->showModal = true;
        $this->dispatch('showModalChanged', selected: $this->sectionIds);
    }

    public function openEdit(int $id): void
    {
        $record = AcademicClass::where('institution_id', auth()->user()->institution_id)
            ->findOrFail($id);

        $this->editId      = $id;
        $this->name        = $record->name;
        $this->numeric     = $record->numeric;
        $this->hasSection  = (bool) $record->has_section;
        $this->sectionIds  = $record->sections->pluck('id')->toArray();
        $this->previousSectionIds = $this->sectionIds; 
        $this->showModal   = true;
        $this->dispatch('showModalChanged', selected: $this->sectionIds);
    }

    public function save(): void
    {
        $this->validate();

        // A class without sections should never carry section assignments
        $sectionIds = $this->hasSection ? $this->sectionIds : [];

        $data = [
            'name'        => $this->name,
            'numeric'     => $this->numeric,
            'has_section' => $this->hasSection,
        ];

        try {
            DB::transaction(function () use ($data, $sectionIds) {
                $institutionId = auth()->user()->institution_id;

                if ($this->editId) {
                    $record = AcademicClass::where('institution_id', $institutionId)->findOrFail($this->editId);
                    $record->update($data);

                    AcademicClassSection::where('institution_id', $institutionId)
                        ->where('class_id', $record->id)
                        ->delete();

                    activity()
                        ->causedBy(auth()->user())
                        ->performedOn($record)
                        ->withProperties(['icon' => 'menu_book', 'type' => 'academic'])
                        ->tap(function ($activity) use ($record) {
                            $activity->institution_id = $record->institution_id;
                            $activity->branch_id = $record->branch_id;
                        })
                        ->log('Class updated: ' . $record->name);

                    $this->dispatch('toast', type: 'success', message: 'Class updated successfully!');
                } else {
                    $data['institution_id'] = $institutionId;
                    $record = AcademicClass::create($data);

                    activity()
                        ->causedBy(auth()->user())
                        ->performedOn($record)
                        ->withProperties(['icon' => 'menu_book', 'type' => 'academic'])
                        ->tap(function ($activity) use ($record) {
                            $activity->institution_id = $record->institution_id;
                            $activity->branch_id = $record->branch_id;
                        })
                        ->log('Class created: ' . $record->name);

                    $this->dispatch('toast', type: 'success', message: 'Class created successfully!');
                }

                foreach ($sectionIds as $sectionId) {
                    AcademicClassSection::create([
                        'institution_id' => $institutionId,
                        'class_id'       => $record->id,
                        'section_id'     => $sectionId,
                    ]);
                }

            });
        } catch (\RuntimeException $e) {
            $this->dispatch('toast', type: 'error', message: $e->getMessage());
            return;
        }

        $this->showModal = false;
        $this->resetForm();
    }

    public function confirmDeleteRecord(int $id): void
    {
        $this->deleteId             = $id;
        $this->deleteBlockedMessage = null;

        $institutionId = auth()->user()->institution_id;
        $class = AcademicClass::where('institution_id', $institutionId)->find($id);

        if ($class && $this->classHasStudents($institutionId, $class->id)) {
            $this->deleteBlockedMessage = 'This class has students assigned to it and cannot be deleted. Please move or remove those students first.';
        }

        $this->confirmDelete = true;
    }

    public function deleteRecord(): void
    {
        $institutionId = auth()->user()->institution_id;

        $class = AcademicClass::where('institution_id', $institutionId)
            ->findOrFail($this->deleteId);

        if ($this->classHasStudents($institutionId, $class->id)) {
            $this->dispatch('toast', type: 'error', message: 'This class has students assigned and cannot be deleted.');
            $this->confirmDelete = false;
            $this->deleteId = null;
            return;
        }

        DB::transaction(function () use ($institutionId, $class) {
            AcademicClassSection::where('institution_id', $institutionId)
                ->where('class_id', $class->id)
                ->delete();

            $className = $class->name;
            $class->delete();

            activity()
                ->causedBy(auth()->user())
                ->performedOn($class)
                ->withProperties(['icon' => 'menu_book', 'type' => 'academic'])
                ->tap(function ($activity) use ($class) {
                    $activity->institution_id = $class->institution_id;
                    $activity->branch_id = $class->branch_id;
                })
                ->log('Class deleted: ' . $className);
        });

        $this->confirmDelete = false;
        $this->deleteId      = null;
        $this->dispatch('toast', type: 'success', message: 'Class deleted successfully!');
    }

    private function classHasStudents(int $institutionId, int $classId): bool
    {
        if (! Schema::hasTable('students')) {
            return false;
        }

        if (Schema::hasColumn('students', 'academic_class_id')) {
            return DB::table('students')
                ->where('institution_id', $institutionId)
                ->where('academic_class_id', $classId)
                ->exists();
        }

        return false;
    }

    private function sectionHasStudents(int $institutionId, int $classId, array $sectionIds): bool
    {
        if (empty($sectionIds) || ! Schema::hasTable('students')) {
            return false;
        }

        if (Schema::hasColumn('students', 'academic_section_id')) {
            return DB::table('students')
                ->where('institution_id', $institutionId)
                ->where('academic_class_id', $classId)
                ->whereIn('academic_section_id', $sectionIds)
                ->exists();
        }

        return false;
    }

    private function resetForm(): void
    {
        $this->reset(['name', 'numeric', 'sectionIds', 'editId', 'previousSectionIds']);
        $this->hasSection = true;
        $this->deleteBlockedMessage = null;
        $this->resetValidation();
    }

    public function render()
    {
        $classes = AcademicClass::with('sections')
            ->when($this->search, fn($q) => $q->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('numeric', 'like', "%{$this->search}%")
                  ->orWhereHas('sections', fn($q) => $q->where('name', 'like', "%{$this->search}%"));
            }))
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        $sections = AcademicSection::orderBy('name')->get();

        return view('livewire.admin.academic.class-component')
            ->with('classes', $classes)
            ->with('sections', $sections)
            ->layout('layouts.admin.app', [
                'title' => 'Classes | ' . institution()->name,
            ]);
    }
}