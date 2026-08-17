<div>
    <div class="card">

      <!-- floating header -->
      <div class="mat-card-header header-primary-gradient">
        <h5><span class="material-icons-round" style="font-size:18px;vertical-align:middle;margin-right:6px">how_to_reg</span>Student Admission</h5>
        <p>Create new student admission record</p>
      </div>

        <!-- ══ ADMISSION TYPE ══ -->
        <div class="form-section" style="padding-top:40px">
            <div class="section-heading">
                <span class="material-icons-round">switch_account</span> Admission Type
            </div>
            <div class="row g-4">
                <div class="col-12">
                    <div class="form-check form-switch">
                        <input wire:model.live="is_new_student" class="form-check-input" type="checkbox" id="isNewStudent" role="switch">
                        <label class="form-check-label" for="isNewStudent">
                            {{ $is_new_student ? 'New Student (Fee Invoice will be generated)' : 'Existing Student (Invoice will be generated next month)' }}
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <!-- ══ ACADEMIC DETAILS ══ -->
        <div class="form-section">
            <div class="section-heading">
                <span class="material-icons-round">school</span> Academic Details
            </div>
            <div class="row g-4">
                <div class="col-md-3">
                    <div class="input-group input-group-outline" wire:ignore>
                        <label class="form-label">Academic Year <span class="req">*</span></label>
                        <select wire:model="session_id" class="form-select">
                            @foreach($sessions as $session)
                                <option value="{{ $session->id }}" @if($session->is_current == true) selected @endif>{{ $session->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @error('session_id') <span class="text-danger">{{ $message }}</span> @enderror
                </div>
                <div class="col-md-3">
                    <div class="input-group input-group-outline">
                        <label class="form-label">Registration No </label>
                        <input type="text" wire:model="registration_no" class="form-control" onfocus="focused(this)" onfocusout="defocused(this)">
                        @error('registration_no') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="input-group input-group-outline">
                        <label class="form-label">Roll (Auto)</label>
                        <input type="text" wire:model="roll_no" class="form-control" readonly style="cursor:not-allowed">
                        @error('roll_no') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="input-group input-group-outline" wire:ignore>
                        <label class="form-label">Admission Date <span class="req">*</span></label>
                        <input type="date" wire:model="admission_date" data-dp-value="{{ $admission_date }}" class="form-control">
                        @error('admission_date') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="input-group input-group-outline" wire:ignore>
                        <label class="form-label">Class <span class="req">*</span></label>
                        <select wire:model.live="class_id" class="form-select" id="classSelect">
                            <option value="">Select Class</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}">{{ $class->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @error('class_id') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <div class="col-md-3">
                    @if($selectedClassHasSection)
                        <div class="input-group input-group-outline">
                            <label class="form-label">Section <span class="req">*</span></label>
                            <select wire:model="section_id" class="form-select" id="sectionSelect" @disabled(!$class_id)>
                                <option value="">
                                    {{ !$class_id ? 'Select class first' : 'Select Section' }}
                                </option>
                                @foreach($availableSections as $section)
                                    <option value="{{ $section['id'] }}">{{ $section['name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        @error('section_id') <span class="text-danger">{{ $message }}</span> @enderror
                    @else
                        <div class="input-group input-group-outline">
                            <label class="form-label">Section</label>
                            <input type="text" class="form-control" value="N/A — this class has no sections" disabled style="background:#f5f5f5;cursor:not-allowed">
                        </div>
                    @endif
                </div>

                <div class="col-md-3">
                    <div class="input-group input-group-outline" wire:ignore>
                        <label class="form-label">Group</label>
                        <select wire:model="group_id" class="form-select">
                            <option value="">Select Group</option>
                            @foreach($groups as $group)
                                <option @if($group->is_current == true) selected @endif value="{{ $group->id }}">{{ $group->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @error('group_id') <span class="text-danger">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>

        <!-- ══ STUDENT DETAILS ══ -->
        <div class="form-section">
            <div class="section-heading">
            <span class="material-icons-round">person</span> Student Details
            </div>
            <div class="row g-4">
            <div class="col-md-6">
                    <div class="input-group input-group-outline">
                    <label class="form-label">Full Name <span class="req">*</span></label>
                    <input type="text" wire:model="name" class="form-control" placeholder=" " onfocus="focused(this)" onfocusout="defocused(this)">
                    @error('name') <span class="text-danger">{{ $message }}</span> @enderror
                </div>
            </div>
            <div class="col-md-6">
                <div class="input-group input-group-outline" wire:ignore>
                    <label class="form-label">Gender</label>
                    <select wire:model="gender" class="form-select">
                        <option value="male" selected>Male</option>
                        <option value="female">Female</option>
                        <option value="other">Other</option>
                    </select>
                    @error('gender') <span class="text-danger">{{ $message }}</span> @enderror
                </div>
            </div>
            <div class="col-md-6">
                <div class="input-group input-group-outline" wire:ignore>
                    <label class="form-label">Blood Group</label>
                    <select wire:model="blood_group" class="form-select">
                        <option value="">Select</option>
                        <option value="A+">A+</option>
                        <option value="A-">A-</option>
                        <option value="B+">B+</option>
                        <option value="B-">B-</option>
                        <option value="AB+">AB+</option>
                        <option value="AB-">AB-</option>
                        <option value="O+">O+</option>
                        <option value="O-">O-</option>
                    </select>
                    @error('blood_group') <span class="text-danger">{{ $message }}</span> @enderror
                </div>
            </div>
            <div class="col-md-6">
                <div class="input-group input-group-outline" wire:ignore>
                    <label class="form-label">Date Of Birth</label>
                    <input type="date" wire:model="dob" class="form-control" placeholder=" " onfocus="focused(this)" onfocusout="defocused(this)">
                    @error('dob') <span class="text-danger">{{ $message }}</span> @enderror
                </div>
            </div>
            <div class="col-md-4">
                <div class="input-group input-group-outline" wire:ignore>
                    <label class="form-label">Religion</label>
                    <select wire:model="religion" class="form-select">
                        <option value="">Select</option>
                        <option value="muslim">Muslim</option>
                        <option value="hindu">Hindu</option>
                        <option value="christian">Christian</option>
                        <option value="buddhist">Buddhist</option>
                    </select>
                </div>
            </div>
            <div class="col-md-4">
                <div class="input-group input-group-outline">
                    <label class="form-label">Mobile No</label>
                    <input type="tel" wire:model="mobile" class="form-control" placeholder=" " onfocus="focused(this)" onfocusout="defocused(this)">
                    @error('mobile') <span class="text-danger">{{ $message }}</span> @enderror
                </div>
            </div>
            <div class="col-md-4">
                <div class="input-group input-group-outline">
                <label class="form-label">Email</label>
                <input type="email" wire:model="email" class="form-control" placeholder=" " onfocus="focused(this)" onfocusout="defocused(this)">
                @error('email') <span class="text-danger">{{ $message }}</span> @enderror
                </div>
            </div>
            <div class="col-md-12">
                <div class="input-group input-group-outline">
                <label class="form-label">Present Address</label>
                <textarea wire:model="present_address" class="form-control" placeholder=" " onfocus="focused(this)" onfocusout="defocused(this)"></textarea>
                </div>
            </div>
            <div class="col-md-12">
                <div class="input-group input-group-outline">
                <label class="form-label">Permanent Address</label>
                <textarea wire:model="permanent_address" class="form-control" placeholder=" " onfocus="focused(this)" onfocusout="defocused(this)"></textarea>
                </div>
            </div>
            <div class="col-md-12">
                <label style="font-size:.73rem;font-weight:600;color:var(--muted);display:block;margin-bottom:8px">
                    Photo
                </label>
                <div class="photo-upload-box">
                    @if($student_photo_upload)
                        <img src="{{ $student_photo_upload->temporaryUrl() }}" alt="Preview"
                            style="max-height:80px;max-width:100%;object-fit:contain;margin-bottom:6px">
                    @else
                        <span class="material-icons-round">image</span>
                        <span class="lbl">Click to upload</span>
                    @endif
                    <small style="color:#bbb;font-size:.7rem">PNG, JPG up to 2MB</small>
                    <input type="file" wire:model="student_photo_upload" accept="image/*">
                </div>
                @error('student_photo_upload') <span class="text-danger">{{ $message }}</span> @enderror
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
                        <label class="form-label">Username <span class="req">*</span></label>
                        <input type="text" wire:model="username" class="form-control" onfocus="focused(this)" onfocusout="defocused(this)">
                        @error('username') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="input-group input-group-outline">
                        <label class="form-label">Password (Default: 12345678)</label>
                        <input type="password" wire:model="password" class="form-control" placeholder="" autocomplete="new-password" onfocus="focused(this)" onfocusout="defocused(this)">
                        @error('password') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- ══ GUARDIAN DETAILS ══ -->
        <div class="form-section">
            <div class="section-heading">
                <span class="material-icons-round">supervisor_account</span> Guardian Details
            </div>
            <div class="row g-4 mb-2">
                <div class="col-12">
                    <div class="form-check">
                        <input wire:model.live="guardian_exists" class="form-check-input" type="checkbox" id="guardianExist">
                        <label class="form-check-label" for="guardianExist">Guardian Already Exist</label>
                    </div>
                </div>
            </div>

            {{-- EXISTING GUARDIAN --}}
            <div class="row g-4 {{ $guardian_exists ? '' : 'd-none' }}">
                <div class="col-md-6 @if($this->guardian_exists === false) d-none @else d-block @endif">
                    <div class="input-group input-group-outline" wire:ignore>
                        <label class="form-label">Guardian</label>
                        <select wire:model="guardian_id" class="form-select">
                            <option value="">Select</option>
                            @foreach ($guardians as $item)
                                <option value="{{ $item->id }}">{{ $item->name }}</option>
                            @endforeach
                        </select>
                        @error('guardian_id') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            {{-- NEW GUARDIAN --}}
            <div class="row g-4 {{ $guardian_exists ? 'd-none' : '' }}">
                <div class="col-md-6">
                    <div class="input-group input-group-outline">
                        <label class="form-label">Name <span class="req">*</span></label>
                        <input type="text" wire:model="guardian_name" class="form-control" placeholder=" " onfocus="focused(this)" onfocusout="defocused(this)">
                        @error('guardian_name') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="input-group input-group-outline">
                        <label class="form-label">Relation <span class="req">*</span></label>
                        <input type="text" wire:model="guardian_relation" class="form-control" placeholder=" " onfocus="focused(this)" onfocusout="defocused(this)">
                        @error('guardian_relation') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="input-group input-group-outline">
                        <label class="form-label">Father Name</label>
                        <input type="text" wire:model="guardian_father_name" class="form-control" placeholder=" " onfocus="focused(this)" onfocusout="defocused(this)">
                        @error('guardian_father_name') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="input-group input-group-outline">
                        <label class="form-label">Mother Name</label>
                        <input type="text" wire:model="guardian_mother_name" class="form-control" placeholder=" " onfocus="focused(this)" onfocusout="defocused(this)">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="input-group input-group-outline">
                        <label class="form-label">Occupation</label>
                        <input type="text" wire:model="guardian_occupation" class="form-control" placeholder=" " onfocus="focused(this)" onfocusout="defocused(this)">
                        @error('guardian_occupation') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="input-group input-group-outline">
                        <label class="form-label">Income</label>
                        <input type="text" wire:model="guardian_income" class="form-control" placeholder=" " onfocus="focused(this)" onfocusout="defocused(this)">
                        @error('guardian_income') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="input-group input-group-outline">
                        <label class="form-label">Education</label>
                        <input type="text" wire:model="guardian_education" class="form-control" placeholder=" " onfocus="focused(this)" onfocusout="defocused(this)">
                        @error('guardian_education') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="input-group input-group-outline">
                        <label class="form-label">Mobile No</label>
                        <input type="tel" wire:model="guardian_mobile" class="form-control" placeholder=" " onfocus="focused(this)" onfocusout="defocused(this)">
                        @error('guardian_mobile') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="input-group input-group-outline">
                        <label class="form-label">Email</label>
                        <input type="email" wire:model="guardian_email" class="form-control" placeholder=" " onfocus="focused(this)" onfocusout="defocused(this)">
                        @error('guardian_email') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="col-12">
                    <div class="input-group input-group-outline">
                        <label class="form-label">Address</label>
                        <textarea wire:model="guardian_address" class="form-control" placeholder=" " onfocus="focused(this)" onfocusout="defocused(this)"></textarea>
                    </div>
                </div>
                <div class="col-md-12">
                    <label style="font-size:.73rem;font-weight:600;color:var(--muted);display:block;margin-bottom:8px">
                        Photo
                    </label>
                    <div class="photo-upload-box">
                        @if($guardian_photo_upload)
                            <img src="{{ $guardian_photo_upload->temporaryUrl() }}" alt="Preview"
                                style="max-height:80px;max-width:100%;object-fit:contain;margin-bottom:6px">
                        @else
                            <span class="material-icons-round">image</span>
                            <span class="lbl">Click to upload</span>
                        @endif
                        <small style="color:#bbb;font-size:.7rem">PNG, JPG up to 2MB</small>
                        <input type="file" wire:model="guardian_photo_upload" accept="image/*">
                    </div>
                    @error('guardian_photo_upload') <span class="text-danger">{{ $message }}</span> @enderror
                </div>
                <div class="col-md-6">
                    <div class="input-group input-group-outline">
                        <label class="form-label">Username <span class="req">*</span></label>
                        <input type="text" wire:model="guardian_username" class="form-control" placeholder=" " onfocus="focused(this)" onfocusout="defocused(this)">
                        @error('guardian_username') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="input-group input-group-outline">
                        <label class="form-label">Password (Default: 12345678)</label>
                        <input type="password" wire:model="guardian_password" class="form-control" placeholder="" autocomplete="new-password" onfocus="focused(this)" onfocusout="defocused(this)">
                        @error('guardian_password') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- ══ PREVIOUS INSTITUTION DETAILS ══ -->
        <div class="form-section">
            <div class="section-heading">
            <span class="material-icons-round">history_edu</span> Previous Institution Details
            </div>
            <div class="row g-4">
            <div class="col-md-6">
                <div class="input-group input-group-outline">
                    <label class="form-label">Institution Name</label>
                    <textarea wire:model="previous_institution" class="form-control" placeholder=" " style="min-height:64px" onfocus="focused(this)" onfocusout="defocused(this)"></textarea>
                </div>
                @error('previous_institution') <span class="text-danger">{{ $message }}</span> @enderror
            </div>
            <div class="col-md-6">
                <div class="input-group input-group-outline">
                    <label class="form-label">Qualification</label>
                    <textarea wire:model="qualification" class="form-control" placeholder=" " style="min-height:64px" onfocus="focused(this)" onfocusout="defocused(this)"></textarea>
                </div>
                @error('qualification') <span class="text-danger">{{ $message }}</span> @enderror
            </div>
            <div class="col-12">
                <div class="input-group input-group-outline">
                    <label class="form-label">Remarks</label>
                    <textarea wire:model="remarks" class="form-control" placeholder=" " style="min-height:80px" onfocus="focused(this)" onfocusout="defocused(this)"></textarea>
                </div>
                @error('remarks') <span class="text-danger">{{ $message }}</span> @enderror
            </div>
            </div>
        </div>

        <!-- Form Footer -->
        <div class="form-footer">
            <button class="btn btn-secondary" type="button" wire:click="resetForm" wire:loading.attr="disabled" wire:target="resetForm,reset">
                <span wire:loading.remove wire:target="resetForm,reset">
                    <span class="material-icons-round">refresh</span>
                    <span>Reset</span>
                </span>

                <span wire:loading wire:target="resetForm,reset">
                    <span class="material-icons-round" style="animation:spin .7s linear infinite">sync</span>
                    Reseting...
                </span>
            </button>

            <button class="btn btn-primary" type="button" wire:click="openFeeConfirmModal" wire:loading.attr="disabled" wire:target="openFeeConfirmModal,save">
                <span wire:loading.remove wire:target="openFeeConfirmModal,save">
                    <span class="material-icons-round">save</span>
                    <span>Save</span> 
                </span>

                <span wire:loading wire:target="openFeeConfirmModal,save">
                    <span class="material-icons-round" style="animation:spin .7s linear infinite">sync</span>
                    <span>Processing...</span>
                </span>
            </button>
        </div>

    </div>

    {{-- ══════════════ STEP 1: FEE CONFIRMATION MODAL ══════════════ --}}
    @if($showFeeModal)
        <div class="modal-dark-overlay" wire:click.self="closeFeeModal">
            <div class="modal-box">
                <div class="modal-box-header">
                    <h5>
                        <span class="material-icons-round" style="font-size:20px;vertical-align:middle;margin-right:6px">receipt_long</span>
                        Confirm Fee for Invoice
                    </h5>
                    <button type="button" class="modal-box-close" wire:click="closeFeeModal">&times;</button>
                </div>

                <div class="modal-box-body">
                    @if(empty($feeItems))
                        <p class="text-muted mb-0">No fee setup found for this class. Student will be saved without any invoice.</p>
                    @else
                        @foreach($feeItems as $key => $item)
                            <label class="fee-item-row">
                                <span class="fee-item-left">
                                    <input type="checkbox" wire:model.live="selectedFees.{{ $key }}">
                                    <span>{{ $item['label'] }}</span>
                                </span>
                                <span class="fee-item-amount">{{ number_format($item['amount'], 2) }}</span>
                            </label>
                        @endforeach

                        <div class="fee-item-row fee-total-row">
                            <span>TOTAL</span>
                            <span>{{ number_format($this->feeModalTotal, 2) }}</span>
                        </div>
                    @endif
                </div>

                <div class="modal-box-footer">
                    <button type="button" class="btn btn-secondary" wire:click="closeFeeModal">Cancel</button>
                    <button type="button" class="btn btn-primary" wire:click="save" wire:loading.attr="disabled" wire:target="save">
                        <span wire:loading.remove wire:target="save">Confirm &amp; Save</span>
                        <span wire:loading wire:target="save">Saving...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>
@push('styles')
    <style>
        :root {
            --primary: rgba(33, 37, 41);
            --primary-light: rgba(239,84,84,.12);
        }

        .card { border: 1px solid var(--border); border-radius: 12px; box-shadow: 0 1px 4px rgba(0,0,0,.04); }
        .card-header { background: #fff; border-bottom: 1px solid var(--border); border-radius: 12px 12px 0 0 !important; padding: 16px 20px; }
        .card-header .card-title { font-size: .95rem; font-weight: 600; margin: 0; }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary); box-shadow: 0 0 0 3px var(--primary-light);
        }

        .btn-sm { font-size: .78rem; padding: .3rem .65rem; border-radius: 6px; }

        /* ── Shared Modal Styles (Fee Confirm) ── */
        .modal-dark-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.65);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1055;
            padding: 20px;
        }

        .modal-box {
            background: #fff;
            border-radius: 12px;
            width: 100%;
            max-width: 480px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 10px 40px rgba(0,0,0,.25);
        }

        .modal-box-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 16px 20px;
            border-bottom: 1px solid var(--border, #eee);
        }

        .modal-box-header h5 { margin: 0; font-size: .95rem; font-weight: 600; }

        .modal-box-close {
            background: none;
            border: none;
            font-size: 1.4rem;
            line-height: 1;
            cursor: pointer;
            color: #888;
        }

        .modal-box-body { padding: 16px 20px; }

        .fee-item-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #f0f0f0;
            font-size: .88rem;
            cursor: pointer;
        }

        .fee-item-left { display: flex; align-items: center; gap: 10px; }
        .fee-item-left input[type="checkbox"] { width: 16px; height: 16px; }

        .fee-total-row {
            font-weight: 700;
            border-bottom: none;
            border-top: 2px solid #eee;
            margin-top: 4px;
            padding-top: 12px;
            cursor: default;
        }

        .modal-box-footer {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            padding: 14px 20px;
            border-top: 1px solid var(--border, #eee);
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('livewire:initialized', () => {

            setTimeout(() => initAllFields(), 100);

            Livewire.hook('morph.updated', ({ el }) => {
                setTimeout(() => initAllFields(), 0);
            });

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

                Livewire.on('date-updated', function (event) {
                    var data = Array.isArray(event) ? event[0] : event;
                    if (!data || !data.field) return;

                    var input = document.querySelector(
                        '.input-group-outline input[type="date"][wire\\:model="' + data.field + '"]'
                    );
                    if (!input) return;

                    var newDate = data.date || '';
                    if (newDate) {
                        input.value = newDate;
                        input.dataset.dpValue = newDate;
                        if (input._dpTriggerSync) {
                            input._dpTriggerSync(newDate);
                        }
                    }
                });
            }

        });
    </script>
@endpush