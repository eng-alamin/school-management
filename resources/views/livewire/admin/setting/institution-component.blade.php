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
                <!-- Institution Name -->
                <div class="col-md-6">
                    <div class="input-group input-group-outline">
                        <label class="form-label">Institution Name <span class="req">*</span></label>
                        <input type="text" wire:model="name" class="form-control"
                               placeholder=" " onfocus="focused(this)" onfocusout="defocused(this)">
                    </div>
                    @error('name') <span class="text-danger">{{ $message }}</span> @enderror
                </div>
                 <!-- EIIN/কোড -->
                <div class="col-md-6">
                    <div class="input-group input-group-outline">
                        <label class="form-label">EIIN/কোড</label>
                        <input type="text" wire:model="eiin" class="form-control"
                               placeholder=" " onfocus="focused(this)" onfocusout="defocused(this)">
                    </div>
                    @error('eiin') <span class="text-danger">{{ $message }}</span> @enderror
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

                <!-- Weekends — selectpicker (subjects pattern) -->
                <div class="col-md-12">
                    <label class="form-label">Weekends</label>
                    <div wire:ignore>
                        <select
                            id="weekendsSelect"
                            multiple
                            title="Select Weekends..."
                            class="form-select w-100 selectpicker">
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
                            <img src="{{ $system_logo_upload->temporaryUrl() }}" alt="Preview"
                                 style="max-height:80px;max-width:100%;object-fit:contain;margin-bottom:6px">
                        @elseif($system_logo)
                            <img src="{{ asset('storage/' . $system_logo) }}" alt="System Logo"
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
                            <img src="{{ $text_logo_upload->temporaryUrl() }}" alt="Preview"
                                 style="max-height:80px;max-width:100%;object-fit:contain;margin-bottom:6px">
                        @elseif($text_logo)
                            <img src="{{ asset('storage/' . $text_logo) }}" alt="Text Logo"
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
                            <img src="{{ $print_logo_upload->temporaryUrl() }}" alt="Preview"
                                 style="max-height:80px;max-width:100%;object-fit:contain;margin-bottom:6px">
                        @elseif($print_logo)
                            <img src="{{ asset('storage/' . $print_logo) }}" alt="Printing Logo"
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
                            <img src="{{ $report_logo_upload->temporaryUrl() }}" alt="Preview"
                                 style="max-height:80px;max-width:100%;object-fit:contain;margin-bottom:6px">
                        @elseif($report_logo)
                            <img src="{{ asset('storage/' . $report_logo) }}" alt="Report Card Logo"
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

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/css/bootstrap-select.min.css">
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.0/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/js/bootstrap-select.min.js"></script>
<script>
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
    }

    // ── Weekends selectpicker ────────────────────────────────────────────
    function initWeekendsPicker() {
        var $sel = $('#weekendsSelect');
        if (!$sel.length) return;

        // আগের instance destroy kore notun kore init kora — idempotent,
        // tai kotobar e function call hoy oita matter kore na
        try { $sel.selectpicker('destroy'); } catch (e) {}

        var currentWeekends = @json($weekends ?? []);
        $sel.val(currentWeekends);
        $sel.selectpicker();
    }

    function refreshWeekendsPicker() {
        var $sel = $('#weekendsSelect');
        if ($sel.length && $sel.data('selectpicker')) {
            $sel.selectpicker('refresh');
        }
    }

    // ✅ MAIN FIX: 'livewire:initialized' / 'livewire:init' pura session-e
    // MAATTRO EKBAR fire hoy — wire:navigate diye onno page theke fire e
    // page-e fire ashle eta abar fire e hoy na, tai picker init e hoy na.
    // 'livewire:navigated' initial load + protek wire:navigate navigation-e
    // fire hoy, tai eta e thik event.
    document.addEventListener('livewire:navigated', () => {
        setTimeout(() => {
            initAllFields();
            initWeekendsPicker();
        }, 250);
    });

    // sync value to Livewire — delegated, ekbar bind korai jothesto
    $(document).on('changed.bs.select', '#weekendsSelect', function () {
        @this.set('weekends', $(this).val() ?? []);
    });

    // Livewire.hook globally protibar register hoy — guard diye duplicate atkano
    if (!window.__institutionSettingsHooksBound) {
        window.__institutionSettingsHooksBound = true;

        Livewire.hook('morph.updated', () => {
            setTimeout(() => initAllFields(), 50);
        });

        Livewire.hook('message.processed', () => {
            setTimeout(() => refreshWeekendsPicker(), 50);
        });

        Livewire.on('saved', () => {
            setTimeout(() => {
                document.querySelectorAll('.alert-success').forEach(el => {
                    el.classList.remove('show');
                    setTimeout(() => el.remove(), 300);
                });
            }, 3000);
        });
    }
</script>
@endpush