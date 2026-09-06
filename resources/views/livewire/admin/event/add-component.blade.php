<div>      
    <div class="card">     

        <div class="card-header-floating card-header-gradient">   
            <h5>Add Event</h5>
            <p>Create new event record</p>
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
                        <select wire:model="event_type_id" class="form-select">
                            <option value="">Select</option>
                            @foreach($eventTypes as $eventType)
                                <option value="{{ $eventType->id }}">{{ $eventType->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @error('event_type_id') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <!-- Audience -->
                <div class="col-md-6">
                    <div class="input-group input-group-outline" wire:ignore>
                        <label class="form-label">Audience <span class="req">*</span></label>
                        <select wire:model.live="audience" class="form-select">
                            <option value="">Select</option>
                            <option value="everyone">Everybody</option>
                            <option value="class">Selected Class</option>
                            <option value="section">Selected Section</option>
                        </select>
                    </div>
                    @error('audience') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <!-- Selected Class (audience = class) -->
                @if($audience === 'class')
                    <div class="col-md-12">
                        <div class="input-group input-group-outline" wire:ignore>
                            <label class="form-label">Class <span class="req">*</span></label>
                            <select class="form-select" id="classMultiSelect" multiple>
                                @foreach($classes as $class)
                                    <option value="{{ $class->id }}" data-name="{{ $class->name }}">
                                        {{ $class->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @error('selectedClasses') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                @endif

                <!--
                    BUG FIX: previously iterated $classes->sections (a relation
                    that wasn't institution-scoped and didn't reflect actual
                    assigned class-section combinations). Now uses
                    $classesWithSections, built in AddComponent::render() from
                    academic_class_assigns, scoped to this institution.
                -->
                @if($audience === 'section')
                    <div class="col-md-12">
                        <div class="input-group input-group-outline" wire:ignore>
                            <label class="form-label">Section <span class="req">*</span></label>
                            <select class="form-select" id="sectionMultiSelect" multiple>
                                @foreach($classesWithSections as $class)
                                    <optgroup label="{{ $class->name }}">
                                        @foreach($class->sections as $section)
                                            <option value="{{ $class->id }}_{{ $section->id }}"
                                                    data-class-id="{{ $class->id }}"
                                                    data-class-name="{{ $class->name }}"
                                                    data-section-id="{{ $section->id }}"
                                                    data-section-name="{{ $section->name }}">
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
                            data-dp-value="{{ $date_from }}"
                            class="form-control">
                    </div>
                    @error('date_from') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <!-- Date To -->
                <div class="col-md-6">
                    <div class="input-group input-group-outline" wire:ignore>
                        <label class="form-label">Date To</label>
                        <input type="date"
                            wire:model="date_to"
                            data-dp-value="{{ $date_to }}"
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

                <!-- Image -->
                <div class="col-md-12">
                    <label style="font-size:.73rem;font-weight:600;color:var(--muted);display:block;margin-bottom:8px">
                        Event Image
                    </label>
                    <div class="photo-upload-box">
                        @if($image_upload)
                            <img src="{{ $image_upload->temporaryUrl() }}" 
                                style="max-height:80px;max-width:100%;object-fit:contain;margin-bottom:6px">
                        @else
                            <span class="material-icons-round">image</span>
                            <span class="lbl">Click to upload</span>
                        @endif
                        <small style="color:#bbb;font-size:.7rem">PNG, JPG up to 2MB</small>
                        <input type="file" wire:model="image_upload" accept="image/*">
                    </div>
                    @error('image_upload') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

            </div>
        </div>

        <!-- FORM FOOTER -->
        <div class="form-footer">

            <button class="btn btn-secondary" type="button" wire:click="resetForm" wire:loading.attr="disabled" wire:target="resetForm,reset">
                <span wire:loading.remove wire:target="resetForm,reset">
                    <span class="material-icons-round">refresh</span>
                    <span>Reset</span>
                </span>

                <span wire:loading wire:target="resetForm,reset">
                    <span class="material-icons-round" style="animation:spin .7s linear infinite">sync</span>
                    <span>Resetting...</span>
                </span>
            </button>

            <button class="btn btn-primary"
                    type="button"
                    wire:click="save"
                    wire:loading.attr="disabled"
                    wire:target="save">

                <span wire:loading.remove wire:target="save" style="display: inline-flex;align-items: center;gap: 6px">
                    <span class="material-icons-round">save</span>
                    <span>Save</span>
                </span>

                <span wire:loading wire:target="save">
                    <span class="material-icons-round"
                        style="font-size:16px;animation:spin .7s linear infinite">
                        sync
                    </span>
                    Saving...
                </span>

            </button>
        </div>

    </div>
</div>


@push('scripts')
    <script>
        document.addEventListener('livewire:initialized', () => {

            setTimeout(() => initMultiSelects(), 50);

            Livewire.hook('morph.updated', ({ el }) => {
                setTimeout(() => initMultiSelects(), 0);
            });

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