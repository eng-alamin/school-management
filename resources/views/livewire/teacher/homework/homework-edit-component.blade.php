<div class="mat-card" style="padding-top:28px">

    <!-- Floating Header -->
    <div class="mat-card-header header-pink-gradient">
        <h5>
            <span class="material-icons-round" style="font-size:18px;vertical-align:middle;margin-right:6px">
                assignment
            </span>
            Edit Homework
        </h5>
        <p>Update existing homework record</p>
    </div>

    <!-- ══ HOMEWORK DETAILS ══ -->
    <div class="form-section">

        <div class="row g-4">

            <!-- Class -->
            <div class="col-md-4">
                <div class="input-group input-group-outline" wire:ignore>
                    <label class="form-label">Class <span class="req">*</span></label>

                    <select wire:model="class_id" class="form-select">
                        <option value="">Select</option>

                        @foreach ($classes as $class)
                            <option value="{{ $class->id }}">
                                {{ $class->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                @error('class_id')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>

            <!-- Section -->
            <div class="col-md-4">
                <div class="input-group input-group-outline" wire:ignore>
                    <label class="form-label">Section</label>

                    <select wire:model="section_id" class="form-select">
                        <option value="">Select</option>

                        @foreach ($sections as $section)
                            <option value="{{ $section->id }}">
                                {{ $section->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                @error('section_id')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>

            <!-- Subject -->
            <div class="col-md-4">
                <div class="input-group input-group-outline" wire:ignore>
                    <label class="form-label">Subject <span class="req">*</span></label>

                    <select wire:model="subject_id" class="form-select">
                        <option value="">Select</option>

                        @foreach ($subjects as $subject)
                            <option value="{{ $subject->id }}">
                                {{ $subject->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                @error('subject_id')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>

            <!-- Teacher -->
            {{-- <div class="col-md-6">
                <div class="input-group input-group-outline" wire:ignore>
                    <label class="form-label">Teacher</label>

                    <select wire:model="teacher_id" class="form-select">
                        <option value="">Select</option>

                        @foreach ($teachers as $teacher)
                            <option value="{{ $teacher->id }}">
                                {{ $teacher->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                @error('teacher_id')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div> --}}

            <!-- Homework Date -->
            <div class="col-md-6">
                <div class="input-group input-group-outline" wire:ignore>
                    <label class="form-label">Homework Date</label>

                    <input type="date"
                           wire:model.live="homework_date"
                           id="homework_date"
                           class="form-control">
                </div>

                @error('homework_date')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>

            <!-- Submission Date -->
            <div class="col-md-6">
                <div class="input-group input-group-outline" wire:ignore>
                    <label class="form-label">Submission Date</label>

                    <input type="date"
                           wire:model.live="submission_date"
                           id="submission_date"
                           class="form-control">
                </div>

                @error('submission_date')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>


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

                @error('title')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>

            <!-- Description -->
            <div class="col-12">
                <div class="input-group input-group-outline">
                    <label class="form-label">Description <span class="req">*</span></label>

                    <textarea wire:model="description"
                              class="form-control"
                              style="min-height:120px"
                              placeholder=" "
                              onfocus="focused(this)"
                              onfocusout="defocused(this)"></textarea>
                </div>

                @error('description')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>

            <!-- Attachment -->
            <div class="col-12">
                <label style="font-size:.73rem;font-weight:600;color:var(--muted);display:block;margin-bottom:8px">
                    Attachment File
                </label>

                <div class="photo-upload-box">
                    <span class="material-icons-round">attach_file</span>

                    <span class="lbl">
                        Click to upload attachment
                    </span>

                    <small style="color:#bbb;font-size:.7rem">
                        PDF, DOC, JPG, PNG up to 5MB
                    </small>

                    <input type="file" wire:model="attachment">
                </div>

                @error('attachment')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>

            <!-- Publish Later -->
            <div class="col-md-12">

                <div class="form-check mt-2">
                    <input wire:model.live="published_later"
                           class="form-check-input"
                           type="checkbox"
                           id="publishedLater">

                    <label class="form-check-label" for="publishedLater">
                        Publish Later
                    </label>
                </div>

            </div>

            <!-- Schedule Date -->
            @if($published_later)

                <div class="col-md-6">
                    <div class="input-group input-group-outline" wire:ignore>
                        <label class="form-label">Schedule Date</label>

                        <input type="datetime-local"
                               wire:model="schedule_date"
                               class="form-control">
                    </div>

                    @error('schedule_date')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

            @endif


            <!-- Send SMS -->
            <div class="col-md-12">

                <div class="form-check mt-2">
                    <input wire:model="send_sms"
                           class="form-check-input"
                           type="checkbox"
                           id="sendSms">

                    <label class="form-check-label" for="sendSms">
                        Send Notification SMS
                    </label>
                </div>

            </div>

            <!-- Status -->
            <div class="col-md-6">
                <div class="input-group input-group-outline" wire:ignore>
                    <label class="form-label">Status</label>

                    <select wire:model="status" class="form-select">
                        <option value="published">Published</option>
                        <option value="draft">Draft</option>
                        <option value="closed">Closed</option>
                    </select>
                </div>

                @error('status')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>

        </div>
    </div>

    <!-- FORM FOOTER -->
    <div class="form-footer">

        <button class="btn-outline"
                type="button"
                wire:click="resetForm">

            <span class="material-icons-round"
                  style="font-size:16px">
                refresh
            </span>

            Reset
        </button>

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


@push('scripts')
    <script src="/assets/js/datepicker.js"></script>

    <script>
        document.addEventListener('livewire:initialized', () => {

            // ✅ Initial load এ সব ঠিক করো
            setTimeout(() => initAllFields(), 100);

            // ✅ Livewire update এর পর
            Livewire.hook('morph.updated', ({ el }) => {
                setTimeout(() => initAllFields(), 50);
            });

            function initAllFields() {

                // ── 1. Text/Textarea is-filled ──
                document.querySelectorAll('.input-group-outline input, .input-group-outline textarea').forEach(function(input) {
                    var group = input.closest('.input-group');
                    if (!group) return;

                    // value থাকলে is-filled দাও
                    if (input.value && input.value.trim() !== '') {
                        group.classList.add('is-filled');
                    } else {
                        group.classList.remove('is-filled');
                    }

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

                // ── 2. Select is-filled + value set ──
                document.querySelectorAll('.input-group-outline select').forEach(function(select) {
                    var group = select.closest('.input-group');
                    if (!group) return;

                    // selected value থাকলে is-filled দাও
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
                    select.addEventListener('focus', function() {
                        group.classList.add('is-focused');
                    });
                    select.addEventListener('blur', function() {
                        group.classList.remove('is-focused');
                    });
                });

                // ── 3. Custom Select rebuild ──
                document.querySelectorAll('.input-group-outline .form-select').forEach(function(select) {
                    // পুরনো custom wrapper থাকলে remove করো
                    var old = select.parentNode.querySelector('.custom-select-wrapper');
                    if (old) old.remove();
                    select.style.display = '';

                    if (typeof buildCustomSelect === 'function') {
                        buildCustomSelect(select);
                    }
                });

                // ── 4. Datepicker ──
                document.querySelectorAll('.input-group-outline input[type="date"]').forEach(function(input) {

                    // already initialized হলে skip
                    if (input.dataset.dpInit === '1') return;

                    input.dataset.dpInit = '1';

                    // Livewire sync fix
                    input.addEventListener('change', function () {

                        input.dispatchEvent(
                            new Event('input', { bubbles: true })
                        );

                    });

                    // custom datepicker init
                    if (typeof buildDatepicker === 'function') {
                        buildDatepicker(input);
                    }
                });
            }

        });
    </script>
@endpush