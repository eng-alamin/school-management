<?php

namespace App\Livewire\Branch\Event;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use App\Models\EventType;
use App\Models\Event;
use App\Models\AcademicClassAssign;
use App\Models\AcademicSession;
use App\Models\Branch;

class AddComponent extends Component
{
    use WithFileUploads;

    public $title = '';
    public $is_holiday = false;
    public $event_type_id = '';
    public $audience = '';
    public $date_from = '';
    public $date_to = '';
    public $description = '';
    public $show_website = false;
    public $image_upload = null;

    public $selectedClasses = [];
    public $selectedSections = [];

    public ?int $currentSessionId = null;

    public function mount(): void
    {
        $this->date_from = now()->format('Y-m-d');
        $this->date_to   = now()->addDays(7)->format('Y-m-d');

        $this->currentSessionId = $this->resolveCurrentSessionId();
    }

    private function activeBranchId(): ?int
    {
        return auth()->user()->branch_id
            ?? Branch::resolveMainBranchId(institution()->id);
    }

    private function resolveCurrentSessionId(): ?int
    {
        return AcademicSession::query()
            ->where('institution_id', institution()->id)
            ->where('branch_id', $this->activeBranchId())
            ->active() // scopeActive() -> is_current = true
            ->value('id');
    }

    public function resetForm()
    {
        $this->reset([
            'title', 'is_holiday', 'event_type_id', 'audience',
            'description', 'show_website', 'image_upload',
            'selectedClasses', 'selectedSections',
        ]);
        $this->date_from = now()->format('Y-m-d');
        $this->date_to   = now()->addDays(7)->format('Y-m-d');
        $this->resetValidation();
    }

    protected function failedValidation($validator)
    {
        $this->dispatch('validation-failed');
    }

    public function rules()
    {
        $institutionId = institution()->id;
        $branchId      = $this->activeBranchId();

        return [
            'title'            => 'required|string|max:255',
            'is_holiday'       => 'boolean',
            'event_type_id'    => [
                'required',
                Rule::exists('event_types', 'id')
                    ->where('institution_id', $institutionId),
            ],
            'audience'         => ['required', Rule::in(['everyone', 'class', 'section'])],
            'date_from'        => 'required|date',
            'date_to'          => 'nullable|date|after_or_equal:date_from',
            'description'      => 'nullable|string',
            'show_website'     => 'boolean',
            'image_upload'     => 'nullable|image|max:2048',

            'selectedClasses'               => 'required_if:audience,class|array',
            'selectedClasses.*.class_id'    => [
                'required',
                Rule::exists('academic_classes', 'id')
                    ->where('institution_id', $institutionId)
                    ->where('branch_id', $branchId),
            ],
            'selectedClasses.*.class_name'  => 'required|string',

            'selectedSections'                  => 'required_if:audience,section|array',
            'selectedSections.*.class_id'       => [
                'required',
                Rule::exists('academic_classes', 'id')
                    ->where('institution_id', $institutionId)
                    ->where('branch_id', $branchId),
            ],
            'selectedSections.*.class_name'     => 'required|string',
            'selectedSections.*.section_id'     => [
                'required',
                Rule::exists('academic_sections', 'id')
                    ->where('institution_id', $institutionId),
            ],
            'selectedSections.*.section_name'   => 'required|string',
        ];
    }

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName, $this->rules());
    }

    public function save()
    {
        abort_unless((bool) $this->currentSessionId, 422, 'No active academic session found. Please set a current session first.');

        $this->validate($this->rules());

        $institutionId = institution()->id;
        $branchId      = $this->activeBranchId();
        $sessionId     = $this->currentSessionId;

        $imagePath = null;

        try {
            $imagePath = $this->image_upload
                ? $this->image_upload->store('events', 'public')
                : null;

            $event = DB::transaction(function () use ($imagePath, $institutionId, $branchId, $sessionId) {
                $event = Event::create([
                    'institution_id' => $institutionId,
                    'branch_id'      => $branchId,
                    'session_id'     => $sessionId,
                    'title'          => $this->title,
                    'is_holiday'     => $this->is_holiday,
                    'event_type_id'  => $this->event_type_id,
                    'audience'       => $this->audience,
                    'date_from'      => $this->date_from,
                    'date_to'        => $this->date_to,
                    'description'    => $this->description,
                    'show_website'   => $this->show_website,
                    'image'          => $imagePath,
                ]);

                if ($this->audience === 'class') {
                    foreach ($this->selectedClasses as $class) {
                        $event->eventClasses()->create([
                            'class_id'   => $class['class_id'],
                            'class_name' => $class['class_name'],
                        ]);
                    }
                }

                if ($this->audience === 'section') {
                    foreach ($this->selectedSections as $section) {
                        $event->eventSections()->create([
                            'class_id'     => $section['class_id'],
                            'class_name'   => $section['class_name'],
                            'section_id'   => $section['section_id'],
                            'section_name' => $section['section_name'],
                        ]);
                    }
                }

                activity()
                    ->causedBy(auth()->user())
                    ->performedOn($event)
                    ->withProperties(['icon' => 'event', 'type' => 'event'])
                    ->tap(fn ($a) => $a->institution_id = $institutionId)
                    ->log('New event created: ' . $event->title);

                return $event;
            });

            $this->dispatch('toast', type: 'success', message: 'Event created successfully!');
            $this->resetForm();

        } catch (\Throwable $e) {

            if ($imagePath) {
                Storage::disk('public')->delete($imagePath);
            }

            $this->dispatch('toast', type: 'error', message: 'Creation failed: ' . $e->getMessage());
            report($e);
        }
    }

    public function render()
    {
        $institutionId = institution()->id;
        $branchId      = $this->activeBranchId();

        $classAssigns = AcademicClassAssign::with(['class', 'section'])
            ->where('institution_id', $institutionId)
            ->where('branch_id', $branchId)
            ->where('session_id', $this->currentSessionId)
            ->get();

        $classes = $classAssigns->pluck('class')->filter()->unique('id')->values();

        $classesWithSections = $classAssigns
            ->whereNotNull('section_id')
            ->groupBy('class_id')
            ->map(function ($rows) {
                $first = $rows->first();
                return (object) [
                    'id'       => $first->class_id,
                    'name'     => $first->class?->name,
                    'sections' => $rows->pluck('section')->filter()->values(),
                ];
            })
            ->values();

        $eventTypes = EventType::where('institution_id', $institutionId)->get();

        return view('livewire.admin.event.add-component')
            ->with('classes', $classes)
            ->with('classesWithSections', $classesWithSections)
            ->with('eventTypes', $eventTypes)
            ->layout('layouts.branch.app', [
                'title' => 'Create Event | ' . institution()->name,
            ]);
    }
}