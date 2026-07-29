<?php

namespace App\Livewire\Teacher\Event;

use Livewire\Component;
use App\Models\EventType;
use App\Models\Event;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;

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

    public function mount(int $id): void
    {
        $event = Event::with(['eventClasses', 'eventSections'])
            ->where('institution_id', institution()->id)
            ->findOrFail($id);

        $this->event_id      = $event->id;

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
        $institutionId = institution()->id;

        return [
            'title'            => 'required|string|max:255',
            'is_holiday'       => 'boolean',
            'event_type_id'    => [
                'required',
                Rule::exists('event_types', 'id')->where(
                    fn($q) => $q->where('institution_id', $institutionId)
                ),
            ],
            'audience'         => ['required', Rule::in(['everyone', 'class', 'section'])],
            'date_from'        => 'required|date',
            'date_to'          => 'nullable|date|after_or_equal:date_from',
            'description'      => 'nullable|string',
            'show_website'     => 'boolean',
            'image_upload'     => 'nullable|image|max:2048',

            // NOTE (bug fix): plain `exists:academic_classes,id` /
            // `exists:academic_sections,id` do NOT respect the model's
            // institution global scope — Rule::exists talks to the DB
            // directly. Without the `institution_id` constraint, a
            // tampered request could reference another institution's
            // class/section id (cross-tenant data leak). Scoped the same
            // way `event_type_id` already was above.
            'selectedClasses'               => 'required_if:audience,class|array',
            'selectedClasses.*.class_id'    => [
                'required',
                Rule::exists('academic_classes', 'id')->where(
                    fn($q) => $q->where('institution_id', $institutionId)
                ),
            ],
            'selectedClasses.*.class_name'  => 'required|string',

            'selectedSections'                  => 'required_if:audience,section|array',
            'selectedSections.*.class_id'       => [
                'required',
                Rule::exists('academic_classes', 'id')->where(
                    fn($q) => $q->where('institution_id', $institutionId)
                ),
            ],
            'selectedSections.*.class_name'     => 'required|string',
            'selectedSections.*.section_id'     => [
                'required',
                Rule::exists('academic_sections', 'id')->where(
                    fn($q) => $q->where('institution_id', $institutionId)
                ),
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
        $institutionId = institution()->id;

        // NOTE (bug fix): validate() must run OUTSIDE the try/catch below.
        // It used to run inside — so a validation failure (ValidationException)
        // was caught by the generic `catch (\Exception $e)` block, rolled the
        // (not-yet-started-work) transaction back, and showed a useless
        // "an error occurred" toast instead of the actual field errors.
        // Running it here lets Livewire's normal validation-error handling
        // (the $errors bag + failedValidation()) work as expected.
        $this->validate($this->rules());

        DB::beginTransaction();
        try {
            $event = Event::where('institution_id', $institutionId)
                ->findOrFail($this->event_id);

            // 🖼️ Image replace logic
            if ($this->image_upload) {
                // পুরানো image delete
                if ($event->image && Storage::disk('public')->exists($event->image)) {
                    Storage::disk('public')->delete($event->image);
                }

                $imagePath = $this->image_upload->store('events', 'public');
            } else {
                $imagePath = $event->image;
            }

            // 🧾 Update main event
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
                    $activity->institution_id = $event->institution_id;
                })
                ->log('Event updated: ' . $event->title);

            DB::commit();

            $this->dispatch('toast', type: 'success', message: 'Event updated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();

            // NOTE (bug fix): the previous code re-threw $e after dispatching
            // the error toast, which aborted the Livewire response lifecycle
            // and prevented the toast from ever reaching the browser. Log it
            // server-side and let the toast do its job instead.
            Log::error('Event update failed: ' . $e->getMessage(), ['exception' => $e]);
            $this->dispatch('toast', type: 'error', message: 'An error occurred while updating the event.');
        }
    }

    public function render()
    {
        $classes = \App\Models\AcademicClass::with('sections')->get();
        $sections = \App\Models\AcademicSection::all();
        $eventTypes = EventType::where('institution_id', institution()->id)->get();

        return view('livewire.teacher.event.edit-component')
            ->with('classes', $classes)
            ->with('sections', $sections)
            ->with('eventTypes', $eventTypes)
            ->layout('layouts.teacher.app', [
                'title' => 'Edit Event | ' . institution()->name,
            ]);
    }

}