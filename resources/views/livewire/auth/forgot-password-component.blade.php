<div class="min-vh-100 d-flex align-items-center justify-content-center login-page">

    {{-- =========================================================
        STYLES — Login page-এর সাথে হুবহু মিল
    ========================================================== --}}
    <style>
        :root {
            --primary: #4f46e5;
            --primary-dark: #4338ca;
            --light: #f8fafc;
            --border: #e5e7eb;
            --text: #111827;
            --muted: #6b7280;
        }

        body {
            background:
                radial-gradient(circle at top left, #6366f1 0%, transparent 30%),
                radial-gradient(circle at bottom right, #8b5cf6 0%, transparent 30%),
                #0f172a;
        }

        .login-page { padding: 40px 15px; }

        .login-card {
            width: 100%;
            max-width: 1100px;
            border: none;
            border-radius: 30px;
            overflow: hidden;
            background: rgba(255,255,255,.96);
            backdrop-filter: blur(15px);
            box-shadow:
                0 10px 40px rgba(0,0,0,.15),
                0 2px 8px rgba(0,0,0,.05);
        }

        .login-left {
            background: linear-gradient(180deg, #4f46e5 0%, #7c3aed 100%);
            color: white;
            padding: 60px 45px;
            position: relative;
            overflow: hidden;
        }

        .login-left::before {
            content: '';
            position: absolute;
            width: 400px; height: 400px;
            border-radius: 50%;
            background: rgba(255,255,255,.08);
            top: -150px; right: -150px;
        }

        .login-left::after {
            content: '';
            position: absolute;
            width: 250px; height: 250px;
            border-radius: 50%;
            background: rgba(255,255,255,.08);
            bottom: -100px; left: -100px;
        }

        .brand-logo {
            width: 75px; height: 75px;
            border-radius: 22px;
            background: rgba(255,255,255,.15);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 30px;
            margin-bottom: 25px;
            backdrop-filter: blur(10px);
            position: relative;
            z-index: 2;
        }

        .left-title {
            font-size: 38px;
            font-weight: 800;
            line-height: 1.2;
            position: relative;
            z-index: 2;
        }

        .left-text {
            margin-top: 18px;
            opacity: .9;
            line-height: 1.8;
            position: relative;
            z-index: 2;
        }

        .feature-list {
            margin-top: 45px;
            position: relative;
            z-index: 2;
        }

        .feature-item {
            display: flex;
            align-items: center;
            margin-bottom: 22px;
        }

        .feature-icon {
            width: 48px; height: 48px;
            border-radius: 14px;
            background: rgba(255,255,255,.12);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-size: 20px;
        }

        .login-right { padding: 60px 55px; }

        .login-heading {
            font-size: 34px;
            font-weight: 800;
            color: var(--text);
            margin-bottom: 10px;
        }

        .login-subtitle {
            color: var(--muted);
            margin-bottom: 40px;
        }

        .form-label {
            font-weight: 700;
            color: #374151;
            margin-bottom: 10px;
        }

        .form-control {
            min-height: 58px;
            border-radius: 16px;
            border: 1px solid var(--border);
            padding-left: 18px;
            transition: .25s;
        }

        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(79,70,229,.1);
        }

        .login-btn {
            min-height: 58px;
            border: none;
            border-radius: 16px;
            background: var(--primary);
            font-weight: 700;
            transition: .25s;
        }

        .login-btn:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }

        .login-btn:disabled {
            opacity: 1;
            background: var(--primary-dark);
            cursor: not-allowed;
        }

        .extra-links { margin-top: 25px; }

        .extra-links a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
        }

        .success-box {
            background: #ecfdf5;
            border: 1px solid #6ee7b7;
            border-radius: 16px;
            padding: 20px 24px;
            color: #065f46;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        @media(max-width: 991px) {
            .login-left { padding: 40px 30px; }
            .login-right { padding: 40px 25px; }
            .left-title { font-size: 30px; }
            .login-heading { font-size: 28px; }
        }
    </style>

    {{-- =========================================================
        CARD
    ========================================================== --}}

    <div class="card login-card">
        <div class="row g-0">

            {{-- =====================================================
                LEFT SIDE
            ====================================================== --}}

            <div class="col-lg-5">
                <div class="login-left h-100">

                    <div class="brand-logo">
                        <i class="bi bi-mortarboard-fill"></i>
                    </div>

                    <h2 class="left-title">
                        Password ভুলে গেছেন?
                    </h2>

                    <p class="left-text">
                        চিন্তা নেই! আপনার Email, Username
                        অথবা Phone নম্বর দিন। আমরা
                        আপনার Email-এ একটি Reset Link
                        পাঠিয়ে দেব।
                    </p>

                    <div class="feature-list">

                        <div class="feature-item">
                            <div class="feature-icon">
                                <i class="bi bi-envelope-check"></i>
                            </div>
                            <div>Reset Link আপনার Email-এ যাবে</div>
                        </div>

                        <div class="feature-item">
                            <div class="feature-icon">
                                <i class="bi bi-clock-history"></i>
                            </div>
                            <div>Link ৬০ মিনিট পর্যন্ত Valid থাকবে</div>
                        </div>

                        <div class="feature-item">
                            <div class="feature-icon">
                                <i class="bi bi-shield-lock"></i>
                            </div>
                            <div>সম্পূর্ণ নিরাপদ প্রক্রিয়া</div>
                        </div>

                    </div>

                </div>
            </div>

            {{-- =====================================================
                RIGHT SIDE
            ====================================================== --}}

            <div class="col-lg-7">
                <div class="login-right">

                    <h2 class="login-heading">
                        Password Reset
                    </h2>

                    <p class="login-subtitle">
                        আপনার Email, Username অথবা Phone দিন।
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
                                Email, Username অথবা Phone
                            </label>
                            <input
                                type="text"
                                class="form-control @error('identifier') is-invalid @enderror"
                                wire:model="identifier"
                                placeholder="admin@example.com / admin / 01700000000"
                                autocomplete="username"
                            >
                            @error('identifier')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        {{-- BUTTON --}}
                        <button
                            type="submit"
                            class="btn login-btn text-white w-100"
                            wire:loading.attr="disabled"
                            wire:target="sendResetLink"
                        >
                            <span wire:loading.remove wire:target="sendResetLink">
                                Reset Link পাঠান
                            </span>
                            <span wire:loading wire:target="sendResetLink">
                                <span class="spinner-border spinner-border-sm me-2"></span>
                                পাঠানো হচ্ছে...
                            </span>
                        </button>

                    </form>

                    <div class="text-center extra-links mt-4">
                        মনে পড়ে গেছে?
                        <a href="{{ route('login') }}">
                            Login-এ ফিরে যান
                        </a>
                    </div>

                </div>
            </div>

        </div>
    </div>

</div>