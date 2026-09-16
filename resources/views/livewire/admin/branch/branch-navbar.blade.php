<div class="card profile-card p-4 mb-4">
        <!-- Avatar + Info -->
        <div class="d-flex flex-wrap gap-4 align-items-start">
            <div class="avatar-wrap me-2">
                <img src="https://ui-avatars.com/api/?name={{ urlencode($branch->name) }}&size=160&background=random" alt="{{ $branch->name }}"/>
                <span class="online-dot"></span>
            </div>
            <div class="flex-grow-1">
                <!-- Name row -->
                <div class="d-flex flex-wrap justify-content-between align-items-start mb-2 gap-2">
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span class="text-dark fw-bold fs-4">{{ $branch->name }}</span>
                            @if($branch->is_main)
                                <i class="bi bi-patch-check-fill badge-verified fs-5"></i>
                            @endif
                        </div>
                        <div class="d-flex flex-wrap gap-3" style="font-size:.88rem;">
                            @if($branch->email)
                            <a href="mailto:{{ $branch->email }}" class="text-muted text-decoration-none d-flex align-items-center gap-1">
                                <span class="material-icons-round fs-6">email</span>{{ $branch->email }}</a>
                            @endif
                            @if($branch->phone)
                            <a href="tel:{{ $branch->phone }}" class="text-muted text-decoration-none d-flex align-items-center gap-1">
                                <span class="material-icons-round fs-6">phone</span>{{ $branch->phone }}</a>
                            @endif
                            @if($branch->address)
                            <span class="text-muted d-flex align-items-center gap-1">
                                <span class="material-icons-round fs-6">location_on</span>{{ $branch->address }}</span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="hero-badges">
                    <span class="badge bg-dark">Code: {{ $branch->code }}</span>
                    @if($branch->is_main)
                        <span class="badge bg-dark">Main Branch</span>
                    @endif
                    <span class="badge bg-dark">{{ $branch->is_active ? 'Active' : 'Inactive' }}</span>
                    @if($branch->institution)
                        <span class="badge bg-dark">{{ $branch->institution->name }}</span>
                    @endif
                </div>

                <!-- Stats -->
                <div class="d-flex flex-wrap align-items-center gap-3 mt-3">
                    <div class="stat-box">
                        <div class="d-flex align-items-center gap-1">
                            <span class="material-icons-round text-success" style="font-size:1rem">badge</span>
                            <span class="stat-num">{{ $branch->code }}</span>
                        </div>
                        <div class="stat-label">Branch Code</div>
                    </div>
                    <div class="stat-box">
                        <div class="d-flex align-items-center gap-1">
                            <span class="material-icons-round text-primary" style="font-size:1rem">group</span>
                            <span class="stat-num">{{ $branch->users_count ?? $branch->users->count() }}</span>
                        </div>
                        <div class="stat-label">Users</div>
                    </div>
                    <div class="stat-box">
                        <div class="d-flex align-items-center gap-1">
                            <span class="material-icons-round text-info" style="font-size:1rem">event</span>
                            <span class="stat-num">{{ $branch->created_at ? \Carbon\Carbon::parse($branch->created_at)->format('d M Y') : '—' }}</span>
                        </div>
                        <div class="stat-label">Created</div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Profile Tabs (hidden on print via no-print) -->
        <ul class="nav profile-tabs border-bottom mt-4 flex-nowrap overflow-auto no-print">
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs($routePrefix . 'branch.view') ? 'active' : '' }}"
                href="{{ route($routePrefix . 'branch.view', ['id' => $branch->id]) }}">
                    Overview
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs($routePrefix . 'branch.account') ? 'active' : '' }}"
                href="{{ route($routePrefix . 'branch.account', ['id' => $branch->id]) }}">
                    Account
                </a>
            </li>
        </ul>
    </div>