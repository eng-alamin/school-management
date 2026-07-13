<nav class="navbar navbar-expand-lg fixed-top py-3">
    <div class="container">
    <a class="navbar-brand" href="#">
        <img
        src="https://placehold.co/150x40/198754/ffffff?text=EMS"
        alt="GovEdu Logo"
        class="rounded"
        />
    </a>
    <button
        class="navbar-toggler"
        type="button"
        data-bs-toggle="collapse"
        data-bs-target="#navbarMain"
    >
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarMain">
        <ul class="navbar-nav mx-auto mb-2 mb-lg-0 gap-1">
            <li class="nav-item">
                <a class="nav-link {{ Route::is('home') == true ? 'active' : '' }}" href="{{ route('home') }}">
                <span class="lang-bn">হোম</span
                ><span class="lang-en">Home</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#security-section">
                <span class="lang-bn">নিরাপত্তা</span
                ><span class="lang-en">Security</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#contact-section">
                <span class="lang-bn">যোগাযোগ</span
                ><span class="lang-en">Contact</span>
                </a>
            </li>

            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle {{ str_contains(request()->url(), 'find-institution') == true ? 'active' : '' }}" href="#" data-bs-toggle="dropdown">
                    <span class="lang-bn">প্রতিষ্ঠান</span><span class="lang-en">Institution</span>
                </a>
                <ul class="dropdown-menu">
                    <li>
                        <a class="dropdown-item" href="{{ route('admission.online') }}">
                            <span class="lang-bn">অনলাইন ভর্তি</span><span class="lang-en">Online Admission</span>
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="{{ route('find.institution') }}">
                            <span class="lang-bn">প্রতিষ্ঠান খুঁজুন</span><span class="lang-en">Find Institution</span>
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href={{ route('teacher.registration') }}>
                            <span class="lang-bn">শিক্ষক নিবন্ধন</span><span class="lang-en">Teacher Registration</span>
                        </a>
                    </li>
                </ul>
            </li>
        </ul>
        <div class="d-flex align-items-center gap-3 flex-wrap">
        <!-- Language Toggle -->
        <button class="lang-btn" id="langToggle">EN</button>
        <!-- Dark Mode Toggle -->
        <div class="theme-toggle-wrap">
            <i
            class="bi bi-sun-fill"
            style="color: var(--accent2); font-size: 0.95rem"
            ></i>
            <div
            class="theme-toggle"
            id="themeToggle"
            role="button"
            aria-label="Toggle dark mode"
            ></div>
            <i
            class="bi bi-moon-fill"
            style="color: #8ca9b5; font-size: 0.85rem"
            ></i>
        </div>
        @auth
            <a href="{{ route('dashboard') }}" class="btn btn-success btn-sm">
                <span class="lang-bn">ড্যাশবোর্ড</span>
                <span class="lang-en">Dashboard</span>
            </a>
        @else
            <a href="{{ route('login') }}" class="btn btn-outline-primary btn-sm">
                <span class="lang-bn">লগইন</span>
                <span class="lang-en">Login</span>
            </a>
            <a href="{{route('institution.registration')}}" class="btn btn-primary btn-sm">
                <span class="lang-bn">বিনামূল্যে নিবন্ধন</span>
                <span class="lang-en">Registration Free</span>
            </a>
        @endauth
        </div>
    </div>
    </div>
</nav>