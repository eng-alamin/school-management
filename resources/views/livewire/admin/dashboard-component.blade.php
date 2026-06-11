{{-- resources/views/livewire/admin/dashboard-component.blade.php --}}

<div class="p-3 p-md-4" style="background:#f5f6fa; min-height:100vh;">

    {{-- ══ Stat Cards ═══════════════════════════════════════════════════════ --}}
    <div class="row g-3 mb-4">

        {{-- Students --}}
        <div class="col-6 col-md-3">
            <div class="card border-0 rounded-4 h-100" style="box-shadow:0 1px 8px rgba(0,0,0,.06);">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-center rounded-3 mb-3"
                         style="width:40px;height:40px;background:#fbeaf0;">
                        <span class="material-icons-round" style="color:#e94d82;font-size:18px;">groups</span>
                    </div>
                    <p class="text-secondary mb-1" style="font-size:11px;">Total Students</p>
                    <h4 class="fw-semibold mb-1 text-dark">{{ number_format($totalStudents) }}</h4>
                    <small class="text-success">
                        <span class="material-icons-round" style="font-size:13px;vertical-align:middle;">trending_up</span> Active this session
                    </small>
                </div>
            </div>
        </div>

        {{-- Fee Collected --}}
        <div class="col-6 col-md-3">
            <div class="card border-0 rounded-4 h-100" style="box-shadow:0 1px 8px rgba(0,0,0,.06);">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-center rounded-3 mb-3"
                         style="width:40px;height:40px;background:#e1f5ee;">
                        <span class="material-icons-round" style="color:#0f6e56;font-size:18px;">payments</span>
                    </div>
                    <p class="text-secondary mb-1" style="font-size:11px;">Total Fee Collected</p>
                    <h4 class="fw-semibold mb-1 text-dark">৳{{ number_format($totalFeeCollected) }}</h4>
                    <small class="text-success">
                        <span class="material-icons-round" style="font-size:13px;vertical-align:middle;">trending_up</span> Today: ৳{{ number_format($totalFeeToday) }}
                    </small>
                </div>
            </div>
        </div>

        {{-- Classes --}}
        <div class="col-6 col-md-3">
            <div class="card border-0 rounded-4 h-100" style="box-shadow:0 1px 8px rgba(0,0,0,.06);">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-center rounded-3 mb-3"
                         style="width:40px;height:40px;background:#faeeda;">
                        <span class="material-icons-round" style="color:#854f0b;font-size:18px;">meeting_room</span>
                    </div>
                    <p class="text-secondary mb-1" style="font-size:11px;">Total Classes</p>
                    <h4 class="fw-semibold mb-1 text-dark">{{ $totalClasses }}</h4>
                    <small class="text-secondary">All sections</small>
                </div>
            </div>
        </div>

        {{-- Employees --}}
        <div class="col-6 col-md-3">
            <div class="card border-0 rounded-4 h-100" style="box-shadow:0 1px 8px rgba(0,0,0,.06);">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-center rounded-3 mb-3"
                         style="width:40px;height:40px;background:#e6f1fb;">
                        <span class="material-icons-round" style="color:#185fa5;font-size:18px;">badge</span>
                    </div>
                    <p class="text-secondary mb-1" style="font-size:11px;">Total Teachers / Staff</p>
                    <h4 class="fw-semibold mb-1 text-dark">{{ number_format($totalEmployees) }}</h4>
                    <small class="text-secondary">
                        <span class="material-icons-round" style="font-size:13px;vertical-align:middle;">how_to_reg</span> Present today: {{ $employeesPresentToday }}
                    </small>
                </div>
            </div>
        </div>

    </div>

    {{-- ══ Chart + Notices ══════════════════════════════════════════════════ --}}
    <div class="row g-3 mb-4">

        {{-- Monthly Fee Bar Chart --}}
        <div class="col-md-8">
            <div class="card border-0 rounded-4 h-100" style="box-shadow:0 1px 8px rgba(0,0,0,.06);">
                <div class="card-body p-4">
                    <h6 class="fw-semibold text-dark mb-0">Monthly Fee Collection</h6>
                    <p class="text-secondary mb-4" style="font-size:11px;">Comparison of the last 6 months</p>

                    @php
                        $chartData = collect($monthlyFeeChart);
                        $chartMax  = $chartData->max('total') ?: 1;
                    @endphp

                    @if($chartData->count())
                        <div class="d-flex align-items-end gap-2" style="height:110px;">
                            @foreach($chartData as $i => $bar)
                                @php $pct = max(6, ($bar['total'] / $chartMax) * 100); @endphp
                                <div class="d-flex flex-column align-items-center justify-content-end flex-fill gap-1">
                                    <div class="w-100 rounded-top-2"
                                         style="height:{{ $pct }}px;
                                                background:{{ $i === $chartData->count()-1 ? '#e94d82' : '#f4c0d1' }};
                                                transition:all .3s;"></div>
                                    <span class="text-secondary text-nowrap" style="font-size:9px;">{{ $bar['month'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="d-flex align-items-center justify-content-center" style="height:110px;">
                            <span class="text-secondary" style="font-size:13px;">No data available</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Recent Notices --}}
        <div class="col-md-4">
            <div class="card border-0 rounded-4 h-100" style="box-shadow:0 1px 8px rgba(0,0,0,.06);">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h6 class="fw-semibold text-dark mb-0">Recent Notices</h6>
                        <a href="#" class="text-decoration-none fw-medium"
                           style="font-size:12px;color:#e94d82;">View all</a>
                    </div>

                    <div class="d-flex flex-column gap-3">
                        @forelse($recentNotices as $notice)
                            <div class="d-flex align-items-start gap-2 pb-3 border-bottom border-light-subtle last-no-border">
                                <div class="flex-grow-1 min-w-0">
                                    <span class="fw-semibold d-block mb-1" style="font-size:10px;
                                        color:{{ $notice->priority === 'urgent' ? '#a32d2d' : ($notice->priority === 'high' ? '#854f0b' : '#185fa5') }};">
                                        #{{ strtoupper($notice->priority) }}
                                    </span>
                                    <p class="mb-0 text-dark text-truncate" style="font-size:12px;">{{ $notice->title }}</p>
                                    <small class="text-secondary">
                                        {{ ucfirst($notice->audience) }} · {{ \Carbon\Carbon::parse($notice->published_at)->format('d M') }}
                                    </small>
                                </div>
                                <a href="#"
                                   class="btn btn-sm btn-outline-secondary border rounded-3 py-0 px-2 text-nowrap"
                                   style="font-size:10px;">View</a>
                            </div>
                        @empty
                            <p class="text-center text-secondary py-3 mb-0" style="font-size:12px;">No notices found</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- ══ Recent Invoices Row ══════════════════════════════════════════════ --}}
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <h6 class="fw-semibold text-dark mb-0">Recent Invoices</h6>
            <small class="text-secondary">List of latest fee invoices</small>
        </div>
        <a href="#"
           class="text-decoration-none fw-medium" style="font-size:12px;color:#e94d82;">View all →</a>
    </div>

    <div class="row g-3 mb-4">

        @forelse($recentInvoices->take(4) as $inv)
            <div class="col-6 col-md">
                <div class="card border-0 rounded-4 h-100" style="box-shadow:0 1px 8px rgba(0,0,0,.06);">
                    <div class="d-flex align-items-center justify-content-center rounded-top-4"
                         style="height:70px;background:linear-gradient(135deg,#fbeaf0,#f4c0d1);">
                        <span class="material-icons-round" style="font-size:32px;color:#e94d82;">description</span>
                    </div>
                    <div class="card-body p-3">
                        <p class="fw-semibold text-dark text-truncate mb-0" style="font-size:12px;">{{ $inv->student_name }}</p>
                        <small class="text-secondary">{{ $inv->invoice_no }}</small>
                        <p class="fw-semibold mt-1 mb-1" style="color:#e94d82;font-size:14px;">
                            ৳{{ number_format($inv->total_amount) }}
                        </p>
                        <span class="badge rounded-pill px-2 py-1
                            @if($inv->payment_status === 'paid') bg-success-subtle text-success
                            @elseif($inv->payment_status === 'partial') bg-warning-subtle text-warning
                            @else bg-danger-subtle text-danger @endif"
                              style="font-size:10px;">
                            {{ ucfirst($inv->payment_status) }}
                        </span>
                    </div>
                </div>
            </div>
        @empty
        @endforelse

        {{-- Add New Card --}}
        <div class="col-6 col-md">
            <a href="#" class="text-decoration-none">
                <div class="card border-0 rounded-4 h-100 d-flex align-items-center justify-content-center"
                     style="min-height:140px;border:2px dashed #e94d82 !important;background:#fff9fb;">
                    <div class="text-center">
                        <span class="material-icons-round" style="font-size:28px;color:#e94d82;">add_circle</span>
                        <p class="mb-0 mt-1" style="font-size:11px;color:#e94d82;">New Invoice</p>
                    </div>
                </div>
            </a>
        </div>

    </div>

</div>