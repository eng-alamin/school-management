<div>
    <div class="container wizard-wrapper mt-5">
        <div class="row justify-content-center">
            <div class="col-12 col-xl-11">
                <div class="card wizard-card">
                    <div class="row g-0">

                        {{-- =========================================================
                            SIDEBAR
                        ========================================================== --}}

                        <div class="col-lg-4">
                            <div class="wizard-sidebar h-100">

                                <div class="brand-logo">
                                    <i class="bi bi-mortarboard-fill"></i>
                                </div>

                                <h2 class="sidebar-title">
                                    <span class="lang-bn">আপনার প্রতিষ্ঠান সেটআপ করুন</span>
                                    <span class="lang-en">Setup Your Institution</span>
                                </h2>

                                <p class="sidebar-subtitle">
                                    <span class="lang-bn">২ মিনিটেরও কম সময়ে আপনার প্রতিষ্ঠান ব্যবস্থাপনা সিস্টেম চালু করুন।</span>
                                    <span class="lang-en">Get your institution management system
                                    up and running in less than
                                    2 minutes.</span>
                                </p>

                                {{-- STEPS --}}

                                <div class="step-list">

                                    {{-- STEP 1 --}}
                                    <div class="step-item
                                        {{ $currentStep >= 1 ? 'active' : '' }}
                                        {{ $currentStep > 1 ? 'completed' : '' }}
                                    ">
                                        <div class="step-circle">
                                            @if($currentStep > 1)
                                                1
                                            @else
                                                1
                                            @endif
                                        </div>
                                        <div>
                                            <div class="step-title">
                                                <span class="lang-bn">প্রতিষ্ঠানের তথ্য</span>
                                                <span class="lang-en">Institution Information</span>
                                            </div>
                                            <div class="step-desc">
                                                <span class="lang-bn">প্রতিষ্ঠানের প্রোফাইল সেটআপ করুন</span>
                                                <span class="lang-en">Setup institution profile</span>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- STEP 2 --}}
                                    <div class="step-item
                                        {{ $currentStep >= 2 ? 'active' : '' }}
                                        {{ $currentStep > 2 ? 'completed' : '' }}
                                    ">
                                        <div class="step-circle">
                                            @if($currentStep > 2)
                                                2
                                            @else
                                                2
                                            @endif
                                        </div>
                                        <div>
                                            <div class="step-title">
                                                <span class="lang-bn">অ্যাডমিন অ্যাকাউন্ট</span>
                                                <span class="lang-en">Admin Account</span>
                                            </div>
                                            <div class="step-desc">
                                                <span class="lang-bn">সুপার অ্যাডমিন তৈরি করুন</span>
                                                <span class="lang-en">Create super admin</span>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- STEP 3 --}}
                                    <div class="step-item
                                        {{ $currentStep >= 3 ? 'active' : '' }}
                                        {{ $currentStep > 3 ? 'completed' : '' }}
                                    ">
                                        <div class="step-circle">
                                            @if($currentStep > 3)
                                                3
                                            @else
                                                3
                                            @endif
                                        </div>
                                        <div>
                                            <div class="step-title">
                                                <span class="lang-bn">পেমেন্ট</span>
                                                <span class="lang-en">Payment</span>
                                            </div>
                                            <div class="step-desc">
                                                <span class="lang-bn">৳৫,০০০ নিবন্ধন ফি</span>
                                                <span class="lang-en">৳5,000 registration fee</span>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- STEP 4 --}}
                                    <div class="step-item
                                        {{ $currentStep >= 4 ? 'active' : '' }}
                                    ">
                                        <div class="step-circle">4</div>
                                        <div>
                                            <div class="step-title">
                                                <span class="lang-bn">নিশ্চিতকরণ</span>
                                                <span class="lang-en">Confirmation</span>
                                            </div>
                                            <div class="step-desc">
                                                <span class="lang-bn">সেটআপ সম্পন্ন করুন</span>
                                                <span class="lang-en">Complete setup</span>
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

                                {{-- PROGRESS BAR --}}
                                <div class="top-progress">
                                    <div
                                        class="top-progress-bar"
                                        style="width: {{ match($currentStep){ 1=>'25%', 2=>'50%', 3=>'75%', 4=>'100%' } }}"
                                    ></div>
                                </div>

                                {{-- =====================================================
                                    STEP 1 — Institution Information
                                ====================================================== --}}

                                @if($currentStep === 1)

                                    <div wire:key="step-1">

                                        <h2 class="wizard-heading">
                                            <span class="lang-bn">প্রতিষ্ঠানের তথ্য</span>
                                            <span class="lang-en">Institution Information</span>
                                        </h2>
                                        <p class="wizard-text">
                                            <span class="lang-bn">আপনার প্রতিষ্ঠান সম্পর্কে আমাদের জানান।</span>
                                            <span class="lang-en">Tell us about your institution.</span>
                                        </p>

                                        <div class="row">

                                            <div class="col-md-6 mb-4">
                                                <label class="form-label">
                                                    <span class="lang-bn">প্রতিষ্ঠানের নাম</span>
                                                    <span class="lang-en">Institution Name</span>
                                                </label>
                                                <input
                                                    type="text"
                                                    class="form-control @error('institution_name') is-invalid @enderror"
                                                    wire:model.live="institution_name"
                                                    placeholder="Green Valley Institution"
                                                >
                                                @error('institution_name')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="col-md-6 mb-4">
                                                <label class="form-label">
                                                    <span class="lang-bn">প্রতিষ্ঠানের ধরন</span>
                                                    <span class="lang-en">Institution Type</span>
                                                </label>
                                                <select
                                                    class="form-select @error('institution_type') is-invalid @enderror"
                                                    wire:model.live="institution_type"
                                                >
                                                    <option value="">Select Type</option>
                                                    <option value="school">School</option>
                                                    <option value="college">College</option>
                                                    <option value="school-college">School & College</option>
                                                    <option value="madrasa">Madrasa</option>
                                                </select>
                                                @error('institution_type')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="col-md-6 mb-4">
                                                <label class="form-label">
                                                    <span class="lang-bn">ইমেইল ঠিকানা</span>
                                                    <span class="lang-en">Email Address</span>
                                                </label>
                                                <input
                                                    type="email"
                                                    class="form-control @error('email') is-invalid @enderror"
                                                    wire:model.live="email"
                                                    placeholder="institution@example.com"
                                                >
                                                @error('email')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="col-md-6 mb-4">
                                                <label class="form-label">
                                                    <span class="lang-bn">ফোন নম্বর</span>
                                                    <span class="lang-en">Phone Number</span>
                                                </label>
                                                <input
                                                    type="text"
                                                    class="form-control @error('phone') is-invalid @enderror"
                                                    wire:model.live="phone"
                                                    placeholder="+8801XXXXXXXXX"
                                                >
                                                @error('phone')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="col-12 mb-4">
                                                <label class="form-label">
                                                    <span class="lang-bn">প্রতিষ্ঠানের লোগো</span>
                                                    <span class="lang-en">Institution Logo</span>
                                                </label>
                                                <label class="upload-box w-100">
                                                    <input
                                                        type="file"
                                                        class="d-none"
                                                        wire:model="logo"
                                                        accept="image/*"
                                                    >
                                                    @if($logo)
                                                        <img
                                                            src="{{ $logo->temporaryUrl() }}"
                                                            alt="Logo Preview"
                                                            style="max-height:80px;border-radius:10px;"
                                                        >
                                                        <div class="mt-2 text-success fw-bold small">
                                                            <i class="bi bi-check-circle-fill me-1"></i>
                                                            <span class="lang-bn">লোগো আপলোড হয়েছে</span>
                                                            <span class="lang-en">Logo uploaded</span>
                                                        </div>
                                                    @else
                                                        <i class="bi bi-cloud-arrow-up-fill fs-1 text-primary"></i>
                                                        <div class="mt-3 fw-bold">
                                                            <span class="lang-bn">প্রতিষ্ঠানের লোগো আপলোড করুন</span>
                                                            <span class="lang-en">Upload Institution Logo</span>
                                                        </div>
                                                        <div class="text-muted small mt-2">
                                                            <span class="lang-bn">২ এমবি পর্যন্ত PNG, JPG</span>
                                                            <span class="lang-en">PNG, JPG up to 2MB</span>
                                                        </div>
                                                    @endif
                                                </label>
                                                @error('logo')
                                                    <div class="text-danger small mt-2">{{ $message }}</div>
                                                @enderror
                                            </div>

                                        </div>

                                        {{-- FEATURES --}}
                                        <div class="row mt-2">

                                            <div class="col-md-4 mb-3">
                                                <div class="feature-box">
                                                    <div class="feature-icon">
                                                        <i class="bi bi-people-fill"></i>
                                                    </div>
                                                    <h6 class="fw-bold">
                                                        <span class="lang-bn">শিক্ষার্থী ব্যবস্থাপনা</span>
                                                        <span class="lang-en">Student Management</span>
                                                    </h6>
                                                    <p class="text-muted small mb-0">
                                                        <span class="lang-bn">শিক্ষার্থী ও ক্লাসের উপর সম্পূর্ণ নিয়ন্ত্রণ।</span>
                                                        <span class="lang-en">Full control over students & classes.</span>
                                                    </p>
                                                </div>
                                            </div>

                                            <div class="col-md-4 mb-3">
                                                <div class="feature-box">
                                                    <div class="feature-icon">
                                                        <i class="bi bi-lightning-charge-fill"></i>
                                                    </div>
                                                    <h6 class="fw-bold">
                                                        <span class="lang-bn">তাৎক্ষণিক সেটআপ</span>
                                                        <span class="lang-en">Instant Setup</span>
                                                    </h6>
                                                    <p class="text-muted small mb-0">
                                                        <span class="lang-bn">কয়েক সেকেন্ডের মধ্যেই ব্যবহারের জন্য প্রস্তুত।</span>
                                                        <span class="lang-en">Ready to use within seconds.</span>
                                                    </p>
                                                </div>
                                            </div>

                                            <div class="col-md-4 mb-3">
                                                <div class="feature-box">
                                                    <div class="feature-icon">
                                                        <i class="bi bi-shield-lock-fill"></i>
                                                    </div>
                                                    <h6 class="fw-bold">
                                                        <span class="lang-bn">নিরাপদ প্রবেশাধিকার</span>
                                                        <span class="lang-en">Secure Access</span>
                                                    </h6>
                                                    <p class="text-muted small mb-0">
                                                        <span class="lang-bn">বিল্ট-ইন রোল-ভিত্তিক প্রবেশাধিকার নিয়ন্ত্রণ।</span>
                                                        <span class="lang-en">Role-based access control built-in.</span>
                                                    </p>
                                                </div>
                                            </div>

                                        </div>

                                    </div>

                                @endif

                                {{-- =====================================================
                                    STEP 2 — Admin Account
                                ====================================================== --}}

                                @if($currentStep === 2)

                                    <div wire:key="step-2">

                                        <h2 class="wizard-heading">
                                            <span class="lang-bn">অ্যাডমিন অ্যাকাউন্ট তৈরি করুন</span>
                                            <span class="lang-en">Create Admin Account</span>
                                        </h2>
                                        <p class="wizard-text">
                                            <span class="lang-bn">আপনার সুপার অ্যাডমিনিস্ট্রেটর অ্যাকাউন্ট সেটআপ করুন।</span>
                                            <span class="lang-en">Setup your super administrator account.</span>
                                        </p>

                                        <div class="row">

                                            <div class="col-md-6 mb-4">
                                                <label class="form-label">
                                                    <span class="lang-bn">পূর্ণ নাম</span>
                                                    <span class="lang-en">Full Name</span>
                                                </label>
                                                <input
                                                    type="text"
                                                    class="form-control @error('admin_name') is-invalid @enderror"
                                                    wire:model.live="admin_name"
                                                    placeholder="John Doe"
                                                >
                                                @error('admin_name')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="col-md-6 mb-4">
                                                <label class="form-label">
                                                    <span class="lang-bn">ইমেইল ঠিকানা</span>
                                                    <span class="lang-en">Email Address</span>
                                                </label>
                                                <input
                                                    type="email"
                                                    class="form-control @error('admin_email') is-invalid @enderror"
                                                    wire:model.live="admin_email"
                                                    placeholder="admin@example.com"
                                                >
                                                @error('admin_email')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="col-md-6 mb-4">
                                                <label class="form-label">
                                                    <span class="lang-bn">পাসওয়ার্ড</span>
                                                    <span class="lang-en">Password</span>
                                                </label>
                                                <input
                                                    type="password"
                                                    class="form-control @error('password') is-invalid @enderror"
                                                    wire:model.live="password"
                                                    placeholder="Minimum 8 characters"
                                                >
                                                @error('password')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="col-md-6 mb-4">
                                                <label class="form-label">
                                                    <span class="lang-bn">পাসওয়ার্ড নিশ্চিত করুন</span>
                                                    <span class="lang-en">Confirm Password</span>
                                                </label>
                                                <input
                                                    type="password"
                                                    class="form-control"
                                                    wire:model.live="password_confirmation"
                                                    placeholder="Repeat password"
                                                >
                                            </div>

                                        </div>

                                        {{-- SECURITY BOX --}}
                                        <div class="summary-box">
                                            <div class="d-flex align-items-center">
                                                <div class="feature-icon me-3 mb-0" style="flex-shrink:0;">
                                                    <i class="bi bi-shield-check"></i>
                                                </div>
                                                <div>
                                                    <h6 class="fw-bold mb-1">
                                                        <span class="lang-bn">নিরাপদ ক্রেডেনশিয়াল</span>
                                                        <span class="lang-en">Secure Credentials</span>
                                                    </h6>
                                                    <div class="text-muted small">
                                                        <span class="lang-bn">সংরক্ষণের আগে আপনার পাসওয়ার্ড bcrypt hashing দিয়ে এনক্রিপ্ট করা হয়।</span>
                                                        <span class="lang-en">Your password is encrypted with bcrypt hashing before being stored.</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                    </div>

                                @endif

                                {{-- =====================================================
                                    STEP 3 — Payment
                                ====================================================== --}}

                                @if($currentStep === 3)

                                    <div wire:key="step-3">

                                        <h2 class="wizard-heading">
                                            <span class="lang-bn">নিবন্ধন পেমেন্ট</span>
                                            <span class="lang-en">Registration Payment</span>
                                        </h2>
                                        <p class="wizard-text">
                                            <span class="lang-bn">একটি one-time registration fee পরিশোধ করুন এবং আপনার institution activate করুন।</span>
                                            <span class="lang-en">Pay a one-time registration fee and activate your institution.</span>
                                        </p>

                                        {{-- AMOUNT BOX --}}
                                        <div class="payment-amount-box mb-4">
                                            <div class="payment-taka">
                                                <span>৳</span>5,000
                                            </div>
                                            <div class="payment-label">
                                                <span class="lang-bn">একবারের নিবন্ধন ফি</span>
                                                <span class="lang-en">One-time Registration Fee</span>
                                            </div>
                                            <div class="mt-3" style="position:relative;z-index:1;">
                                                <span class="ssl-badge" style="background:rgba(255,255,255,.15);border-color:rgba(255,255,255,.3);color:white;">
                                                    <i class="bi bi-shield-lock-fill"></i>
                                                    <span class="lang-bn">SSLCommerz দ্বারা সুরক্ষিত</span>
                                                    <span class="lang-en">Secured by SSLCommerz</span>
                                                </span>
                                            </div>
                                        </div>

                                        <div class="row">

                                            {{-- WHAT YOU GET --}}
                                            <div class="col-md-6 mb-4">
                                                <div class="summary-box h-100">
                                                    <h6 class="fw-bold mb-3">
                                                        <i class="bi bi-gift-fill text-primary me-2"></i>
                                                        <span class="lang-bn">আপনি যা পাবেন</span>
                                                        <span class="lang-en">What You Get</span>
                                                    </h6>
                                                    <div class="payment-feature-row">
                                                        <i class="bi bi-check-circle-fill"></i>
                                                        <span class="lang-bn">সীমাহীন শিক্ষার্থী</span>
                                                        <span class="lang-en">Unlimited Students</span>
                                                    </div>
                                                    <div class="payment-feature-row">
                                                        <i class="bi bi-check-circle-fill"></i>
                                                        <span class="lang-bn">সম্পূর্ণ অ্যাডমিন প্যানেল</span>
                                                        <span class="lang-en">Full Admin Panel</span>
                                                    </div>
                                                    <div class="payment-feature-row">
                                                        <i class="bi bi-check-circle-fill"></i>
                                                        <span class="lang-bn">উপস্থিতি ব্যবস্থাপনা</span>
                                                        <span class="lang-en">Attendance Management</span>
                                                    </div>
                                                    <div class="payment-feature-row">
                                                        <i class="bi bi-check-circle-fill"></i>
                                                        <span class="lang-bn">ফি ও বিলিং সিস্টেম</span>
                                                        <span class="lang-en">Fee & Billing System</span>
                                                    </div>
                                                    <div class="payment-feature-row">
                                                        <i class="bi bi-check-circle-fill"></i>
                                                        <span class="lang-bn">ফলাফল ও পরীক্ষা ব্যবস্থাপনা</span>
                                                        <span class="lang-en">Result & Exam Management</span>
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- PAYMENT METHODS --}}
                                            <div class="col-md-6 mb-4">
                                                <div class="summary-box h-100">
                                                    <h6 class="fw-bold mb-3">
                                                        <i class="bi bi-wallet2 text-primary me-2"></i>
                                                        <span class="lang-bn">পেমেন্ট পদ্ধতি</span>
                                                        <span class="lang-en">Payment Methods</span>
                                                    </h6>
                                                    <div class="payment-feature-row">
                                                        <i class="bi bi-check-circle-fill"></i>
                                                        bKash / Nagad / Rocket
                                                    </div>
                                                    <div class="payment-feature-row">
                                                        <i class="bi bi-check-circle-fill"></i>
                                                        Visa / Mastercard
                                                    </div>
                                                    <div class="payment-feature-row">
                                                        <i class="bi bi-check-circle-fill"></i>
                                                        <span class="lang-bn">ইন্টারনেট ব্যাংকিং</span>
                                                        <span class="lang-en">Internet Banking</span>
                                                    </div>
                                                    <div class="payment-feature-row">
                                                        <i class="bi bi-check-circle-fill"></i>
                                                        DBBL / Dutch-Bangla
                                                    </div>
                                                    <div class="payment-feature-row">
                                                        <i class="bi bi-check-circle-fill"></i>
                                                        <span class="lang-bn">সকল প্রধান ব্যাংক</span>
                                                        <span class="lang-en">All Major Banks</span>
                                                    </div>
                                                </div>
                                            </div>

                                        </div>

                                        {{-- ORDER SUMMARY --}}
                                        <div class="summary-box">
                                            <h6 class="fw-bold mb-0">
                                                <i class="bi bi-receipt me-2 text-primary"></i>
                                                <span class="lang-bn">অর্ডার সারসংক্ষেপ</span>
                                                <span class="lang-en">Order Summary</span>
                                            </h6>
                                            <div class="summary-item mt-3">
                                                <div class="summary-label">
                                                    <span class="lang-bn">প্রতিষ্ঠানের নাম</span>
                                                    <span class="lang-en">Institution Name</span>
                                                </div>
                                                <div class="summary-value">{{ $institution_name }}</div>
                                            </div>
                                            <div class="summary-item">
                                                <div class="summary-label">
                                                    <span class="lang-bn">অ্যাডমিন ইমেইল</span>
                                                    <span class="lang-en">Admin Email</span>
                                                </div>
                                                <div class="summary-value">{{ $admin_email }}</div>
                                            </div>
                                            <div class="summary-item">
                                                <div class="summary-label">
                                                    <span class="lang-bn">নিবন্ধন ফি</span>
                                                    <span class="lang-en">Registration Fee</span>
                                                </div>
                                                <div class="summary-value text-primary">
                                                    ৳ {{ number_format(setting('register_fee', 4), 0) }}
                                                </div>
                                            </div>
                                            <div class="summary-item" style="border-bottom:none;">
                                                <div class="summary-label fw-bold text-dark">
                                                    <span class="lang-bn">মোট পরিশোধযোগ্য</span>
                                                    <span class="lang-en">Total Payable</span>
                                                </div>
                                                <div class="summary-value fs-5 text-success">
                                                    ৳ @if(setting('register_type') === 'paid') {{ number_format(setting('register_fee'), 0) }} @else 00 @endif
                                                </div>
                                            </div>
                                        </div>

                                    </div>

                                @endif

                                {{-- =====================================================
                                    STEP 4 — Confirmation (Redirect করার আগে দেখাবে না,
                                    SSLCommerz থেকে ফিরলে login page-এ যাবে।
                                    তবু fallback হিসেবে রাখা হলো।)
                                ====================================================== --}}

                                @if($currentStep === 4)

                                    <div wire:key="step-4">

                                        <div class="text-center">
                                            <div class="success-animation mb-4">
                                                <i class="bi bi-check-lg"></i>
                                            </div>
                                            <h2 class="wizard-heading">
                                                <span class="lang-bn">চালু করার জন্য প্রস্তুত 🚀</span>
                                                <span class="lang-en">Ready To Launch 🚀</span>
                                            </h2>
                                            <p class="wizard-text">
                                                <span class="lang-bn">পেমেন্টের আগে আপনার তথ্য পর্যালোচনা করুন।</span>
                                                <span class="lang-en">Review your details before payment.</span>
                                            </p>
                                        </div>

                                        <div class="summary-box mt-4">
                                            <h5 class="fw-bold mb-3">
                                                <span class="lang-bn">সেটআপ সারসংক্ষেপ</span>
                                                <span class="lang-en">Setup Summary</span>
                                            </h5>
                                            <div class="summary-item">
                                                <div class="summary-label">
                                                    <span class="lang-bn">প্রতিষ্ঠানের নাম</span>
                                                    <span class="lang-en">Institution Name</span>
                                                </div>
                                                <div class="summary-value">{{ $institution_name }}</div>
                                            </div>
                                            <div class="summary-item">
                                                <div class="summary-label">
                                                    <span class="lang-bn">প্রতিষ্ঠানের ধরন</span>
                                                    <span class="lang-en">Institution Type</span>
                                                </div>
                                                <div class="summary-value">{{ ucfirst($institution_type) }}</div>
                                            </div>
                                            <div class="summary-item">
                                                <div class="summary-label">
                                                    <span class="lang-bn">প্রতিষ্ঠানের ইমেইল</span>
                                                    <span class="lang-en">Institution Email</span>
                                                </div>
                                                <div class="summary-value">{{ $email }}</div>
                                            </div>
                                            <div class="summary-item">
                                                <div class="summary-label">
                                                    <span class="lang-bn">অ্যাডমিন অ্যাকাউন্ট</span>
                                                    <span class="lang-en">Admin Account</span>
                                                </div>
                                                <div class="summary-value">{{ $admin_email }}</div>
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
                                            <button
                                                type="button"
                                                class="btn btn-light-custom wizard-btn"
                                                wire:click="previousStep"
                                            >
                                                <i class="bi bi-arrow-left me-1"></i>
                                                <span class="lang-bn">পূর্ববর্তী</span>
                                                <span class="lang-en">Previous</span>
                                            </button>
                                        @endif
                                    </div>

                                    <div>

                                        {{-- Steps 1 & 2: Continue --}}
                                        @if($currentStep < (setting('register_type') === 'paid' ? 3 : 4))
                                            <button
                                                type="button"
                                                class="btn btn-primary wizard-btn"
                                                wire:click="nextStep"
                                            >
                                                <span class="lang-bn">এগিয়ে যান</span>
                                                <span class="lang-en">Continue</span>
                                                <i class="bi bi-arrow-right ms-2"></i>
                                            </button>
                                        @endif

                                        @if (setting('register_type') === 'paid')
                                            {{-- Step 3: Proceed to Payment --}}
                                            @if($currentStep === 3)
                                                <button
                                                    type="button"
                                                    class="btn btn-primary wizard-btn px-5"
                                                    wire:click="initiatePayment"
                                                    wire:loading.attr="disabled"
                                                >
                                                    <span wire:loading.remove wire:target="initiatePayment">
                                                        <i class="bi bi-credit-card me-2"></i>
                                                        <span class="lang-bn">SSLCommerz দিয়ে ৳৫,০০০ পরিশোধ করুন</span>
                                                        <span class="lang-en">Pay ৳5,000 via SSLCommerz</span>
                                                    </span>
                                                    <span wire:loading wire:target="initiatePayment">
                                                        <span class="spinner-border spinner-border-sm me-2"></span>
                                                        <span class="lang-bn">গেটওয়ের সাথে সংযোগ হচ্ছে...</span>
                                                        <span class="lang-en">Connecting to gateway...</span>
                                                    </span>
                                                </button>
                                            @endif

                                            {{-- Step 4 (fallback): Launch --}}
                                            @if($currentStep === 4)
                                                <button
                                                    type="button"
                                                    class="btn btn-primary wizard-btn px-5"
                                                    wire:click="initiatePayment"
                                                    wire:loading.attr="disabled"
                                                >
                                                    <span wire:loading.remove wire:target="initiatePayment">
                                                        <i class="bi bi-rocket-takeoff me-2"></i>
                                                        <span class="lang-bn">প্রতিষ্ঠান চালু করুন</span>
                                                        <span class="lang-en">Launch Institution</span>
                                                    </span>
                                                    <span wire:loading wire:target="initiatePayment">
                                                        <span class="spinner-border spinner-border-sm me-2"></span>
                                                        <span class="lang-bn">সেটআপ হচ্ছে...</span>
                                                        <span class="lang-en">Setting up...</span>
                                                    </span>
                                                </button>
                                            @endif
                                        @else
                                            @if($currentStep === 4)
                                                <button
                                                    type="button"
                                                    class="btn btn-primary wizard-btn px-5"
                                                    wire:click="initiateFree"
                                                    wire:loading.attr="disabled"
                                                >
                                                    <span wire:loading.remove wire:target="initiateFree">
                                                        <i class="bi bi-rocket-takeoff me-2"></i>
                                                        <span class="lang-bn">প্রতিষ্ঠান চালু করুন</span>
                                                        <span class="lang-en">Launch Institution</span>
                                                    </span>
                                                    <span wire:loading wire:target="initiateFree">
                                                        <span class="spinner-border spinner-border-sm me-2"></span>
                                                        <span class="lang-bn">সেটআপ হচ্ছে...</span>
                                                        <span class="lang-en">Setting up...</span>
                                                    </span>
                                                </button>
                                            @endif
                                        @endif

                                    </div>

                                </div>

                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

</div>