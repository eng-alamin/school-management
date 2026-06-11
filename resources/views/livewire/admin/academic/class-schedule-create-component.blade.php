    <div class="mat-card" style="padding-top:28px">

        <!-- Floating Header -->
        <div class="mat-card-header header-pink-gradient">
            <h5><span class="material-icons-round" style="font-size:18px;vertical-align:middle;margin-right:6px">how_to_reg</span>Add Schedule</h5>
            <p>Create new student admission record</p>
        </div>

        <div class="form-section" style="padding-top:40px; padding-bottom:20px">
            <div class="section-heading">
                <span class="material-icons-round">school</span> Select Ground
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="input-group input-group-outline">
                        <label class="form-label">Class <span class="req">*</span></label>
                        <select wire:model.live="class_id" class="form-select">
                            <option value="">Select Class</option>
                            @foreach ($classes as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @error('class_id') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <div class="col-md-4">
                    <div class="input-group input-group-outline">
                        <label class="form-label">Section <span class="req">*</span></label>
                        <select wire:model="section_id" class="form-select">
                            <option value="">{{ empty($availableSections) ? 'Select class first' : 'Select Section' }}</option>
                            @foreach ($availableSections as $s)
                                <option value="{{ $s['id'] }}">{{ $s['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    @error('section_id') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <div class="col-md-4">
                    <div wire:ignore class="input-group input-group-outline">
                        <label class="form-label">Day <span class="req">*</span></label>
                        <select wire:model="day" class="form-select">
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
                    @error('day') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <div class="col-md-12 text-center">
                    <button wire:click="filter" class="btn-pink w-100 d-flex justify-content-center align-items-center" type="button" onclick="handleSave(this)">
                        Filter
                    </button>
                </div>
    
            </div>
        </div>

            @if($hasSchedule)
                <div class="form-section">
                    <div class="section-heading">
                        <span class="material-icons-round">schedule</span> Schedule Details
                    </div>
                    @foreach($data as $index => $item)
                    <div class="row g-4 mt-2">
                        <div wire:ignore class="col-md-2">
                            <div class="input-group input-group-outline">
                                <label class="form-label">Subject <span class="req">*</span></label>
                                <select wire:model="data.{{ $index }}.subject" class="form-select">
                                    <option value="">Select Subject</option>
                                    @foreach ($subjects as $s)
                                        <option value="{{ $s->name }}">{{ $s->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @error('data.'.$index.'.subject') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-2">
                            <div wire:ignore class="input-group input-group-outline">
                                <label class="form-label">Teacher <span class="req">*</span></label>
                                <select wire:model="data.{{ $index }}.teacher" class="form-select">
                                    <option value="">Select Teacher</option>
                                    @foreach ($teachers as $t)
                                        <option value="{{ $t->id }}">{{ $t->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @error('data.'.$index.'.teacher') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-2">
                            <div wire:ignore class="input-group input-group-outline">
                                <label class="form-label">Start Time <span class="req">*</span></label>
                                <input type="time" wire:model="data.{{ $index }}.start_time" value="{{ $this->data[$index]['start_time'] }}" class="form-control" placeholder=" " onfocus="focused(this)" onfocusout="defocused(this)">
                            </div>
                            @error('data.'.$index.'.start_time') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-2">
                            <div wire:ignore class="input-group input-group-outline">
                                <label class="form-label">End Time <span class="req">*</span></label>
                                <input type="time" wire:model="data.{{ $index }}.end_time" value="{{ $this->data[$index]['end_time'] }}" class="form-control" placeholder=" " onfocus="focused(this)" onfocusout="defocused(this)">
                            </div>
                            @error('data.'.$index.'.end_time') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-2">
                            <div wire:ignore class="input-group input-group-outline">
                                <label class="form-label">Class Room</label>
                                <input type="text" wire:model="data.{{ $index }}.class_room" class="form-control">
                            </div>
                            @error('data.'.$index.'.class_room') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-2">
                            @if(count($data) > 1)
                                <button type="button" wire:click="removeRow({{ $index }})" class="btn-outline" >
                                    <span class="material-icons-round">close</span> <span id="removeSectionBtn"> Remove</span>
                                </button>
                            @endif
                        </div>

                    </div> 
                    @endforeach

                    <div class="mt-4">
                            <button type="button" wire:click="addRow" class="btn-outline" >
                                <span class="material-icons-round">add</span> <span id="newSectionBtn"> Add More</span>
                            </button>
                        </div>

                </div>

                <div class="form-footer">
                    <button class="btn-outline" type="button" wire:click="resetForm">
                        <span class="material-icons-round" style="font-size:16px">refresh</span> Reset
                    </button>

                    <button class="btn-pink" type="button" wire:click="save" wire:loading.attr="disabled" wire:target="save">
                        <span wire:loading.remove wire:target="save">
                            <span class="material-icons-round">save</span> Save
                        </span>

                        <span wire:loading wire:target="save">
                            <span class="material-icons-round" style="font-size:16px;animation:spin .7s linear infinite">sync</span>
                            Saving...
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

                    // ✅ Select re-init
                    el.querySelectorAll('.input-group-outline .form-select').forEach(function(select) {
                        if (!select.nextElementSibling || !select.nextElementSibling.classList.contains('custom-select-wrapper')) {
                            buildCustomSelect(select);
                        }
                    });

                    // ✅ Text/Time input — is-filled re-apply
                    el.querySelectorAll('.input-group-outline input').forEach(function(input) {
                        var group = input.closest('.input-group');
                        if (!group) return;

                        // value থাকলে is-filled দাও
                        if (input.value && input.value.trim() !== '') {
                            group.classList.add('is-filled');
                        } else {
                            group.classList.remove('is-filled');
                        }

                        // Duplicate listener এড়াতে flag চেক
                        if (input._materialInit) return;
                        input._materialInit = true;

                        input.addEventListener('focus', function() {
                            group.classList.add('is-focused');
                        });
                        input.addEventListener('blur', function() {
                            group.classList.remove('is-focused');
                            group.classList.toggle('is-filled', !!input.value.trim());
                        });
                        input.addEventListener('input', function() {
                            group.classList.toggle('is-filled', !!input.value.trim());
                        });
                    });

                    // ✅ Datepicker re-init
                    el.querySelectorAll('.input-group-outline input[type="date"]').forEach(function(input) {
                        if (!input._dpInit) {
                            _initDatepickers();
                        }
                    });

                }, 0);
            });
        });
    </script>
    @endpush