{{-- resources/views/livewire/ministry/dashboard-component.blade.php --}}

<div class="dash-wrap">

    {{-- ══ Welcome Header ══════════════════════════════════════════════════ --}}
    <div class="dash-header px-3 pt-3 pb-2">
        <h5 class="fw-bold mb-0 text-dark" data-en="Ministry Dashboard" data-bn="মিনিস্ট্রি ড্যাশবোর্ড">Ministry Dashboard</h5>
        <p class="text-secondary mb-0" style="font-size:12px;" data-en="Government oversight — all registered institutions" data-bn="সরকারি তত্ত্বাবধান — সকল নিবন্ধিত প্রতিষ্ঠান">Government oversight — all registered institutions</p>
    </div>

    {{-- ══ Stat Cards ══════════════════════════════════════════════════════ --}}
    <div class="px-3 pt-2">
        <div class="row g-3">

            {{-- Total Institutions --}}
            <div class="col-6 col-md-4">
                <div class="dash-stat-card">
                    <div class="dash-stat-icon" style="background:#eef2ff;">
                        <span class="material-icons-round" style="color:#4f46e5;">account_balance</span>
                    </div>
                    <p class="dash-stat-label" data-en="Total Institutions" data-bn="মোট প্রতিষ্ঠান">Total Institutions</p>
                    <h4 class="dash-stat-value">{{ number_format($totalInstitutions) }}</h4>
                    <span class="dash-stat-badge text-secondary" data-en="Registered nationwide" data-bn="দেশব্যাপী নিবন্ধিত">Registered nationwide</span>
                </div>
            </div>

            {{-- Active Institutions --}}
            <div class="col-6 col-md-4">
                <div class="dash-stat-card">
                    <div class="dash-stat-icon" style="background:#d1fae5;">
                        <span class="material-icons-round" style="color:#059669;">check_circle</span>
                    </div>
                    <p class="dash-stat-label" data-en="Active Institutions" data-bn="সক্রিয় প্রতিষ্ঠান">Active Institutions</p>
                    <h4 class="dash-stat-value">{{ number_format($activeInstitutions) }}</h4>
                    <span class="dash-stat-badge text-secondary">
                        @if($totalInstitutions > 0)
                            <span data-en="{{ round(($activeInstitutions / $totalInstitutions) * 100) }}% active" data-bn="{{ round(($activeInstitutions / $totalInstitutions) * 100) }}% সক্রিয়">{{ round(($activeInstitutions / $totalInstitutions) * 100) }}% active</span>
                        @else
                            <span data-en="0% active" data-bn="0% সক্রিয়">0% active</span>
                        @endif
                    </span>
                </div>
            </div>

            {{-- Inactive Institutions --}}
            <div class="col-6 col-md-4">
                <div class="dash-stat-card">
                    <div class="dash-stat-icon" style="background:#fef2f2;">
                        <span class="material-icons-round" style="color:#dc2626;">warning_amber</span>
                    </div>
                    <p class="dash-stat-label" data-en="Inactive Institutions" data-bn="নিষ্ক্রিয় প্রতিষ্ঠান">Inactive Institutions</p>
                    <h4 class="dash-stat-value">{{ number_format($inactiveInstitutions) }}</h4>
                    <span class="dash-stat-badge text-danger" data-en="Needs review" data-bn="পর্যালোচনা প্রয়োজন">Needs review</span>
                </div>
            </div>

            {{-- Total Students --}}
            <div class="col-6 col-md-4">
                <div class="dash-stat-card">
                    <div class="dash-stat-icon" style="background:#fef3c7;">
                        <span class="material-icons-round" style="color:#d97706;">school</span>
                    </div>
                    <p class="dash-stat-label" data-en="Total Students" data-bn="মোট শিক্ষার্থী">Total Students</p>
                    <h4 class="dash-stat-value">{{ number_format($totalStudents) }}</h4>
                    <span class="dash-stat-badge text-success" data-en="All institutions" data-bn="সকল প্রতিষ্ঠান">All institutions</span>
                </div>
            </div>

            {{-- Total Teachers --}}
            <div class="col-6 col-md-4">
                <div class="dash-stat-card">
                    <div class="dash-stat-icon" style="background:#ede9fe;">
                        <span class="material-icons-round" style="color:#7c3aed;">badge</span>
                    </div>
                    <p class="dash-stat-label" data-en="Total Teachers" data-bn="মোট শিক্ষক">Total Teachers</p>
                    <h4 class="dash-stat-value">{{ number_format($totalTeachers) }}</h4>
                    <span class="dash-stat-badge text-success" data-en="All institutions" data-bn="সকল প্রতিষ্ঠান">All institutions</span>
                </div>
            </div>

            {{-- Active Teachers --}}
            <div class="col-6 col-md-4">
                <div class="dash-stat-card">
                    <div class="dash-stat-icon" style="background:#ecfeff;">
                        <span class="material-icons-round" style="color:#0891b2;">how_to_reg</span>
                    </div>
                    <p class="dash-stat-label" data-en="Active Teachers" data-bn="সক্রিয় শিক্ষক">Active Teachers</p>
                    <h4 class="dash-stat-value">{{ number_format($activeTeachers) }}</h4>
                    <span class="dash-stat-badge text-secondary" data-en="Currently active" data-bn="বর্তমানে সক্রিয়">Currently active</span>
                </div>
            </div>

        </div>
    </div>

    {{-- ══ Recent Institutions ═══════════════════════════════════════════════════ --}}
    @can('institution.view')
    <div class="px-3 mt-4">
        <div class="dash-section-card">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div class="dash-section-title mb-0">
                    <span class="material-icons-round text-primary" style="font-size:18px;">account_balance</span>
                    <span data-en="Recent Institutions" data-bn="সাম্প্রতিক প্রতিষ্ঠান">Recent Institutions</span>
                </div>
                <a href="{{ route('ministry.institutions.index') }}" class="dash-view-all" data-en="View all" data-bn="সব দেখুন">View all</a>
            </div>

            @forelse($recentInstitutions as $institution)
                <div class="dash-notice-row">
                    <div class="dash-institution-avatar">
                        {{ strtoupper(substr($institution->name, 0, 1)) }}
                    </div>
                    <div class="flex-grow-1 min-w-0">
                        <p class="mb-0 text-dark fw-semibold text-truncate" style="font-size:13px;">
                            {{ $institution->name }}
                        </p>
                        <small class="text-secondary" style="font-size:11px;">
                            {{ $institution->email }}
                        </small>
                    </div>
                    @if($institution->status)
                        <span class="inv-badge paid" data-en="Active" data-bn="সক্রিয়">Active</span>
                    @else
                        <span class="inv-badge unpaid" data-en="Inactive" data-bn="নিষ্ক্রিয়">Inactive</span>
                    @endif
                </div>
            @empty
                <p class="text-center text-secondary py-2 mb-0" style="font-size:13px;" data-en="No Institutions Yet" data-bn="এখনো কোনো প্রতিষ্ঠান নেই">
                    No Institutions Yet
                </p>
            @endforelse
        </div>
    </div>
    @endcan

    {{-- ══ Recent Activity ══════════════════════════════════════════════════ --}}
    @can('audit-log.view')
    <div class="px-3 mt-4 mb-4">
        <div class="dash-section-card">
            <div class="dash-section-title">
                <span class="material-icons-round text-warning" style="font-size:18px;">bolt</span>
                <span data-en="Recent Activity" data-bn="সাম্প্রতিক কার্যক্রম">Recent Activity</span>
            </div>

            @forelse($recentActivities as $act)
                <div class="dash-activity-item">
                    <div class="dash-activity-icon">
                        <span class="material-icons-round" style="font-size:16px;color:#6b7280;">
                            {{ $act->icon ?? 'notifications' }}
                        </span>
                    </div>
                    <div class="flex-grow-1 min-w-0">
                        <p class="mb-0 text-dark text-truncate" style="font-size:13px;">
                            {{ $act->description }}
                        </p>
                    </div>
                    <small class="text-secondary text-nowrap ms-2" style="font-size:11px;">
                        {{ \Carbon\Carbon::parse($act->created_at)->diffForHumans() }}
                    </small>
                </div>
            @empty
                <p class="text-center text-secondary py-2 mb-0" style="font-size:13px;" data-en="No Activity Yet" data-bn="এখনো কোনো কার্যক্রম নেই">
                    No Activity Yet
                </p>
            @endforelse
        </div>
    </div>
    @endcan

</div>

{{-- ══ Scoped CSS ══════════════════════════════════════════════════════════ --}}
@push('styles')
<style>
    .dash-wrap { background: var(--body-bg); min-height: 100vh; padding-bottom: 24px; }
    .dash-header { padding-top: 16px; }

    .dash-stat-card {
        background: var(--card);
        border-radius: var(--radius-card);
        padding: 14px;
        box-shadow: var(--shadow);
        height: 100%;
        border: 1px solid var(--border);
    }
    .dash-stat-icon {
        width: 38px; height: 38px; border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        margin-bottom: 10px;
    }
    .dash-stat-icon .material-icons-round { font-size: 20px; }
    .dash-stat-label { font-size: 11px; color: var(--lbl); margin-bottom: 2px; }
    .dash-stat-value { font-size: 20px; font-weight: 700; color: var(--val); margin-bottom: 4px; }
    .dash-stat-badge { font-size: 11px; font-weight: 500; }

    .dash-section-card {
        background: var(--card);
        border-radius: var(--radius-card);
        padding: 16px;
        box-shadow: var(--shadow);
        border: 1px solid var(--border);
    }
    .dash-section-title {
        font-size: 14px; font-weight: 600; color: var(--val);
        display: flex; align-items: center; gap: 6px; margin-bottom: 12px;
    }
    .dash-view-all { font-size: 12px; color: var(--primary); font-weight: 500; text-decoration: none; }

    .dash-notice-row {
        display: flex; align-items: center; padding: 11px 12px;
        border-radius: 10px; background: var(--section-bg);
        margin-bottom: 8px; gap: 10px;
    }
    .dash-notice-row:last-child { margin-bottom: 0; }

    .dash-institution-avatar {
        width: 36px; height: 36px; border-radius: 10px;
        background: linear-gradient(135deg, var(--primary), #7ba3ff);
        color: #fff; font-size: 14px; font-weight: 700;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }

    .inv-badge {
        display: inline-block; padding: 3px 10px; border-radius: 4px;
        font-size: 11px; font-weight: 600; border: 1px solid transparent;
        flex-shrink: 0;
    }
    .inv-badge.paid   { background: transparent; border-color: #22c55e; color: #22c55e; }
    .inv-badge.unpaid { background: transparent; border-color: #ef4444; color: #ef4444; }

    .dash-activity-item {
        display: flex; align-items: center; gap: 10px;
        padding: 8px 0; border-bottom: 1px solid var(--border);
    }
    .dash-activity-item:last-child { border-bottom: none; padding-bottom: 0; }

    .dash-activity-icon {
        width: 32px; height: 32px; border-radius: 8px;
        background: var(--section-bg);
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }

    @media (min-width: 768px) {
        .dash-stat-value { font-size: 22px; }
    }
</style>
@endpush