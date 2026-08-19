<div class="card-custom profile-card p-4 mb-4">
    <!-- Avatar + Info -->
    <div class="d-flex flex-wrap gap-4 align-items-start">
        <div class="avatar-wrap me-2">
            @if($guardian->photo)
                <img src="{{ asset('storage/' . $guardian->photo) }}" alt="{{ $guardian->name }}"/>
            @else
                <img src="https://ui-avatars.com/api/?name={{ urlencode($guardian->name) }}&size=160&background=random" alt="{{ $guardian->name }}"/>
            @endif
            <span class="online-dot"></span>
        </div>
        <div class="flex-grow-1">
            <!-- Name + Actions row -->
            <div class="d-flex flex-wrap justify-content-between align-items-start mb-2 gap-2">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <a href="#" class="text-decoration-none text-dark fw-bold fs-4">{{ $guardian->name }}</a>
                        <i class="bi bi-patch-check-fill badge-verified fs-5"></i>
                    </div>
                    <div class="d-flex flex-wrap gap-3" style="font-size:.88rem;">
                        @if($guardian->email)
                        <a href="mailto:{{ $guardian->email }}" class="text-muted text-decoration-none d-flex align-items-center gap-1">
                            <span class="material-icons-round fs-6">email</span>{{ $guardian->email }}</a>
                        @endif
                        @if($guardian->mobile)
                        <a href="tel:{{ $guardian->mobile }}" class="text-muted text-decoration-none d-flex align-items-center gap-1">
                            <span class="material-icons-round fs-6">phone</span>{{ $guardian->mobile }}</a>
                        @endif
                        @if($guardian->address)
                        <span class="text-muted d-flex align-items-center gap-1">
                            <span class="material-icons-round fs-6">location_on</span>{{ $guardian->address }}</span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="hero-badges">
                @if($guardian->relation)
                    <span class="badge bg-dark">{{ ucfirst($guardian->relation) }}</span>
                @endif
                @if($guardian->gender)
                    <span class="badge bg-dark">{{ ucfirst($guardian->gender) }}</span>
                @endif
                @if($guardian->nid_no)
                    <span class="badge bg-dark">NID: {{ $guardian->nid_no }}</span>
                @endif
                @if($guardian->students->count())
                    <span class="badge bg-dark">{{ $guardian->students->count() }} Student(s)</span>
                @endif
            </div>

            <!-- Stats -->
            <div class="d-flex flex-wrap align-items-center gap-3 mt-3">
                <div class="stat-box">
                    <div class="d-flex align-items-center gap-1">
                        <span class="material-icons-round text-success" style="font-size:1rem">people</span>
                        <span class="stat-num">{{ $guardian->students->count() }}</span>
                    </div>
                    <div class="stat-label">Children</div>
                </div>
                <div class="stat-box">
                    <div class="d-flex align-items-center gap-1">
                        <span class="material-icons-round text-primary" style="font-size:1rem">work</span>
                        <span class="stat-num">{{ $guardian->occupation ?? '—' }}</span>
                    </div>
                    <div class="stat-label">Occupation</div>
                </div>
                <div class="stat-box">
                    <div class="d-flex align-items-center gap-1">
                        <span class="material-icons-round text-info" style="font-size:1rem">family_restroom</span>
                        <span class="stat-num">{{ ucfirst($guardian->relation ?? '—') }}</span>
                    </div>
                    <div class="stat-label">Relation</div>
                </div>
            </div>

        </div>
    </div>

    <!-- Profile Tabs -->
    <ul class="nav profile-tabs border-bottom mt-4 flex-nowrap overflow-auto no-print">
        <li class="nav-item"><a class="nav-link {{ request()->routeIs($routePrefix . 'parent.overview') ? 'active' : '' }}" href="{{ route($routePrefix . 'parent.overview', ['id' => $guardian->id]) }}">Overview</a></li>
        <li class="nav-item"><a class="nav-link {{ request()->routeIs($routePrefix . 'parent.child') ? 'active' : '' }}" href="{{ route($routePrefix . 'parent.child', ['id' => $guardian->id]) }}">Children</a></li>
        <li class="nav-item"><a class="nav-link {{ request()->routeIs($routePrefix . 'parent.account') ? 'active' : '' }}" href="{{ route($routePrefix . 'parent.account', ['id' => $guardian->id]) }}">Account</a></li>
    </ul>
</div>