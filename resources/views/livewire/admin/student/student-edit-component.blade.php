<div class="mat-card" style="padding-top:28px">

        <!-- Floating Header -->
        <div class="mat-card-header header-pink-gradient">
            <h5><span class="material-icons-round" style="font-size:18px;vertical-align:middle;margin-right:6px">edit</span>Edit Student</h5>
            <p>Update student information</p>
        </div>

        <!-- ══ ACADEMIC DETAILS ══ -->
        <div class="form-section" style="padding-top:40px">
            <div class="section-heading">
            <span class="material-icons-round">school</span> Academic Details
            </div>
            <div class="row g-4">
            <div class="col-md-3">
                <div class="input-group input-group-outline" wire:ignore>
                    <label class="form-label">Academic Year <span class="req">*</span></label>
                    <select wire:model="session_id" class="form-select">
                        @foreach($sessions as $session)
                            <option value="{{ $session->id }}">{{ $session->name }}</option>
                        @endforeach
                    </select>
                </div>
                @error('session_id') <span class="text-danger">{{ $message }}</span> @enderror
            </div>
            <div class="col-md-3">
                <div class="input-group input-group-outline">
                    <label class="form-label">Register No <span class="req">*</span></label>
                    <input type="text" wire:model="register_no" class="form-control" value="ISC-0001" onfocus="focused(this)" onfocusout="defocused(this)">
                     @error('register_no') <span class="text-danger">{{ $message }}</span> @enderror
                </div>
            </div>
            <div class="col-md-3">
                <div class="input-group input-group-outline">
                    <label class="form-label">Roll</label>
                    <input type="text" wire:model="roll_no" class="form-control" placeholder=" " onfocus="focused(this)" onfocusout="defocused(this)">
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
            <div class="col-md-4">
                <div class="input-group input-group-outline" wire:ignore>
                    <label class="form-label">Class <span class="req">*</span></label>
                    <select wire:model="class_id" class="form-select" id="classSelect">
                        <option value="">Select Class</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}">{{ $class->name }}</option>
                        @endforeach
                    </select>
                </div>
                @error('class_id') <span class="text-danger">{{ $message }}</span> @enderror
            </div>
            <div class="col-md-4">
                <div class="input-group input-group-outline" wire:ignore>
                    <label class="form-label">Section</label>
                    <select wire:model="section_id" class="form-select" id="sectionSelect">
                        <option value="">Select Section</option>
                        @foreach($sections as $section)
                            <option value="{{ $section->id }}">{{ $section->name }}</option>
                        @endforeach
                    </select>
                </div>
                @error('section_id') <span class="text-danger">{{ $message }}</span> @enderror
            </div>
            <div class="col-md-4">
                <div class="input-group input-group-outline" wire:ignore>
                    <label class="form-label">Category</label>
                    <select wire:model="category_id" class="form-select">
                        <option value="">Select Category</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                @error('category_id') <span class="text-danger">{{ $message }}</span> @enderror
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
                        <input type="date" wire:model="dob" data-dp-value="{{ $dob }}" class="form-control" placeholder=" " onfocus="focused(this)" onfocusout="defocused(this)">
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
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="input-group input-group-outline">
                    <label class="form-label">Email</label>
                    <input type="email" wire:model="email" class="form-control" placeholder=" " onfocus="focused(this)" onfocusout="defocused(this)">
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
                        @elseif($student_photo)
                            <img src="{{ asset('storage/' . $student_photo) }}" alt="Photo"
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
                    <input type="text" wire:model="username" class="form-control" value="admin@ramom.com" onfocus="focused(this)" onfocusout="defocused(this)">
                    @error('username') <span class="text-danger">{{ $message }}</span> @enderror
                </div>
            </div>
            <div class="col-md-6">
                <div class="input-group input-group-outline">
                    <label class="form-label">Password</label>
                    <input type="password" wire:model="password" class="form-control" value="1234" onfocus="focused(this)" onfocusout="defocused(this)">
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
                        <label class="form-label">Mobile No <span class="req">*</span></label>
                        <input type="tel" wire:model="guardian_mobile" class="form-control" placeholder=" " onfocus="focused(this)" onfocusout="defocused(this)">
                        @error('guardian_mobile') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="input-group input-group-outline">
                        <label class="form-label">Email <span class="req">*</span></label>
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
                        @elseif($guardian_photo)
                            <img src="{{ asset('storage/' . $guardian_photo) }}" alt="Photo"
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
                        <label class="form-label">Password</label>
                        <input type="password" wire:model="guardian_password" class="form-control" placeholder=" " onfocus="focused(this)" onfocusout="defocused(this)">
                        @error('guardian_password') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- ══ PREVIOUS SCHOOL DETAILS ══ -->
        <div class="form-section">
            <div class="section-heading">
            <span class="material-icons-round">history_edu</span> Previous School Details
            </div>
            <div class="row g-4">
            <div class="col-md-6">
                <div class="input-group input-group-outline">
                    <label class="form-label">School Name</label>
                    <textarea wire:model="previous_school" class="form-control" placeholder=" " style="min-height:64px" onfocus="focused(this)" onfocusout="defocused(this)"></textarea>
                </div>
                @error('previous_school') <span class="text-danger">{{ $message }}</span> @enderror
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
            <button type="button" class="btn-outline" onclick="history.back()">
                <span class="material-icons-round" style="font-size:16px">arrow_back</span>
                Back
            </button>

            <button class="btn-pink" type="button" wire:click="update" wire:loading.attr="disabled" wire:target="update">
                <span wire:loading.remove wire:target="update" style="display: inline-flex;align-items: center;gap: 6px">
                    <span class="material-icons-round">update</span> Update
                </span>

                <span wire:loading wire:target="update">
                    <span class="material-icons-round" style="font-size:16px;animation:spin .7s linear infinite">sync</span>
                    Saving...
                </span>
            </button>
        </div>

    </div>

@push('scripts')
    <script>
        document.addEventListener('livewire:initialized', () => {

            setTimeout(() => initAllFields(), 100);

            Livewire.hook('morph.updated', ({ el }) => {
                setTimeout(() => initAllFields(), 0);
            });

            function initAllFields() {

                // ── 1. Text/Textarea/Number is-filled ──
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
                Livewire.on('date-updated', function (event) {
                    var input = document.querySelector('.input-group-outline input[type="date"]');
                    if (!input) return;
                    var newDate = event.date || '';
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