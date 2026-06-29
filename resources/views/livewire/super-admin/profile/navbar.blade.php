<div class="card profile-card p-4 mb-4">
    <!-- Avatar + Info -->
    <div class="d-flex flex-wrap gap-4 align-items-start">
        <div class="avatar-wrap me-2">
            @if($user->avatar)
                <img src="{{ asset('storage/' . $user->avatar) }}" alt="{{ $user->name }}"/>
            @else
                <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&size=160&background=random" alt="{{ $user->name }}"/>
            @endif
            <span class="online-dot"></span>
        </div>
        <div class="flex-grow-1">
            <!-- Name + Actions row -->
            <div class="d-flex flex-wrap justify-content-between align-items-start mb-2 gap-2">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <a href="#" class="text-decoration-none text-dark fw-bold fs-4">{{ $user->name }}</a>
                        <i class="bi bi-patch-check-fill badge-verified fs-5"></i>
                    </div>
                    <div class="d-flex flex-wrap gap-3" style="font-size:.88rem;">
                        @if($user->email)
                        <a href="mailto:{{ $user->email }}" class="text-muted text-decoration-none d-flex align-items-center gap-1">
                            <span class="material-icons-round fs-6">email</span>{{ $user->email }}</a>
                        @endif
                        @if($user->username)
                        <span class="text-muted d-flex align-items-center gap-1">
                            <span class="material-icons-round fs-6">alternate_email</span>{{ $user->username }}</span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Stats -->
            <div class="d-flex flex-wrap align-items-center gap-3 mt-3">
                <div class="stat-box">
                    <div class="d-flex align-items-center gap-1">
                        <span class="material-icons-round text-primary" style="font-size:1rem">shield</span>
                        <span class="stat-num">{{ ucfirst($user->role) }}</span>
                    </div>
                    <div class="stat-label">Role</div>
                </div>
                <div class="stat-box">
                    <div class="d-flex align-items-center gap-1">
                        <span class="material-icons-round text-success" style="font-size:1rem">how_to_reg</span>
                        <span class="stat-num">{{ $user->created_at ? $user->created_at->format('M Y') : '—' }}</span>
                    </div>
                    <div class="stat-label">Joined</div>
                </div>
                <div class="stat-box">
                    <div class="d-flex align-items-center gap-1">
                        <span class="material-icons-round text-info" style="font-size:1rem">login</span>
                        <span class="stat-num">{{ $user->updated_at ? $user->updated_at->diffForHumans() : '—' }}</span>
                    </div>
                    <div class="stat-label">Last Active</div>
                </div>
            </div>

        </div>
    </div>

    <!-- Profile Tabs -->
    <ul class="nav profile-tabs border-bottom mt-4 flex-nowrap overflow-auto">
        <li class="nav-item"><a class="nav-link {{ request()->routeIs('superadmin.profile.overview') ? 'active' : '' }}" href="{{ route('superadmin.profile.overview') }}">Overview</a></li>
        <li class="nav-item"><a class="nav-link {{ request()->routeIs('superadmin.profile.setting') ? 'active' : '' }}" href="{{ route('superadmin.profile.setting') }}">Settings</a></li>
        <li class="nav-item"><a class="nav-link {{ request()->routeIs('superadmin.profile.loginlog') ? 'active' : '' }}" href="{{ route('superadmin.profile.loginlog') }}">Login Logs </a></li>
    </ul>
</div>