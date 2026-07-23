<div>
<div class="container admission-wizard-wrapper mt-5">
        <div class="row justify-content-center">
            <div class="col-12 col-xl-11">
                <div class="card wizard-card">

                    @if($alreadyApproved)

                        {{-- ══════════ ALREADY APPROVED — CANNOT EDIT ══════════ --}}
                        <div class="p-5 text-center">
                            <div class="brand-logo mx-auto" style="background:#dbeafe;color:#2563eb;">
                                <i class="bi bi-info-circle-fill"></i>
                            </div>
                            <h4 class="mt-3">This Application Is Already Approved</h4>
                            <p class="text-muted">
                                This admission has already been approved and a student account has been created.
                                It can no longer be edited from here. Please contact the institution office
                                if you need any changes.
                            </p>
                        </div>

                    @elseif($updated_successfully)

                        {{-- ══════════ SUCCESS PANEL ══════════ --}}
                        <div class="p-5 text-center">
                            <div class="success-animation mb-4">
                                <i class="bi bi-check-lg"></i>
                            </div>
                            <h4 class="mt-2">Application Updated Successfully!</h4>
                            <p class="text-muted mt-2">
                                আপনার আবেদনটি আপডেট হয়েছে এবং আবার রিভিউ-এর জন্য পাঠানো হয়েছে।
                            </p>
                        </div>

                    @else

                        <div class="row g-0">

                            {{-- =========================================================
                                SIDEBAR
                            ========================================================== --}}
                            <div class="col-lg-4">
                                <div class="wizard-sidebar h-100">

                                    <div class="brand-logo">
                                        <i class="bi bi-pencil-square"></i>
                                    </div>

                                    <h2 class="sidebar-title">Update Application</h2>

                                    <p class="sidebar-subtitle">
                                        {{ $admission->institution?->name ?? 'Your Institution' }}
                                    </p>

                                    <div class="step-list">

                                        <div class="step-item {{ $currentStep >= 1 ? 'active' : '' }} {{ $currentStep > 1 ? 'completed' : '' }}">
                                            <div class="step-circle">1</div>
                                            <div>
                                                <div class="step-title">Academic Details</div>
                                                <div class="step-desc">Session, class & group</div>
                                            </div>
                                        </div>

                                        <div class="step-item {{ $currentStep >= 2 ? 'active' : '' }} {{ $currentStep > 2 ? 'completed' : '' }}">
                                            <div class="step-circle">2</div>
                                            <div>
                                                <div class="step-title">Student Details</div>
                                                <div class="step-desc">Personal information</div>
                                            </div>
                                        </div>

                                        <div class="step-item {{ $currentStep >= 3 ? 'active' : '' }} {{ $currentStep > 3 ? 'completed' : '' }}">
                                            <div class="step-circle">3</div>
                                            <div>
                                                <div class="step-title">Guardian Details</div>
                                                <div class="step-desc">Parent/guardian info</div>
                                            </div>
                                        </div>

                                        <div class="step-item {{ $currentStep >= 4 ? 'active' : '' }}">
                                            <div class="step-circle">4</div>
                                            <div>
                                                <div class="step-title">Previous Institution</div>
                                                <div class="step-desc">Optional & resubmit</div>
                                            </div>
                                        </div>

                                    </div>

                                </div>
                            </div>

                            {{-- =========================================================
                                CONTENT
                            ========================================================== --}}
                            <div class="col-lg-8">
                                <div class="wizard-content">

                                    <div class="top-progress">
                                        <div class="top-progress-bar"
                                             style="width: {{ match($currentStep){ 1=>'25%', 2=>'50%', 3=>'75%', 4=>'100%' } }}">
                                        </div>
                                    </div>

                                    @if($admission->status === 'rejected')
                                        <div class="status-banner rejected">
                                            <i class="bi bi-exclamation-circle-fill text-danger"></i>
                                            <div>
                                                <div class="fw-bold">Your previous application was rejected</div>
                                                @if($admission->rejection_reason)
                                                    <div class="text-muted small mt-1">Reason: {{ $admission->rejection_reason }}</div>
                                                @endif
                                                <div class="text-muted small mt-1">Please review and update the information below, then resubmit.</div>
                                            </div>
                                        </div>
                                    @elseif($admission->status === 'pending')
                                        <div class="status-banner pending">
                                            <i class="bi bi-hourglass-split text-warning"></i>
                                            <div>
                                                <div class="fw-bold">Your application is currently under review</div>
                                                <div class="text-muted small mt-1">You can still update the information below if needed.</div>
                                            </div>
                                        </div>
                                    @endif

                                    {{-- =====================================================
                                        STEP 1 — Academic Details
                                    ====================================================== --}}
                                    @if($currentStep === 1)
                                        <div wire:key="edit-step-1">
                                            <h2 class="wizard-heading">Academic Details</h2>
                                            <p class="wizard-text">Session, class ebong group update korun.</p>

                                            <div class="row g-3">
                                                <div class="col-md-4">
                                                    <label class="form-label">Academic Year <span class="text-danger">*</span></label>
                                                    <select wire:model="session_id" class="form-select @error('session_id') is-invalid @enderror">
                                                        <option value="">Select</option>
                                                        @foreach($sessions as $session)
                                                            <option value="{{ $session->id }}">{{ $session->name }}</option>
                                                        @endforeach
                                                    </select>
                                                    @error('session_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label">Class <span class="text-danger">*</span></label>
                                                    <select wire:model="class_id" class="form-select @error('class_id') is-invalid @enderror">
                                                        <option value="">Select Class</option>
                                                        @foreach($classes as $class)
                                                            <option value="{{ $class->id }}">{{ $class->name }}</option>
                                                        @endforeach
                                                    </select>
                                                    @error('class_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label">Group</label>
                                                    <select wire:model="group_id" class="form-select">
                                                        <option value="">Select Group</option>
                                                        @foreach($groups as $group)
                                                            <option value="{{ $group->id }}">{{ $group->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    {{-- =====================================================
                                        STEP 2 — Student Details
                                    ====================================================== --}}
                                    @if($currentStep === 2)
                                        <div wire:key="edit-step-2">
                                            <h2 class="wizard-heading">Student Details</h2>
                                            <p class="wizard-text">Student er information update korun.</p>

                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <label class="form-label">Full Name <span class="text-danger">*</span></label>
                                                    <input type="text" wire:model="name" class="form-control @error('name') is-invalid @enderror">
                                                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label">Gender</label>
                                                    <select wire:model="gender" class="form-select">
                                                        <option value="male">Male</option>
                                                        <option value="female">Female</option>
                                                        <option value="other">Other</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label">Blood Group</label>
                                                    <select wire:model="blood_group" class="form-select">
                                                        <option value="">Select</option>
                                                        @foreach(['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $bg)
                                                            <option value="{{ $bg }}">{{ $bg }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label">Date of Birth</label>
                                                    <input type="date" wire:model="dob" class="form-control @error('dob') is-invalid @enderror">
                                                    @error('dob') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label">Religion</label>
                                                    <select wire:model="religion" class="form-select">
                                                        <option value="">Select</option>
                                                        <option value="muslim">Muslim</option>
                                                        <option value="hindu">Hindu</option>
                                                        <option value="christian">Christian</option>
                                                        <option value="buddhist">Buddhist</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label">Mobile No</label>
                                                    <input type="tel" wire:model="mobile" class="form-control @error('mobile') is-invalid @enderror" placeholder="01XXXXXXXXX">
                                                    @error('mobile') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Email</label>
                                                    <input type="email" wire:model="email" class="form-control @error('email') is-invalid @enderror">
                                                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Photo</label>
                                                    <label class="upload-box w-100 d-block">
                                                        <input type="file" class="d-none" wire:model="student_photo_upload" accept="image/*">
                                                        @if($student_photo_upload)
                                                            <img src="{{ $student_photo_upload->temporaryUrl() }}" class="rounded" style="max-height:70px">
                                                            <div class="mt-2 text-success fw-bold small">
                                                                <i class="bi bi-check-circle-fill me-1"></i> New photo selected
                                                            </div>
                                                        @elseif($existing_photo_path)
                                                            <img src="{{ asset('storage/' . $existing_photo_path) }}" class="rounded" style="max-height:70px">
                                                            <div class="mt-2 text-muted small">Click to change photo</div>
                                                        @else
                                                            <i class="bi bi-cloud-arrow-up-fill fs-2 text-danger"></i>
                                                            <div class="mt-2 fw-bold small">Upload Student Photo</div>
                                                        @endif
                                                    </label>
                                                    @error('student_photo_upload') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Present Address</label>
                                                    <textarea wire:model="present_address" class="form-control"></textarea>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Permanent Address</label>
                                                    <textarea wire:model="permanent_address" class="form-control"></textarea>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    {{-- =====================================================
                                        STEP 3 — Guardian Details
                                    ====================================================== --}}
                                    @if($currentStep === 3)
                                        <div wire:key="edit-step-3">
                                            <h2 class="wizard-heading">Guardian Details</h2>
                                            <p class="wizard-text">Guardian er information update korun.</p>

                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <label class="form-label">Guardian Name <span class="text-danger">*</span></label>
                                                    <input type="text" wire:model="guardian_name" class="form-control @error('guardian_name') is-invalid @enderror">
                                                    @error('guardian_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Relation <span class="text-danger">*</span></label>
                                                    <input type="text" wire:model="guardian_relation" class="form-control @error('guardian_relation') is-invalid @enderror">
                                                    @error('guardian_relation') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Father's Name</label>
                                                    <input type="text" wire:model="guardian_father_name" class="form-control">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Mother's Name</label>
                                                    <input type="text" wire:model="guardian_mother_name" class="form-control">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Occupation</label>
                                                    <input type="text" wire:model="guardian_occupation" class="form-control">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Guardian Mobile <span class="text-danger">*</span></label>
                                                    <input type="tel" wire:model="guardian_mobile" class="form-control @error('guardian_mobile') is-invalid @enderror" placeholder="01XXXXXXXXX">
                                                    @error('guardian_mobile') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Guardian Email <span class="text-danger">*</span></label>
                                                    <input type="email" wire:model="guardian_email" class="form-control @error('guardian_email') is-invalid @enderror">
                                                    @error('guardian_email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Address</label>
                                                    <textarea wire:model="guardian_address" class="form-control"></textarea>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    {{-- =====================================================
                                        STEP 4 — Previous Institution (Final)
                                    ====================================================== --}}
                                    @if($currentStep === 4)
                                        <div wire:key="edit-step-4">
                                            <h2 class="wizard-heading">Previous Institution</h2>
                                            <p class="wizard-text">Optional — update korun ba blank rakhun, tarpor resubmit korun.</p>

                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <label class="form-label">Institution Name</label>
                                                    <textarea wire:model="previous_institution" class="form-control"></textarea>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Qualification</label>
                                                    <textarea wire:model="qualification" class="form-control"></textarea>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    {{-- =====================================================
                                        BUTTONS
                                    ====================================================== --}}
                                    <div class="d-flex justify-content-between mt-5">
                                        <div>
                                            @if($currentStep > 1)
                                                <button type="button" class="btn btn-light-custom wizard-btn" wire:click="previousStep">
                                                    <i class="bi bi-arrow-left me-1"></i> Previous
                                                </button>
                                            @endif
                                        </div>

                                        <div>
                                            @if($currentStep < 4)
                                                <button type="button" class="btn btn-primary wizard-btn" wire:click="nextStep">
                                                    Continue <i class="bi bi-arrow-right ms-2"></i>
                                                </button>
                                            @else
                                                <button type="button" class="btn btn-primary wizard-btn px-4"
                                                        wire:click="update"
                                                        wire:loading.attr="disabled"
                                                        wire:target="update">
                                                    <span wire:loading.remove wire:target="update">
                                                        <i class="bi bi-arrow-repeat me-1"></i> Update & Resubmit
                                                    </span>
                                                    <span wire:loading wire:target="update">
                                                        <span class="spinner-border spinner-border-sm me-2"></span>
                                                        Updating...
                                                    </span>
                                                </button>
                                            @endif
                                        </div>
                                    </div>

                                </div>
                            </div>

                        </div>

                    @endif

                </div>
            </div>
        </div>
    </div>

</div>