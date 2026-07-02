<div>

    {{-- =========================================================
        STYLES
    ========================================================== --}}
    <style>
        :root{
            --primary:#4f46e5;
            --primary-dark:#4338ca;
            --success:#10b981;
            --danger:#ef4444;
            --light:#f8fafc;
            --border:#e5e7eb;
            --text:#111827;
            --muted:#6b7280;
        }

        body{
            background:
                radial-gradient(circle at top left,#6366f1 0%,transparent 30%),
                radial-gradient(circle at bottom right,#8b5cf6 0%,transparent 30%),
                #0f172a;
        }

        .wizard-wrapper{
            min-height:100vh;
            padding:40px 0;
        }

        .wizard-card{
            border:none;
            border-radius:30px;
            overflow:hidden;
            background:rgba(255,255,255,.96);
            backdrop-filter:blur(15px);
            box-shadow:
                0 10px 40px rgba(0,0,0,.15),
                0 2px 8px rgba(0,0,0,.05);
        }

        .wizard-sidebar{
            background:linear-gradient(180deg,#4f46e5 0%,#7c3aed 100%);
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
            font-size:34px;
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
        }

        .step-item{
            display:flex;
            align-items:center;
            margin-bottom:25px;
            position:relative;
            z-index:2;
        }

        .step-circle{
            width:48px;
            height:48px;
            border-radius:50%;
            background:rgba(255,255,255,.15);
            display:flex;
            align-items:center;
            justify-content:center;
            font-weight:700;
            margin-right:18px;
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
            margin-bottom:3px;
        }

        .step-desc{
            opacity:.8;
            font-size:13px;
        }

        .wizard-content{
            padding:50px;
        }

        .top-progress{
            height:10px;
            border-radius:30px;
            background:#eef2ff;
            overflow:hidden;
            margin-bottom:40px;
        }

        .top-progress-bar{
            height:100%;
            background:linear-gradient(90deg,#4f46e5,#8b5cf6);
            border-radius:30px;
            transition:width .4s ease;
        }

        .wizard-heading{
            font-size:32px;
            font-weight:800;
            color:var(--text);
            margin-bottom:10px;
        }

        .wizard-text{
            color:var(--muted);
            margin-bottom:35px;
        }

        .form-label{
            font-weight:700;
            color:#374151;
            margin-bottom:10px;
        }

        .form-control,
        .form-select{
            border-radius:16px;
            min-height:56px;
            border:1px solid var(--border);
            padding-left:18px;
            font-size:15px;
            transition:.25s;
        }

        .form-control:focus,
        .form-select:focus{
            border-color:var(--primary);
            box-shadow:0 0 0 4px rgba(79,70,229,.1);
        }

        .wizard-btn{
            min-height:56px;
            border-radius:16px;
            font-weight:700;
            padding:0 30px;
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
            border:2px dashed #c7d2fe;
            border-radius:20px;
            padding:35px;
            text-align:center;
            background:#f8faff;
            transition:.25s;
            cursor:pointer;
        }

        .upload-box:hover{
            border-color:var(--primary);
            background:#eef2ff;
        }

        .feature-box{
            border-radius:20px;
            background:#f8fafc;
            padding:25px;
            border:1px solid #eef2f7;
            height:100%;
        }

        .feature-icon{
            width:55px;
            height:55px;
            border-radius:16px;
            display:flex;
            align-items:center;
            justify-content:center;
            background:#eef2ff;
            color:var(--primary);
            font-size:22px;
            margin-bottom:18px;
        }

        .summary-box{
            border-radius:20px;
            background:#f8fafc;
            border:1px solid #eef2f7;
            padding:25px;
        }

        .summary-item{
            display:flex;
            justify-content:space-between;
            align-items:center;
            padding:12px 0;
            border-bottom:1px solid #f1f5f9;
        }

        .summary-item:last-child{
            border-bottom:none;
            margin-bottom:0;
        }

        .summary-label{
            color:#6b7280;
            font-weight:600;
        }

        .summary-value{
            font-weight:700;
            color:#111827;
        }

        .payment-amount-box{
            background:linear-gradient(135deg,#4f46e5,#7c3aed);
            border-radius:24px;
            padding:40px;
            text-align:center;
            color:white;
            position:relative;
            overflow:hidden;
        }

        .payment-amount-box::before{
            content:'';
            position:absolute;
            width:200px;
            height:200px;
            background:rgba(255,255,255,.08);
            border-radius:50%;
            top:-80px;
            right:-60px;
        }

        .payment-amount-box::after{
            content:'';
            position:absolute;
            width:150px;
            height:150px;
            background:rgba(255,255,255,.06);
            border-radius:50%;
            bottom:-50px;
            left:-40px;
        }

        .payment-taka{
            font-size:64px;
            font-weight:900;
            line-height:1;
            position:relative;
            z-index:1;
        }

        .payment-taka span{
            font-size:32px;
            font-weight:600;
            opacity:.85;
            vertical-align:super;
        }

        .payment-label{
            opacity:.85;
            margin-top:8px;
            font-size:15px;
            position:relative;
            z-index:1;
        }

        .payment-feature-row{
            display:flex;
            align-items:center;
            gap:10px;
            padding:10px 0;
            border-bottom:1px solid #f1f5f9;
            font-weight:500;
            color:#374151;
        }

        .payment-feature-row:last-child{
            border-bottom:none;
        }

        .payment-feature-row i{
            color:var(--success);
            font-size:18px;
            flex-shrink:0;
        }

        .ssl-badge{
            display:inline-flex;
            align-items:center;
            gap:6px;
            background:#f0fdf4;
            border:1px solid #bbf7d0;
            border-radius:30px;
            padding:6px 16px;
            font-size:13px;
            font-weight:600;
            color:#16a34a;
        }

        .success-animation{
            width:120px;
            height:120px;
            border-radius:50%;
            background:#dcfce7;
            display:flex;
            align-items:center;
            justify-content:center;
            margin:auto;
            font-size:55px;
            color:#16a34a;
            animation:pulse 2s infinite;
        }

        @keyframes pulse{
            0%{transform:scale(1);box-shadow:0 0 0 0 rgba(16,185,129,.4);}
            70%{transform:scale(1.05);box-shadow:0 0 0 20px rgba(16,185,129,0);}
            100%{transform:scale(1);}
        }

        @media(max-width:991px){
            .wizard-content{padding:35px 25px;}
            .wizard-sidebar{padding:35px 25px;}
            .sidebar-title{font-size:26px;}
            .payment-taka{font-size:48px;}
        }
    </style>

    {{-- =========================================================
        WRAPPER
    ========================================================== --}}

    <div class="container wizard-wrapper">
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
                                    Setup Your Institution
                                </h2>

                                <p class="sidebar-subtitle">
                                    Get your institution management system
                                    up and running in less than
                                    2 minutes.
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
                                            <div class="step-title">Institution Information</div>
                                            <div class="step-desc">Setup institution profile</div>
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
                                            <div class="step-title">Admin Account</div>
                                            <div class="step-desc">Create super admin</div>
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
                                            <div class="step-title">Payment</div>
                                            <div class="step-desc">৳5,000 registration fee</div>
                                        </div>
                                    </div>

                                    {{-- STEP 4 --}}
                                    <div class="step-item
                                        {{ $currentStep >= 4 ? 'active' : '' }}
                                    ">
                                        <div class="step-circle">4</div>
                                        <div>
                                            <div class="step-title">Confirmation</div>
                                            <div class="step-desc">Complete setup</div>
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

                                        <h2 class="wizard-heading">Institution Information</h2>
                                        <p class="wizard-text">Tell us about your institution.</p>

                                        <div class="row">

                                            <div class="col-md-6 mb-4">
                                                <label class="form-label">Institution Name</label>
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
                                                <label class="form-label">Institution Type</label>
                                                <select
                                                    class="form-select @error('institution_type') is-invalid @enderror"
                                                    wire:model.live="institution_type"
                                                >
                                                    <option value="">Select Type</option>
                                                    <option value="school">School</option>
                                                    <option value="college">College</option>
                                                    <option value="madrasa">Madrasa</option>
                                                    <option value="university">University</option>
                                                </select>
                                                @error('institution_type')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="col-md-6 mb-4">
                                                <label class="form-label">Email Address</label>
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
                                                <label class="form-label">Phone Number</label>
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
                                                <label class="form-label">Institution Logo</label>
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
                                                            Logo uploaded
                                                        </div>
                                                    @else
                                                        <i class="bi bi-cloud-arrow-up-fill fs-1 text-primary"></i>
                                                        <div class="mt-3 fw-bold">Upload Institution Logo</div>
                                                        <div class="text-muted small mt-2">PNG, JPG up to 2MB</div>
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
                                                    <h6 class="fw-bold">Student Management</h6>
                                                    <p class="text-muted small mb-0">Full control over students & classes.</p>
                                                </div>
                                            </div>

                                            <div class="col-md-4 mb-3">
                                                <div class="feature-box">
                                                    <div class="feature-icon">
                                                        <i class="bi bi-lightning-charge-fill"></i>
                                                    </div>
                                                    <h6 class="fw-bold">Instant Setup</h6>
                                                    <p class="text-muted small mb-0">Ready to use within seconds.</p>
                                                </div>
                                            </div>

                                            <div class="col-md-4 mb-3">
                                                <div class="feature-box">
                                                    <div class="feature-icon">
                                                        <i class="bi bi-shield-lock-fill"></i>
                                                    </div>
                                                    <h6 class="fw-bold">Secure Access</h6>
                                                    <p class="text-muted small mb-0">Role-based access control built-in.</p>
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

                                        <h2 class="wizard-heading">Create Admin Account</h2>
                                        <p class="wizard-text">Setup your super administrator account.</p>

                                        <div class="row">

                                            <div class="col-md-6 mb-4">
                                                <label class="form-label">Full Name</label>
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
                                                <label class="form-label">Email Address</label>
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
                                                <label class="form-label">Password</label>
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
                                                <label class="form-label">Confirm Password</label>
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
                                                    <h6 class="fw-bold mb-1">Secure Credentials</h6>
                                                    <div class="text-muted small">
                                                        Your password is encrypted with bcrypt hashing before being stored.
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

                                        <h2 class="wizard-heading">Registration Payment</h2>
                                        <p class="wizard-text">
                                            একটি one-time registration fee পরিশোধ করুন এবং আপনার institution activate করুন।
                                        </p>

                                        {{-- AMOUNT BOX --}}
                                        <div class="payment-amount-box mb-4">
                                            <div class="payment-taka">
                                                <span>৳</span>5,000
                                            </div>
                                            <div class="payment-label">
                                                One-time Registration Fee
                                            </div>
                                            <div class="mt-3" style="position:relative;z-index:1;">
                                                <span class="ssl-badge" style="background:rgba(255,255,255,.15);border-color:rgba(255,255,255,.3);color:white;">
                                                    <i class="bi bi-shield-lock-fill"></i>
                                                    Secured by SSLCommerz
                                                </span>
                                            </div>
                                        </div>

                                        <div class="row">

                                            {{-- WHAT YOU GET --}}
                                            <div class="col-md-6 mb-4">
                                                <div class="summary-box h-100">
                                                    <h6 class="fw-bold mb-3">
                                                        <i class="bi bi-gift-fill text-primary me-2"></i>
                                                        What You Get
                                                    </h6>
                                                    <div class="payment-feature-row">
                                                        <i class="bi bi-check-circle-fill"></i>
                                                        Unlimited Students
                                                    </div>
                                                    <div class="payment-feature-row">
                                                        <i class="bi bi-check-circle-fill"></i>
                                                        Full Admin Panel
                                                    </div>
                                                    <div class="payment-feature-row">
                                                        <i class="bi bi-check-circle-fill"></i>
                                                        Attendance Management
                                                    </div>
                                                    <div class="payment-feature-row">
                                                        <i class="bi bi-check-circle-fill"></i>
                                                        Fee & Billing System
                                                    </div>
                                                    <div class="payment-feature-row">
                                                        <i class="bi bi-check-circle-fill"></i>
                                                        Result & Exam Management
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- PAYMENT METHODS --}}
                                            <div class="col-md-6 mb-4">
                                                <div class="summary-box h-100">
                                                    <h6 class="fw-bold mb-3">
                                                        <i class="bi bi-wallet2 text-primary me-2"></i>
                                                        Payment Methods
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
                                                        Internet Banking
                                                    </div>
                                                    <div class="payment-feature-row">
                                                        <i class="bi bi-check-circle-fill"></i>
                                                        DBBL / Dutch-Bangla
                                                    </div>
                                                    <div class="payment-feature-row">
                                                        <i class="bi bi-check-circle-fill"></i>
                                                        All Major Banks
                                                    </div>
                                                </div>
                                            </div>

                                        </div>

                                        {{-- ORDER SUMMARY --}}
                                        <div class="summary-box">
                                            <h6 class="fw-bold mb-0">
                                                <i class="bi bi-receipt me-2 text-primary"></i>
                                                Order Summary
                                            </h6>
                                            <div class="summary-item mt-3">
                                                <div class="summary-label">Institution Name</div>
                                                <div class="summary-value">{{ $institution_name }}</div>
                                            </div>
                                            <div class="summary-item">
                                                <div class="summary-label">Admin Email</div>
                                                <div class="summary-value">{{ $admin_email }}</div>
                                            </div>
                                            <div class="summary-item">
                                                <div class="summary-label">Registration Fee</div>
                                                <div class="summary-value text-primary">
                                                    ৳ {{ number_format(setting('register_fee', 4), 0) }}
                                                </div>
                                            </div>
                                            <div class="summary-item" style="border-bottom:none;">
                                                <div class="summary-label fw-bold text-dark">Total Payable</div>
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
                                            <h2 class="wizard-heading">Ready To Launch 🚀</h2>
                                            <p class="wizard-text">Review your details before payment.</p>
                                        </div>

                                        <div class="summary-box mt-4">
                                            <h5 class="fw-bold mb-3">Setup Summary</h5>
                                            <div class="summary-item">
                                                <div class="summary-label">Institution Name</div>
                                                <div class="summary-value">{{ $institution_name }}</div>
                                            </div>
                                            <div class="summary-item">
                                                <div class="summary-label">Institution Type</div>
                                                <div class="summary-value">{{ ucfirst($institution_type) }}</div>
                                            </div>
                                            <div class="summary-item">
                                                <div class="summary-label">Institution Email</div>
                                                <div class="summary-value">{{ $email }}</div>
                                            </div>
                                            <div class="summary-item">
                                                <div class="summary-label">Admin Account</div>
                                                <div class="summary-value">{{ $admin_email }}</div>
                                            </div>
                                            <div class="summary-item">
                                                <div class="summary-label">Timezone</div>
                                                <div class="summary-value">{{ $timezone }}</div>
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
                                                Previous
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
                                                Continue
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
                                                        Pay ৳5,000 via SSLCommerz
                                                    </span>
                                                    <span wire:loading wire:target="initiatePayment">
                                                        <span class="spinner-border spinner-border-sm me-2"></span>
                                                        Connecting to gateway...
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
                                                        Launch Institution
                                                    </span>
                                                    <span wire:loading wire:target="initiatePayment">
                                                        <span class="spinner-border spinner-border-sm me-2"></span>
                                                        Setting up...
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
                                                        Launch Institution
                                                    </span>
                                                    <span wire:loading wire:target="initiateFree">
                                                        <span class="spinner-border spinner-border-sm me-2"></span>
                                                        Setting up...
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