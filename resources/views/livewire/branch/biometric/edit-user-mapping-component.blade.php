{{-- resources/views/livewire/admin/biometric/edit-user-mapping-component.blade.php --}}
<div>

    <div class="card">
        <div class="mat-card-header header-primary-gradient">
            <h5>Edit Mapping</h5>
            <p>{{ $device->device_name }} ({{ $device->device_serial }})</p>
        </div>

        <div class="card-body">
            <a wire:navigate href="{{ route('admin.biometric.mapping.index', ['device_id' => $device->id]) }}"
               class="btn btn-light btn-sm mb-3">
                <span class="material-icons-round" style="font-size:1rem;vertical-align:middle;">arrow_back</span>
                Back to list
            </a>

            <form wire:submit.prevent="save">
                <div class="row g-3">

                    <div class="col-12 col-md-6">
                        <label class="form-label">Device User ID <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('device_user_id') is-invalid @enderror" wire:model.defer="device_user_id"
                               placeholder="e.g. 1001">
                        @error('device_user_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        <small class="text-muted d-block mt-1"
                               data-en="Changing this will remove the old ID from the device and push the new one."
                               data-bn="এটা বদলালে পুরোনো ID device থেকে সরিয়ে নতুনটা push করা হবে।">
                            এটা বদলালে পুরোনো ID device থেকে সরিয়ে নতুনটা push করা হবে।
                        </small>
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label">Card Number</label>
                        <input type="text" inputmode="numeric" class="form-control @error('card_number') is-invalid @enderror" wire:model.defer="card_number"
                            placeholder="Numbers only, e.g. 0089345621">
                        @error('card_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label">Type</label>
                        <input type="text" class="form-control" value="{{ $attendable_type === 'employee' ? 'Employee' : 'Student' }}" disabled>
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label">{{ $attendable_type === 'employee' ? 'Employee' : 'Student' }}</label>
                        <input type="text" class="form-control" value="{{ $personLabel }}" disabled>
                    </div>

                    <div class="col-12">
                        <small class="text-muted d-block"
                               data-en="Type/Person can't be changed here. Delete and re-add if wrong."
                               data-bn="এখানে Type/Person পরিবর্তন করা যাবে না। ভুল হলে ডিলিট করে নতুন করে যোগ করো।">
                            এখানে Type/Person পরিবর্তন করা যাবে না। ভুল হলে ডিলিট করে নতুন করে যোগ করো।
                        </small>
                    </div>

                </div>

                <div class="d-flex justify-content-end gap-2 mt-4">
                    <a wire:navigate href="{{ route('admin.biometric.mapping.index', ['device_id' => $device->id]) }}" class="btn btn-light">Cancel</a>
                    <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="save">
                        <span wire:loading wire:target="save" class="spinner-border spinner-border-sm me-1"></span>
                        Update Mapping
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>