{{-- livewire/tenant/theme/employee-add-component.blade.php --}}

<div>

    <div class="card">

        <div class="mat-card-header header-pink-gradient">
            <h5 id="emp-add-header-title">Employee Registration</h5>
            <p id="emp-add-header-subtitle">Create new employee record</p>
        </div>

        <!-- ══ JOB DETAILS ══ -->
        <div class="form-section" style="padding-top:40px">
            <div class="section-heading">
                <span class="material-icons-round">work</span> Job Details
            </div>

            <div class="row g-4">
                <div class="col-md-6">
                    <div class="input-group input-group-outline" wire:ignore>
                        <label class="form-label"><span id="emp-add-lbl-role">Role</span> <span class="req">*</span></label>
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
                        <label class="form-label"><span id="emp-add-lbl-joining-date">Joining Date</span> <span class="req">*</span></label>
                        <input type="date" wire:model="joining_date" class="form-control">
                    </div>
                    @error('joining_date') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <div class="col-md-6">
                    <div class="input-group input-group-outline" wire:ignore>
                        <label class="form-label"><span id="emp-add-lbl-designation">Designation</span> <span class="req">*</span></label>
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
                        <label class="form-label"><span id="emp-add-lbl-department">Department</span> <span class="req">*</span></label>
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
                        <label class="form-label" id="emp-add-lbl-qualification">Qualification</label>
                        <input type="text" wire:model="qualification" class="form-control" placeholder=" " onfocus="focused(this)" onfocusout="defocused(this)">
                    </div>
                    @error('qualification') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <div class="col-md-6">
                    <div class="input-group input-group-outline">
                        <label class="form-label" id="emp-add-lbl-total-experience">Total Experience</label>
                        <input type="text" wire:model="total_experience" class="form-control" placeholder=" " onfocus="focused(this)" onfocusout="defocused(this)">
                    </div>
                    @error('total_experience') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <div class="col-12">
                    <div class="input-group input-group-outline">
                        <label class="form-label" id="emp-add-lbl-experience-detail">Experience Details</label>
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
                        <label class="form-label"><span id="emp-add-lbl-name">Name</span> <span class="req">*</span></label>
                        <input type="text" wire:model="name" class="form-control" placeholder=" " onfocus="focused(this)" onfocusout="defocused(this)">
                    </div>
                    @error('name') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <div class="col-md-6">
                    <div class="input-group input-group-outline" wire:ignore>
                        <label class="form-label" id="emp-add-lbl-dob">Date Of Birth</label>
                        <input type="date" wire:model="dob" class="form-control">
                    </div>
                    @error('dob') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <div class="col-md-4">
                    <div class="input-group input-group-outline" wire:ignore>
                        <label class="form-label" id="emp-add-lbl-religion">Religion</label>
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
                        <label class="form-label" id="emp-add-lbl-mobile">Mobile</label>
                        <input type="text" wire:model="mobile" class="form-control" placeholder=" " onfocus="focused(this)" onfocusout="defocused(this)">
                    </div>
                    @error('mobile') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <div class="col-md-4">
                    <div class="input-group input-group-outline">
                        <label class="form-label" id="emp-add-lbl-email">Email</label>
                        <input type="email" wire:model="email" class="form-control" placeholder=" " onfocus="focused(this)" onfocusout="defocused(this)">
                    </div>
                    @error('email') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <div class="col-md-6">
                    <div class="input-group input-group-outline">
                        <label class="form-label" id="emp-add-lbl-present-address">Present Address</label>
                        <textarea wire:model="present_address" class="form-control" placeholder=" " onfocus="focused(this)" onfocusout="defocused(this)"></textarea>
                    </div>
                    @error('present_address') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <div class="col-md-6">
                    <div class="input-group input-group-outline">
                        <label class="form-label" id="emp-add-lbl-permanent-address">Permanent Address</label>
                        <textarea wire:model="permanent_address" class="form-control" placeholder=" " onfocus="focused(this)" onfocusout="defocused(this)"></textarea>
                    </div>
                    @error('permanent_address') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <div class="col-md-12">
                    <label id="emp-add-lbl-photo" style="font-size:.73rem;font-weight:600;color:var(--muted);display:block;margin-bottom:8px">
                        Photo
                    </label>
                    <div class="photo-upload-box">
                        @if($photo_upload)
                            <img src="{{ $photo_upload->temporaryUrl() }}" 
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
                        <label class="form-label"><span id="emp-add-lbl-username">Username</span> <span class="req">*</span></label>
                        <input type="text" wire:model="username" class="form-control" placeholder=" " onfocus="focused(this)" onfocusout="defocused(this)">
                    </div>
                    @error('username') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <div class="col-md-6">
                    <div class="input-group input-group-outline">
                        <label class="form-label" id="emp-add-lbl-password">Password</label>
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
                        <label class="form-label" id="emp-add-lbl-bank-name">Bank Name</label>
                        <input type="text" wire:model="bank_name" class="form-control" placeholder=" " onfocus="focused(this)" onfocusout="defocused(this)">
                    </div>
                    @error('bank_name') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <div class="col-md-6">
                    <div class="input-group input-group-outline">
                        <label class="form-label" id="emp-add-lbl-holder-name">Holder Name</label>
                        <input type="text" wire:model="holder_name" class="form-control" placeholder=" " onfocus="focused(this)" onfocusout="defocused(this)">
                    </div>
                    @error('holder_name') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <div class="col-md-6">
                    <div class="input-group input-group-outline">
                        <label class="form-label" id="emp-add-lbl-bank-branch">Bank Branch</label>
                        <input type="text" wire:model="bank_branch" class="form-control" placeholder=" " onfocus="focused(this)" onfocusout="defocused(this)">
                    </div>
                    @error('bank_branch') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <div class="col-md-6">
                    <div class="input-group input-group-outline">
                        <label class="form-label" id="emp-add-lbl-ifsc-code">IFSC Code</label>
                        <input type="text" wire:model="ifsc_code" class="form-control" placeholder=" " onfocus="focused(this)" onfocusout="defocused(this)">
                    </div>
                    @error('ifsc_code') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <div class="col-md-6">
                    <div class="input-group input-group-outline">
                        <label class="form-label" id="emp-add-lbl-account-no">Account No</label>
                        <input type="text" wire:model="account_no" class="form-control" placeholder=" " onfocus="focused(this)" onfocusout="defocused(this)">
                    </div>
                    @error('account_no') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <div class="col-md-6">
                    <div class="input-group input-group-outline">
                        <label class="form-label" id="emp-add-lbl-bank-address">Bank Address</label>
                        <textarea wire:model="bank_address" class="form-control" placeholder=" " onfocus="focused(this)" onfocusout="defocused(this)"></textarea>
                    </div>
                    @error('bank_address') <span class="text-danger">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>

        <!-- FORM FOOTER -->
        <div class="form-footer">
            <button class="btn-outline" type="button" wire:click="resetForm">
                <span class="material-icons-round" style="font-size:16px">refresh</span>
                Reset
            </button>

            <button class="btn-pink" type="button" wire:click="save" wire:loading.attr="disabled" wire:target="save">
                <span wire:loading.remove wire:target="save" style="display: inline-flex;align-items: center;gap: 6px">
                    <span class="material-icons-round">save</span>
                    Save
                </span>

                <span wire:loading wire:target="save">
                    <span class="material-icons-round" style="font-size:16px;animation:spin .7s linear infinite">sync</span>
                    Saving...
                </span>
            </button>
        </div>
        
    </div>

</div>