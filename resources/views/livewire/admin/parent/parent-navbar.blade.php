<div class="card-custom profile-card p-4 mb-4">
    <!-- Avatar + Info -->
    <div class="d-flex flex-wrap gap-4 align-items-start">
        <div class="avatar-wrap me-2">
            @if($parent->photo)
                <img src="{{ asset('storage/' . $parent->photo) }}" alt="{{ $parent->name }}"/>
            @else
                <img src="https://ui-avatars.com/api/?name={{ urlencode($parent->name) }}&size=160&background=random" alt="{{ $parent->name }}"/>
            @endif
            <span class="online-dot"></span>
        </div>
        <div class="flex-grow-1">
            <!-- Name + Actions row -->
            <div class="d-flex flex-wrap justify-content-between align-items-start mb-2 gap-2">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <a href="#" class="text-decoration-none text-dark fw-bold fs-4">{{ $parent->name }}</a>
                        <i class="bi bi-patch-check-fill badge-verified fs-5"></i>
                    </div>
                    <div class="d-flex flex-wrap gap-3" style="font-size:.88rem;">
                        @if($parent->email)
                        <a href="mailto:{{ $parent->email }}" class="text-muted text-decoration-none d-flex align-items-center gap-1">
                            <span class="material-icons-round fs-6">email</span>{{ $parent->email }}</a>
                        @endif
                        @if($parent->mobile)
                        <a href="tel:{{ $parent->mobile }}" class="text-muted text-decoration-none d-flex align-items-center gap-1">
                            <span class="material-icons-round fs-6">phone</span>{{ $parent->mobile }}</a>
                        @endif
                        @if($parent->address)
                        <span class="text-muted d-flex align-items-center gap-1">
                            <span class="material-icons-round fs-6">location_on</span>{{ $parent->address }}</span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="hero-badges">
                @if($parent->relation)
                    <span class="badge bg-dark">{{ ucfirst($parent->relation) }}</span>
                @endif
                @if($parent->gender)
                    <span class="badge bg-dark">{{ ucfirst($parent->gender) }}</span>
                @endif
                @if($parent->nid_no)
                    <span class="badge bg-dark">NID: {{ $parent->nid_no }}</span>
                @endif
                @if($parent->students->count())
                    <span class="badge bg-dark">{{ $parent->students->count() }} Student(s)</span>
                @endif
            </div>

            <!-- Stats -->
            <div class="d-flex flex-wrap align-items-center gap-3 mt-3">
                <div class="stat-box">
                    <div class="d-flex align-items-center gap-1">
                        <span class="material-icons-round text-success" style="font-size:1rem">people</span>
                        <span class="stat-num">{{ $parent->students->count() }}</span>
                    </div>
                    <div class="stat-label">Children</div>
                </div>
                <div class="stat-box">
                    <div class="d-flex align-items-center gap-1">
                        <span class="material-icons-round text-primary" style="font-size:1rem">work</span>
                        <span class="stat-num">{{ $parent->occupation ?? '—' }}</span>
                    </div>
                    <div class="stat-label">Occupation</div>
                </div>
                <div class="stat-box">
                    <div class="d-flex align-items-center gap-1">
                        <span class="material-icons-round text-info" style="font-size:1rem">family_restroom</span>
                        <span class="stat-num">{{ ucfirst($parent->relation ?? '—') }}</span>
                    </div>
                    <div class="stat-label">Relation</div>
                </div>
            </div>

        </div>
    </div>

    <!-- Profile Tabs -->
    <ul class="nav profile-tabs border-bottom mt-4 flex-nowrap overflow-auto">
        <li class="nav-item"><a class="nav-link {{ request()->routeIs('admin.parent.overview') ? 'active' : '' }}" href="{{ route('admin.parent.overview', ['id' => $parent->id]) }}">Overview</a></li>
        <li class="nav-item"><a class="nav-link {{ request()->routeIs('admin.parent.child') ? 'active' : '' }}" href="{{ route('admin.parent.child', ['id' => $parent->id]) }}">Children</a></li>
        <li class="nav-item"><a class="nav-link" href="#">Documents</a></li>
        <li class="nav-item"><a class="nav-link" href="#">Activity</a></li>
    </ul>
</div>