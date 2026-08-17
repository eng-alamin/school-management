<div>
    <div class="container wizard-wrapper mt-5">
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
                            <h4 class="mt-2">
                                <span class="lang-bn">আবেদন সফলভাবে জমা হয়েছে!</span>
                                <span class="lang-en">Application Submitted Successfully!</span>
                            </h4>

                            @if($applicationNo)
                                <p class="text-muted mb-1">
                                    <span class="lang-bn">আপনার আবেদন নম্বর:</span>
                                    <span class="lang-en">Your Application Number:</span>
                                </p>
                                <h3 class="fw-bold text-danger">#{{ $applicationNo }}</h3>
                                <p class="text-muted mt-2">
                                    <span class="lang-bn">এই নম্বরটি লিখে রাখুন — আবেদনের অবস্থা জানতে এটি প্রয়োজন হবে।</span>
                                    <span class="lang-en">Write down this number — you will need it to check the status of your application.</span>
                                </p>
                            @endif

                            <button type="button" class="btn btn-outline-danger mt-3" wire:click="resetForm">
                                <span class="lang-bn">আরেকটি আবেদন জমা দিন</span>
                                <span class="lang-en">Submit Another Application</span>
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

                                    <h2 class="sidebar-title">
                                        <span class="lang-bn">অনলাইন ভর্তি</span>
                                        <span class="lang-en">Online Admission</span>
                                    </h2>

                                    <p class="sidebar-subtitle">
                                        <span class="lang-bn">ভর্তির জন্য ধাপে ধাপে ফর্মটি পূরণ করুন।</span>
                                        <span class="lang-en">Fill up the form step by step to apply for admission.</span>
                                    </p>

                                    <div class="step-list">

                                        <div class="step-item {{ $currentStep >= 1 ? 'active' : '' }} {{ $currentStep > 1 ? 'completed' : '' }}">
                                            <div class="step-circle">1</div>
                                            <div>
                                                <div class="step-title">
                                                    <span class="lang-bn">প্রতিষ্ঠান নির্বাচন</span>
                                                    <span class="lang-en">Select Institution</span>
                                                </div>
                                                <div class="step-desc">
                                                    <span class="lang-bn">প্রতিষ্ঠান খুঁজুন ও নির্বাচন করুন</span>
                                                    <span class="lang-en">Search & choose institution</span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="step-item {{ $currentStep >= 2 ? 'active' : '' }} {{ $currentStep > 2 ? 'completed' : '' }}">
                                            <div class="step-circle">2</div>
                                            <div>
                                                <div class="step-title">
                                                    <span class="lang-bn">একাডেমিক তথ্য</span>
                                                    <span class="lang-en">Academic Details</span>
                                                </div>
                                                <div class="step-desc">
                                                    <span class="lang-bn">সেশন, ক্লাস ও গ্রুপ</span>
                                                    <span class="lang-en">Session, class & group</span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="step-item {{ $currentStep >= 3 ? 'active' : '' }} {{ $currentStep > 3 ? 'completed' : '' }}">
                                            <div class="step-circle">3</div>
                                            <div>
                                                <div class="step-title">
                                                    <span class="lang-bn">শিক্ষার্থীর তথ্য</span>
                                                    <span class="lang-en">Student Details</span>
                                                </div>
                                                <div class="step-desc">
                                                    <span class="lang-bn">ব্যক্তিগত তথ্য</span>
                                                    <span class="lang-en">Personal information</span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="step-item {{ $currentStep >= 4 ? 'active' : '' }} {{ $currentStep > 4 ? 'completed' : '' }}">
                                            <div class="step-circle">4</div>
                                            <div>
                                                <div class="step-title">
                                                    <span class="lang-bn">অভিভাবকের তথ্য</span>
                                                    <span class="lang-en">Guardian Details</span>
                                                </div>
                                                <div class="step-desc">
                                                    <span class="lang-bn">পিতা-মাতা/অভিভাবকের তথ্য</span>
                                                    <span class="lang-en">Parent/guardian info</span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="step-item {{ $currentStep >= 5 ? 'active' : '' }}">
                                            <div class="step-circle">5</div>
                                            <div>
                                                <div class="step-title">
                                                    <span class="lang-bn">পূর্ববর্তী প্রতিষ্ঠান</span>
                                                    <span class="lang-en">Previous Institution</span>
                                                </div>
                                                <div class="step-desc">
                                                    <span class="lang-bn">ঐচ্ছিক ও চূড়ান্ত জমা</span>
                                                    <span class="lang-en">Optional & final submit</span>
                                                </div>
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

                                            <h2 class="wizard-heading">
                                                <span class="lang-bn">প্রতিষ্ঠান নির্বাচন করুন</span>
                                                <span class="lang-en">Select Institution</span>
                                            </h2>
                                            <p class="wizard-text">
                                                <span class="lang-bn">আপনার প্রতিষ্ঠান খুঁজুন এবং নির্বাচন করুন।</span>
                                                <span class="lang-en">Search for and select your institution.</span>
                                            </p>

                                            @if(!$institution_id)

                                                <div class="mb-2">
                                                    <label class="form-label">
                                                        <span class="lang-bn">প্রতিষ্ঠান খুঁজুন</span>
                                                        <span class="lang-en">Search Institution</span>
                                                        <span class="text-danger">*</span>
                                                    </label>
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
                                                            <span class="lang-bn">"{{ $institutionSearch }}" এর সাথে মিলে এমন কোনো প্রতিষ্ঠান পাওয়া যায়নি।</span>
                                                            <span class="lang-en">No institution found matching "{{ $institutionSearch }}".</span>
                                                        @else
                                                            <span class="lang-bn">কোনো প্রতিষ্ঠান পাওয়া যায়নি।</span>
                                                            <span class="lang-en">No institution available.</span>
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
                                                            <div class="text-muted small">
                                                                <span class="lang-bn">নির্বাচিত প্রতিষ্ঠান</span>
                                                                <span class="lang-en">Selected Institution</span>
                                                            </div>
                                                            <div class="fw-bold">{{ $institutionSearch }}</div>
                                                        </div>
                                                    </div>
                                                    <button type="button" wire:click="changeInstitution" class="btn btn-light-custom btn-sm">
                                                        <i class="bi bi-arrow-repeat me-1"></i>
                                                        <span class="lang-bn">পরিবর্তন করুন</span>
                                                        <span class="lang-en">Change</span>
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

                                            <h2 class="wizard-heading">
                                                <span class="lang-bn">একাডেমিক তথ্য</span>
                                                <span class="lang-en">Academic Details</span>
                                            </h2>
                                            <p class="wizard-text">
                                                <span class="lang-bn">সেশন, ক্লাস এবং গ্রুপ (যদি থাকে) নির্বাচন করুন</span>
                                                <span class="lang-en">Select session, class, and group (if any)</span>
                                                @if($is_new !== false)
                                                    <span class="badge bg-light text-dark border ms-1">
                                                        @if($is_new)
                                                            <span class="lang-bn">নতুন শিক্ষার্থী</span>
                                                            <span class="lang-en">New Student</span>
                                                        @else
                                                            <span class="lang-bn">পুরাতন শিক্ষার্থী</span>
                                                            <span class="lang-en">Existing Student</span>
                                                        @endif
                                                    </span>
                                                @endif
                                            </p>

                                            <div class="row g-3">
                                                <div class="col-md-4">
                                                    <label class="form-label">
                                                        <span class="lang-bn">শিক্ষাবর্ষ</span>
                                                        <span class="lang-en">Academic Year</span>
                                                        <span class="text-danger">*</span>
                                                    </label>
                                                    <select wire:model="session_id" class="selectpicker" @error('session_id') is-invalid @enderror">
                                                        <option value="">Select</option>
                                                        @foreach($sessions as $session)
                                                            <option value="{{ $session->id }}"@if($session->is_current === true) selected @endif>{{ $session->name }}</option>
                                                        @endforeach
                                                    </select>
                                                    @error('session_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label">
                                                        <span class="lang-bn">ক্লাস</span>
                                                        <span class="lang-en">Class</span>
                                                        <span class="text-danger">*</span>
                                                    </label>
                                                    <select wire:model="class_id" class="selectpicker @error('class_id') is-invalid @enderror">
                                                        <option value="">Select Class</option>
                                                        @foreach($classes as $class)
                                                            <option value="{{ $class->id }}">{{ $class->name }}</option>
                                                        @endforeach
                                                    </select>
                                                    @error('class_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label">
                                                        <span class="lang-bn">গ্রুপ</span>
                                                        <span class="lang-en">Group</span>
                                                    </label>
                                                    <select wire:model="group_id" class="selectpicker">
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

                                            <h2 class="wizard-heading">
                                                <span class="lang-bn">শিক্ষার্থীর তথ্য</span>
                                                <span class="lang-en">Student Details</span>
                                            </h2>
                                            <p class="wizard-text">
                                                <span class="lang-bn">শিক্ষার্থীর ব্যক্তিগত তথ্য প্রদান করুন।</span>
                                                <span class="lang-en">Enter the student's personal information.</span>
                                            </p>

                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <label class="form-label">
                                                        <span class="lang-bn">পূর্ণ নাম</span>
                                                        <span class="lang-en">Full Name</span>
                                                        <span class="text-danger">*</span>
                                                    </label>
                                                    <input type="text" wire:model="name" class="form-control @error('name') is-invalid @enderror">
                                                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label">
                                                        <span class="lang-bn">লিঙ্গ</span>
                                                        <span class="lang-en">Gender</span>
                                                    </label>
                                                    <select wire:model="gender" class="selectpicker">
                                                        <option value="male">Male</option>
                                                        <option value="female">Female</option>
                                                        <option value="other">Other</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label">
                                                        <span class="lang-bn">রক্তের গ্রুপ</span>
                                                        <span class="lang-en">Blood Group</span>
                                                    </label>
                                                    <select wire:model="blood_group" class="selectpicker">
                                                        <option value="">Select</option>
                                                        @foreach(['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $bg)
                                                            <option value="{{ $bg }}">{{ $bg }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label">
                                                        <span class="lang-bn">জন্ম তারিখ</span>
                                                        <span class="lang-en">Date of Birth</span>
                                                    </label>
                                                    <input type="date" wire:model="dob" class="form-control @error('dob') is-invalid @enderror">
                                                    @error('dob') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label">
                                                        <span class="lang-bn">ধর্ম</span>
                                                        <span class="lang-en">Religion</span>
                                                    </label>
                                                    <select wire:model="religion" class="selectpicker">
                                                        <option value="">Select</option>
                                                        <option value="muslim">Muslim</option>
                                                        <option value="hindu">Hindu</option>
                                                        <option value="christian">Christian</option>
                                                        <option value="buddhist">Buddhist</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label">
                                                        <span class="lang-bn">মোবাইল নম্বর</span>
                                                        <span class="lang-en">Mobile No</span>
                                                    </label>
                                                    <input type="tel" wire:model="mobile" class="form-control @error('mobile') is-invalid @enderror" placeholder="01XXXXXXXXX">
                                                    @error('mobile') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">
                                                        <span class="lang-bn">ইমেইল</span>
                                                        <span class="lang-en">Email</span>
                                                    </label>
                                                    <input type="email" wire:model="email" class="form-control @error('email') is-invalid @enderror">
                                                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">
                                                        <span class="lang-bn">ছবি</span>
                                                        <span class="lang-en">Photo</span>
                                                    </label>
                                                    <label class="upload-box w-100 d-block">
                                                        <input type="file" class="d-none" wire:model="student_photo_upload" accept="image/*">
                                                        @if($student_photo_upload)
                                                            <img src="{{ $student_photo_upload->temporaryUrl() }}" class="rounded" style="max-height:70px">
                                                            <div class="mt-2 text-success fw-bold small">
                                                                <i class="bi bi-check-circle-fill me-1"></i>
                                                                <span class="lang-bn">ছবি আপলোড হয়েছে</span>
                                                                <span class="lang-en">Photo uploaded</span>
                                                            </div>
                                                        @else
                                                            <i class="bi bi-cloud-arrow-up-fill fs-2 text-danger"></i>
                                                            <div class="mt-2 fw-bold small">
                                                                <span class="lang-bn">শিক্ষার্থীর ছবি আপলোড করুন</span>
                                                                <span class="lang-en">Upload Student Photo</span>
                                                            </div>
                                                        @endif
                                                    </label>
                                                    @error('student_photo_upload') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">
                                                        <span class="lang-bn">বর্তমান ঠিকানা</span>
                                                        <span class="lang-en">Present Address</span>
                                                    </label>
                                                    <textarea wire:model="present_address" class="form-control"></textarea>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">
                                                        <span class="lang-bn">স্থায়ী ঠিকানা</span>
                                                        <span class="lang-en">Permanent Address</span>
                                                    </label>
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

                                            <h2 class="wizard-heading">
                                                <span class="lang-bn">অভিভাবকের তথ্য</span>
                                                <span class="lang-en">Guardian Details</span>
                                            </h2>
                                            <p class="wizard-text">
                                                <span class="lang-bn">অভিভাবক / পিতা-মাতার তথ্য দিন।</span>
                                                <span class="lang-en">Guardian / Parent er information din.</span>
                                            </p>

                                            {{-- ── "Guardian Already Exist" toggle ── --}}
                                            <div class="guardian-toggle-box">
                                                <div class="form-check form-switch mb-0">
                                                    <input wire:model.live="guardian_exists" class="form-check-input" type="checkbox" id="guardianExist" role="switch">
                                                    <label class="form-check-label fw-semibold" for="guardianExist">
                                                        <span class="lang-bn">অভিভাবক ইতিমধ্যে বিদ্যমান</span>
                                                        <span class="lang-en">Guardian Already Exist</span>
                                                    </label>
                                                </div>
                                                <div class="text-muted small mt-1">
                                                    <span class="lang-bn">এই অভিভাবকের অন্য কোনো সন্তান যদি আগে থেকেই কোনো প্রতিষ্ঠানে (এখানে বা অন্য কোথাও) ভর্তি হয়ে থাকে, তাহলে টগল চালু করে খুঁজুন।</span>
                                                    <span class="lang-en">If any other child of this guardian has already taken admission in any institution (either here or at any other institution), toggle on and search.</span>
                                                </div>
                                            </div>

                                            @if($guardian_exists)

                                                {{-- ══ EXISTING GUARDIAN — GLOBAL SEARCH & SELECT ══ --}}
                                                <div wire:key="guardian-existing">

                                                    @if(!$guardian_user_id)

                                                        <div class="mb-2">
                                                            <label class="form-label">
                                                                <span class="lang-bn">অভিভাবক খুঁজুন (সকল প্রতিষ্ঠান)</span>
                                                                <span class="lang-en">Search Guardian (All Schools)</span>
                                                                <span class="text-danger">*</span>
                                                            </label>
                                                            <input type="text"
                                                                   wire:model.live.debounce.300ms="guardianSearch"
                                                                   class="form-control @error('guardian_user_id') is-invalid @enderror"
                                                                   placeholder="Search by name or email...">
                                                            @error('guardian_user_id')
                                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                                            @enderror
                                                        </div>

                                                        @if($guardianResults->count())
                                                            <div class="list-group institution-search-list">
                                                                @foreach($guardianResults as $g)
                                                                    <button type="button"
                                                                            wire:click="selectGuardian({{ $g->user_id }})"
                                                                            class="list-group-item list-group-item-action d-flex align-items-center gap-2">
                                                                        <i class="bi bi-person-fill text-danger"></i>
                                                                        <span>
                                                                            <span class="fw-semibold d-block">{{ $g->name }}</span>
                                                                            <span class="text-muted small">
                                                                                {{ $g->email ?? 'Unknown' }}
                                                                            </span>
                                                                            @if($g->institution)
                                                                                <span class="guardian-school-badge">
                                                                                    <i class="bi bi-building"></i> {{ $g->institution->name }}
                                                                                </span>
                                                                            @endif
                                                                        </span>
                                                                    </button>
                                                                @endforeach
                                                            </div>
                                                        @else
                                                            <div class="text-muted small mt-2">
                                                                @if(strlen($guardianSearch) > 0)
                                                                    <span class="lang-bn">"{{ $guardianSearch }}" এর সাথে মিলে এমন কোনো অভিভাবক পাওয়া যায়নি।</span>
                                                                    <span class="lang-en">No guardian found matching "{{ $guardianSearch }}".</span>
                                                                @else
                                                                    <span class="lang-bn">সকল প্রতিষ্ঠান জুড়ে খুঁজতে অভিভাবকের নাম বা ইমেইল লিখুন।</span>
                                                                    <span class="lang-en">Type guardian's name or email to search across all schools.</span>
                                                                @endif
                                                            </div>
                                                        @endif

                                                    @else

                                                        <div class="summary-box d-flex justify-content-between align-items-center">
                                                            <div class="d-flex align-items-center gap-3">
                                                                <div class="brand-logo mb-0" style="background:#fee2e2;color:var(--primary);width:50px;height:50px;font-size:20px;">
                                                                    <i class="bi bi-person-fill"></i>
                                                                </div>
                                                                <div>
                                                                    <div class="text-muted small">
                                                                        <span class="lang-bn">নির্বাচিত অভিভাবক</span>
                                                                        <span class="lang-en">Selected Guardian</span>
                                                                    </div>
                                                                    <div class="fw-bold">{{ $guardianSearch }}</div>
                                                                    <div class="text-muted small">
                                                                        <span class="lang-bn">এই লগইন এই প্রতিষ্ঠানের জন্যও পুনরায় ব্যবহার করা হবে।</span>
                                                                        <span class="lang-en">This login will be reused for this school too.</span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <button type="button" wire:click="changeGuardian" class="btn btn-light-custom btn-sm">
                                                                <i class="bi bi-arrow-repeat me-1"></i>
                                                                <span class="lang-bn">পরিবর্তন করুন</span>
                                                                <span class="lang-en">Change</span>
                                                            </button>
                                                        </div>

                                                    @endif

                                                </div>

                                            @else

                                                {{-- ══ NEW GUARDIAN — FULL FORM ══ --}}
                                                <div class="row g-3" wire:key="guardian-new">
                                                    <div class="col-md-6">
                                                        <label class="form-label">
                                                            <span class="lang-bn">অভিভাবকের নাম</span>
                                                            <span class="lang-en">Guardian Name</span>
                                                            <span class="text-danger">*</span>
                                                        </label>
                                                        <input type="text" wire:model="guardian_name" class="form-control @error('guardian_name') is-invalid @enderror">
                                                        @error('guardian_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">
                                                            <span class="lang-bn">সম্পর্ক</span>
                                                            <span class="lang-en">Relation</span>
                                                            <span class="text-danger">*</span>
                                                        </label>
                                                        <input type="text" wire:model="guardian_relation" class="form-control @error('guardian_relation') is-invalid @enderror" placeholder="e.g. Father, Mother">
                                                        @error('guardian_relation') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">
                                                            <span class="lang-bn">পিতার নাম</span>
                                                            <span class="lang-en">Father's Name</span>
                                                        </label>
                                                        <input type="text" wire:model="guardian_father_name" class="form-control">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">
                                                            <span class="lang-bn">মাতার নাম</span>
                                                            <span class="lang-en">Mother's Name</span>
                                                        </label>
                                                        <input type="text" wire:model="guardian_mother_name" class="form-control">
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label">
                                                            <span class="lang-bn">পেশা</span>
                                                            <span class="lang-en">Occupation</span>
                                                        </label>
                                                        <input type="text" wire:model="guardian_occupation" class="form-control">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">
                                                            <span class="lang-bn">অভিভাবকের মোবাইল</span>
                                                            <span class="lang-en">Guardian Mobile</span>
                                                            <span class="text-danger">*</span>
                                                        </label>
                                                        <input type="tel" wire:model="guardian_mobile" class="form-control @error('guardian_mobile') is-invalid @enderror" placeholder="01XXXXXXXXX">
                                                        @error('guardian_mobile') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">
                                                            <span class="lang-bn">অভিভাবকের ইমেইল</span>
                                                            <span class="lang-en">Guardian Email</span>
                                                            <span class="text-danger">*</span>
                                                        </label>
                                                        <input type="email" wire:model="guardian_email" class="form-control @error('guardian_email') is-invalid @enderror">
                                                        @error('guardian_email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">
                                                            <span class="lang-bn">ঠিকানা</span>
                                                            <span class="lang-en">Address</span>
                                                        </label>
                                                        <textarea wire:model="guardian_address" class="form-control"></textarea>
                                                    </div>
                                                </div>

                                            @endif

                                        </div>

                                    @endif

                                    {{-- =====================================================
                                        STEP 5 — Previous Institution (Final)
                                    ====================================================== --}}

                                    @if($currentStep === 5)

                                        <div wire:key="step-5">

                                            <h2 class="wizard-heading">
                                                <span class="lang-bn">পূর্ববর্তী প্রতিষ্ঠান</span>
                                                <span class="lang-en">Previous Institution</span>
                                            </h2>
                                            <p class="wizard-text">
                                                <span class="lang-bn">ঐচ্ছিক — খালি ঘরটি পূরণ করুন, অথবা খালি রাখুন।</span>
                                                <span class="lang-en">Optional — Fill in the blank, or leave the blank.</span>
                                            </p>

                                            <div class="row g-3 mb-3">
                                                <div class="col-md-6">
                                                    <label class="form-label">
                                                        <span class="lang-bn">প্রতিষ্ঠানের নাম</span>
                                                        <span class="lang-en">Institution Name</span>
                                                    </label>
                                                    <textarea wire:model="previous_institution" class="form-control"></textarea>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">
                                                        <span class="lang-bn">যোগ্যতা</span>
                                                        <span class="lang-en">Qualification</span>
                                                    </label>
                                                    <textarea wire:model="qualification" class="form-control"></textarea>
                                                </div>
                                            </div>

                                            <div class="summary-box">
                                                <h6 class="fw-bold mb-3">
                                                    <i class="bi bi-clipboard-check text-danger me-2"></i>
                                                    <span class="lang-bn">দ্রুত পর্যালোচনা</span>
                                                    <span class="lang-en">Quick Review</span>
                                                </h6>
                                                <div class="d-flex justify-content-between py-2 border-bottom">
                                                    <span class="text-muted">
                                                        <span class="lang-bn">প্রতিষ্ঠান</span>
                                                        <span class="lang-en">Institution</span>
                                                    </span>
                                                    <span class="fw-semibold">{{ $institutionSearch }}</span>
                                                </div>
                                                <div class="d-flex justify-content-between py-2 border-bottom">
                                                    <span class="text-muted">
                                                        <span class="lang-bn">ভর্তির ধরন</span>
                                                        <span class="lang-en">Admission Type</span>
                                                    </span>
                                                    <span class="fw-semibold">
                                                        @if($is_new)
                                                            <span class="lang-bn">নতুন শিক্ষার্থী</span>
                                                            <span class="lang-en">New Student</span>
                                                        @else
                                                            <span class="lang-bn">পুরাতন শিক্ষার্থী</span>
                                                            <span class="lang-en">Existing Student</span>
                                                        @endif
                                                    </span>
                                                </div>
                                                <div class="d-flex justify-content-between py-2 border-bottom">
                                                    <span class="text-muted">
                                                        <span class="lang-bn">শিক্ষার্থীর নাম</span>
                                                        <span class="lang-en">Student Name</span>
                                                    </span>
                                                    <span class="fw-semibold">{{ $name }}</span>
                                                </div>
                                                <div class="d-flex justify-content-between py-2">
                                                    <span class="text-muted">
                                                        <span class="lang-bn">অভিভাবকের নাম</span>
                                                        <span class="lang-en">Guardian Name</span>
                                                    </span>
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
                                                    <i class="bi bi-arrow-left me-1"></i>
                                                    <span class="lang-bn">পূর্ববর্তী</span>
                                                    <span class="lang-en">Previous</span>
                                                </button>
                                            @endif
                                        </div>

                                        <div>
                                            @if($currentStep < 5)
                                                <button type="button" class="btn btn-primary wizard-btn" wire:click="nextStep">
                                                    <span class="lang-bn">এগিয়ে যান</span>
                                                    <span class="lang-en">Continue</span>
                                                    <i class="bi bi-arrow-right ms-2"></i>
                                                </button>
                                            @else
                                                <button type="button" class="btn btn-primary wizard-btn px-4"
                                                        wire:click="submit"
                                                        wire:loading.attr="disabled"
                                                        wire:target="submit">
                                                    <span wire:loading.remove wire:target="submit">
                                                        <i class="bi bi-send-fill me-1"></i>
                                                        <span class="lang-bn">আবেদন জমা দিন</span>
                                                        <span class="lang-en">Submit Application</span>
                                                    </span>
                                                    <span wire:loading wire:target="submit">
                                                        <span class="spinner-border spinner-border-sm me-2"></span>
                                                        <span class="lang-bn">জমা হচ্ছে...</span>
                                                        <span class="lang-en">Submitting...</span>
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

                                        <h5 class="fw-bold text-dark mb-2">
                                            <span class="lang-bn">ভর্তির ধরন?</span>
                                            <span class="lang-en">Admission Type?</span>
                                        </h5>
                                        <p class="text-muted small mb-4">
                                            <span class="lang-bn">এই ভর্তি কি পুরাতন নাকি নতুন শিক্ষার্থীর জন্য?</span>
                                            <span class="lang-en">Is this admission for an existing student or a new student?</span>
                                        </p>

                                        <div class="d-flex gap-2 justify-content-center">
                                            <button type="button" class="btn btn-light-custom wizard-btn flex-fill"
                                                    wire:click="selectAdmissionType(false)">
                                                <span class="lang-bn">পুরাতন</span>
                                                <span class="lang-en">Existing</span>
                                            </button>
                                            <button type="button" class="btn btn-primary wizard-btn flex-fill"
                                                    style="background:#16a34a;border-color:#16a34a;"
                                                    wire:click="selectAdmissionType(true)">
                                                <span class="lang-bn">নতুন</span>
                                                <span class="lang-en">New</span>
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