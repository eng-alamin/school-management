<div>
    <div class="card">

        <div class="mat-card-header header-pink-gradient">
            <h5>Edit Profile</h5>
            <p>Update your personal information</p>
        </div>

    @include('livewire.tenant.student.profile.profile-navbar', ['student' => $student])

    {{-- ══ ACADEMIC DETAILS (read-only) ══ --}}
    <div class="form-section" style="padding-top:40px">
        <div class="section-heading">
            <span class="material-icons-round">school</span> Academic Details
        </div>
        <div class="row g-4">
            <div class="col-md-3">
                <div class="input-group input-group-outline">
                    <label class="form-label">Academic Year</label>
                    <input type="text" class="form-control" value="{{ $student->session->name ?? '—' }}" disabled onfocus="focused(this)" onfocusout="defocused(this)">
                </div>
            </div>
            <div class="col-md-3">
                <div class="input-group input-group-outline">
                    <label class="form-label">Register No</label>
                    <input type="text" class="form-control" value="{{ $register_no }}" disabled onfocus="focused(this)" onfocusout="defocused(this)">
                </div>
            </div>
            <div class="col-md-3">
                <div class="input-group input-group-outline">
                    <label class="form-label">Roll</label>
                    <input type="text" class="form-control" value="{{ $roll_no ?? '—' }}" disabled onfocus="focused(this)" onfocusout="defocused(this)">
                </div>
            </div>
            <div class="col-md-3">
                <div class="input-group input-group-outline">
                    <label class="form-label">Admission Date</label>
                    <input type="text" class="form-control" value="{{ $admission_date ? \Carbon\Carbon::parse($admission_date)->format('d M Y') : '—' }}" disabled onfocus="focused(this)" onfocusout="defocused(this)">
                </div>
            </div>
            <div class="col-md-4">
                <div class="input-group input-group-outline">
                    <label class="form-label">Class</label>
                    <input type="text" class="form-control" value="{{ $student->class->name ?? '—' }}" disabled onfocus="focused(this)" onfocusout="defocused(this)">
                </div>
            </div>
            <div class="col-md-4">
                <div class="input-group input-group-outline">
                    <label class="form-label">Section</label>
                    <input type="text" class="form-control" value="{{ $student->section->name ?? '—' }}" disabled onfocus="focused(this)" onfocusout="defocused(this)">
                </div>
            </div>
            <div class="col-md-4">
                <div class="input-group input-group-outline">
                    <label class="form-label">Category</label>
                    <input type="text" class="form-control" value="{{ $student->category->name ?? '—' }}" disabled onfocus="focused(this)" onfocusout="defocused(this)">
                </div>
            </div>
        </div>
    </div>

    {{-- ══ STUDENT DETAILS ══ --}}
    <div class="form-section">
        <div class="section-heading">
            <span class="material-icons-round">person</span> Personal Details
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
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                        <option value="other">Other</option>
                    </select>
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
                </div>
            </div>
            <div class="col-md-6">
                <div class="input-group input-group-outline" wire:ignore>
                    <label class="form-label">Date Of Birth</label>
                    <input type="date" wire:model="dob" data-dp-value="{{ $dob }}" class="form-control" onfocus="focused(this)" onfocusout="defocused(this)">
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
                <label style="font-size:.73rem;font-weight:600;color:var(--muted);display:block;margin-bottom:8px">Photo</label>
                <div class="photo-upload-box">
                    @if($student_photo_upload)
                        @if($this->safePreviewUrl($student_photo_upload))
                            <img src="{{ $this->safePreviewUrl($student_photo_upload) }}" alt="Preview"
                                 style="max-height:80px;max-width:100%;object-fit:contain;margin-bottom:6px">
                        @else
                            <span class="material-icons-round">check_circle</span>
                            <span class="lbl">File selected</span>
                        @endif
                    @elseif($student_photo)
                        <img src="{{ asset($student_photo) }}" alt="Photo"
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

    {{-- ══ PREVIOUS SCHOOL DETAILS ══ --}}
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
            </div>
            <div class="col-md-6">
                <div class="input-group input-group-outline">
                    <label class="form-label">Qualification</label>
                    <textarea wire:model="qualification" class="form-control" placeholder=" " style="min-height:64px" onfocus="focused(this)" onfocusout="defocused(this)"></textarea>
                </div>
            </div>
            <div class="col-12">
                <div class="input-group input-group-outline">
                    <label class="form-label">Remarks</label>
                    <textarea wire:model="remarks" class="form-control" placeholder=" " style="min-height:80px" onfocus="focused(this)" onfocusout="defocused(this)"></textarea>
                </div>
            </div>
        </div>
    </div>

    {{-- Form Footer --}}
    <div class="form-footer">
        <button type="button" class="btn-outline" onclick="history.back()">
            <span class="material-icons-round" style="font-size:16px">arrow_back</span> Back
        </button>
        <button class="btn-pink" type="button" wire:click="update" wire:loading.attr="disabled" wire:target="update">
            <span wire:loading.remove wire:target="update" style="display:inline-flex;align-items:center;gap:6px">
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

            Livewire.on('date-updated', function(event) {
                var input = document.querySelector('.input-group-outline input[type="date"]');
                if (!input) return;
                var newDate = event.date || '';
                if (newDate) {
                    input.value = newDate;
                    input.dataset.dpValue = newDate;
                    if (input._dpTriggerSync) input._dpTriggerSync(newDate);
                }
            });
        }
    });
</script>
@endpush
@push('styles')
<style>
    :root {
        --primary: rgba(33, 37, 41);
        --primary-light: rgba(239,84,84,.12);
    }
    .card { border: 1px solid var(--border); border-radius: 12px; box-shadow: 0 1px 4px rgba(0,0,0,.04); }
    .card-header { background: #fff; border-bottom: 1px solid var(--border); border-radius: 12px 12px 0 0 !important; padding: 16px 20px; }
</style>
@endpush