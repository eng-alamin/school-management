{{-- livewire/tenant/theme/employee-edit-component.blade.php --}}

<div>

    <div class="card">

        <div class="mat-card-header header-pink-gradient">
            <h5 id="emp-edit-header-title">Employee Registration</h5>
            <p id="emp-edit-header-subtitle">Update existing employee record</p>
        </div>

        <!-- ══ JOB DETAILS ══ -->
        <div class="form-section" style="padding-top:40px">
            <div class="section-heading">
                <span class="material-icons-round">work</span> Job Details
            </div>

            <div class="row g-4">
                <div class="col-md-6">
                    <div class="input-group input-group-outline" wire:ignore>
                        <label class="form-label"><span id="emp-edit-lbl-role">Role</span> <span class="req">*</span></label>
                        <select wire:model="role" class="form-select">
                            <option value="">Select Role</option>
                            <option value="admin">Admin</option>
                            <option value="teacher">Teacher</option>
                            <option value="accountant">Accountant</option>
                            <option value="staff">Staff</option>
                        </select>
                    </div>
                    @error('role') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <div class="col-md-6">
                    <div class="input-group input-group-outline" wire:ignore>
                        <label class="form-label"><span id="emp-edit-lbl-joining-date">Joining Date</span> <span class="req">*</span></label>
                        <input type="date" wire:model="joining_date" data-dp-value="{{ $joining_date }}" class="form-control">
                    </div>
                    @error('joining_date') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <div class="col-md-6">
                    <div class="input-group input-group-outline" wire:ignore>
                        <label class="form-label"><span id="emp-edit-lbl-designation">Designation</span> <span class="req">*</span></label>
                        <select wire:model="designation_id" class="form-select">
                            <option value="">Select Designation</option>
                            @foreach ($designations as $designation)
                                <option value="{{ $designation->id }}">{{ $designation->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @error('designation_id') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <div class="col-md-6">
                    <div class="input-group input-group-outline" wire:ignore>
                        <label class="form-label"><span id="emp-edit-lbl-department">Department</span> <span class="req">*</span></label>
                        <select wire:model="department_id" class="form-select">
                            <option value="">Select Department</option>
                            @foreach ($departments as $department)
                                <option value="{{ $department->id }}">{{ $department->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @error('department_id') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <div class="col-md-6">
                    <div class="input-group input-group-outline">
                        <label class="form-label" id="emp-edit-lbl-qualification">Qualification</label>
                        <input type="text" wire:model="qualification" class="form-control" placeholder=" " onfocus="focused(this)" onfocusout="defocused(this)">
                    </div>
                    @error('qualification') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <div class="col-md-6">
                    <div class="input-group input-group-outline">
                        <label class="form-label" id="emp-edit-lbl-total-experience">Total Experience</label>
                        <input type="text" wire:model="total_experience" class="form-control" placeholder=" " onfocus="focused(this)" onfocusout="defocused(this)">
                    </div>
                    @error('total_experience') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <div class="col-12">
                    <div class="input-group input-group-outline">
                        <label class="form-label" id="emp-edit-lbl-experience-detail">Experience Details</label>
                        <textarea wire:model="experience_detail" class="form-control" placeholder=" " style="min-height:90px" onfocus="focused(this)" onfocusout="defocused(this)"></textarea>
                    </div>
                    @error('experience_detail') <span class="text-danger">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>

        <!-- ══ EMPLOYEE DETAILS ══ -->
        <div class="form-section">
            <div class="section-heading">
                <span class="material-icons-round">person</span> Employee Details
            </div>

            <div class="row g-4">
                <div class="col-md-6">
                    <div class="input-group input-group-outline">
                        <label class="form-label"><span id="emp-edit-lbl-name">Name</span> <span class="req">*</span></label>
                        <input type="text" wire:model="name" class="form-control" placeholder=" " onfocus="focused(this)" onfocusout="defocused(this)">
                    </div>
                    @error('name') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <div class="col-md-6">
                    <div class="input-group input-group-outline" wire:ignore>
                        <label class="form-label" id="emp-edit-lbl-dob">Date Of Birth</label>
                        <input type="date" wire:model="dob" data-dp-value="{{ $dob }}" class="form-control">
                    </div>
                    @error('dob') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <div class="col-md-4">
                    <div class="input-group input-group-outline" wire:ignore>
                        <label class="form-label" id="emp-edit-lbl-religion">Religion</label>
                        <select wire:model="religion" class="form-select">
                            <option value="">Select</option>
                            <option value="muslim">Muslim</option>
                            <option value="hindu">Hindu</option>
                            <option value="christian">Christian</option>
                            <option value="buddhist">Buddhist</option>
                        </select>
                    </div>
                    @error('religion') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <div class="col-md-4">
                    <div class="input-group input-group-outline">
                        <label class="form-label" id="emp-edit-lbl-mobile">Mobile</label>
                        <input type="text" wire:model="mobile" class="form-control" placeholder=" " onfocus="focused(this)" onfocusout="defocused(this)">
                    </div>
                    @error('mobile') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <div class="col-md-4">
                    <div class="input-group input-group-outline">
                        <label class="form-label" id="emp-edit-lbl-email">Email</label>
                        <input type="email" wire:model="email" class="form-control" placeholder=" " onfocus="focused(this)" onfocusout="defocused(this)">
                    </div>
                    @error('email') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <div class="col-md-6">
                    <div class="input-group input-group-outline">
                        <label class="form-label" id="emp-edit-lbl-present-address">Present Address</label>
                        <textarea wire:model="present_address" class="form-control" placeholder=" " onfocus="focused(this)" onfocusout="defocused(this)"></textarea>
                    </div>
                    @error('present_address') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <div class="col-md-6">
                    <div class="input-group input-group-outline">
                        <label class="form-label" id="emp-edit-lbl-permanent-address">Permanent Address</label>
                        <textarea wire:model="permanent_address" class="form-control" placeholder=" " onfocus="focused(this)" onfocusout="defocused(this)"></textarea>
                    </div>
                    @error('permanent_address') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <div class="col-md-12">
                    <label id="emp-edit-lbl-photo" style="font-size:.73rem;font-weight:600;color:var(--muted);display:block;margin-bottom:8px">
                        Photo
                    </label>
                    <div class="photo-upload-box">
                        @if($photo_upload)
                            <img src="{{ $photo_upload->temporaryUrl() }}" alt="Preview"
                                style="max-height:80px;max-width:100%;object-fit:contain;margin-bottom:6px">
                        @elseif($photo)
                            <img src="{{ asset('storage/' . $photo) }}" alt="Photo"
                                style="max-height:80px;max-width:100%;object-fit:contain;margin-bottom:6px">
                        @else
                            <span class="material-icons-round">image</span>
                            <span class="lbl">Click to upload</span>
                        @endif
                        <small style="color:#bbb;font-size:.7rem">PNG, JPG up to 2MB</small>
                        <input type="file" wire:model="photo_upload" accept="image/*">
                    </div>
                    @error('photo_upload') <span class="text-danger">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>

        <!-- ══ LOGIN DETAILS ══ -->
        <div class="form-section">
            <div class="section-heading">
                <span class="material-icons-round">lock</span> Login Details
            </div>

            <div class="row g-4">
                <div class="col-md-6">
                    <div class="input-group input-group-outline">
                        <label class="form-label"><span id="emp-edit-lbl-username">Username</span> <span class="req">*</span></label>
                        <input type="text" wire:model="username" class="form-control" placeholder=" " onfocus="focused(this)" onfocusout="defocused(this)">
                    </div>
                    @error('username') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <div class="col-md-6">
                    <div class="input-group input-group-outline">
                        <label class="form-label" id="emp-edit-lbl-password">Password</label>
                        <input type="password" wire:model="password" class="form-control" placeholder=" " onfocus="focused(this)" onfocusout="defocused(this)">
                    </div>
                    @error('password') <span class="text-danger">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>

        <!-- ══ BANK DETAILS ══ -->
        <div class="form-section">
            <div class="section-heading">
                <span class="material-icons-round">account_balance</span> Bank Details
            </div>

            <div class="row g-4">
                <div class="col-md-6">
                    <div class="input-group input-group-outline">
                        <label class="form-label" id="emp-edit-lbl-bank-name">Bank Name</label>
                        <input type="text" wire:model="bank_name" class="form-control" placeholder=" " onfocus="focused(this)" onfocusout="defocused(this)">
                    </div>
                    @error('bank_name') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <div class="col-md-6">
                    <div class="input-group input-group-outline">
                        <label class="form-label" id="emp-edit-lbl-holder-name">Holder Name</label>
                        <input type="text" wire:model="holder_name" class="form-control" placeholder=" " onfocus="focused(this)" onfocusout="defocused(this)">
                    </div>
                    @error('holder_name') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <div class="col-md-6">
                    <div class="input-group input-group-outline">
                        <label class="form-label" id="emp-edit-lbl-bank-branch">Bank Branch</label>
                        <input type="text" wire:model="bank_branch" class="form-control" placeholder=" " onfocus="focused(this)" onfocusout="defocused(this)">
                    </div>
                    @error('bank_branch') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <div class="col-md-6">
                    <div class="input-group input-group-outline">
                        <label class="form-label" id="emp-edit-lbl-ifsc-code">IFSC Code</label>
                        <input type="text" wire:model="ifsc_code" class="form-control" placeholder=" " onfocus="focused(this)" onfocusout="defocused(this)">
                    </div>
                    @error('ifsc_code') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <div class="col-md-6">
                    <div class="input-group input-group-outline">
                        <label class="form-label" id="emp-edit-lbl-account-no">Account No</label>
                        <input type="text" wire:model="account_no" class="form-control" placeholder=" " onfocus="focused(this)" onfocusout="defocused(this)">
                    </div>
                    @error('account_no') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <div class="col-md-6">
                    <div class="input-group input-group-outline">
                        <label class="form-label" id="emp-edit-lbl-bank-address">Bank Address</label>
                        <textarea wire:model="bank_address" class="form-control" placeholder=" " onfocus="focused(this)" onfocusout="defocused(this)"></textarea>
                    </div>
                    @error('bank_address') <span class="text-danger">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>

        <!-- FORM FOOTER -->
        <div class="form-footer">
            <button type="button" class="btn-outline" onclick="history.back()">
                <span class="material-icons-round" style="font-size:16px">arrow_back</span>
                Back
            </button>

            <button class="btn-pink" type="button" wire:click="update" wire:loading.attr="disabled" wire:target="update">
                <span wire:loading.remove wire:target="update" style="display: inline-flex;align-items: center;gap: 6px">
                    <span class="material-icons-round">save</span>
                    Update
                </span>

                <span wire:loading wire:target="update">
                    <span class="material-icons-round" style="font-size:16px;animation:spin .7s linear infinite">sync</span>
                    Updating...
                </span>
            </button>
        </div>

    </div>

</div>

@push('scripts')
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
                if (typeof _initDatepickers === 'function') {
                    _initDatepickers();
                }
            }

        });
    </script>
@endpush