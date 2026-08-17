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
                                <span class="lang-bn">নিবন্ধন সফলভাবে জমা হয়েছে!</span>
                                <span class="lang-en">Registration Submitted Successfully!</span>
                            </h4>

                            @if($employeeIdRef)
                                <p class="text-muted mb-1">
                                    <span class="lang-bn">আপনার কর্মচারী আইডি:</span>
                                    <span class="lang-en">Your Employee ID:</span>
                                </p>
                                <h3 class="fw-bold text-danger">#{{ $employeeIdRef }}</h3>
                                <p class="text-muted mt-2">
                                    <span class="lang-bn">এই আইডিটি লিখে রাখুন — আপনার ইউজারনেমের সাথে এটি লগইন করতে ব্যবহার করতে পারবেন।</span>
                                    <span class="lang-en">Write down this ID — you can use it, along with your username, to log in.</span>
                                </p>
                            @endif

                            <button type="button" class="btn btn-outline-danger mt-3" wire:click="resetForm">
                                <span class="lang-bn">আরেকটি নিবন্ধন জমা দিন</span>
                                <span class="lang-en">Submit Another Registration</span>
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
                                        <i class="bi bi-person-workspace"></i>
                                    </div>

                                    <h2 class="sidebar-title">
                                        <span class="lang-bn">শিক্ষক নিবন্ধন</span>
                                        <span class="lang-en">Teacher Registration</span>
                                    </h2>

                                    <p class="sidebar-subtitle">
                                        <span class="lang-bn">শিক্ষক হিসেবে নিবন্ধনের জন্য ধাপে ধাপে ফর্মটি পূরণ করুন।</span>
                                        <span class="lang-en">Fill up the form step by step to register as a teacher.</span>
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
                                                    <span class="lang-bn">কর্মসংক্রান্ত তথ্য</span>
                                                    <span class="lang-en">Job Details</span>
                                                </div>
                                                <div class="step-desc">
                                                    <span class="lang-bn">অভিজ্ঞতা ও যোগদান</span>
                                                    <span class="lang-en">Experience & joining</span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="step-item {{ $currentStep >= 3 ? 'active' : '' }} {{ $currentStep > 3 ? 'completed' : '' }}">
                                            <div class="step-circle">3</div>
                                            <div>
                                                <div class="step-title">
                                                    <span class="lang-bn">ব্যক্তিগত তথ্য</span>
                                                    <span class="lang-en">Personal Details</span>
                                                </div>
                                                <div class="step-desc">
                                                    <span class="lang-bn">আপনার ব্যক্তিগত তথ্য</span>
                                                    <span class="lang-en">Your personal information</span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="step-item {{ $currentStep >= 4 ? 'active' : '' }} {{ $currentStep > 4 ? 'completed' : '' }}">
                                            <div class="step-circle">4</div>
                                            <div>
                                                <div class="step-title">
                                                    <span class="lang-bn">লগইন তথ্য</span>
                                                    <span class="lang-en">Login Details</span>
                                                </div>
                                                <div class="step-desc">
                                                    <span class="lang-bn">ইউজারনেম ও পাসওয়ার্ড</span>
                                                    <span class="lang-en">Username & password</span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="step-item {{ $currentStep >= 5 ? 'active' : '' }}">
                                            <div class="step-circle">5</div>
                                            <div>
                                                <div class="step-title">
                                                    <span class="lang-bn">ব্যাংক তথ্য</span>
                                                    <span class="lang-en">Bank Details</span>
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
                                                <span class="lang-bn">আপনি যে প্রতিষ্ঠানে পড়াতে চান তা খুঁজুন এবং নির্বাচন করুন।</span>
                                                <span class="lang-en">Search for and select the institution you want to teach at.</span>
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
                                        STEP 2 — Job Details
                                    ====================================================== --}}

                                    @if($currentStep === 2)

                                        <div wire:key="step-2">

                                            <h2 class="wizard-heading">
                                                <span class="lang-bn">কর্মসংক্রান্ত তথ্য</span>
                                                <span class="lang-en">Job Details</span>
                                            </h2>
                                            <p class="wizard-text">
                                                <span class="lang-bn">আপনার অভিজ্ঞতা এবং যোগদানের তারিখ প্রদান করুন।</span>
                                                <span class="lang-en">Enter your experience and joining date.</span>
                                            </p>

                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <label class="form-label">
                                                        <span class="lang-bn">যোগদানের তারিখ</span>
                                                        <span class="lang-en">Joining Date</span>
                                                        <span class="text-danger">*</span>
                                                    </label>
                                                    <input type="date" wire:model="joining_date" class="form-control @error('joining_date') is-invalid @enderror">
                                                    @error('joining_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">
                                                        <span class="lang-bn">যোগ্যতা</span>
                                                        <span class="lang-en">Qualification</span>
                                                    </label>
                                                    <input type="text" wire:model="qualification" class="form-control">
                                                </div>
                                                <div class="col-md-12">
                                                    <label class="form-label">
                                                        <span class="lang-bn">মোট অভিজ্ঞতা</span>
                                                        <span class="lang-en">Total Experience</span>
                                                    </label>
                                                    <input type="text" wire:model="total_experience" class="form-control" placeholder="e.g. 3 years">
                                                </div>
                                                <div class="col-12">
                                                    <label class="form-label">
                                                        <span class="lang-bn">অভিজ্ঞতার বিবরণ</span>
                                                        <span class="lang-en">Experience Details</span>
                                                    </label>
                                                    <textarea wire:model="experience_detail" class="form-control"></textarea>
                                                </div>
                                            </div>

                                        </div>

                                    @endif

                                    {{-- =====================================================
                                        STEP 3 — Personal Details
                                    ====================================================== --}}

                                    @if($currentStep === 3)

                                        <div wire:key="step-3">

                                            <h2 class="wizard-heading">
                                                <span class="lang-bn">ব্যক্তিগত তথ্য</span>
                                                <span class="lang-en">Personal Details</span>
                                            </h2>
                                            <p class="wizard-text">
                                                <span class="lang-bn">আপনার ব্যক্তিগত তথ্য প্রদান করুন।</span>
                                                <span class="lang-en">Enter your personal information.</span>
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
                                                        <option value="">Select</option>
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
                                                <div class="col-md-3">
                                                    <label class="form-label">
                                                        <span class="lang-bn">জন্ম তারিখ</span>
                                                        <span class="lang-en">Date of Birth</span>
                                                    </label>
                                                    <input type="date" wire:model="dob" class="form-control @error('dob') is-invalid @enderror">
                                                    @error('dob') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                                </div>
                                                <div class="col-md-3">
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
                                                <div class="col-md-6">
                                                    <label class="form-label">
                                                        <span class="lang-bn">মোবাইল নম্বর</span>
                                                        <span class="lang-en">Mobile No</span>
                                                    </label>
                                                    <input type="tel" wire:model="mobile" class="form-control @error('mobile') is-invalid @enderror">
                                                    @error('mobile') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">
                                                        <span class="lang-bn">ইমেইল</span>
                                                        <span class="lang-en">Email</span>
                                                        <span class="text-danger">*</span>
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
                                                        <input type="file" class="d-none" wire:model="photo_upload" accept="image/*">
                                                        @if($photo_upload)
                                                            <img src="{{ $photo_upload->temporaryUrl() }}" class="rounded" style="max-height:70px">
                                                            <div class="mt-2 text-success fw-bold small">
                                                                <i class="bi bi-check-circle-fill me-1"></i>
                                                                <span class="lang-bn">ছবি আপলোড হয়েছে</span>
                                                                <span class="lang-en">Photo uploaded</span>
                                                            </div>
                                                        @else
                                                            <i class="bi bi-cloud-arrow-up-fill fs-2 text-danger"></i>
                                                            <div class="mt-2 fw-bold small">
                                                                <span class="lang-bn">আপনার ছবি আপলোড করুন</span>
                                                                <span class="lang-en">Upload Your Photo</span>
                                                            </div>
                                                        @endif
                                                    </label>
                                                    @error('photo_upload') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
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
                                        STEP 4 — Login Details
                                    ====================================================== --}}

                                    @if($currentStep === 4)

                                        <div wire:key="step-4">

                                            <h2 class="wizard-heading">
                                                <span class="lang-bn">লগইন তথ্য</span>
                                                <span class="lang-en">Login Details</span>
                                            </h2>
                                            <p class="wizard-text">
                                                <span class="lang-bn">লগইন করার জন্য আপনার ইউজারনেম ও পাসওয়ার্ড সেট করুন।</span>
                                                <span class="lang-en">Set your username and password for logging in.</span>
                                            </p>

                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <label class="form-label">
                                                        <span class="lang-bn">ইউজারনেম</span>
                                                        <span class="lang-en">Username</span>
                                                        <span class="text-danger">*</span>
                                                    </label>
                                                    <input type="text" wire:model="username" class="form-control @error('username') is-invalid @enderror">
                                                    @error('username') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">
                                                        <span class="lang-bn">পাসওয়ার্ড</span>
                                                        <span class="lang-en">Password</span>
                                                    </label>
                                                    <input type="password" wire:model="password" class="form-control @error('password') is-invalid @enderror" placeholder="Leave blank for default password">
                                                    @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                                </div>
                                            </div>

                                        </div>

                                    @endif

                                    {{-- =====================================================
                                        STEP 5 — Bank Details (Final)
                                    ====================================================== --}}

                                    @if($currentStep === 5)

                                        <div wire:key="step-5">

                                            <h2 class="wizard-heading">
                                                <span class="lang-bn">ব্যাংক তথ্য</span>
                                                <span class="lang-en">Bank Details</span>
                                            </h2>
                                            <p class="wizard-text">
                                                <span class="lang-bn">ঐচ্ছিক — থাকলে পূরণ করুন, অথবা খালি রাখুন।</span>
                                                <span class="lang-en">Optional — Fill in if available, or leave blank.</span>
                                            </p>

                                            <div class="row g-3 mb-3">
                                                <div class="col-md-6">
                                                    <label class="form-label">
                                                        <span class="lang-bn">ব্যাংকের নাম</span>
                                                        <span class="lang-en">Bank Name</span>
                                                    </label>
                                                    <input type="text" wire:model="bank_name" class="form-control">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">
                                                        <span class="lang-bn">হিসাবধারীর নাম</span>
                                                        <span class="lang-en">Holder Name</span>
                                                    </label>
                                                    <input type="text" wire:model="holder_name" class="form-control">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">
                                                        <span class="lang-bn">ব্যাংক শাখা</span>
                                                        <span class="lang-en">Bank Branch</span>
                                                    </label>
                                                    <input type="text" wire:model="bank_branch" class="form-control">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">
                                                        <span class="lang-bn">আইএফএসসি কোড</span>
                                                        <span class="lang-en">IFSC Code</span>
                                                    </label>
                                                    <input type="text" wire:model="ifsc_code" class="form-control">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">
                                                        <span class="lang-bn">অ্যাকাউন্ট নম্বর</span>
                                                        <span class="lang-en">Account No</span>
                                                    </label>
                                                    <input type="text" wire:model="account_no" class="form-control">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">
                                                        <span class="lang-bn">ব্যাংকের ঠিকানা</span>
                                                        <span class="lang-en">Bank Address</span>
                                                    </label>
                                                    <textarea wire:model="bank_address" class="form-control"></textarea>
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
                                                        <span class="lang-bn">নাম</span>
                                                        <span class="lang-en">Name</span>
                                                    </span>
                                                    <span class="fw-semibold">{{ $name }}</span>
                                                </div>
                                                <div class="d-flex justify-content-between py-2">
                                                    <span class="text-muted">
                                                        <span class="lang-bn">ইউজারনেম</span>
                                                        <span class="lang-en">Username</span>
                                                    </span>
                                                    <span class="fw-semibold">{{ $username }}</span>
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
                                                        <span class="lang-bn">নিবন্ধন জমা দিন</span>
                                                        <span class="lang-en">Submit Registration</span>
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

                </div>
            </div>
        </div>
    </div>

</div>