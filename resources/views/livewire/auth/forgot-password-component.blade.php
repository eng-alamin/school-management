<div class="auth-page-wrap d-flex align-items-center justify-content-center">

    <div class="container wizard-wrapper mt-5">
        <div class="row justify-content-center">
            <div class="col-12 col-xl-11">
                <div class="card wizard-card">

                    <div class="row g-0">

                        {{-- =========================================================
                            SIDEBAR (Left)
                        ========================================================== --}}

                        <div class="col-lg-5">
                            <div class="wizard-sidebar h-100">

                                <div class="brand-logo">
                                    <i class="bi bi-mortarboard-fill"></i>
                                </div>

                                <h2 class="sidebar-title">
                                    <span class="lang-bn">পাসওয়ার্ড ভুলে গেছেন?</span>
                                    <span class="lang-en">Forgot Your Password?</span>
                                </h2>

                                <p class="sidebar-subtitle">
                                    <span class="lang-bn">
                                        চিন্তা নেই! আপনার ইমেইল, ইউজারনেম
                                        অথবা ফোন নম্বর দিন। আমরা
                                        আপনার ইমেইলে একটি রিসেট লিংক
                                        পাঠিয়ে দেব।
                                    </span>
                                    <span class="lang-en">
                                        No worries! Enter your email, username,
                                        or phone number and we'll send a
                                        reset link to your email.
                                    </span>
                                </p>

                                <div class="step-list">

                                    <div class="step-item active">
                                        <div class="step-circle"><i class="bi bi-envelope-check"></i></div>
                                        <div>
                                            <div class="step-title">
                                                <span class="lang-bn">রিসেট লিংক আপনার ইমেইলে যাবে</span>
                                                <span class="lang-en">Reset link will be sent to your email</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="step-item active">
                                        <div class="step-circle"><i class="bi bi-clock-history"></i></div>
                                        <div>
                                            <div class="step-title">
                                                <span class="lang-bn">লিংক ৬০ মিনিট পর্যন্ত কার্যকর থাকবে</span>
                                                <span class="lang-en">Link stays valid for 60 minutes</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="step-item active">
                                        <div class="step-circle"><i class="bi bi-shield-lock"></i></div>
                                        <div>
                                            <div class="step-title">
                                                <span class="lang-bn">সম্পূর্ণ নিরাপদ প্রক্রিয়া</span>
                                                <span class="lang-en">Completely secure process</span>
                                            </div>
                                        </div>
                                    </div>

                                </div>

                            </div>
                        </div>

                        {{-- =========================================================
                            CONTENT (Right)
                        ========================================================== --}}

                        <div class="col-lg-7">
                            <div class="wizard-content">

                                <h2 class="wizard-heading">
                                    <span class="lang-bn">পাসওয়ার্ড রিসেট</span>
                                    <span class="lang-en">Password Reset</span>
                                </h2>
                                <p class="wizard-text">
                                    <span class="lang-bn">আপনার ইমেইল, ইউজারনেম অথবা ফোন নম্বর দিন।</span>
                                    <span class="lang-en">Enter your email, username, or phone.</span>
                                </p>

                                {{-- SUCCESS MESSAGE --}}
                                @if($successMessage)
                                    <div class="success-box mb-4">
                                        <i class="bi bi-check-circle-fill fs-4"></i>
                                        <span>{{ $successMessage }}</span>
                                    </div>
                                @endif

                                <form wire:submit.prevent="sendResetLink">

                                    {{-- IDENTIFIER --}}
                                    <div class="mb-4">
                                        <label class="form-label">
                                            <span class="lang-bn">ইমেইল, ইউজারনেম অথবা ফোন নম্বর</span>
                                            <span class="lang-en">Email, Username, or Phone</span>
                                        </label>
                                        <input
                                            type="text"
                                            wire:model="identifier"
                                            class="form-control @error('identifier') is-invalid @enderror"
                                            placeholder="admin@example.com / admin / 01700000000"
                                            autocomplete="username"
                                        >
                                        @error('identifier')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    {{-- BUTTON --}}
                                    <button
                                        type="submit"
                                        class="btn btn-primary wizard-btn w-100"
                                        wire:loading.attr="disabled"
                                        wire:target="sendResetLink"
                                    >
                                        <span wire:loading.remove wire:target="sendResetLink">
                                            <span class="lang-bn">রিসেট লিংক পাঠান</span>
                                            <span class="lang-en">Send Reset Link</span>
                                        </span>
                                        <span wire:loading wire:target="sendResetLink">
                                            <span class="spinner-border spinner-border-sm me-2"></span>
                                            <span class="lang-bn">পাঠানো হচ্ছে...</span>
                                            <span class="lang-en">Sending...</span>
                                        </span>
                                    </button>

                                </form>

                                <div class="text-center auth-extra-links mt-4">
                                    <span class="lang-bn">মনে পড়ে গেছে?</span>
                                    <span class="lang-en">Remembered your password?</span>
                                    <a href="{{ route('login') }}">
                                        <span class="lang-bn">লগইনে ফিরে যান</span>
                                        <span class="lang-en">Back to Login</span>
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