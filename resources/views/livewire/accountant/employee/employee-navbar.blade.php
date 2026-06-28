<div class="card-custom profile-card p-4 mb-4">
    <!-- Avatar + Info -->
    <div class="d-flex flex-wrap gap-4 align-items-start">
        <div class="avatar-wrap me-2">
            @if($employee->photo)
                <img src="{{ asset('storage/' . $employee->photo) }}" alt="{{ $employee->name }}"/>
            @else
                <img src="https://ui-avatars.com/api/?name={{ urlencode($employee->name) }}&size=160&background=random" alt="{{ $employee->name }}"/>
            @endif
            <span class="online-dot"></span>
        </div>
        <div class="flex-grow-1">
            <!-- Name + Actions row -->
            <div class="d-flex flex-wrap justify-content-between align-items-start mb-2 gap-2">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <a href="#" class="text-decoration-none text-dark fw-bold fs-4">{{ $employee->name }}</a>
                        <i class="bi bi-patch-check-fill badge-verified fs-5"></i>
                    </div>
                    <div class="d-flex flex-wrap gap-3" style="font-size:.88rem;">
                        @if($employee->email)
                        <a href="mailto:{{ $employee->email }}" class="text-muted text-decoration-none d-flex align-items-center gap-1">
                            <span class="material-icons-round fs-6">email</span>{{ $employee->email }}</a>
                        @endif
                        @if($employee->mobile)
                        <a href="tel:{{ $employee->mobile }}" class="text-muted text-decoration-none d-flex align-items-center gap-1">
                            <span class="material-icons-round fs-6">phone</span>{{ $employee->mobile }}</a>
                        @endif
                        @if($employee->present_address)
                        <span class="text-muted d-flex align-items-center gap-1">
                            <span class="material-icons-round fs-6">location_on</span>{{ $employee->present_address }}</span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="hero-badges">
                @if($employee->user->role ?? null)
                    <span class="badge bg-dark">{{ ucfirst($employee->user->role) }}</span>
                @endif
                @if($employee->designation)
                    <span class="badge bg-dark">{{ $employee->designation->name }}</span>
                @endif
                @if($employee->department)
                    <span class="badge bg-dark">{{ $employee->department->name }}</span>
                @endif
                @if($employee->employee_id)
                    <span class="badge bg-dark">ID: {{ $employee->employee_id }}</span>
                @endif
            </div>

            <!-- Stats -->
            <div class="d-flex flex-wrap align-items-center gap-3 mt-3">
                <div class="stat-box">
                    <div class="d-flex align-items-center gap-1">
                        <span class="material-icons-round text-success" style="font-size:1rem">badge</span>
                        <span class="stat-num">{{ $employee->designation->name ?? '—' }}</span>
                    </div>
                    <div class="stat-label">Designation</div>
                </div>
                <div class="stat-box">
                    <div class="d-flex align-items-center gap-1">
                        <span class="material-icons-round text-primary" style="font-size:1rem">apartment</span>
                        <span class="stat-num">{{ $employee->department->name ?? '—' }}</span>
                    </div>
                    <div class="stat-label">Department</div>
                </div>
                <div class="stat-box">
                    <div class="d-flex align-items-center gap-1">
                        <span class="material-icons-round text-info" style="font-size:1rem">event</span>
                        <span class="stat-num">{{ $employee->joining_date ? \Carbon\Carbon::parse($employee->joining_date)->format('d M Y') : '—' }}</span>
                    </div>
                    <div class="stat-label">Joined</div>
                </div>
            </div>

        </div>
    </div>

    <!-- Profile Tabs -->
    <ul class="nav profile-tabs border-bottom mt-4 flex-nowrap overflow-auto no-print">
        <li class="nav-item"><a class="nav-link {{ request()->routeIs('admin.employee.view') ? 'active' : '' }}" href="{{ route('admin.employee.view', ['id' => $employee->id]) }}">Overview</a></li>
    </ul>
</div>