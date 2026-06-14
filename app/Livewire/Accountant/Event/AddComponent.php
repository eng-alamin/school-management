<?php

namespace App\Livewire\Accountant\Event;

use Livewire\Component;
use App\Models\EventType;
use App\Models\Event;
use Livewire\WithFileUploads;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;

class AddComponent extends Component
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
    public $image_upload = null;

    public $selectedClasses = [];
    public $selectedSections = []; 

    public function resetForm()
    {
        $this->reset();
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

    public function save()
    {
        try {
            $this->validate($this->rules());

            $imagePath = $this->image_upload
            ? $this->image_upload->store('events', 'public')
            : null;

            $event = Event::create([
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

            // Selected Class
            if ($this->audience === 'class') {
                foreach ($this->selectedClasses as $class) {
                    $event->eventClasses()->create([
                        'class_id'   => $class['class_id'],
                        'class_name' => $class['class_name'],
                    ]);
                }
            }

            // Selected Section
            if ($this->audience === 'section') {
                foreach ($this->selectedSections as $section) {
                    $event->eventSections()->create([
                        'class_id'    => $section['class_id'],
                        'class_name'  => $section['class_name'],
                        'section_id'  => $section['section_id'],
                        'section_name'=> $section['section_name'],
                    ]);
                }
            }

            // ── Activity Log ───────────────────────────────────────
            activity()
                ->causedBy(auth()->user())
                ->performedOn($event)
                ->withProperties(['icon' => 'event', 'type' => 'event'])
                ->log('New event created: ' . $event->title);

            $this->dispatch('toast', type: 'success', message: 'Event created successfully!');
            $this->resetForm();

        } catch (\Exception $e) {
            $this->dispatch('toast', type: 'error', message: 'An error occurred while creating the event.');
            throw $e;
        }
    }

    public function render()
    {
        $classes = \App\Models\AcademicClass::with('sections')->get();
        $sections = \App\Models\AcademicSection::all();

        return view('livewire.accountant.event.add-component')
            ->with('classes', $classes)
            ->with('sections', $sections)
            ->layout('layouts.accountant.app', [
                'title' => 'Create Event | Monarchy School',
            ]);
    }

}
