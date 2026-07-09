<div>

    {{-- =========================================================
        STYLES
    ========================================================== --}}
    <style>
        :root{
            --primary:#ef4444;
            --primary-dark:#dc2626;
            --success:#10b981;
            --danger:#ef4444;
            --light:#f8fafc;
            --border:#e5e7eb;
            --text:#111827;
            --muted:#6b7280;
        }

        .admission-wizard-wrapper{
            padding:40px 0;
        }

        .wizard-card{
            border:none;
            border-radius:30px;
            overflow:hidden;
            background:#ffffff;
            box-shadow:
                0 10px 40px rgba(0,0,0,.08),
                0 2px 8px rgba(0,0,0,.04);
        }

        .wizard-sidebar{
            background:linear-gradient(180deg,#ef4444 0%,#b91c1c 100%);
            color:white;
            padding:50px 40px;
            height:100%;
            position:relative;
            overflow:hidden;
        }

        .wizard-sidebar::before{
            content:'';
            position:absolute;
            width:400px;
            height:400px;
            background:rgba(255,255,255,.08);
            border-radius:50%;
            top:-150px;
            right:-150px;
        }

        .wizard-sidebar::after{
            content:'';
            position:absolute;
            width:250px;
            height:250px;
            background:rgba(255,255,255,.08);
            border-radius:50%;
            bottom:-100px;
            left:-100px;
        }

        .brand-logo{
            width:70px;
            height:70px;
            border-radius:20px;
            background:rgba(255,255,255,.15);
            display:flex;
            align-items:center;
            justify-content:center;
            font-size:28px;
            margin-bottom:25px;
            backdrop-filter:blur(10px);
        }

        .sidebar-title{
            font-size:30px;
            font-weight:800;
            line-height:1.2;
        }

        .sidebar-subtitle{
            opacity:.85;
            margin-top:15px;
            line-height:1.8;
        }

        .step-list{
            margin-top:50px;
            position:relative;
            z-index:2;
        }

        .step-item{
            display:flex;
            align-items:center;
            margin-bottom:25px;
        }

        .step-circle{
            width:44px;
            height:44px;
            border-radius:50%;
            background:rgba(255,255,255,.15);
            display:flex;
            align-items:center;
            justify-content:center;
            font-weight:700;
            margin-right:16px;
            flex-shrink:0;
            transition:.3s;
            border:2px solid rgba(255,255,255,.2);
        }

        .step-item.active .step-circle{
            background:white;
            color:var(--primary);
            transform:scale(1.08);
        }

        .step-item.completed .step-circle{
            background:var(--success);
            border-color:var(--success);
        }

        .step-title{
            font-weight:700;
            margin-bottom:2px;
            font-size:15px;
        }

        .step-desc{
            opacity:.8;
            font-size:12px;
        }

        .wizard-content{
            padding:50px;
        }

        .top-progress{
            height:10px;
            border-radius:30px;
            background:#fee2e2;
            overflow:hidden;
            margin-bottom:40px;
        }

        .top-progress-bar{
            height:100%;
            background:linear-gradient(90deg,#ef4444,#b91c1c);
            border-radius:30px;
            transition:width .4s ease;
        }

        .wizard-heading{
            font-size:28px;
            font-weight:800;
            color:var(--text);
            margin-bottom:8px;
        }

        .wizard-text{
            color:var(--muted);
            margin-bottom:30px;
        }

        .form-label{
            font-weight:700;
            color:#374151;
            margin-bottom:8px;
        }

        .form-control,
        .form-select{
            border-radius:14px;
            min-height:52px;
            border:1px solid var(--border);
            padding-left:16px;
            font-size:15px;
            transition:.25s;
        }

        .form-control:focus,
        .form-select:focus{
            border-color:var(--primary);
            box-shadow:0 0 0 4px rgba(239,68,68,.1);
        }

        .wizard-btn{
            min-height:52px;
            border-radius:14px;
            font-weight:700;
            padding:0 28px;
            transition:.25s;
        }

        .btn-primary{
            background:var(--primary);
            border:none;
        }

        .btn-primary:hover{
            background:var(--primary-dark);
            transform:translateY(-2px);
        }

        .btn-light-custom{
            background:#f3f4f6;
            border:none;
            color:#374151;
        }

        .upload-box{
            border:2px dashed #fecaca;
            border-radius:18px;
            padding:30px;
            text-align:center;
            background:#fff5f5;
            transition:.25s;
            cursor:pointer;
        }

        .upload-box:hover{
            border-color:var(--primary);
            background:#fee2e2;
        }

        .summary-box{
            border-radius:18px;
            background:#f8fafc;
            border:1px solid #eef2f7;
            padding:20px;
        }

        .institution-search-list{
            max-height:260px;
            overflow-y:auto;
            border-radius:14px;
            border:1px solid var(--border);
            margin-top:8px;
        }

        .institution-search-list .list-group-item{
            border:none;
            border-bottom:1px solid #f1f5f9;
            padding:14px 16px;
        }

        .institution-search-list .list-group-item:last-child{
            border-bottom:none;
        }

        .institution-search-list .list-group-item:hover{
            background:#fef2f2;
        }

        .success-animation{
            width:110px;
            height:110px;
            border-radius:50%;
            background:#dcfce7;
            display:flex;
            align-items:center;
            justify-content:center;
            margin:auto;
            font-size:50px;
            color:#16a34a;
            animation:pulse 2s infinite;
        }

        @keyframes pulse{
            0%{transform:scale(1);box-shadow:0 0 0 0 rgba(16,185,129,.4);}
            70%{transform:scale(1.05);box-shadow:0 0 0 20px rgba(16,185,129,0);}
            100%{transform:scale(1);}
        }

        .admission-type-icon{
            width:70px;
            height:70px;
            border-radius:50%;
            background:#dcfce7;
            display:flex;
            align-items:center;
            justify-content:center;
            margin:0 auto 16px;
            font-size:30px;
            color:#16a34a;
        }

        @media(max-width:991px){
            .wizard-content{padding:35px 25px;}
            .wizard-sidebar{padding:35px 25px;}
            .sidebar-title{font-size:24px;}
        }
    </style>

    {{-- =========================================================
        WRAPPER
    ========================================================== --}}

    <div class="container admission-wizard-wrapper mt-5">
        <div class="row justify-content-center">
            <div class="col-12 col-xl-11">
                <div class="card wizard-card">

                    {{-- Anti-spam Honeypot: normal user er kache invisible --}}
                    <div style="position:absolute; left:-9999px" aria-hidden="true">
                        <input type="text" wire:model="website" tabindex="-1" autocomplete="off">
                    </div>

                    @if($submitted)

                        {{-- ══════════ SUCCESS PANEL ══════════ --}}
                        <div class="p-5 text-center">
                            <div class="success-animation mb-4">
                                <i class="bi bi-check-lg"></i>
                            </div>
                            <h4 class="mt-2">Application Submitted Successfully!</h4>

                            @if($applicationNo)
                                <p class="text-muted mb-1">Apnar Application Number:</p>
                                <h3 class="fw-bold text-danger">#{{ $applicationNo }}</h3>
                                <p class="text-muted mt-2">Ei number ta note kore rakhun — application status track korte lagbe.</p>
                            @endif

                            <button type="button" class="btn btn-outline-danger mt-3" wire:click="resetForm">
                                Submit Another Application
                            </button>
                        </div>

                    @else

                        <div class="row g-0">

                            {{-- =========================================================
                                SIDEBAR
                            ========================================================== --}}

                            <div class="col-lg-4">
                                <div class="wizard-sidebar h-100">

                                    <div class="brand-logo">
                                        <i class="bi bi-person-plus-fill"></i>
                                    </div>

                                    <h2 class="sidebar-title">Online Admission</h2>

                                    <p class="sidebar-subtitle">
                                        Fill up the form step by step to apply for admission.
                                    </p>

                                    <div class="step-list">

                                        <div class="step-item {{ $currentStep >= 1 ? 'active' : '' }} {{ $currentStep > 1 ? 'completed' : '' }}">
                                            <div class="step-circle">1</div>
                                            <div>
                                                <div class="step-title">Select Institution</div>
                                                <div class="step-desc">Search & choose institution</div>
                                            </div>
                                        </div>

                                        <div class="step-item {{ $currentStep >= 2 ? 'active' : '' }} {{ $currentStep > 2 ? 'completed' : '' }}">
                                            <div class="step-circle">2</div>
                                            <div>
                                                <div class="step-title">Academic Details</div>
                                                <div class="step-desc">Session, class & group</div>
                                            </div>
                                        </div>

                                        <div class="step-item {{ $currentStep >= 3 ? 'active' : '' }} {{ $currentStep > 3 ? 'completed' : '' }}">
                                            <div class="step-circle">3</div>
                                            <div>
                                                <div class="step-title">Student Details</div>
                                                <div class="step-desc">Personal information</div>
                                            </div>
                                        </div>

                                        <div class="step-item {{ $currentStep >= 4 ? 'active' : '' }} {{ $currentStep > 4 ? 'completed' : '' }}">
                                            <div class="step-circle">4</div>
                                            <div>
                                                <div class="step-title">Guardian Details</div>
                                                <div class="step-desc">Parent/guardian info</div>
                                            </div>
                                        </div>

                                        <div class="step-item {{ $currentStep >= 5 ? 'active' : '' }}">
                                            <div class="step-circle">5</div>
                                            <div>
                                                <div class="step-title">Previous Institution</div>
                                                <div class="step-desc">Optional & final submit</div>
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
                                        <div
                                            class="top-progress-bar"
                                            style="width: {{ match($currentStep){ 1=>'20%', 2=>'40%', 3=>'60%', 4=>'80%', 5=>'100%' } }}"
                                        ></div>
                                    </div>

                                    {{-- =====================================================
                                        STEP 1 — Select Institution (Search System)
                                    ====================================================== --}}

                                    @if($currentStep === 1)

                                        <div wire:key="step-1">

                                            <h2 class="wizard-heading">Select Institution</h2>
                                            <p class="wizard-text">Apnar institution ta search kore select korun.</p>

                                            @if(!$institution_id)

                                                <div class="mb-2">
                                                    <label class="form-label">Search Institution <span class="text-danger">*</span></label>
                                                    <input type="text"
                                                           wire:model.live.debounce.300ms="institutionSearch"
                                                           class="form-control @error('institution_id') is-invalid @enderror"
                                                           placeholder="Type institution name to search...">
                                                    @error('institution_id')
                                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                                @if($institutionResults->count())
                                                    <div class="list-group institution-search-list">
                                                        @foreach($institutionResults as $inst)
                                                            <button type="button"
                                                                    wire:click="selectInstitution({{ $inst->id }})"
                                                                    class="list-group-item list-group-item-action d-flex align-items-center gap-2">
                                                                <i class="bi bi-building text-danger"></i>
                                                                <span class="fw-semibold">{{ $inst->name }}</span>
                                                            </button>
                                                        @endforeach
                                                    </div>
                                                @else
                                                    <div class="text-muted small mt-2">
                                                        @if(strlen($institutionSearch) > 0)
                                                            No institution found matching "{{ $institutionSearch }}".
                                                        @else
                                                            No institution available.
                                                        @endif
                                                    </div>
                                                @endif

                                            @else

                                                <div class="summary-box d-flex justify-content-between align-items-center">
                                                    <div class="d-flex align-items-center gap-3">
                                                        <div class="brand-logo mb-0" style="background:#fee2e2;color:var(--primary);width:50px;height:50px;font-size:20px;">
                                                            <i class="bi bi-building"></i>
                                                        </div>
                                                        <div>
                                                            <div class="text-muted small">Selected Institution</div>
                                                            <div class="fw-bold">{{ $institutionSearch }}</div>
                                                        </div>
                                                    </div>
                                                    <button type="button" wire:click="changeInstitution" class="btn btn-light-custom btn-sm">
                                                        <i class="bi bi-arrow-repeat me-1"></i> Change
                                                    </button>
                                                </div>

                                            @endif

                                        </div>

                                    @endif

                                    {{-- =====================================================
                                        STEP 2 — Academic Details
                                    ====================================================== --}}

                                    @if($currentStep === 2)

                                        <div wire:key="step-2">

                                            <h2 class="wizard-heading">Academic Details</h2>
                                            <p class="wizard-text">
                                                Session, class ebong group (jodi thake) select korun.
                                                @if($is_new !== false)
                                                    <span class="badge bg-light text-dark border ms-1">
                                                        {{ $is_new ? 'New Student' : 'Existing Student' }}
                                                    </span>
                                                @endif
                                            </p>

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
                                        STEP 3 — Student Details
                                    ====================================================== --}}

                                    @if($currentStep === 3)

                                        <div wire:key="step-3">

                                            <h2 class="wizard-heading">Student Details</h2>
                                            <p class="wizard-text">Student er personal information din.</p>

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
                                                                <i class="bi bi-check-circle-fill me-1"></i> Photo uploaded
                                                            </div>
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
                                        STEP 4 — Guardian Details
                                    ====================================================== --}}

                                    @if($currentStep === 4)

                                        <div wire:key="step-4">

                                            <h2 class="wizard-heading">Guardian Details</h2>
                                            <p class="wizard-text">Guardian / Parent er information din.</p>

                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <label class="form-label">Guardian Name <span class="text-danger">*</span></label>
                                                    <input type="text" wire:model="guardian_name" class="form-control @error('guardian_name') is-invalid @enderror">
                                                    @error('guardian_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Relation <span class="text-danger">*</span></label>
                                                    <input type="text" wire:model="guardian_relation" class="form-control @error('guardian_relation') is-invalid @enderror" placeholder="e.g. Father, Mother">
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
                                                <div class="col-md-4">
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
                                        STEP 5 — Previous Institution (Final)
                                    ====================================================== --}}

                                    @if($currentStep === 5)

                                        <div wire:key="step-5">

                                            <h2 class="wizard-heading">Previous Institution</h2>
                                            <p class="wizard-text">Optional — thakle pূরন করুন, na thakle blank rakhte paren.</p>

                                            <div class="row g-3 mb-3">
                                                <div class="col-md-6">
                                                    <label class="form-label">Institution Name</label>
                                                    <textarea wire:model="previous_institution" class="form-control"></textarea>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Qualification</label>
                                                    <textarea wire:model="qualification" class="form-control"></textarea>
                                                </div>
                                            </div>

                                            <div class="summary-box">
                                                <h6 class="fw-bold mb-3"><i class="bi bi-clipboard-check text-danger me-2"></i>Quick Review</h6>
                                                <div class="d-flex justify-content-between py-2 border-bottom">
                                                    <span class="text-muted">Institution</span>
                                                    <span class="fw-semibold">{{ $institutionSearch }}</span>
                                                </div>
                                                <div class="d-flex justify-content-between py-2 border-bottom">
                                                    <span class="text-muted">Admission Type</span>
                                                    <span class="fw-semibold">{{ $is_new ? 'New Student' : 'Existing Student' }}</span>
                                                </div>
                                                <div class="d-flex justify-content-between py-2 border-bottom">
                                                    <span class="text-muted">Student Name</span>
                                                    <span class="fw-semibold">{{ $name }}</span>
                                                </div>
                                                <div class="d-flex justify-content-between py-2">
                                                    <span class="text-muted">Guardian Name</span>
                                                    <span class="fw-semibold">{{ $guardian_name }}</span>
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
                                            @if($currentStep < 5)
                                                <button type="button" class="btn btn-primary wizard-btn" wire:click="nextStep">
                                                    Continue <i class="bi bi-arrow-right ms-2"></i>
                                                </button>
                                            @else
                                                <button type="button" class="btn btn-primary wizard-btn px-4"
                                                        wire:click="submit"
                                                        wire:loading.attr="disabled"
                                                        wire:target="submit">
                                                    <span wire:loading.remove wire:target="submit">
                                                        <i class="bi bi-send-fill me-1"></i> Submit Application
                                                    </span>
                                                    <span wire:loading wire:target="submit">
                                                        <span class="spinner-border spinner-border-sm me-2"></span>
                                                        Submitting...
                                                    </span>
                                                </button>
                                            @endif
                                        </div>

                                    </div>

                                </div>
                            </div>

                        </div>

                    @endif

                    {{-- =========================================================
                        ADMISSION TYPE MODAL (Step 1 -> Step 2 transition)
                    ========================================================== --}}

                    @if($showAdmissionTypeModal)
                        <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,.5);">
                            <div class="modal-dialog modal-dialog-centered modal-sm">
                                <div class="modal-content">
                                    <div class="modal-body text-center py-4 px-4">
                                        <button type="button" class="btn-close position-absolute" style="top:16px;right:16px"
                                                wire:click="closeAdmissionTypeModal"></button>

                                        <div class="admission-type-icon">
                                            <i class="bi bi-person-check-fill"></i>
                                        </div>

                                        <h5 class="fw-bold text-dark mb-2">Admission Type?</h5>
                                        <p class="text-muted small mb-4">
                                            Is this admission for an existing student or a new student?
                                        </p>

                                        <div class="d-flex gap-2 justify-content-center">
                                            <button type="button" class="btn btn-light-custom wizard-btn flex-fill"
                                                    wire:click="selectAdmissionType(false)">
                                                Existing
                                            </button>
                                            <button type="button" class="btn btn-primary wizard-btn flex-fill"
                                                    style="background:#16a34a;border-color:#16a34a;"
                                                    wire:click="selectAdmissionType(true)">
                                                New
                                            </button>
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

@push('scripts')
<script>
    document.addEventListener('livewire:initialized', () => {
        Livewire.on('date-updated', function (event) {
            var input = document.querySelector('input[type="date"][wire\\:model="dob"]');
            if (input && event.date) {
                input.value = event.date;
            }
        });
    });
</script>
@endpush