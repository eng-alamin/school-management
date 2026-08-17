<?php

namespace App\Livewire\Admin\Biometric;

use App\Models\BiometricDevice;
use App\Models\BiometricDeviceUserMapping;
use App\Services\BiometricCommandService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin.app')]
class EditUserMappingComponent extends Component
{
    public BiometricDeviceUserMapping $mapping;
    public ?BiometricDevice $device = null;

    public string $device_user_id = '';
    public string $card_number = '';
    public string $attendable_type = 'student';
    public string $personLabel = '';

    public function mount(int $id): void
    {
        $this->mapping = BiometricDeviceUserMapping::where('institution_id', institution()->id)
            ->with('attendable')
            ->findOrFail($id);

        // Device is derived from the mapping itself, not from user input.
        $this->device = BiometricDevice::where('institution_id', institution()->id)
            ->findOrFail($this->mapping->biometric_device_id);

        $this->device_user_id = $this->mapping->device_user_id;
        $this->card_number = $this->mapping->card_number ?? '';
        $this->attendable_type = str_contains($this->mapping->attendable_type, 'Employee') ? 'employee' : 'student';

        $idNumber = $this->attendable_type === 'employee'
            ? $this->mapping->attendable?->employee_id
            : $this->mapping->attendable?->student_id;

        $this->personLabel = trim(($this->mapping->attendable?->name ?? '— (deleted)') . ' (' . $idNumber . ')');
    }

    public function save()
    {
        $this->validate([
            'device_user_id' => [
                'required', 'string', 'max:50',
                Rule::unique('biometric_device_user_mappings', 'device_user_id')
                    ->where('biometric_device_id', $this->device->id)
                    ->whereNull('deleted_at')
                    ->ignore($this->mapping->id),
            ],
            'card_number' => [
                'nullable', 'digits_between:1,10',
                Rule::unique('biometric_device_user_mappings', 'card_number')
                    ->where('biometric_device_id', $this->device->id)
                    ->whereNotNull('card_number')
                    ->whereNot('card_number', '')
                    ->whereNull('deleted_at')
                    ->ignore($this->mapping->id),
            ],
        ], [
            'device_user_id.unique' => 'এই Device User ID ইতিমধ্যে এই Device-এ অন্য কারো সাথে যুক্ত আছে।',
            'card_number.unique' => 'এই Card Number ইতিমধ্যে এই Device-এ অন্য কারো সাথে যুক্ত আছে।',
            'card_number.digits_between' => 'Card Number শুধু সংখ্যা (numeric) হতে হবে, সর্বোচ্চ ১০ ডিজিট।',
        ]);

        DB::beginTransaction();

        try {
            // Re-scoped fetch inside the transaction against tampering / stale state.
            $mapping = BiometricDeviceUserMapping::where('institution_id', institution()->id)
                ->where('biometric_device_id', $this->device->id)
                ->findOrFail($this->mapping->id);

            $oldDeviceUserId = $mapping->device_user_id;
            $deviceUserIdChanged = $oldDeviceUserId !== $this->device_user_id;

            $mapping->update([
                'device_user_id' => $this->device_user_id,
                'card_number' => $this->card_number !== '' ? $this->card_number : null,
            ]);

            if ($deviceUserIdChanged) {
                BiometricCommandService::queueUserDelete($this->device, $oldDeviceUserId);
            }

            $person = $mapping->attendable;

            BiometricCommandService::queueUserUpsert(
                $this->device,
                $this->device_user_id,
                $person?->name ?? '',
                $this->card_number !== '' ? $this->card_number : null
            );

            activity()
                ->causedBy(auth()->user())
                ->performedOn($mapping)
                ->withProperties(['icon' => 'edit', 'type' => 'biometric_mapping_update'])
                ->tap(function ($activity) use ($mapping) {
                    $activity->institution_id = $mapping->institution_id;
                })
                ->log("Device user mapping updated: {$this->device->device_name} -> " . ($person?->name ?? ''));

            DB::commit();

            session()->flash('toast_success', 'Mapping আপডেট হয়েছে, device-এ sync হওয়ার জন্য কিউতে রাখা হয়েছে।');

            return redirect()->route('admin.biometric.mapping.index', ['device_id' => $this->device->id]);

        } catch (\Throwable $e) {
            DB::rollBack();
            $this->dispatch('toast', type: 'error', message: 'Something went wrong!');
        }
    }

    public function render()
    {
        return view('livewire.admin.biometric.edit-user-mapping-component');
    }
}