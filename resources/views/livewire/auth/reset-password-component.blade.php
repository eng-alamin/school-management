<div class="min-vh-100 d-flex align-items-center justify-content-center login-page">

    <style>
        :root {
            --primary: #4f46e5;
            --primary-dark: #4338ca;
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

        .input-wrap {
            position: relative;
        }

        .form-control {
            min-height: 58px;
            border-radius: 16px;
            border: 1px solid var(--border);
            padding-left: 18px;
            padding-right: 52px;
            transition: .25s;
        }

        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(79,70,229,.1);
        }

        .toggle-eye {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--muted);
            cursor: pointer;
            padding: 4px;
            font-size: 18px;
            line-height: 1;
        }

        .toggle-eye:hover { color: var(--primary); }

        /* Password strength bar */
        .strength-bar {
            height: 4px;
            border-radius: 4px;
            margin-top: 8px;
            background: #e5e7eb;
            overflow: hidden;
        }

        .strength-fill {
            height: 100%;
            border-radius: 4px;
            transition: width .3s, background .3s;
        }

        .strength-text {
            font-size: 12px;
            margin-top: 4px;
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

        @media(max-width: 991px) {
            .login-left { padding: 40px 30px; }
            .login-right { padding: 40px 25px; }
            .left-title { font-size: 30px; }
            .login-heading { font-size: 28px; }
        }
    </style>

    <div class="card login-card">
        <div class="row g-0">

            {{-- LEFT SIDE --}}
            <div class="col-lg-5">
                <div class="login-left h-100">

                    <div class="brand-logo">
                        <i class="bi bi-mortarboard-fill"></i>
                    </div>

                    <h2 class="left-title">
                        নতুন Password সেট করুন
                    </h2>

                    <p class="left-text">
                        শক্তিশালী Password ব্যবহার করুন।
                        অক্ষর, সংখ্যা এবং চিহ্নের মিশ্রণে
                        তৈরি Password সবচেয়ে নিরাপদ।
                    </p>

                    <div class="feature-list">

                        <div class="feature-item">
                            <div class="feature-icon">
                                <i class="bi bi-key-fill"></i>
                            </div>
                            <div>কমপক্ষে ৮ অক্ষরের Password দিন</div>
                        </div>

                        <div class="feature-item">
                            <div class="feature-icon">
                                <i class="bi bi-shield-check"></i>
                            </div>
                            <div>Password কাউকে শেয়ার করবেন না</div>
                        </div>

                        <div class="feature-item">
                            <div class="feature-icon">
                                <i class="bi bi-arrow-repeat"></i>
                            </div>
                            <div>এই Link একবারই ব্যবহার করা যাবে</div>
                        </div>

                    </div>

                </div>
            </div>

            {{-- RIGHT SIDE --}}
            <div class="col-lg-7">
                <div class="login-right">

                    <h2 class="login-heading">Password Reset</h2>

                    <p class="login-subtitle">
                        নতুন Password দিন এবং নিশ্চিত করুন।
                    </p>

                    <form wire:submit.prevent="resetPassword">

                        {{-- EMAIL (readonly) --}}
                        <div class="mb-4">
                            <label class="form-label">Email</label>
                            <input
                                type="email"
                                class="form-control @error('email') is-invalid @enderror"
                                wire:model="email"
                                placeholder="your@email.com"
                                autocomplete="email"
                            >
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- NEW PASSWORD --}}
                        <div class="mb-2">
                            <label class="form-label">নতুন Password</label>
                            <div class="input-wrap">
                                <input
                                    type="{{ $showPassword ? 'text' : 'password' }}"
                                    class="form-control @error('password') is-invalid @enderror"
                                    wire:model="password"
                                    placeholder="কমপক্ষে ৮ অক্ষর"
                                    autocomplete="new-password"
                                >
                                <button
                                    type="button"
                                    class="toggle-eye"
                                    wire:click="$toggle('showPassword')"
                                    tabindex="-1">
                                    <i class="bi {{ $showPassword ? 'bi-eye-slash' : 'bi-eye' }}"></i>
                                </button>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Strength bar --}}
                            @if ($password)
                            <div class="strength-bar">
                                <div class="strength-fill" id="strength-fill"></div>
                            </div>
                            <div class="strength-text" id="strength-text"></div>
                            @endif
                        </div>

                        {{-- CONFIRM PASSWORD --}}
                        <div class="mb-4">
                            <label class="form-label">Password নিশ্চিত করুন</label>
                            <div class="input-wrap">
                                <input
                                    type="{{ $showConfirmPassword ? 'text' : 'password' }}"
                                    class="form-control @error('password_confirmation') is-invalid @enderror"
                                    wire:model="password_confirmation"
                                    placeholder="Password আবার দিন"
                                    autocomplete="new-password"
                                >
                                <button
                                    type="button"
                                    class="toggle-eye"
                                    wire:click="$toggle('showConfirmPassword')"
                                    tabindex="-1">
                                    <i class="bi {{ $showConfirmPassword ? 'bi-eye-slash' : 'bi-eye' }}"></i>
                                </button>
                                @error('password_confirmation')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Match indicator --}}
                            @if ($password && $password_confirmation)
                                @if ($password === $password_confirmation)
                                    <small class="text-success">
                                        <i class="bi bi-check-circle-fill me-1"></i>Password মিলেছে
                                    </small>
                                @else
                                    <small class="text-danger">
                                        <i class="bi bi-x-circle-fill me-1"></i>Password মিলছে না
                                    </small>
                                @endif
                            @endif
                        </div>

                        {{-- SUBMIT --}}
                        <button
                            type="submit"
                            class="btn login-btn text-white w-100"
                            wire:loading.attr="disabled"
                            wire:target="resetPassword"
                        >
                            <span wire:loading.remove wire:target="resetPassword">
                                <i class="bi bi-check2-circle me-2"></i>Password পরিবর্তন করুন
                            </span>
                            <span wire:loading wire:target="resetPassword">
                                <span class="spinner-border spinner-border-sm me-2"></span>
                                পরিবর্তন হচ্ছে...
                            </span>
                        </button>

                    </form>

                    <div class="text-center extra-links mt-4">
                        মনে পড়ে গেছে?
                        <a href="{{ route('login') }}">Login-এ ফিরে যান</a>
                    </div>

                </div>
            </div>

        </div>
    </div>

</div>

@push('scripts')
<script>
    document.addEventListener('livewire:initialized', () => {
        Livewire.on('input', () => checkStrength());

        function checkStrength() {
            const pw   = @js($password ?? '');
            const fill = document.getElementById('strength-fill');
            const text = document.getElementById('strength-text');
            if (!fill || !text || !pw) return;

            let score = 0;
            if (pw.length >= 8)  score++;
            if (pw.length >= 12) score++;
            if (/[A-Z]/.test(pw))        score++;
            if (/[0-9]/.test(pw))        score++;
            if (/[^A-Za-z0-9]/.test(pw)) score++;

            const levels = [
                { w: '20%',  bg: '#ef4444', label: 'খুব দুর্বল' },
                { w: '40%',  bg: '#f97316', label: 'দুর্বল' },
                { w: '60%',  bg: '#eab308', label: 'মোটামুটি' },
                { w: '80%',  bg: '#22c55e', label: 'ভালো' },
                { w: '100%', bg: '#16a34a', label: 'শক্তিশালী' },
            ];

            const lvl = levels[Math.min(score, 4)];
            fill.style.width      = lvl.w;
            fill.style.background = lvl.bg;
            text.textContent      = lvl.label;
            text.style.color      = lvl.bg;
        }

        // Livewire update হলে strength check করো
        Livewire.hook('commit', () => { setTimeout(checkStrength, 10); });
    });
</script>
@endpush