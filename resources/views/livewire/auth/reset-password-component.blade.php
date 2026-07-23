<div class="auth-page-wrap d-flex align-items-center justify-content-center">

    <div class="container wizard-wrapper mt-5">
        <div class="row justify-content-center">
            <div class="col-12 col-xl-11">
                <div class="card wizard-card">

                    @if($submitted)

                        {{-- ══════════ SUCCESS PANEL ══════════ --}}
                        <div class="p-5 text-center">
                            <div class="success-animation mb-4">
                                <i class="bi bi-check-lg"></i>
                            </div>
                            <h4 class="mt-2">
                                <span class="lang-bn">পাসওয়ার্ড সফলভাবে পরিবর্তন হয়েছে!</span>
                                <span class="lang-en">Password Changed Successfully!</span>
                            </h4>
                            <p class="text-muted mt-2">
                                <span class="lang-bn">আপনার নতুন পাসওয়ার্ড দিয়ে এখন লগইন করতে পারবেন।</span>
                                <span class="lang-en">You can now log in using your new password.</span>
                            </p>

                            <a href="{{ route('login') }}" class="btn btn-primary mt-3 px-4">
                                <span class="lang-bn">লগইনে যান</span>
                                <span class="lang-en">Go to Login</span>
                            </a>
                        </div>

                    @else

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
                                        <span class="lang-bn">নতুন পাসওয়ার্ড সেট করুন</span>
                                        <span class="lang-en">Set a New Password</span>
                                    </h2>

                                    <p class="sidebar-subtitle">
                                        <span class="lang-bn">
                                            শক্তিশালী পাসওয়ার্ড ব্যবহার করুন।
                                            অক্ষর, সংখ্যা এবং চিহ্নের মিশ্রণে
                                            তৈরি পাসওয়ার্ড সবচেয়ে নিরাপদ।
                                        </span>
                                        <span class="lang-en">
                                            Use a strong password. A mix of letters,
                                            numbers, and symbols keeps your account
                                            the safest.
                                        </span>
                                    </p>

                                    <div class="step-list">

                                        <div class="step-item active">
                                            <div class="step-circle"><i class="bi bi-key-fill"></i></div>
                                            <div>
                                                <div class="step-title">
                                                    <span class="lang-bn">কমপক্ষে ৮ অক্ষরের পাসওয়ার্ড দিন</span>
                                                    <span class="lang-en">Use at least 8 characters</span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="step-item active">
                                            <div class="step-circle"><i class="bi bi-shield-check"></i></div>
                                            <div>
                                                <div class="step-title">
                                                    <span class="lang-bn">পাসওয়ার্ড কাউকে শেয়ার করবেন না</span>
                                                    <span class="lang-en">Never share your password with anyone</span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="step-item active">
                                            <div class="step-circle"><i class="bi bi-arrow-repeat"></i></div>
                                            <div>
                                                <div class="step-title">
                                                    <span class="lang-bn">এই লিংক একবারই ব্যবহার করা যাবে</span>
                                                    <span class="lang-en">This link can only be used once</span>
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
                                        <span class="lang-bn">নতুন পাসওয়ার্ড দিন এবং নিশ্চিত করুন।</span>
                                        <span class="lang-en">Enter and confirm your new password.</span>
                                    </p>

                                    <form wire:submit.prevent="resetPassword">

                                        {{-- NEW PASSWORD --}}
                                        <div class="mb-2">
                                            <label class="form-label">
                                                <span class="lang-bn">নতুন পাসওয়ার্ড</span>
                                                <span class="lang-en">New Password</span>
                                            </label>
                                            <div class="input-wrap">
                                                <input
                                                    type="{{ $showPassword ? 'text' : 'password' }}"
                                                    wire:model="password"
                                                    class="form-control input-wrap-control @error('password') is-invalid @enderror"
                                                    placeholder="At least 8 characters"
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
                                            <label class="form-label">
                                                <span class="lang-bn">পাসওয়ার্ড নিশ্চিত করুন</span>
                                                <span class="lang-en">Confirm Password</span>
                                            </label>
                                            <div class="input-wrap">
                                                <input
                                                    type="{{ $showConfirmPassword ? 'text' : 'password' }}"
                                                    wire:model="password_confirmation"
                                                    class="form-control input-wrap-control @error('password_confirmation') is-invalid @enderror"
                                                    placeholder="Re-enter password"
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
                                                        <i class="bi bi-check-circle-fill me-1"></i>
                                                        <span class="lang-bn">পাসওয়ার্ড মিলেছে</span>
                                                        <span class="lang-en">Passwords match</span>
                                                    </small>
                                                @else
                                                    <small class="text-danger">
                                                        <i class="bi bi-x-circle-fill me-1"></i>
                                                        <span class="lang-bn">পাসওয়ার্ড মিলছে না</span>
                                                        <span class="lang-en">Passwords do not match</span>
                                                    </small>
                                                @endif
                                            @endif
                                        </div>

                                        {{-- SUBMIT --}}
                                        <button
                                            type="submit"
                                            class="btn btn-primary wizard-btn w-100"
                                            wire:loading.attr="disabled"
                                            wire:target="resetPassword"
                                        >
                                            <span wire:loading.remove wire:target="resetPassword">
                                                <i class="bi bi-check2-circle me-2"></i>
                                                <span class="lang-bn">পাসওয়ার্ড পরিবর্তন করুন</span>
                                                <span class="lang-en">Change Password</span>
                                            </span>
                                            <span wire:loading wire:target="resetPassword">
                                                <span class="spinner-border spinner-border-sm me-2"></span>
                                                <span class="lang-bn">পরিবর্তন হচ্ছে...</span>
                                                <span class="lang-en">Changing...</span>
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

                    @endif

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

            const isBn = document.documentElement.getAttribute('data-lang') === 'bn';

            const levels = isBn ? [
                { w: '20%',  bg: '#ef4444', label: 'খুব দুর্বল' },
                { w: '40%',  bg: '#f97316', label: 'দুর্বল' },
                { w: '60%',  bg: '#eab308', label: 'মোটামুটি' },
                { w: '80%',  bg: '#22c55e', label: 'ভালো' },
                { w: '100%', bg: '#16a34a', label: 'শক্তিশালী' },
            ] : [
                { w: '20%',  bg: '#ef4444', label: 'Very Weak' },
                { w: '40%',  bg: '#f97316', label: 'Weak' },
                { w: '60%',  bg: '#eab308', label: 'Fair' },
                { w: '80%',  bg: '#22c55e', label: 'Good' },
                { w: '100%', bg: '#16a34a', label: 'Strong' },
            ];

            const lvl = levels[Math.min(score, 4)];
            fill.style.width      = lvl.w;
            fill.style.background = lvl.bg;
            text.textContent      = lvl.label;
            text.style.color      = lvl.bg;
        }

        // Livewire update হলে strength check করো
        Livewire.hook('commit', () => { setTimeout(checkStrength, 10); });

        // Language toggle হলেও strength text ঠিক ভাষায় re-render হবে
        document.addEventListener('click', (e) => {
            if (e.target.closest('#langToggle')) setTimeout(checkStrength, 50);
        });
    });
</script>
@endpush