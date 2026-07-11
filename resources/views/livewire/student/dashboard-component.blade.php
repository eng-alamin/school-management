@push('styles')
<style>
    .std-welcome-card {
        border-radius: 16px;
        padding: 1.75rem 2rem;
        background: linear-gradient(195deg, #444, #111);
        color: #ffffff;
        display: flex;
        align-items: center;
        gap: 1.25rem;
        flex-wrap: wrap;
    }

    .std-avatar {
        width: 68px;
        height: 68px;
        border-radius: 50%;
        overflow: hidden;
        border: 3px solid rgba(255,255,255,.35);
        background: rgba(255,255,255,.15);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.3rem;
        font-weight: 600;
        flex-shrink: 0;
    }

    .std-avatar img { width: 100%; height: 100%; object-fit: cover; }

    .std-welcome-card h4 { margin: 0; font-weight: 600; }
    .std-welcome-card p { margin: 2px 0 0; opacity: .9; font-size: 13.5px; }

    .std-meta-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        background: rgba(255,255,255,.15);
        padding: 3px 10px;
        border-radius: 999px;
        font-size: 12px;
        margin-top: 8px;
        margin-right: 6px;
    }

    .std-stat-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        padding: 1.1rem 1.25rem;
        display: flex;
        align-items: center;
        gap: .9rem;
        height: 100%;
    }

    .std-stat-icon {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .std-stat-icon .material-icons-round { font-size: 22px; }

    .std-stat-value { font-size: 1.35rem; font-weight: 700; color: #1f2937; line-height: 1.1; }
    .std-stat-label { font-size: 12px; color: #9ca3af; margin-top: 2px; }

    .std-panel {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        height: 100%;
    }

    .std-panel-header {
        padding: 14px 18px;
        border-bottom: 1px solid #f3f4f6;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .std-panel-header h6 { margin: 0; font-weight: 600; font-size: .9rem; }

    .std-panel-body { padding: 0.5rem 0.75rem 1rem; }

    .std-list-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 10px 10px;
        border-radius: 10px;
        font-size: 13px;
    }

    .std-list-row:hover { background: #f9fafb; }

    .std-list-empty { text-align: center; padding: 2rem 1rem; color: #9ca3af; font-size: 13px; }

    .std-badge { font-size: 11px; font-weight: 600; padding: 3px 10px; border-radius: 999px; }
    .std-badge-unpaid  { background: #fee2e2; color: #b91c1c; }
    .std-badge-partial { background: #fef3c7; color: #b45309; }
    .std-badge-paid    { background: #dcfce7; color: #15803d; }

    .std-quick-link {
        display: flex;
        align-items: center;
        gap: 8px;
        background: #f8f9fa;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 12px 16px;
        font-size: 13px;
        font-weight: 500;
        color: #374151;
        text-decoration: none;
        transition: background .15s ease, border-color .15s ease;
    }

    .std-quick-link:hover { background: #eff6ff; border-color: #bfdbfe; color: #1d4ed8; }
    .std-quick-link .material-icons-round { font-size: 18px; color: #2563eb; }
</style>
@endpush

<div class="p-4">

    {{-- ===== WELCOME HEADER ===== --}}
    <div class="std-welcome-card mb-4">
        <div class="std-avatar">
            @if($student?->photo)
                <img src="{{ asset('storage/' . $student->photo) }}" alt="{{ $student->name }}">
            @else
                {{ strtoupper(substr($student->name ?? auth()->user()->name, 0, 1)) }}
            @endif
        </div>
        <div>
            <h4>Welcome, {{ $student->name ?? auth()->user()->name }}</h4>
            <p>
                {{ optional($assign?->class)->name ?? 'No class assigned' }}
                @if($assign?->section) ({{ $assign->section->name }}) @endif
            </p>
            <div>
                @if($student?->roll_no)
                    <span class="std-meta-badge">
                        <span class="material-icons-round" style="font-size:13px">badge</span>
                        Roll: {{ $student->roll_no }}
                    </span>
                @endif
                @if($student?->registration_no)
                    <span class="std-meta-badge">
                        <span class="material-icons-round" style="font-size:13px">confirmation_number</span>
                        Reg: {{ $student->registration_no }}
                    </span>
                @endif
            </div>
        </div>
    </div>

    {{-- ===== STAT CARDS ===== --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="std-stat-card">
                <div class="std-stat-icon" style="background:#eff6ff;color:#2563eb">
                    <span class="material-icons-round">menu_book</span>
                </div>
                <div>
                    <div class="std-stat-value">{{ $subjectCount }}</div>
                    <div class="std-stat-label">Subjects</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="std-stat-card">
                <div class="std-stat-icon" style="background:#fdf2f8;color:#db2777">
                    <span class="material-icons-round">event</span>
                </div>
                <div>
                    <div class="std-stat-value">{{ $upcomingExams->count() }}</div>
                    <div class="std-stat-label">Upcoming Exams</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="std-stat-card">
                <div class="std-stat-icon" style="background:#fef2f2;color:#dc2626">
                    <span class="material-icons-round">payments</span>
                </div>
                <div>
                    <div class="std-stat-value">৳{{ number_format($totalDue, 2) }}</div>
                    <div class="std-stat-label">Fee Due</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="std-stat-card">
                <div class="std-stat-icon" style="background:#f0fdf4;color:#16a34a">
                    <span class="material-icons-round">fact_check</span>
                </div>
                <div>
                    <div class="std-stat-value">—</div>
                    <div class="std-stat-label">Attendance</div>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== EXAMS + FEE INVOICES ===== --}}
    <div class="row g-3 mb-4">
        <div class="col-12 col-lg-6">
            <div class="std-panel">
                <div class="std-panel-header">
                    <h6>Upcoming Exams</h6>
                    @if(Route::has('student.exams'))
                        <a href="{{ route('student.exams') }}" class="small text-decoration-none">View all</a>
                    @endif
                </div>
                <div class="std-panel-body">
                    @forelse($upcomingExams as $exam)
                        <div class="std-list-row">
                            <div>
                                <div class="fw-medium">{{ $exam->examSetupDetail?->classAssignDetail?->subject?->name ?? '—' }}</div>
                                <div class="text-muted" style="font-size:11.5px">{{ $exam->examSetup?->name ?? '—' }}</div>
                            </div>
                            <div class="text-end">
                                <div class="fw-medium">{{ $exam->exam_date?->format('d M Y') ?? '—' }}</div>
                                <div class="text-muted" style="font-size:11.5px">{{ \Carbon\Carbon::parse($exam->start_time)->format('h:i A') }}</div>
                            </div>
                        </div>
                    @empty
                        <div class="std-list-empty">No upcoming exams found.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-6">
            <div class="std-panel">
                <div class="std-panel-header">
                    <h6>Recent Fee Invoices</h6>
                    @if(Route::has('student.fees'))
                        <a href="{{ route('student.fees') }}" class="small text-decoration-none">View all</a>
                    @endif
                </div>
                <div class="std-panel-body">
                    @forelse($recentInvoices as $invoice)
                        <div class="std-list-row">
                            <div>
                                <div class="fw-medium">{{ $invoice->invoice_no ?? '—' }}</div>
                                <div class="text-muted" style="font-size:11.5px">
                                    {{ $invoice->invoice_date ? \Carbon\Carbon::parse($invoice->invoice_date)->format('d M Y') : '—' }}
                                </div>
                            </div>
                            <div class="text-end">
                                <div class="fw-medium">৳{{ number_format($invoice->total_amount ?? 0, 2) }}</div>
                                <span class="std-badge std-badge-{{ $invoice->payment_status ?? 'unpaid' }}">
                                    {{ ucfirst($invoice->payment_status ?? 'unpaid') }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <div class="std-list-empty">No fee invoices found.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- ===== QUICK LINKS ===== --}}
    <div class="row g-3">
        @if(Route::has('student.subjects'))
        <div class="col-6 col-md-3">
            <a href="{{ route('student.subjects') }}" class="std-quick-link">
                <span class="material-icons-round">menu_book</span> My Subjects
            </a>
        </div>
        @endif
        @if(Route::has('student.exams'))
        <div class="col-6 col-md-3">
            <a href="{{ route('student.exams') }}" class="std-quick-link">
                <span class="material-icons-round">event</span> Exam Schedule
            </a>
        </div>
        @endif
        @if(Route::has('student.fees'))
        <div class="col-6 col-md-3">
            <a href="{{ route('student.fees') }}" class="std-quick-link">
                <span class="material-icons-round">payments</span> Fee & Payments
            </a>
        </div>
        @endif
        @if(Route::has('student.profile'))
        <div class="col-6 col-md-3">
            <a href="{{ route('student.profile') }}" class="std-quick-link">
                <span class="material-icons-round">person</span> My Profile
            </a>
        </div>
        @endif
    </div>

</div>