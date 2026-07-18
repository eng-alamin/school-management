<?php

namespace App\Http\Controllers\Api\Admin\Event;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventType;
use App\Models\AcademicClassAssign;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Database\QueryException;

class EventController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $institutionId = $request->user()->institution_id;
        $search = trim((string) $request->query('search', ''));
        $perPage = (int) $request->query('per_page', 20);
        if ($perPage <= 0 || $perPage > 100) {
            $perPage = 20;
        }

        $query = Event::with(['eventType', 'eventClasses', 'eventSections'])
            ->where('institution_id', $institutionId);

        if ($search !== '') {
            $query->where('title', 'like', "%{$search}%");
        }

        $events = $query->orderBy('date_from', 'desc')->paginate($perPage);

        return response()->json([
            'message' => 'Events fetched successfully.',
            'data'    => $events->items(),
            'meta'    => [
                'current_page' => $events->currentPage(),
                'last_page'    => $events->lastPage(),
                'total'        => $events->total(),
                'per_page'     => $events->perPage(),
            ],
        ]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $institutionId = $request->user()->institution_id;

        $event = Event::with(['eventType', 'eventClasses', 'eventSections'])
            ->where('institution_id', $institutionId)
            ->findOrFail($id);

        return response()->json([
            'message' => 'Event fetched successfully.',
            'data'    => $event,
        ]);
    }

    /**
     * Add/Edit form-er jonno dropdown data: event types, classes (audience=class
     * dropdown-er jonno), classes-with-sections (audience=section dropdown-er
     * jonno). Sob kichu academic_class_assigns theke asche — tai actual
     * assigned combination-i dekhabe, raw AcademicClass/AcademicSection na.
     */
    public function formData(Request $request): JsonResponse
    {
        $institutionId = $request->user()->institution_id;

        $eventTypes = EventType::where('institution_id', $institutionId)
            ->orderBy('name')
            ->get(['id', 'name']);

        $classAssigns = AcademicClassAssign::with(['class', 'section'])
            ->where('institution_id', $institutionId)
            ->get();

        $classes = $classAssigns
            ->pluck('class')
            ->filter()
            ->unique('id')
            ->values()
            ->map(fn($c) => ['id' => $c->id, 'name' => $c->name]);

        $classesWithSections = $classAssigns
            ->whereNotNull('section_id')
            ->groupBy('class_id')
            ->map(function ($rows) {
                $first = $rows->first();
                return [
                    'id'       => $first->class_id,
                    'name'     => $first->class->name,
                    'sections' => $rows->pluck('section')
                        ->filter()
                        ->unique('id')
                        ->values()
                        ->map(fn($s) => ['id' => $s->id, 'name' => $s->name]),
                ];
            })
            ->values();

        return response()->json([
            'message' => 'Form data fetched successfully.',
            'data'    => [
                'event_types'           => $eventTypes,
                'classes'                => $classes,
                'classes_with_sections'  => $classesWithSections,
            ],
        ]);
    }

    private function rules(Request $request): array
    {
        $institutionId = $request->user()->institution_id;

        return [
            'title'            => 'required|string|max:255',
            'is_holiday'       => 'nullable|boolean',
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
            'show_website'     => 'nullable|boolean',
            'image'            => 'nullable|image|max:2048',

            'selected_classes'                 => 'required_if:audience,class|array',
            'selected_classes.*.class_id'      => 'required|exists:academic_classes,id',
            'selected_classes.*.class_name'    => 'required|string',

            'selected_sections'                    => 'required_if:audience,section|array',
            'selected_sections.*.class_id'         => 'required|exists:academic_classes,id',
            'selected_sections.*.class_name'       => 'required|string',
            'selected_sections.*.section_id'       => 'required|exists:academic_sections,id',
            'selected_sections.*.section_name'     => 'required|string',
        ];
    }

    /**
     * Multipart/form-data-te array field gula JSON string hishebe ashe
     * (e.g. selected_classes = '[{"class_id":1,"class_name":"Six"}]'),
     * tai validate/store korar age eigula decode kore Request-e boshiye
     * newa hocche.
     */
    private function decodeArrayFields(Request $request): void
    {
        foreach (['selected_classes', 'selected_sections'] as $field) {
            $raw = $request->input($field);
            if (is_string($raw)) {
                $decoded = json_decode($raw, true);
                $request->merge([$field => is_array($decoded) ? $decoded : []]);
            }
        }
    }

    public function store(Request $request): JsonResponse
    {
        $institutionId = $request->user()->institution_id;
        $this->decodeArrayFields($request);
        $validated = $request->validate($this->rules($request));

        $imagePath = null;

        try {
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('events', 'public');
            }

            $event = DB::transaction(function () use ($request, $validated, $institutionId, $imagePath) {
                $event = Event::create([
                    'institution_id' => $institutionId,
                    'title'          => $validated['title'],
                    'is_holiday'     => $request->boolean('is_holiday'),
                    'event_type_id'  => $validated['event_type_id'],
                    'audience'       => $validated['audience'],
                    'date_from'      => $validated['date_from'],
                    'date_to'        => $validated['date_to'] ?? null,
                    'description'    => $validated['description'] ?? null,
                    'show_website'   => $request->boolean('show_website'),
                    'image'          => $imagePath,
                ]);

                if ($validated['audience'] === 'class') {
                    foreach ($validated['selected_classes'] as $class) {
                        $event->eventClasses()->create([
                            'class_id'   => $class['class_id'],
                            'class_name' => $class['class_name'],
                        ]);
                    }
                }

                if ($validated['audience'] === 'section') {
                    foreach ($validated['selected_sections'] as $section) {
                        $event->eventSections()->create([
                            'class_id'     => $section['class_id'],
                            'class_name'   => $section['class_name'],
                            'section_id'   => $section['section_id'],
                            'section_name' => $section['section_name'],
                        ]);
                    }
                }

                if (function_exists('activity')) {
                    activity()
                        ->causedBy($request->user())
                        ->performedOn($event)
                        ->withProperties(['icon' => 'event', 'type' => 'event'])
                        ->tap(function ($activity) use ($event) {
                            $activity->institution_id = $event->institution_id;
                        })
                        ->log('New event created: ' . $event->title);
                }

                return $event;
            });

            return response()->json([
                'message' => 'Event created successfully.',
                'data'    => $event->fresh(['eventType', 'eventClasses', 'eventSections']),
            ], 201);
        } catch (\Throwable $e) {
            if ($imagePath) {
                Storage::disk('public')->delete($imagePath);
            }
            throw $e;
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $institutionId = $request->user()->institution_id;

        $event = Event::where('institution_id', $institutionId)->findOrFail($id);

        $this->decodeArrayFields($request);
        $validated = $request->validate($this->rules($request));

        $newImagePath = null;
        $oldImagePath = null;

        try {
            if ($request->hasFile('image')) {
                $newImagePath = $request->file('image')->store('events', 'public');
                $oldImagePath = $event->image;
            }

            $imagePath = $newImagePath ?: $event->image;

            DB::transaction(function () use ($request, $event, $validated, $imagePath) {
                $event->update([
                    'title'         => $validated['title'],
                    'is_holiday'    => $request->boolean('is_holiday'),
                    'event_type_id' => $validated['event_type_id'],
                    'audience'      => $validated['audience'],
                    'date_from'     => $validated['date_from'],
                    'date_to'       => $validated['date_to'] ?? null,
                    'description'   => $validated['description'] ?? null,
                    'show_website'  => $request->boolean('show_website'),
                    'image'         => $imagePath,
                ]);

                $event->eventClasses()->delete();
                $event->eventSections()->delete();

                if ($validated['audience'] === 'class') {
                    foreach ($validated['selected_classes'] as $class) {
                        $event->eventClasses()->create([
                            'class_id'   => $class['class_id'],
                            'class_name' => $class['class_name'],
                        ]);
                    }
                }

                if ($validated['audience'] === 'section') {
                    foreach ($validated['selected_sections'] as $section) {
                        $event->eventSections()->create([
                            'class_id'     => $section['class_id'],
                            'class_name'   => $section['class_name'],
                            'section_id'   => $section['section_id'],
                            'section_name' => $section['section_name'],
                        ]);
                    }
                }

                if (function_exists('activity')) {
                    activity()
                        ->causedBy($request->user())
                        ->performedOn($event)
                        ->withProperties(['icon' => 'event', 'type' => 'event'])
                        ->tap(function ($activity) use ($event) {
                            $activity->institution_id = $event->institution_id;
                        })
                        ->log('Event updated: ' . $event->title);
                }
            });

            // DB commit successful hoyar por-i purono image delete kora — orphan file avoid korte.
            if ($oldImagePath) {
                Storage::disk('public')->delete($oldImagePath);
            }

            return response()->json([
                'message' => 'Event updated successfully.',
                'data'    => $event->fresh(['eventType', 'eventClasses', 'eventSections']),
            ]);
        } catch (\Throwable $e) {
            if ($newImagePath) {
                Storage::disk('public')->delete($newImagePath);
            }
            throw $e;
        }
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $institutionId = $request->user()->institution_id;

        $event = Event::where('institution_id', $institutionId)->findOrFail($id);

        DB::beginTransaction();
        try {
            $imagePath = $event->image;

            if (function_exists('activity')) {
                activity()
                    ->causedBy($request->user())
                    ->performedOn($event)
                    ->withProperties(['icon' => 'event', 'type' => 'event'])
                    ->tap(function ($activity) use ($event) {
                        $activity->institution_id = $event->institution_id;
                    })
                    ->log('Event deleted: ' . $event->title);
            }

            $event->eventClasses()->delete();
            $event->eventSections()->delete();
            $event->delete();

            DB::commit();

            if ($imagePath) {
                Storage::disk('public')->delete($imagePath);
            }

            return response()->json(['message' => 'Event deleted successfully.']);
        } catch (QueryException $e) {
            DB::rollBack();

            if ($e->getCode() === '23000') {
                return response()->json([
                    'message' => 'Ei Event ke delete kora jacche na, karon tar shathe kono related record linked ache.',
                ], 422);
            }

            throw $e;
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}