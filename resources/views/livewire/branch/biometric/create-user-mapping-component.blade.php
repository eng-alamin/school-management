{{-- resources/views/livewire/admin/biometric/create-user-mapping-component.blade.php --}}
<div>

    <div class="card">
        <div class="mat-card-header header-primary-gradient">
            <h5>Add Mapping</h5>
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
                               placeholder="e.g. 1001 (the ID given during device enrollment)"
                               data-en-placeholder="e.g. 1001 (the ID given during device enrollment)"
                               data-bn-placeholder="e.g. 1001 (device-এ enroll করার সময় যেটা দিয়েছো)">
                        @error('device_user_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label">Card Number</label>
                        <input type="text" inputmode="numeric" class="form-control @error('card_number') is-invalid @enderror" wire:model.defer="card_number"
                            placeholder="Numbers only, e.g. 0089345621"
                            data-en-placeholder="Numbers only, e.g. 0089345621"
                            data-bn-placeholder="শুধু সংখ্যা লিখুন, e.g. 0089345621">
                        @error('card_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label">Type <span class="text-danger">*</span></label>
                        <select class="form-select" wire:model.live="attendable_type">
                            <option value="student">Student</option>
                            <option value="employee">Employee</option>
                        </select>
                    </div>

                    <div class="col-12 position-relative">
                        <label class="form-label">{{ $attendable_type === 'employee' ? 'Employee' : 'Student' }} Search <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('selectedPersonId') is-invalid @enderror" wire:model="personSearch"
                               placeholder="Search by name, {{ $attendable_type === 'employee' ? 'Employee ID' : 'Student ID / Roll' }}..."
                               data-en-placeholder="Search by name, {{ $attendable_type === 'employee' ? 'Employee ID' : 'Student ID / Roll' }}..."
                               data-bn-placeholder="নাম, {{ $attendable_type === 'employee' ? 'Employee ID' : 'Student ID / Roll' }} দিয়ে সার্চ করো...">
                        @error('selectedPersonId') <div class="invalid-feedback">{{ $message }}</div> @enderror

                        @if (count($personResults) > 0)
                            <div class="person-search-results">
                                @foreach ($personResults as $person)
                                    <button type="button" wire:click="selectPerson({{ $person['id'] }})">
                                        {{ $person['label'] }}
                                    </button>
                                @endforeach
                            </div>
                        @endif

                        @if ($selectedPersonId)
                            <div class="small text-success mt-1">
                                <span class="material-icons-round" style="font-size:16px;vertical-align:middle;">check_circle</span>
                                <span data-en="Selected" data-bn="সিলেক্ট করা হয়েছে">সিলেক্ট করা হয়েছে</span>
                            </div>
                        @endif
                    </div>

                </div>

                <div class="d-flex justify-content-end gap-2 mt-4">
                    <a wire:navigate href="{{ route('admin.biometric.mapping.index', ['device_id' => $device->id]) }}" class="btn btn-light">Cancel</a>
                    <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="save">
                        <span wire:loading wire:target="save" class="spinner-border spinner-border-sm me-1"></span>
                        Save Mapping
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

@push('styles')
    <style>
        .person-search-results {
            position: absolute;
            z-index: 1060;
            width: 100%;
            max-height: 220px;
            overflow-y: auto;
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,.08);
            margin-top: 2px;
        }
        .person-search-results button {
            width: 100%;
            text-align: left;
            border: none;
            background: none;
            padding: 8px 12px;
            font-size: .82rem;
        }
        .person-search-results button:hover { background: #f8f9fc; }
    </style>
@endpush