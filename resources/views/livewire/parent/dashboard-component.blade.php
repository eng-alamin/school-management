@push('styles')
<style>
    .child-card {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 16px;
        padding: 1.5rem 1.25rem;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.75rem;
        transition: box-shadow 0.2s ease, transform 0.2s ease;
        height: 100%;
    }

    .child-card:hover {
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
        transform: translateY(-2px);
    }

    .child-avatar {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        overflow: hidden;
        border: 3px solid #dbeafe;
        background: #eff6ff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.4rem;
        font-weight: 600;
        color: #2563eb;
        flex-shrink: 0;
    }

    .child-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .child-label {
        font-size: 11px;
        color: #9ca3af;
        margin-bottom: 2px;
        text-align: center;
    }

    .child-name {
        font-size: 15px;
        font-weight: 600;
        color: #1f2937;
        text-align: center;
        line-height: 1.3;
        margin: 0;
    }

    .child-class {
        font-size: 13px;
        color: #6b7280;
        text-align: center;
        margin: 2px 0 0;
    }

    .child-divider {
        width: 100%;
        border: none;
        border-top: 1px solid #f3f4f6;
        margin: 0.25rem 0;
    }

    .child-info {
        width: 100%;
    }

    .child-info-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 5px 0;
        font-size: 12.5px;
    }

    .child-info-row .info-key {
        color: #9ca3af;
    }

    .child-info-row .info-val {
        font-weight: 600;
        color: #374151;
    }

    .child-dashboard-btn {
        margin-top: 4px;
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        background: #2563eb;
        color: #ffffff;
        font-size: 13px;
        font-weight: 500;
        padding: 9px 12px;
        border-radius: 10px;
        border: none;
        cursor: pointer;
        transition: background 0.15s ease;
    }

    .child-dashboard-btn:hover {
        background: #1d4ed8;
    }

    .child-dashboard-btn:active {
        background: #1e40af;
        transform: scale(0.98);
    }
</style>
@endpush

<div class="p-4">
    <div class="mb-4">
        <h5 class="fw-medium mb-1">My children</h5>
        <p class="text-muted small mb-0">Select a child to view their dashboard</p>
    </div>

    @if($children->isEmpty())
        <div class="text-center py-5 text-muted">
            <svg xmlns="http://www.w3.org/2000/svg" class="mb-3" width="48" height="48" fill="none" viewBox="0 0 24 24" stroke="currentColor" opacity="0.4">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            <p class="small">No children found linked to your account.</p>
        </div>
    @else
        <div class="row g-3">
            @foreach($children as $child)
                <div class="col-12 col-sm-6 col-md-4 col-xl-3">
                    <div class="child-card">

                        {{-- Avatar --}}
                        <div class="child-avatar">
                            @if($child->photo)
                                <img src="{{ asset('storage/' . $child->photo) }}" alt="{{ $child->name }}">
                            @else
                                {{ strtoupper(substr($child->name, 0, 1)) }}{{ strtoupper(substr(strrchr($child->name, ' '), 1, 1)) }}
                            @endif
                        </div>

                        {{-- Name & Class --}}
                        <div>
                            <p class="child-label">My child</p>
                            <p class="child-name">{{ $child->name }}</p>
                            <p class="child-class">
                                {{ optional($child->class)->name }}
                                @if($child->section)({{ $child->section->name }})@endif
                            </p>
                        </div>

                        <hr class="child-divider">

                        {{-- Info --}}
                        <div class="child-info">
                            <div class="child-info-row">
                                <span class="info-key">Roll no</span>
                                <span class="info-val">{{ $child->roll_no ?? '—' }}</span>
                            </div>
                            <div class="child-info-row">
                                <span class="info-key">Reg no</span>
                                <span class="info-val">{{ $child->register_no }}</span>
                            </div>
                            <div class="child-info-row">
                                <span class="info-key">DOB</span>
                                <span class="info-val">
                                    {{ $child->dob ? \Carbon\Carbon::parse($child->dob)->format('d M Y') : '—' }}
                                </span>
                            </div>
                        </div>

                        {{-- Button --}}
                        <button 
                        wire:click="goToDashboard({{ $child->id }})"
                        wire:loading.attr="disabled"
                        wire:target="goToDashboard({{ $child->id }})"
                        class="child-dashboard-btn">
                        <span wire:loading.remove wire:target="goToDashboard({{ $child->id }})">
                            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                            </svg>
                            Dashboard
                        </span>
                        <span wire:loading wire:target="goToDashboard({{ $child->id }})">
                            <span class="spinner-border spinner-border-sm" role="status"></span>
                            Loading...
                        </span>
                    </button>

                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>