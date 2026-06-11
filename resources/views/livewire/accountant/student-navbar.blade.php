<div class="card-custom profile-card p-4 mb-4">
    <!-- Avatar + Info -->
    <div class="d-flex flex-wrap gap-4 align-items-start">
        <div class="avatar-wrap me-2">
            @if($student->photo)
                <img src="{{ $student->photo ? asset($student->photo) : global_asset('assets/img/default-user.jpg') }}" alt="{{ $student->name }}"/>
            @else
                <img src="https://ui-avatars.com/api/?name={{ urlencode($student->name) }}&size=160&background=random" alt="{{ $student->name }}"/>
            @endif
            <span class="online-dot"></span>
        </div>
        <div class="flex-grow-1">
            <!-- Name + Actions row -->
            <div class="d-flex flex-wrap justify-content-between align-items-start mb-2 gap-2">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <a href="#" class="text-decoration-none text-dark fw-bold fs-4">{{ $student->name }}</a>
                        <i class="bi bi-patch-check-fill badge-verified fs-5"></i>
                    </div>
                    <div class="d-flex flex-wrap gap-3" style="font-size:.88rem;">
                        <a href="#" class="text-muted text-decoration-none d-flex align-items-center gap-1">
                            <span class="material-icons-round fs-6">dashboard</span>{{ $student->register_no }}</a>
                        <a href="#" class="text-muted text-decoration-none d-flex align-items-center gap-1">
                            <span class="material-icons-round fs-6">calendar_today</span>Admitted: {{ \Carbon\Carbon::parse($student->admission_date)->format('d M Y') }}</a>
                        <a href="#" class="text-muted text-decoration-none d-flex align-items-center gap-1">
                            <span class="material-icons-round fs-6">email</span>{{ $student->email ?? '—' }}</a>
                        <a href="#" class="text-muted text-decoration-none d-flex align-items-center gap-1">
                            <span class="material-icons-round fs-6">phone</span>{{ $student->mobile ?? '—' }}</a>
                    </div>
                </div>
            </div>

            <div class="hero-badges">
                <span class="badge bg-dark">{{ $student->class->name ?? '—' }} · Section {{ $student->section->name ?? '—' }}</span>
                <span class="badge bg-dark">{{ $student->category->name ?? '—' }}</span>
                <span class="badge bg-dark">{{ ucfirst($student->gender) }} · {{ $student->blood_group ?? '—' }}</span>
                <span class="badge bg-dark">{{ ucfirst($student->religion) ?? '—' }}</span>
                <span class="badge bg-dark">Roll: {{ $student->roll_no ?? '—' }}</span>
            </div>

            <!-- Stats -->
            <div class="d-flex flex-wrap align-items-center gap-3 mt-3">
                <div class="stat-box">
                    <div class="d-flex align-items-center gap-1">
                        <i class="bi bi-arrow-up text-success"></i>
                        <span class="stat-num">{{ $student->session->name ?? '—' }}</span>
                    </div>
                    <div class="stat-label">Academic Year</div>
                </div>
                <div class="stat-box">
                    <div class="d-flex align-items-center gap-1">
                        <i class="bi bi-arrow-down text-danger"></i>
                        <span class="stat-num">{{ $student->roll_no ?? '—' }}</span>
                    </div>
                    <div class="stat-label">Roll No</div>
                </div>
                <div class="stat-box">
                    <div class="d-flex align-items-center gap-1">
                        <i class="bi bi-arrow-up text-success"></i>
                        <span class="stat-num">{{ $student->class->name ?? '—' }}</span>
                    </div>
                    <div class="stat-label">Class</div>
                </div>
                <div class="stat-box">
                    <div class="d-flex align-items-center gap-1">
                        <i class="bi bi-arrow-up text-success"></i>
                        <span class="stat-num">{{ $student->section->name ?? '—' }}</span>
                    </div>
                    <div class="stat-label">Section</div>
                </div>
            </div>

        </div>
    </div>

    <!-- Profile Tabs -->
    <ul class="nav profile-tabs border-bottom mt-4 flex-nowrap overflow-auto">
        <li class="nav-item"><a class="nav-link {{ request()->routeIs('accountant.student.overview') ? 'active' : '' }}" href="{{ route('accountant.student.overview', ['tenant' => tenant('id'), 'id' => $student->id]) }}">Overview</a></li>
        <li class="nav-item"><a class="nav-link {{ request()->routeIs('accountant.student.invoice') ? 'active' : '' }}" href="{{ route('accountant.student.invoice', ['tenant' => tenant('id'), 'id' => $student->id]) }}">Invoice</a></li>
        <li wire:ignore class="nav-item"><a class="nav-link {{ request()->routeIs('accountant.student.payment.add') ? 'active' : '' }}" href="{{ route('accountant.student.payment.add', ['tenant' => tenant('id'), 'id' => $student->id]) }}">Payment</a></li>
        <li class="nav-item"><a class="nav-link" href="#">Campaigns</a></li>
        <li class="nav-item"><a class="nav-link" href="#">Documents</a></li>
        <li class="nav-item"><a class="nav-link" href="#">Followers</a></li>
        <li class="nav-item"><a class="nav-link" href="#">Activity</a></li>
    </ul>
</div>