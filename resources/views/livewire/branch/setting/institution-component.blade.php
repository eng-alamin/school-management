<div>
    <div class="page-header-area">
        <div class="container-fluid">
            <h4>{{ __('Institution Settings') }}</h4>
        </div>
    </div>

    <div class="container-fluid mt-4">

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        {{-- Tabs --}}
        <ul class="nav nav-tabs mb-4">
            @foreach([
                'general'    => ['icon' => 'fa-gear',              'label' => 'General'],
                'register'   => ['icon' => 'fa-id-badge',          'label' => 'Register Prefix'],
                'fees'       => ['icon' => 'fa-money-bill',        'label' => 'Fees'],
                'logo'       => ['icon' => 'fa-image',             'label' => 'Logo'],
            ] as $tab => $info)
                <li class="nav-item">
                    <button
                        wire:click="$set('activeTab', '{{ $tab }}')"
                        type="button"
                        class="nav-link {{ $activeTab === $tab ? 'active' : '' }}"
                    >
                        <i class="fas {{ $info['icon'] }} me-1"></i> {{ $info['label'] }}
                    </button>
                </li>
            @endforeach
        </ul>

        <div class="card">

            <div class="form-section">

                {{-- ===== TAB: GENERAL ===== --}}
                @if($activeTab === 'general')
                    <div class="row g-4 mb-4">
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

                        <!-- Institution Medium -->
                        <div class="col-md-6">
                            <div class="input-group input-group-outline" wire:ignore>
                                <label class="form-label">Institution Medium</label>
                                <select wire:model="medium" class="form-select">
                                    <option value="">Select Medium</option>
                                    @foreach (\App\Models\Institution::MEDIUMS as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @error('medium') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>

                        <!-- Division -->
                        <div class="col-md-6">
                            <div class="input-group input-group-outline" wire:ignore>
                                <label class="form-label">Division</label>
                                <select wire:model="division" class="form-select">
                                    <option value="">Select Division</option>
                                    @foreach (\App\Models\Institution::DIVISIONS as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @error('division') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>

                        <!-- District -->
                        <div class="col-md-6">
                            <div class="input-group input-group-outline">
                                <label class="form-label">District</label>
                                <input type="text" wire:model="district" class="form-control"
                                    placeholder=" " onfocus="focused(this)" onfocusout="defocused(this)">
                            </div>
                            @error('district') <span class="text-danger">{{ $message }}</span> @enderror
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
                    </div>
                @endif

                {{-- ===== TAB: REGISTER PREFIX ===== --}}
                @if($activeTab === 'register')
                    <div class="row g-4 mb-4">
                        <!-- Enable Prefix -->
                        <div class="col-md-12">
                            <div class="form-check mt-1">
                                <input wire:model.live="enable_registration_prefix" class="form-check-input"
                                       type="checkbox" id="enablePrefix">
                                <label class="form-check-label" for="enablePrefix">
                                    Enable Auto Prefix for Student Registration No.
                                </label>
                            </div>
                        </div>

                        <!-- Registration Code Prefix -->
                        <div class="col-md-6">
                            <div class="input-group input-group-outline">
                                <label class="form-label">Registration Code (Prefix) <span class="req">*</span></label>
                                <input type="text" wire:model="registration_code_prefix" class="form-control"
                                       placeholder=" " onfocus="focused(this)" onfocusout="defocused(this)">
                            </div>
                            @error('registration_code_prefix') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>

                        <!-- Register No Start From -->
                        <div class="col-md-3">
                            <div class="input-group input-group-outline">
                                <label class="form-label">Registration Start From <span class="req">*</span></label>
                                <input type="number" wire:model="registration_start_from" class="form-control"
                                       min="1" placeholder=" " onfocus="focused(this)" onfocusout="defocused(this)">
                            </div>
                            @error('registration_start_from') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>

                        <!-- Register No Digit -->
                        <div class="col-md-3">
                            <div class="input-group input-group-outline" wire:ignore>
                                <label class="form-label">Registration Digit Length <span class="req">*</span></label>
                                <select wire:model="registration_digit_length" class="form-select">
                                    @for($i = 1; $i <= 10; $i++)
                                        <option value="{{ $i }}">{{ $i }}</option>
                                    @endfor
                                </select>
                            </div>
                            @error('registration_digit_length') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>

                        <!-- Divider between Registration and Student ID -->
                        <div class="col-md-12">
                            <hr>
                        </div>

                        <!-- Enable Student ID Prefix -->
                        <div class="col-md-12">
                            <div class="form-check mt-1">
                                <input wire:model.live="enable_student_id_prefix" class="form-check-input"
                                       type="checkbox" id="enableStudentIdPrefix">
                                <label class="form-check-label" for="enableStudentIdPrefix">
                                    Enable Auto Prefix for Student ID
                                </label>
                            </div>
                        </div>

                        <!-- Student ID Code Prefix -->
                        <div class="col-md-6">
                            <div class="input-group input-group-outline">
                                <label class="form-label">Student ID Code (Prefix) <span class="req">*</span></label>
                                <input type="text" wire:model="student_id_code_prefix" class="form-control"
                                       placeholder=" " onfocus="focused(this)" onfocusout="defocused(this)">
                            </div>
                            @error('student_id_code_prefix') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>

                        <!-- Student ID Start From -->
                        <div class="col-md-3">
                            <div class="input-group input-group-outline">
                                <label class="form-label">Student ID Start From <span class="req">*</span></label>
                                <input type="number" wire:model="student_id_start_from" class="form-control"
                                       min="1" placeholder=" " onfocus="focused(this)" onfocusout="defocused(this)">
                            </div>
                            @error('student_id_start_from') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>

                        <!-- Student ID Digit Length -->
                        <div class="col-md-3">
                            <div class="input-group input-group-outline" wire:ignore>
                                <label class="form-label">Student ID Digit Length <span class="req">*</span></label>
                                <select wire:model="student_id_digit_length" class="form-select">
                                    @for($i = 1; $i <= 10; $i++)
                                        <option value="{{ $i }}">{{ $i }}</option>
                                    @endfor
                                </select>
                            </div>
                            @error('student_id_digit_length') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>

                        <!-- Divider between Student ID and Employee ID -->
                        <div class="col-md-12">
                            <hr>
                        </div>

                        <!-- Enable Employee ID Prefix -->
                        <div class="col-md-12">
                            <div class="form-check mt-1">
                                <input wire:model.live="enable_employee_id_prefix" class="form-check-input"
                                       type="checkbox" id="enableEmployeeIdPrefix">
                                <label class="form-check-label" for="enableEmployeeIdPrefix">
                                    Enable Auto Prefix for Employee ID
                                </label>
                            </div>
                        </div>

                        <!-- Employee ID Code Prefix -->
                        <div class="col-md-6">
                            <div class="input-group input-group-outline">
                                <label class="form-label">Employee ID Code (Prefix) <span class="req">*</span></label>
                                <input type="text" wire:model="employee_id_code_prefix" class="form-control"
                                       placeholder=" " onfocus="focused(this)" onfocusout="defocused(this)">
                            </div>
                            @error('employee_id_code_prefix') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>

                        <!-- Employee ID Start From -->
                        <div class="col-md-3">
                            <div class="input-group input-group-outline">
                                <label class="form-label">Employee ID Start From <span class="req">*</span></label>
                                <input type="number" wire:model="employee_id_start_from" class="form-control"
                                       min="1" placeholder=" " onfocus="focused(this)" onfocusout="defocused(this)">
                            </div>
                            @error('employee_id_start_from') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>

                        <!-- Employee ID Digit Length -->
                        <div class="col-md-3">
                            <div class="input-group input-group-outline" wire:ignore>
                                <label class="form-label">Employee ID Digit Length <span class="req">*</span></label>
                                <select wire:model="employee_id_digit_length" class="form-select">
                                    @for($i = 1; $i <= 10; $i++)
                                        <option value="{{ $i }}">{{ $i }}</option>
                                    @endfor
                                </select>
                            </div>
                            @error('employee_id_digit_length') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                    </div>
                @endif

                {{-- ===== TAB: FEES ===== --}}
                @if($activeTab === 'fees')
                    <div class="row g-4 mb-4">
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
                @endif

                {{-- ===== TAB: LOGO ===== --}}
                @if($activeTab === 'logo')
                    <div class="row g-4 mb-4">

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
                @endif

            </div>

            <div class="form-footer">
                <button class="btn btn-primary px-4" type="button" wire:click="save" wire:loading.attr="disabled" wire:target="save">
                    <span wire:loading.remove wire:target="save">
                        <span class="material-icons-round">save</span>
                        <span>Save</span>
                    </span>

                    <span wire:loading wire:target="save">
                        <span class="material-icons-round" style="animation:spin .7s linear infinite">sync</span>
                        <span>Saving...</span>
                    </span>
                </button>
            </div>

        </div>

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

        document.querySelectorAll('.input-group-outline .form-select').forEach(function(select) {
            var old = select.parentNode.querySelector('.custom-select-wrapper');
            if (old) old.remove();
            select.style.display = '';
            if (typeof buildCustomSelect === 'function') {
                buildCustomSelect(select);
            }
        });
    }

    function initWeekendsPicker() {
        var $sel = $('#weekendsSelect');
        if (!$sel.length) return;

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

    document.addEventListener('livewire:navigated', () => {
        setTimeout(() => {
            initAllFields();
            initWeekendsPicker();
        }, 250);
    });

    // Tab change kore General tab e ashle weekendsSelect abar dom-e render hoy,
    // tai eta abar init korte hobe — noile purono DOM-er sathe bind thake.
    document.addEventListener('livewire:navigated', () => {
        if (!window.__institutionTabHookBound) {
            window.__institutionTabHookBound = true;
            Livewire.hook('morph.updated', () => {
                setTimeout(() => {
                    initAllFields();
                    if (document.getElementById('weekendsSelect')) {
                        initWeekendsPicker();
                    }
                }, 50);
            });
        }
    });

    $(document).on('changed.bs.select', '#weekendsSelect', function () {
        @this.set('weekends', $(this).val() ?? []);
    });

    if (!window.__institutionSettingsHooksBound) {
        window.__institutionSettingsHooksBound = true;

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