<?php

namespace App\Livewire\Admin\Event;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use App\Models\EventType;
use App\Models\Event;
use App\Models\AcademicClassAssign;
use App\Models\Branch;

class EditComponent extends Component
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
    public $image = null;
    public $image_upload = null;

    public $selectedClasses = [];
    public $selectedSections = [];

    public $event_id;

    public ?int $eventBranchId  = null;
    public ?int $eventSessionId = null;

    public function mount(int $id): void
    {
        $institutionId = institution()->id;
        $branchId      = $this->activeBranchId();

        $event = Event::with(['eventClasses', 'eventSections'])
            ->where('institution_id', $institutionId)
            ->where('branch_id', $branchId)
            ->findOrFail($id);

        $this->event_id       = $event->id;
        $this->eventBranchId  = $event->branch_id;
        $this->eventSessionId = $event->session_id;

        $this->title         = $event->title;
        $this->is_holiday    = $event->is_holiday;
        $this->event_type_id = $event->event_type_id;
        $this->audience      = $event->audience;
        $this->date_from     = $event->date_from;
        $this->date_to       = $event->date_to;
        $this->description   = $event->description;
        $this->show_website  = $event->show_website;
        $this->image         = $event->image;

        $this->selectedClasses  = $event->eventClasses->map(fn($c) => [
            'class_id'   => $c->class_id,
            'class_name' => $c->class_name,
        ])->toArray();

        $this->selectedSections = $event->eventSections->map(fn($s) => [
            'class_id'     => $s->class_id,
            'class_name'   => $s->class_name,
            'section_id'   => $s->section_id,
            'section_name' => $s->section_name,
        ])->toArray();
    }

    private function activeBranchId(): ?int
    {
        return auth()->user()->branch_id
            ?? Branch::resolveMainBranchId(institution()->id);
    }

    protected function failedValidation($validator)
    {
        $this->dispatch('validation-failed');
    }

    public function rules()
    {
        $institutionId = institution()->id;

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
                    ->where('branch_id', $this->eventBranchId),
            ],
            'selectedClasses.*.class_name'  => 'required|string',

            'selectedSections'                  => 'required_if:audience,section|array',
            'selectedSections.*.class_id'       => [
                'required',
                Rule::exists('academic_classes', 'id')
                    ->where('institution_id', $institutionId)
                    ->where('branch_id', $this->eventBranchId),
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

    public function update()
    {
        $this->validate($this->rules());

        $institutionId = institution()->id;

        $newImagePath = null;
        $oldImagePath = null;

        try {
            $event = Event::where('institution_id', $institutionId)
                ->where('branch_id', $this->eventBranchId)
                ->findOrFail($this->event_id);

            if ($this->image_upload) {
                $newImagePath = $this->image_upload->store('events', 'public');
                $oldImagePath = $event->image;
            }

            $imagePath = $newImagePath ?: $event->image;

            DB::transaction(function () use ($event, $imagePath, $institutionId) {
                $event->update([
                    'title'         => $this->title,
                    'is_holiday'    => $this->is_holiday,
                    'event_type_id' => $this->event_type_id,
                    'audience'      => $this->audience,
                    'date_from'     => $this->date_from,
                    'date_to'       => $this->date_to,
                    'description'   => $this->description,
                    'show_website'  => $this->show_website,
                    'image'         => $imagePath,
                ]);

                $event->eventClasses()->delete();
                $event->eventSections()->delete();

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
                    ->log('Event updated: ' . $event->title);
            });

            if ($oldImagePath) {
                Storage::disk('public')->delete($oldImagePath);
            }

            $this->image = $imagePath;

            $this->dispatch('toast', type: 'success', message: 'Event updated successfully!');

        } catch (\Throwable $e) {

            if ($newImagePath) {
                Storage::disk('public')->delete($newImagePath);
            }

            $this->dispatch('toast', type: 'error', message: 'Update failed: ' . $e->getMessage());
            report($e);
        }
    }

    public function render()
    {
        $institutionId = institution()->id;

        $classAssigns = AcademicClassAssign::with(['class', 'section'])
            ->where('institution_id', $institutionId)
            ->where('branch_id', $this->eventBranchId)
            ->where('session_id', $this->eventSessionId)
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

        return view('livewire.admin.event.edit-component')
            ->with('classes', $classes)
            ->with('classesWithSections', $classesWithSections)
            ->with('eventTypes', $eventTypes)
            ->layout('layouts.admin.app', [
                'title' => 'Edit Event | ' . institution()->name,
            ]);
    }
}