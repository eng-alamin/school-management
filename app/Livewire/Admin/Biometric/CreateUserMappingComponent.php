<?php

namespace App\Livewire\Admin\Biometric;

use App\Models\BiometricDevice;
use App\Models\BiometricDeviceUserMapping;
use App\Models\Employee;
use App\Models\Student;
use App\Services\BiometricCommandService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.admin.app')]
class CreateUserMappingComponent extends Component
{
    #[Url(as: 'device_id')]
    public ?int $deviceId = null;

    public ?BiometricDevice $device = null;

    public string $device_user_id = '';
    public string $card_number = '';
    public string $attendable_type = 'student'; // 'student' | 'employee'
    public string $personSearch = '';
    public ?int $selectedPersonId = null;
    public array $personResults = [];

    public function mount(): void
    {
        if (! $this->deviceId) {
            abort(404, 'Device not specified.');
        }

        // Scoped lookup: a foreign/forged device_id 404s instead of silently working.
        $this->device = BiometricDevice::where('institution_id', institution()->id)
            ->findOrFail($this->deviceId);
    }

    protected function attendableClass(): string
    {
        return $this->attendable_type === 'employee' ? Employee::class : Student::class;
    }

    public function updatedPersonSearch(): void
    {
        $this->searchPeople();
    }

    public function updatedAttendableType(): void
    {
        $this->personSearch = '';
        $this->personResults = [];
        $this->selectedPersonId = null;
    }

    public function searchPeople(): void
    {
        if (mb_strlen($this->personSearch) < 2) {
            $this->personResults = [];
            return;
        }

        if ($this->attendable_type === 'employee') {
            $this->personResults = Employee::query()
                ->where('institution_id', institution()->id)
                ->where('status', 'active')
                ->where(function ($q) {
                    $q->where('name', 'like', "%{$this->personSearch}%")
                        ->orWhere('employee_id', 'like', "%{$this->personSearch}%");
                })
                ->with(['designation:id,name'])
                ->limit(10)
                ->get(['id', 'name', 'employee_id', 'designation_id'])
                ->map(fn ($e) => [
                    'id' => $e->id,
                    'label' => $e->name . ' (' . $e->employee_id . ')' . ($e->designation ? ' - ' . $e->designation->name : ''),
                ])
                ->toArray();
        } else {
            $this->personResults = Student::query()
                ->where('institution_id', institution()->id)
                ->where('status', 'active')
                ->where(function ($q) {
                    $q->where('name', 'like', "%{$this->personSearch}%")
                        ->orWhere('student_id', 'like', "%{$this->personSearch}%")
                        ->orWhere('roll_no', 'like', "%{$this->personSearch}%");
                })
                ->with(['academicClass:id,name', 'academicSection:id,name'])
                ->limit(10)
                ->get(['id', 'name', 'student_id', 'roll_no', 'class_id', 'section_id'])
                ->map(fn ($s) => [
                    'id' => $s->id,
                    'label' => $s->name . ' (' . $s->student_id . ')'
                        . ($s->academicClass ? ' - ' . $s->academicClass->name : '')
                        . ($s->academicSection ? ' ' . $s->academicSection->name : ''),
                ])
                ->toArray();
        }
    }

    public function selectPerson(int $id): void
    {
        $this->selectedPersonId = $id;
        $match = collect($this->personResults)->firstWhere('id', $id);
        $this->personSearch = $match['label'] ?? '';
        $this->personResults = [];
    }

    public function save()
    {
        $this->validate([
            'device_user_id' => [
                'required', 'string', 'max:50',
                Rule::unique('biometric_device_user_mappings', 'device_user_id')
                    ->where('biometric_device_id', $this->device->id)
                    ->whereNull('deleted_at'),
            ],
            'card_number' => [
                'nullable', 'digits_between:1,10',
                Rule::unique('biometric_device_user_mappings', 'card_number')
                    ->where('biometric_device_id', $this->device->id)
                    ->whereNotNull('card_number')
                    ->whereNot('card_number', '')
                    ->whereNull('deleted_at'),
            ],
            'attendable_type' => [Rule::in(['student', 'employee'])],
            'selectedPersonId' => ['required', 'integer'],
        ], [
            'selectedPersonId.required' => 'Student/Employee সিলেক্ট করা আবশ্যক।',
            'device_user_id.unique' => 'এই Device User ID ইতিমধ্যে এই Device-এ অন্য কারো সাথে যুক্ত আছে।',
            'card_number.unique' => 'এই Card Number ইতিমধ্যে এই Device-এ অন্য কারো সাথে যুক্ত আছে।',
            'card_number.digits_between' => 'Card Number শুধু সংখ্যা (numeric) হতে হবে, সর্বোচ্চ ১০ ডিজিট।',
        ]);

        $attendableClass = $this->attendableClass();

        // Server-side re-verification of ownership (IDOR prevention) —
        // never trust the client-side selectedPersonId alone.
        $person = $attendableClass::where('institution_id', institution()->id)
            ->findOrFail($this->selectedPersonId);

        DB::beginTransaction();

        try {
            $mapping = BiometricDeviceUserMapping::create([
                'institution_id' => institution()->id,
                'biometric_device_id' => $this->device->id,
                'device_user_id' => $this->device_user_id,
                'card_number' => $this->card_number !== '' ? $this->card_number : null,
                'attendable_type' => $attendableClass,
                'attendable_id' => $person->id,
            ]);

            BiometricCommandService::queueUserUpsert(
                $this->device,
                $this->device_user_id,
                $person->name,
                $this->card_number !== '' ? $this->card_number : null
            );

            activity()
                ->causedBy(auth()->user())
                ->performedOn($mapping)
                ->withProperties(['icon' => 'add', 'type' => 'biometric_mapping_create'])
                ->tap(function ($activity) use ($mapping) {
                    $activity->institution_id = $mapping->institution_id;
                })
                ->log("Device user mapping created: {$this->device->device_name} -> {$person->name}");

            DB::commit();

            session()->flash('toast_success', 'Mapping যোগ হয়েছে, device-এ push হওয়ার জন্য কিউতে রাখা হয়েছে।');

            return redirect()->route('admin.biometric.mapping.index', ['device_id' => $this->device->id]);

        } catch (\Throwable $e) {
            DB::rollBack();
            $this->dispatch('toast', type: 'error', message: 'Something went wrong!');
        }
    }

    public function render()
    {
        return view('livewire.admin.biometric.create-user-mapping-component');
    }
}