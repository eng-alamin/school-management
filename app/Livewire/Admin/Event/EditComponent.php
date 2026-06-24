<?php

namespace App\Livewire\Admin\Event;

use Livewire\Component;
use App\Models\EventType;
use App\Models\Event;
use Livewire\WithFileUploads;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;

class EditComponent extends Component
{
    use WithFileUploads;

    public $title = '';
    public $is_holiday = false;
    public $type = '';
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

    public function mount(int $id): void
    {
        $event = Event::with(['eventClasses', 'eventSections'])->findOrFail($id);

        $this->event_id      = $event->id;

        $this->title         = $event->title;
        $this->is_holiday    = $event->is_holiday;
        $this->type          = $event->type;
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
            'class_id'    => $s->class_id,
            'class_name'  => $s->class_name,
            'section_id'  => $s->section_id,
            'section_name'=> $s->section_name,
        ])->toArray();
    }

    protected function failedValidation($validator)
    {
        $this->dispatch('validation-failed');
    }

    public function rules()
    {
        return [
            'title'            => 'required|string|max:255',
            'is_holiday'       => 'boolean',
            'type'             => 'required|string|max:100',
            'audience'         => ['required', Rule::in(['everyone', 'class', 'section'])],
            'date_from'        => 'required|date',
            'date_to'          => 'nullable|date|after_or_equal:date_from',
            'description'      => 'nullable|string',
            'show_website'     => 'boolean',
            'image_upload'     => 'nullable|image|max:2048',

            'selectedClasses'               => 'required_if:audience,class|array',
            'selectedClasses.*.class_id'    => 'required|exists:academic_classes,id',
            'selectedClasses.*.class_name'  => 'required|string',

            'selectedSections'                  => 'required_if:audience,section|array',
            'selectedSections.*.class_id'       => 'required|exists:academic_classes,id',
            'selectedSections.*.class_name'     => 'required|string',
            'selectedSections.*.section_id'     => 'required|exists:academic_sections,id',
            'selectedSections.*.section_name'   => 'required|string',
        ];
    }

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName, $this->rules());
    }

    public function update()
    {
        try {
            $this->validate($this->rules());

            $event = Event::findOrFail($this->event_id);

            // 🖼️ Image replace logic
            if ($this->image_upload) {
                // পুরানো image delete
                if ($event->image && \Storage::disk('public')->exists($event->image)) {
                    Storage::disk('public')->delete($event->image);
                }

                $imagePath = $this->image_upload->store('events', 'public');
            } else {
                $imagePath = $event->image;
            }

            // 🧾 Update main event
            $event->update([
                'title'        => $this->title,
                'is_holiday'   => $this->is_holiday,
                'type'         => $this->type,
                'audience'     => $this->audience,
                'date_from'    => $this->date_from,
                'date_to'      => $this->date_to,
                'description'  => $this->description,
                'show_website' => $this->show_website,
                'image'        => $imagePath,
            ]);

            // 🧹 Old relations delete
            $event->eventClasses()->delete();
            $event->eventSections()->delete();

            // 🎯 Selected Class
            if ($this->audience === 'class') {
                foreach ($this->selectedClasses as $class) {
                    $event->eventClasses()->create([
                        'class_id'   => $class['class_id'],
                        'class_name' => $class['class_name'],
                    ]);
                }
            }

            // 🎯 Selected Section
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

            // ── Activity Log ───────────────────────────────────────
            activity()
                ->causedBy(auth()->user())
                ->performedOn($event)
                ->withProperties(['icon' => 'event', 'type' => 'event'])
                ->tap(function ($activity) use ($event) {
                    $activity->school_id = $event->school_id;
                })
                ->log('Event updated: ' . $event->title);

            $this->dispatch('toast', type: 'success', message: 'Event updated successfully!');

        } catch (\Exception $e) {
            $this->dispatch('toast', type: 'error', message: 'An error occurred while updating the event.');
            throw $e;
        }
    }

    public function render()
    {
        $classes = \App\Models\AcademicClass::with('sections')->get();
        $sections = \App\Models\AcademicSection::all();

        return view('livewire.admin.event.edit-component')
            ->with('classes', $classes)
            ->with('sections', $sections)
            ->layout('layouts.admin.app', [
                'title' => 'Edit Event | ' . institution()->name,
            ]);
    }

}
