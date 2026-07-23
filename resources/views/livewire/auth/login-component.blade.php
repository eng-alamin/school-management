<div class="auth-page-wrap d-flex align-items-center justify-content-center">

    <div class="container wizard-wrapper mt-5">
        <div class="row justify-content-center">
            <div class="col-12 col-xl-11">
                <div class="card wizard-card">

                    <div class="row g-0">

                        {{-- =========================================================
                            SIDEBAR (Left) — same pattern as Teacher Registration Wizard
                        ========================================================== --}}

                        <div class="col-lg-5">
                            <div class="wizard-sidebar h-100">

                                <div class="brand-logo">
                                    <i class="bi bi-mortarboard-fill"></i>
                                </div>

                                <h2 class="sidebar-title">
                                    <span class="lang-bn">এডুকেশন ইআরপি-তে স্বাগতম</span>
                                    <span class="lang-en">Welcome Back To Education ERP</span>
                                </h2>

                                <p class="sidebar-subtitle">
                                    <span class="lang-bn">শিক্ষার্থী, উপস্থিতি, পরীক্ষা, হিসাব, পে-রোল এবং আরও সবকিছু একটি শক্তিশালী ড্যাশবোর্ড থেকে পরিচালনা করুন।</span>
                                    <span class="lang-en">Manage students, attendance, exams, accounts, payroll
                                    and everything from one powerful dashboard.</span>
                                </p>

                                <div class="step-list">

                                    <div class="step-item active">
                                        <div class="step-circle"><i class="bi bi-shield-check"></i></div>
                                        <div>
                                            <div class="step-title">
                                                <span class="lang-bn">নিরাপদ টেন্যান্ট প্রমাণীকরণ</span>
                                                <span class="lang-en">Secure Tenant Authentication</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="step-item active">
                                        <div class="step-circle"><i class="bi bi-speedometer2"></i></div>
                                        <div>
                                            <div class="step-title">
                                                <span class="lang-bn">দ্রুত ও আধুনিক ড্যাশবোর্ড</span>
                                                <span class="lang-en">Fast &amp; Modern Dashboard</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="step-item active">
                                        <div class="step-circle"><i class="bi bi-cloud-check"></i></div>
                                        <div>
                                            <div class="step-title">
                                                <span class="lang-bn">ক্লাউড ভিত্তিক শিক্ষা ব্যবস্থাপনা</span>
                                                <span class="lang-en">Cloud Based Education Management</span>
                                            </div>
                                        </div>
                                    </div>

                                </div>

                            </div>
                        </div>

                        {{-- =========================================================
                            CONTENT (Right) — same pattern as Wizard Content
                        ========================================================== --}}

                        <div class="col-lg-7">
                            <div class="wizard-content">

                                <h2 class="wizard-heading">
                                    <span class="lang-bn">লগ ইন</span>
                                    <span class="lang-en">Log In</span>
                                </h2>
                                <p class="wizard-text">
                                    <span class="lang-bn">আপনার প্রতিষ্ঠানের প্যানেলে যেতে লগইন করুন।</span>
                                    <span class="lang-en">Login to continue to your institution panel.</span>
                                </p>

                                <form wire:submit.prevent="login">

                                    {{-- IDENTIFIER --}}
                                    <div class="mb-3">
                                        <label class="form-label">
                                            <span class="lang-bn">ইউজারনেম</span>
                                            <span class="lang-en">Username</span>
                                            <span class="text-danger">*</span>
                                        </label>
                                        <input
                                            type="text"
                                            wire:model="identifier"
                                            class="form-control @error('identifier') is-invalid @enderror"
                                            placeholder="Username"
                                            autocomplete="username"
                                        >
                                        @error('identifier')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    {{-- PASSWORD --}}
                                    <div class="mb-3">
                                        <label class="form-label">
                                            <span class="lang-bn">পাসওয়ার্ড</span>
                                            <span class="lang-en">Password</span>
                                            <span class="text-danger">*</span>
                                        </label>
                                        <input
                                            type="password"
                                            wire:model="password"
                                            class="form-control @error('password') is-invalid @enderror"
                                            placeholder="********"
                                        >
                                        @error('password')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    {{-- REMEMBER + FORGOT --}}
                                    <div class="d-flex justify-content-between align-items-center mb-4">
                                        <div class="form-check">
                                            <input
                                                class="form-check-input"
                                                type="checkbox"
                                                wire:model="remember"
                                                id="remember"
                                            >
                                            <label class="form-check-label" for="remember">
                                                <span class="lang-bn">মনে রাখুন</span>
                                                <span class="lang-en">Remember Me</span>
                                            </label>
                                        </div>

                                        <a href="{{ route('forgot.password') }}" class="text-primary fw-semibold">
                                            <span class="lang-bn">পাসওয়ার্ড ভুলে গেছেন?</span>
                                            <span class="lang-en">Forgot Password?</span>
                                        </a>
                                    </div>

                                    {{-- SUBMIT --}}
                                    <button
                                        type="submit"
                                        class="btn btn-primary wizard-btn w-100"
                                        wire:loading.attr="disabled"
                                        wire:target="login"
                                    >
                                        <span wire:loading.remove wire:target="login">
                                            <span class="lang-bn">এখন লগইন করুন</span>
                                            <span class="lang-en">Login Now</span>
                                        </span>
                                        <span wire:loading wire:target="login">
                                            <span class="spinner-border spinner-border-sm me-2"></span>
                                            <span class="lang-bn">যাচাই করা হচ্ছে...</span>
                                            <span class="lang-en">Authenticating...</span>
                                        </span>
                                    </button>

                                </form>

                                <div class="auth-divider">
                                    <span>Institution ERP SaaS</span>
                                </div>

                                <div class="text-center auth-extra-links">
                                    <span class="lang-bn">অ্যাকাউন্ট নেই?</span>
                                    <span class="lang-en">Don't have an account?</span>
                                    <a href="{{ route('institution.registration') }}">
                                        <span class="lang-bn">প্রতিষ্ঠান তৈরি করুন</span>
                                        <span class="lang-en">Create Institution</span>
                                    </a>
                                </div>

                            </div>
                        </div>

                    </div>

                </div>
            </div>
        </div>
    </div>

</div>