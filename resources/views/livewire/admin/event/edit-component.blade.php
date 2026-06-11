<div>      
    <div class="card">                  
        <div class="card-header-floating card-header-gradient">   
            <h5> Edit Event</h5>
            <p>Update existing event record</p>
        </div>

        <!-- ══ EVENT DETAILS ══ -->
        <div class="form-section">
            <div class="row g-4">

                <!-- Title -->
                <div class="col-md-12">
                    <div class="input-group input-group-outline">
                        <label class="form-label">Title <span class="req">*</span></label>
                        <input type="text"
                            wire:model="title"
                            class="form-control"
                            placeholder=" "
                            onfocus="focused(this)"
                            onfocusout="defocused(this)">
                    </div>
                    @error('title') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <!-- Type -->
                <div class="col-md-6">
                    <div class="input-group input-group-outline" wire:ignore>
                        <label class="form-label">Type <span class="req">*</span></label>
                        <select wire:model="type" class="form-select">
                            <option value="">Select</option>
                            <option value="Independent Day">Independent Day</option>
                            <option value="Sports Day">Sports Day</option>
                            <option value="Cultural Program">Cultural Program</option>
                            <option value="Exam">Exam</option>
                            <option value="Holiday">Holiday</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    @error('type') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <!-- Audience -->
                <div class="col-md-6">
                    <div class="input-group input-group-outline" wire:ignore>
                        <label class="form-label">Audience <span class="req">*</span></label>
                        <select wire:model.live="audience" class="form-select">
                            <option value="">Select</option>
                            <option value="Everybody">Everybody</option>
                            <option value="Selected Class">Selected Class</option>
                            <option value="Selected Section">Selected Section</option>
                        </select>
                    </div>
                    @error('audience') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <!-- Selected Class -->
                @if($audience === 'Selected Class')
                    <div class="col-md-12">
                        <div class="input-group input-group-outline" wire:ignore>
                            <label class="form-label">Class <span class="req">*</span></label>
                            <select class="form-select" id="classMultiSelect" multiple>
                                @foreach($classes as $class)
                                    <option value="{{ $class->id }}"
                                            data-name="{{ $class->name }}"
                                            {{ collect($selectedClasses)->pluck('class_id')->contains($class->id) ? 'selected' : '' }}>
                                        {{ $class->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @error('selectedClasses') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                @endif

                <!-- Selected Section -->
                @if($audience === 'Selected Section')
                    <div class="col-md-12">
                        <div class="input-group input-group-outline" wire:ignore>
                            <label class="form-label">Section <span class="req">*</span></label>
                            <select class="form-select" id="sectionMultiSelect" multiple>
                                @foreach($classes as $class)
                                    <optgroup label="{{ $class->name }}">
                                        @foreach($class->sections as $section)
                                            <option value="{{ $class->id }}_{{ $section->id }}"
                                                    data-class-id="{{ $class->id }}"
                                                    data-class-name="{{ $class->name }}"
                                                    data-section-id="{{ $section->id }}"
                                                    data-section-name="{{ $section->name }}"
                                                    {{ collect($selectedSections)->contains(fn($s) => $s['class_id'] == $class->id && $s['section_id'] == $section->id) ? 'selected' : '' }}>
                                                {{ $section->name }}
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                        </div>
                        @error('selectedSections') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                @endif

                <!-- Date From -->
                <div class="col-md-6">
                    <div class="input-group input-group-outline" wire:ignore>
                        <label class="form-label">Date From <span class="req">*</span></label>
                        <input type="date"
                            wire:model.live="date_from"
                            class="form-control">
                    </div>
                    @error('date_from') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <!-- Date To -->
                <div class="col-md-6">
                    <div class="input-group input-group-outline" wire:ignore>
                        <label class="form-label">Date To</label>
                        <input type="date"
                            wire:model.live="date_to"
                            class="form-control">
                    </div>
                    @error('date_to') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <!-- Description -->
                <div class="col-12">
                    <div class="input-group input-group-outline">
                        <label class="form-label">Description</label>
                        <textarea wire:model="description"
                                class="form-control"
                                style="min-height:120px"
                                placeholder=" "
                                onfocus="focused(this)"
                                onfocusout="defocused(this)"></textarea>
                    </div>
                    @error('description') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <!-- Show on Website -->
                <div class="col-md-12">
                    <div class="form-check mt-1">
                        <input wire:model="show_website"
                            class="form-check-input"
                            type="checkbox"
                            id="showWebsite">
                        <label class="form-check-label" for="showWebsite">Show on Website</label>
                    </div>
                </div>

                <!-- Holiday Checkbox -->
                <div class="col-md-12">
                    <div class="form-check mt-1">
                        <input wire:model="is_holiday"
                            class="form-check-input"
                            type="checkbox"
                            id="isHoliday">
                        <label class="form-check-label" for="isHoliday">Holiday</label>
                    </div>
                </div>

                <!-- Current Image Preview -->
                @if($existingImage)
                    <div class="col-12">
                        <label style="font-size:.73rem;font-weight:600;color:var(--muted);display:block;margin-bottom:8px">
                            Current Image
                        </label>
                        <div style="display:flex;align-items:center;gap:12px;">
                            <img src="{{ Storage::url($existingImage) }}"
                                alt="Event Image"
                                style="width:80px;height:60px;object-fit:cover;border-radius:8px;border:1px solid rgba(0,0,0,.1)">
                            <small class="text-muted">Upload a new image to replace</small>
                        </div>
                    </div>
                @endif

                <!-- New Image Upload -->
                <div class="col-12">
                    <label style="font-size:.73rem;font-weight:600;color:var(--muted);display:block;margin-bottom:8px">
                        {{ $existingImage ? 'Replace Image' : 'Event Image' }}
                    </label>
                    <div class="photo-upload-box">
                        <span class="material-icons-round">image</span>
                        <span class="lbl">Click to upload image</span>
                        <small style="color:#bbb;font-size:.7rem">JPG, PNG up to 2MB</small>
                        <input type="file" wire:model="image" accept="image/*">
                    </div>
                    @error('image') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

            </div>
        </div>

        <!-- FORM FOOTER -->
        <div class="form-footer">

            <button class="btn-pink"
                    type="button"
                    wire:click="update"
                    wire:loading.attr="disabled"
                    wire:target="update">

                <span wire:loading.remove wire:target="update">
                    <span class="material-icons-round">save</span>
                    Update
                </span>

                <span wire:loading wire:target="update">
                    <span class="material-icons-round"
                        style="font-size:16px;animation:spin .7s linear infinite">
                        sync
                    </span>
                    Updating...
                </span>

            </button>
        </div>
    </div>
</div>


@push('scripts')
    <script src="/assets/js/datepicker.js"></script>

    <script>
        document.addEventListener('livewire:initialized', () => {

            setTimeout(() => initAllFields(), 100);

            Livewire.hook('morph.updated', ({ el }) => {
                setTimeout(() => initAllFields(), 50);
                initMultiSelects();
            });

            function initAllFields() {

                // ── 1. Text/Textarea is-filled ──
                document.querySelectorAll('.input-group-outline input, .input-group-outline textarea').forEach(function(input) {
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

                // ── 2. Select is-filled ──
                document.querySelectorAll('.input-group-outline select').forEach(function(select) {
                    var group = select.closest('.input-group');
                    if (!group) return;
                    if (select.value && select.value !== '') {
                        group.classList.add('is-filled');
                    } else {
                        group.classList.remove('is-filled');
                    }
                    if (select._materialInit) return;
                    select._materialInit = true;
                    select.addEventListener('change', function() {
                        group.classList.toggle('is-filled', !!select.value);
                    });
                    select.addEventListener('focus', function() { group.classList.add('is-focused'); });
                    select.addEventListener('blur', function() { group.classList.remove('is-focused'); });
                });

                // ── 3. Custom Select rebuild ──
                document.querySelectorAll('.input-group-outline .form-select').forEach(function(select) {
                    var old = select.parentNode.querySelector('.custom-select-wrapper');
                    if (old) old.remove();
                    select.style.display = '';
                    if (typeof buildCustomSelect === 'function') {
                        buildCustomSelect(select);
                    }
                });

                // ── 4. Datepicker ──
                document.querySelectorAll('.input-group-outline input[type="date"]').forEach(function(input) {
                    if (input.dataset.dpInit === '1') return;
                    input.dataset.dpInit = '1';
                    input.addEventListener('change', function() {
                        input.dispatchEvent(new Event('input', { bubbles: true }));
                    });
                    if (typeof buildDatepicker === 'function') {
                        buildDatepicker(input);
                    }
                });

                initMultiSelects();
            }

            // ── 5. Multi-select → Livewire sync ──
            function initMultiSelects() {

                // Selected Class
                var classSelect = document.getElementById('classMultiSelect');
                if (classSelect && !classSelect._multiInit) {
                    classSelect._multiInit = true;
                    classSelect.addEventListener('change', function() {
                        var selected = Array.from(classSelect.selectedOptions).map(opt => ({
                            class_id: parseInt(opt.value),
                            class_name: opt.dataset.name,
                        }));
                        @this.set('selectedClasses', selected);
                    });
                }

                // Selected Section
                var sectionSelect = document.getElementById('sectionMultiSelect');
                if (sectionSelect && !sectionSelect._multiInit) {
                    sectionSelect._multiInit = true;
                    sectionSelect.addEventListener('change', function() {
                        var selected = Array.from(sectionSelect.selectedOptions).map(opt => ({
                            class_id: parseInt(opt.dataset.classId),
                            class_name: opt.dataset.className,
                            section_id: parseInt(opt.dataset.sectionId),
                            section_name: opt.dataset.sectionName,
                        }));
                        @this.set('selectedSections', selected);
                    });
                }
            }

        });
    </script>
@endpush