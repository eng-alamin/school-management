<div class="mat-card" style="padding-top:28px">

    <!-- Floating Header -->
    <div class="mat-card-header header-pink-gradient">
        <h5><span class="material-icons-round" style="font-size:18px;vertical-align:middle;margin-right:6px">how_to_reg</span>Add Schedule</h5>
        <p>Create new class schedule</p>
    </div>

    <div class="form-section" style="padding-top:40px; padding-bottom:20px">
        <div class="section-heading">
            <span class="material-icons-round">school</span> Select Ground
        </div>
        <div class="row g-4">

            {{-- Class --}}
            <div class="col-md-4">
                <div class="input-group input-group-outline">
                    <label class="form-label">Class</label>
                    <select wire:model.live="filterClass" class="form-select no-custom-select">
                        <option value="">Select Class</option>
                        @foreach ($classes as $item)
                            <option value="{{ $item->id }}">{{ $item->name }}</option>
                        @endforeach
                    </select>
                </div>
                @error('filterClass') <span class="text-danger small">{{ $message }}</span> @enderror
            </div>

            {{-- Section --}}
            <div class="col-md-4">
                <div class="input-group input-group-outline">
                    <label class="form-label">Section</label>
                    <select wire:model.live="filterSection" class="form-select no-custom-select"
                        {{ empty($sections) ? 'disabled' : '' }}>
                        <option value="">{{ !$filterClass ? 'Select Class First' : 'Select Section' }}</option>
                        @if(!empty($sections))
                            <option value="all">All Section</option>
                            @foreach ($sections as $item)
                                <option value="{{ $item->id }}">{{ $item->name }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>
                @error('filterSection') <span class="text-danger small">{{ $message }}</span> @enderror
            </div>

            {{-- Day --}}
            <div class="col-md-4">
                <div class="input-group input-group-outline">
                    <label class="form-label">Day</label>
                    <select wire:model="filterDay" class="form-select no-custom-select">
                        <option value="">Select Day</option>
                        <option value="Sunday" selected>Sunday</option>
                        <option value="Monday">Monday</option>
                        <option value="Tuesday">Tuesday</option>
                        <option value="Wednesday">Wednesday</option>
                        <option value="Thursday">Thursday</option>
                        <option value="Friday">Friday</option>
                        <option value="Saturday">Saturday</option>
                    </select>
                </div>
                @error('filterDay') <span class="text-danger small">{{ $message }}</span> @enderror
            </div>

            {{-- Filter Button --}}
            <div class="col-md-12 text-center">
                <button wire:click="filter"
                        wire:loading.attr="disabled"
                        wire:target="filter"
                        class="btn-pink w-100 d-flex justify-content-center align-items-center"
                        type="button">
                    <span wire:loading.remove wire:target="filter">
                        <span class="material-icons-round" style="font-size:16px;vertical-align:middle;margin-right:4px">filter_alt</span> Filter
                    </span>
                    <span wire:loading wire:target="filter">
                        <span class="material-icons-round" style="font-size:16px;animation:spin .7s linear infinite">sync</span> Filtering...
                    </span>
                </button>
            </div>

        </div>
    </div>

    {{-- Schedule Table --}}
    @if($hasSchedule)
    <div class="form-section">
        <div class="section-heading">
            <span class="material-icons-round">schedule</span> Schedule Details
        </div>

        @foreach($data as $index => $item)
        <div class="row g-4 mt-2" wire:key="schedule-row-{{ $index }}">

            {{-- Subject --}}
            <div class="col-md-2">
                <div class="input-group input-group-outline">
                    <label class="form-label">Subject <span class="req">*</span></label>
                    <select wire:model="data.{{ $index }}.subject" class="form-select no-custom-select">
                        <option value="">Select Subject</option>
                        @foreach ($subjects as $s)
                            <option value="{{ $s['name'] }}">{{ $s['name'] }}</option>
                        @endforeach
                    </select>
                </div>
                @error('data.'.$index.'.subject') <span class="text-danger small">{{ $message }}</span> @enderror
            </div>

            {{-- Teacher --}}
            <div class="col-md-2">
                <div class="input-group input-group-outline">
                    <label class="form-label">Teacher <span class="req">*</span></label>
                    <select wire:model="data.{{ $index }}.teacher" class="form-select no-custom-select">
                        <option value="">Select Teacher</option>
                        @foreach ($teachers as $t)
                            <option value="{{ $t['name'] }}">{{ $t['name'] }}</option>
                        @endforeach
                    </select>
                </div>
                @error('data.'.$index.'.teacher') <span class="text-danger small">{{ $message }}</span> @enderror
            </div>

            {{-- Start Time --}}
            <div class="col-md-2">
                <div wire:ignore class="input-group input-group-outline">
                    <label class="form-label">Start Time <span class="req">*</span></label>
                    <input type="time" wire:model="data.{{ $index }}.start_time"
                        value="{{ $item['start_time'] }}" class="form-control"
                        onfocus="focused(this)" onfocusout="defocused(this)">
                </div>
                @error('data.'.$index.'.start_time') <span class="text-danger small">{{ $message }}</span> @enderror
            </div>

            {{-- End Time --}}
            <div class="col-md-2">
                <div wire:ignore class="input-group input-group-outline">
                    <label class="form-label">End Time <span class="req">*</span></label>
                    <input type="time" wire:model="data.{{ $index }}.end_time"
                        value="{{ $item['end_time'] }}" class="form-control"
                        onfocus="focused(this)" onfocusout="defocused(this)">
                </div>
                @error('data.'.$index.'.end_time') <span class="text-danger small">{{ $message }}</span> @enderror
            </div>

            {{-- Class Room --}}
            <div class="col-md-2">
                <div wire:ignore class="input-group input-group-outline">
                    <label class="form-label">Class Room</label>
                    <input type="text" wire:model="data.{{ $index }}.class_room"
                        class="form-control" onfocus="focused(this)" onfocusout="defocused(this)">
                </div>
                @error('data.'.$index.'.class_room') <span class="text-danger small">{{ $message }}</span> @enderror
            </div>

            {{-- Remove --}}
            <div class="col-md-2 d-flex align-items-center">
                @if(count($data) > 1)
                    <button type="button" wire:click="removeRow({{ $index }})" class="btn-outline">
                        <span class="material-icons-round">close</span> <span>Remove</span>
                    </button>
                @endif
            </div>

        </div>
        @endforeach

        <div class="mt-4">
            <button type="button" wire:click="addRow" class="btn-outline">
                <span class="material-icons-round">add</span> <span>Add More</span>
            </button>
        </div>
    </div>

    <div class="form-footer">
        <button class="btn-outline" type="button" wire:click="resetForm">
            <span class="material-icons-round" style="font-size:16px">refresh</span> Reset
        </button>

        <button class="btn-pink" type="button"
                wire:click="save"
                wire:loading.attr="disabled"
                wire:target="save">
            <span wire:loading.remove wire:target="save">
                <span class="material-icons-round">save</span> Save
            </span>
            <span wire:loading wire:target="save">
                <span class="material-icons-round" style="font-size:16px;animation:spin .7s linear infinite">sync</span> Saving...
            </span>
        </button>
    </div>
    @endif

</div>

@push('scripts')
<script>
    document.addEventListener('livewire:initialized', () => {
        Livewire.hook('morph.updated', ({ el }) => {
            setTimeout(() => {
                el.querySelectorAll('.input-group-outline .form-select').forEach(function(select) {
                    if (!select.nextElementSibling || !select.nextElementSibling.classList.contains('custom-select-wrapper')) {
                        buildCustomSelect(select);
                    }
                });

                el.querySelectorAll('.input-group-outline input').forEach(function(input) {
                    var group = input.closest('.input-group');
                    if (!group) return;
                    if (input.value && input.value.trim() !== '') {
                        group.classList.add('is-filled');
                    } else {
                        group.classList.remove('is-filled');
                    }
                    if (input._materialInit) return;
                    input._materialInit = true;
                    input.addEventListener('focus', function() { group.classList.add('is-focused'); });
                    input.addEventListener('blur', function() {
                        group.classList.remove('is-focused');
                        group.classList.toggle('is-filled', !!input.value.trim());
                    });
                    input.addEventListener('input', function() {
                        group.classList.toggle('is-filled', !!input.value.trim());
                    });
                });

                el.querySelectorAll('.input-group-outline input[type="date"]').forEach(function(input) {
                    if (!input._dpInit) { _initDatepickers(); }
                });
            }, 0);
        });
    });
</script>
@endpush