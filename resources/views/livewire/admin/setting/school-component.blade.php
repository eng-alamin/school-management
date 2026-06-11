<div>

    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- ══ GENERAL SETTING ══ -->
    <div class="mat-card" style="padding-top:28px; margin-bottom:24px">

        <div class="mat-card-header header-pink-gradient">
            <h5>
                <span class="material-icons-round" style="font-size:18px;vertical-align:middle;margin-right:6px">settings</span>
                General Setting
            </h5>
        </div>

        <div class="form-section">
            <div class="row g-4">
                <!-- School Name -->
                <div class="col-md-6">
                    <div class="input-group input-group-outline">
                        <label class="form-label">School Name <span class="req">*</span></label>
                        <input type="text" wire:model="name" class="form-control"
                               placeholder=" " onfocus="focused(this)" onfocusout="defocused(this)">
                    </div>
                    @error('name') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <!-- Email -->
                <div class="col-md-6">
                    <div class="input-group input-group-outline">
                        <label class="form-label">Email</label>
                        <input type="email" wire:model="email" class="form-control"
                               placeholder=" " onfocus="focused(this)" onfocusout="defocused(this)">
                    </div>
                    @error('email') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <!-- Phone No -->
                <div class="col-md-6">
                    <div class="input-group input-group-outline">
                        <label class="form-label">Phone No</label>
                        <input type="text" wire:model="phone" class="form-control"
                               placeholder=" " onfocus="focused(this)" onfocusout="defocused(this)">
                    </div>
                    @error('phone') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <!-- City -->
                <div class="col-md-6">
                    <div class="input-group input-group-outline">
                        <label class="form-label">City</label>
                        <input type="text" wire:model="city" class="form-control"
                               placeholder=" " onfocus="focused(this)" onfocusout="defocused(this)">
                    </div>
                    @error('city') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <!-- Address -->
                <div class="col-md-12">
                    <div class="input-group input-group-outline">
                        <label class="form-label">Address</label>
                        <textarea wire:model="address" class="form-control" style="min-height:80px"
                                  placeholder=" " onfocus="focused(this)" onfocusout="defocused(this)"></textarea>
                    </div>
                    @error('address') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <!-- Language -->
                <div class="col-md-6">
                    <div class="input-group input-group-outline" wire:ignore>
                        <label class="form-label">Language <span class="req">*</span></label>
                        <select wire:model="language" class="form-select">
                            <option value="English">English</option>
                            <option value="Bangla">Bangla</option>
                            <option value="Arabic">Arabic</option>
                            <option value="Hindi">Hindi</option>
                            <option value="Urdu">Urdu</option>
                            <option value="French">French</option>
                        </select>
                    </div>
                    @error('language') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <!-- Timezone -->
                <div class="col-md-6">
                    <div class="input-group input-group-outline" wire:ignore>
                        <label class="form-label">Timezone <span class="req">*</span></label>
                        <select wire:model="timezone" class="form-select">
                            @foreach(\DateTimeZone::listIdentifiers() as $tz)
                                <option value="{{ $tz }}">{{ $tz }}</option>
                            @endforeach
                        </select>
                    </div>
                    @error('timezone') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <!-- Weekends -->
                <div class="col-md-12">
                    <div class="input-group input-group-outline" wire:ignore>
                        <label class="form-label">Weekends</label>
                        <select wire:model="weekends" class="form-select" id="weekendsSelect" multiple>
                            <option value="Saturday">Saturday</option>
                            <option value="Sunday">Sunday</option>
                            <option value="Monday">Monday</option>
                            <option value="Tuesday">Tuesday</option>
                            <option value="Wednesday">Wednesday</option>
                            <option value="Thursday">Thursday</option>
                            <option value="Friday">Friday</option>
                        </select>
                    </div>
                    @error('weekends') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <!-- Unique Roll -->
                <div class="col-md-12">
                    <label style="font-size:.73rem;font-weight:600;color:var(--muted);display:block;margin-bottom:8px">
                        Unique Roll
                    </label>
                    <div class="d-flex gap-3 flex-wrap align-items-center">
                        <div class="form-check">
                            <input wire:model="unique_roll" class="form-check-input" type="radio"
                                   value="class_wise" id="rollClassWise">
                            <label class="form-check-label" for="rollClassWise">Classes Wise</label>
                        </div>
                        <div class="form-check">
                            <input wire:model="unique_roll" class="form-check-input" type="radio"
                                   value="section_wise" id="rollSectionWise">
                            <label class="form-check-label" for="rollSectionWise">Section Wise</label>
                        </div>
                        <div class="form-check">
                            <input wire:model="unique_roll" class="form-check-input" type="radio"
                                   value="disabled" id="rollDisabled">
                            <label class="form-check-label" for="rollDisabled">Disabled</label>
                        </div>
                    </div>
                    @error('unique_roll') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <!-- Teacher Restricted -->
                <div class="col-md-12">
                    <div class="form-check mt-1">
                        <input wire:model="teacher_restricted" class="form-check-input" type="checkbox" id="teacherRestricted">
                        <label class="form-check-label" for="teacherRestricted">Teacher Restricted</label>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- ══ CURRENCY ══ -->
    <div class="mat-card" style="padding-top:28px; margin-bottom:24px">

        <div class="mat-card-header header-pink-gradient">
            <h5>
                <span class="material-icons-round" style="font-size:18px;vertical-align:middle;margin-right:6px">payments</span>
                Currency
            </h5>
        </div>

        <div class="form-section">
            <div class="row g-4">

                <!-- Currency -->
                <div class="col-md-6">
                    <div class="input-group input-group-outline">
                        <label class="form-label">Currency <span class="req">*</span></label>
                        <input type="text" wire:model="currency" class="form-control"
                               placeholder=" " onfocus="focused(this)" onfocusout="defocused(this)">
                    </div>
                    @error('currency') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <!-- Currency Symbol -->
                <div class="col-md-6">
                    <div class="input-group input-group-outline">
                        <label class="form-label">Currency Symbol <span class="req">*</span></label>
                        <input type="text" wire:model="currency_symbol" class="form-control"
                               placeholder=" " onfocus="focused(this)" onfocusout="defocused(this)">
                    </div>
                    @error('currency_symbol') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <!-- Currency Formats -->
                <div class="col-md-6">
                    <div class="input-group input-group-outline" wire:ignore>
                        <label class="form-label">Currency Formats <span class="req">*</span></label>
                        <select wire:model="currency_format" class="form-select">
                            <option value="1230000.50">1230000.50</option>
                            <option value="1,230,000.50">1,230,000.50</option>
                            <option value="1.230.000,50">1.230.000,50</option>
                            <option value="1 230 000.50">1 230 000.50</option>
                        </select>
                    </div>
                    @error('currency_format') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <!-- Symbol Position -->
                <div class="col-md-6">
                    <div class="input-group input-group-outline" wire:ignore>
                        <label class="form-label">Symbol Position <span class="req">*</span></label>
                        <select wire:model="symbol_position" class="form-select">
                            <option value="prefix">$123,000.00</option>
                            <option value="suffix">123,000.00$</option>
                        </select>
                    </div>
                    @error('symbol_position') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

            </div>
        </div>
    </div>

    <!-- ══ REGISTER NO PREFIX ══ -->
    <div class="mat-card" style="padding-top:28px; margin-bottom:24px">

        <div class="mat-card-header header-pink-gradient">
            <h5>
                <span class="material-icons-round" style="font-size:18px;vertical-align:middle;margin-right:6px">badge</span>
                Register No Prefix
            </h5>
        </div>

        <div class="form-section">
            <div class="row g-4">

                <!-- Enable Prefix -->
                <div class="col-md-12">
                    <div class="form-check mt-1">
                        <input wire:model.live="enable_registration_prefix" class="form-check-input"
                               type="checkbox" id="enablePrefix">
                        <label class="form-check-label" for="enablePrefix">
                            Enable Student Admission Registration No Prefix Auto.
                        </label>
                    </div>
                </div>

                <!-- Institution Code Prefix -->
                <div class="col-md-6">
                    <div class="input-group input-group-outline">
                        <label class="form-label">Institution Code (Prefix) <span class="req">*</span></label>
                        <input type="text" wire:model="institution_code_prefix" class="form-control"
                               placeholder=" " onfocus="focused(this)" onfocusout="defocused(this)">
                    </div>
                    @error('institution_code_prefix') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <!-- Register No Start From -->
                <div class="col-md-3">
                    <div class="input-group input-group-outline">
                        <label class="form-label">Register No Start From <span class="req">*</span></label>
                        <input type="number" wire:model="register_start_from" class="form-control"
                               min="1" placeholder=" " onfocus="focused(this)" onfocusout="defocused(this)">
                    </div>
                    @error('register_start_from') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <!-- Register No Digit -->
                <div class="col-md-3">
                    <div class="input-group input-group-outline" wire:ignore>
                        <label class="form-label">Register No Digit <span class="req">*</span></label>
                        <select wire:model="register_no_digit" class="form-select">
                            @for($i = 1; $i <= 10; $i++)
                                <option value="{{ $i }}">{{ $i }}</option>
                            @endfor
                        </select>
                    </div>
                    @error('register_no_digit') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

            </div>
        </div>
    </div>

    <!-- ══ OFFLINE PAYMENTS SETTING ══ -->
    <div class="mat-card" style="padding-top:28px; margin-bottom:24px">

        <div class="mat-card-header header-pink-gradient">
            <h5>
                <span class="material-icons-round" style="font-size:18px;vertical-align:middle;margin-right:6px">account_balance_wallet</span>
                Offline Payments Setting
            </h5>
        </div>

        <div class="form-section">
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="input-group input-group-outline" wire:ignore>
                        <label class="form-label">Offline Payments</label>
                        <select wire:model="offline_payment_enabled" class="form-select">
                            <option value="1">Enabled</option>
                            <option value="0">Disabled</option>
                        </select>
                    </div>
                    @error('offline_payment_enabled') <span class="text-danger">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>
    </div>

    <!-- ══ ONLINE EXAM ══ -->
    <div class="mat-card" style="padding-top:28px; margin-bottom:24px">

        <div class="mat-card-header header-pink-gradient">
            <h5>
                <span class="material-icons-round" style="font-size:18px;vertical-align:middle;margin-right:6px">quiz</span>
                Online Exam
            </h5>
        </div>

        <div class="form-section">
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="input-group input-group-outline" wire:ignore>
                        <label class="form-label">Show Only Own Question</label>
                        <select wire:model="show_only_own_question" class="form-select">
                            <option value="0">Disabled</option>
                            <option value="1">Enabled</option>
                        </select>
                    </div>
                    @error('show_only_own_question') <span class="text-danger">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>
    </div>

    <!-- ══ FEES CARRY FORWARD SETTING ══ -->
    <div class="mat-card" style="padding-top:28px; margin-bottom:24px">

        <div class="mat-card-header header-pink-gradient">
            <h5>
                <span class="material-icons-round" style="font-size:18px;vertical-align:middle;margin-right:6px">currency_exchange</span>
                Fees Carry Forward Setting
            </h5>
        </div>

        <div class="form-section">
            <div class="row g-4">

                <!-- Due Days -->
                <div class="col-md-6">
                    <div class="input-group input-group-outline">
                        <label class="form-label">Due Days</label>
                        <input type="number" wire:model="due_days" class="form-control"
                               min="0" placeholder=" " onfocus="focused(this)" onfocusout="defocused(this)">
                    </div>
                    @error('due_days') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <!-- Due Fees Calculation With Fine -->
                <div class="col-md-12">
                    <div class="form-check mt-1">
                        <input wire:model="due_fees_calculation_with_fine" class="form-check-input"
                               type="checkbox" id="dueFeesWithFine">
                        <label class="form-check-label" for="dueFeesWithFine">Due Fees Calculation With Fine</label>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- ══ AUTOMATICALLY GENERATE LOGIN DETAILS ══ -->
    <div class="mat-card" style="padding-top:28px; margin-bottom:24px">

        <div class="mat-card-header header-pink-gradient">
            <h5>
                <span class="material-icons-round" style="font-size:18px;vertical-align:middle;margin-right:6px">manage_accounts</span>
                Automatically Generate Login Details
            </h5>
        </div>

        <div class="form-section">
            <div class="row g-4">

                <div class="col-md-12">
                    <div class="form-check mt-1">
                        <input wire:model="auto_generate_student_login" class="form-check-input"
                               type="checkbox" id="autoStudentLogin">
                        <label class="form-check-label" for="autoStudentLogin">
                            Automatically Generate Student Login Details.
                        </label>
                    </div>
                </div>

                <div class="col-md-12">
                    <div class="form-check mt-1">
                        <input wire:model="auto_generate_guardian_login" class="form-check-input"
                               type="checkbox" id="autoGuardianLogin">
                        <label class="form-check-label" for="autoGuardianLogin">
                            Automatically Generate Guardian Login Details.
                        </label>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- ══ LOGO SETTING ══ -->
    <div class="mat-card" style="padding-top:28px; margin-bottom:24px">

        <div class="mat-card-header header-pink-gradient">
            <h5>
                <span class="material-icons-round" style="font-size:18px;vertical-align:middle;margin-right:6px">image</span>
                Logo Setting
            </h5>
        </div>
<form wire:submit="save" enctype="multipart/form-data">
        <div class="form-section">
            <div class="row g-4">

                <!-- System Logo -->
                <div class="col-md-6 col-lg-3">
                    <label style="font-size:.73rem;font-weight:600;color:var(--muted);display:block;margin-bottom:8px">
                        System Logo
                    </label>
                    <div class="photo-upload-box">
                        @if($system_logo_upload)
                            @if($this->safePreviewUrl($system_logo_upload))
                                <img src="{{ $this->safePreviewUrl($system_logo_upload) }}" alt="Preview"
                                     style="max-height:80px;max-width:100%;object-fit:contain;margin-bottom:6px">
                            @else
                                <span class="material-icons-round">check_circle</span>
                                <span class="lbl">File selected</span>
                            @endif
                        @elseif($system_logo)
                            <img src="{{ url($system_logo) }}" alt="System Logo"
                                 style="max-height:80px;max-width:100%;object-fit:contain;margin-bottom:6px">
                        @else
                            <span class="material-icons-round">image</span>
                            <span class="lbl">Click to upload</span>
                        @endif
                        <small style="color:#bbb;font-size:.7rem">PNG, JPG up to 2MB</small>
                        <input type="file" wire:model="system_logo_upload" accept="image/*">
                    </div>
                    @error('system_logo_upload') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <!-- Text Logo -->
                <div class="col-md-6 col-lg-3">
                    <label style="font-size:.73rem;font-weight:600;color:var(--muted);display:block;margin-bottom:8px">
                        Text Logo
                    </label>
                    <div class="photo-upload-box">
                        @if($text_logo_upload)
                            @if($this->safePreviewUrl($text_logo_upload))
                                <img src="{{ $this->safePreviewUrl($text_logo_upload) }}" alt="Preview"
                                     style="max-height:80px;max-width:100%;object-fit:contain;margin-bottom:6px">
                            @else
                                <span class="material-icons-round">check_circle</span>
                                <span class="lbl">File selected</span>
                            @endif
                        @elseif($text_logo)
                            <img src="{{ url($text_logo) }}" alt="Text Logo"
                                 style="max-height:80px;max-width:100%;object-fit:contain;margin-bottom:6px">
                        @else
                            <span class="material-icons-round">image</span>
                            <span class="lbl">Click to upload</span>
                        @endif
                        <small style="color:#bbb;font-size:.7rem">PNG, JPG up to 2MB</small>
                        <input type="file" wire:model="text_logo_upload" accept="image/*">
                    </div>
                    @error('text_logo_upload') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <!-- Printing Logo -->
                <div class="col-md-6 col-lg-3">
                    <label style="font-size:.73rem;font-weight:600;color:var(--muted);display:block;margin-bottom:8px">
                        Printing Logo
                    </label>
                    <div class="photo-upload-box">
                        @if($print_logo_upload)
                            @if($this->safePreviewUrl($print_logo_upload))
                                <img src="{{ $this->safePreviewUrl($print_logo_upload) }}" alt="Preview"
                                     style="max-height:80px;max-width:100%;object-fit:contain;margin-bottom:6px">
                            @else
                                <span class="material-icons-round">check_circle</span>
                                <span class="lbl">File selected</span>
                            @endif
                        @elseif($print_logo)
                            <img src="{{ url($print_logo) }}" alt="Printing Logo"
                                 style="max-height:80px;max-width:100%;object-fit:contain;margin-bottom:6px">
                        @else
                            <span class="material-icons-round">image</span>
                            <span class="lbl">Click to upload</span>
                        @endif
                        <small style="color:#bbb;font-size:.7rem">PNG, JPG up to 2MB</small>
                        <input type="file" wire:model="print_logo_upload" accept="image/*">
                    </div>
                    @error('print_logo_upload') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <!-- Report Card Logo -->
                <div class="col-md-6 col-lg-3">
                    <label style="font-size:.73rem;font-weight:600;color:var(--muted);display:block;margin-bottom:8px">
                        Report Card
                    </label>
                    <div class="photo-upload-box">
                        @if($report_logo_upload)
                            @if($this->safePreviewUrl($report_logo_upload))
                                <img src="{{ $this->safePreviewUrl($report_logo_upload) }}" alt="Preview"
                                     style="max-height:80px;max-width:100%;object-fit:contain;margin-bottom:6px">
                            @else
                                <span class="material-icons-round">check_circle</span>
                                <span class="lbl">File selected</span>
                            @endif
                        @elseif($report_logo)
                            <img src="{{ url($report_logo) }}" alt="Report Card Logo"
                                 style="max-height:80px;max-width:100%;object-fit:contain;margin-bottom:6px">
                        @else
                            <span class="material-icons-round">image</span>
                            <span class="lbl">Click to upload</span>
                        @endif
                        <small style="color:#bbb;font-size:.7rem">PNG, JPG up to 2MB</small>
                        <input type="file" wire:model="report_logo_upload" accept="image/*">
                    </div>
                    @error('report_logo_upload') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

            </div>
        </div>

        <!-- FORM FOOTER -->
        <div class="form-footer">
            <button class="btn-pink"
                    type="submit"
                    {{-- type="button" --}}
                    {{-- wire:click="save" --}}
                    wire:loading.attr="disabled"
                    wire:target="save">

                <span wire:loading.remove wire:target="save">
                    <span class="material-icons-round">save</span>
                    Save
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
</form>
    </div>

</div>

@push('scripts')
<script>
    document.addEventListener('livewire:initialized', () => {

        setTimeout(() => initAllFields(), 100);

        Livewire.hook('morph.updated', ({ el }) => {
            setTimeout(() => initAllFields(), 50);
        });

        function initAllFields() {

            // Text/Textarea is-filled
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

            // Select is-filled
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

            // Custom Select rebuild
            document.querySelectorAll('.input-group-outline .form-select').forEach(function(select) {
                var old = select.parentNode.querySelector('.custom-select-wrapper');
                if (old) old.remove();
                select.style.display = '';
                if (typeof buildCustomSelect === 'function') {
                    buildCustomSelect(select);
                }
            });

            // Weekends multi-select sync
            var weekendsSelect = document.getElementById('weekendsSelect');
            if (weekendsSelect && !weekendsSelect._multiInit) {
                weekendsSelect._multiInit = true;

                // Set initial selected values from Livewire
                var currentWeekends = @json($weekends ?? []);
                Array.from(weekendsSelect.options).forEach(function(opt) {
                    opt.selected = currentWeekends.includes(opt.value);
                });

                weekendsSelect.addEventListener('change', function() {
                    var selected = Array.from(weekendsSelect.selectedOptions).map(opt => opt.value);
                    @this.set('weekends', selected);
                });
            }

            
        }

        // Flash message auto-dismiss
        Livewire.on('saved', () => {
            setTimeout(() => {
                document.querySelectorAll('.alert-success').forEach(el => {
                    el.classList.remove('show');
                    setTimeout(() => el.remove(), 300);
                });
            }, 3000);
        });

    });
</script>
@endpush