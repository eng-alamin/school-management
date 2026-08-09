{{-- resources/views/livewire/ministry/institution/show-component.blade.php --}}

<div class="inst-show-wrap">

    {{-- ══ Back link ═══════════════════════════════════════════════════════ --}}
    <div class="px-3 pt-3">
        <a href="{{ route('ministry.institutions.index') }}" class="inst-back-link">
            <span class="material-icons-round" style="font-size:16px;vertical-align:middle;">arrow_back</span>
            <span data-en="Back to Institutions" data-bn="প্রতিষ্ঠান তালিকায় ফিরে যান">Back to Institutions</span>
        </a>
    </div>

    {{-- ══ Profile Header ══════════════════════════════════════════════════ --}}
    <div class="px-3 pt-3">
        <div class="inst-show-header-card">
            <div class="d-flex align-items-start gap-3 flex-wrap">
                <div class="inst-show-avatar">
                    {{ strtoupper(substr($institution->name, 0, 1)) }}
                </div>
                <div class="flex-grow-1 min-w-0">
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <h5 class="fw-bold mb-0 text-dark">{{ $institution->name }}</h5>
                        @if($institution->status)
                            <span class="inv-badge paid" data-en="Active" data-bn="সক্রিয়">Active</span>
                        @else
                            <span class="inv-badge unpaid" data-en="Inactive" data-bn="নিষ্ক্রিয়">Inactive</span>
                        @endif

                        @php
                            $vMap = [
                                'pending'   => 'pending',
                                'verified'  => 'paid',
                                'rejected'  => 'unpaid',
                                'suspended' => 'unpaid',
                            ];
                        @endphp
                        <span class="inv-badge {{ $vMap[$institution->verification_status] ?? 'pending' }}">
                            {{ $institution->verificationLabel() }}
                        </span>
                    </div>
                    <p class="text-secondary mb-0 mt-1" style="font-size:12px;">
                        EIIN: {{ $institution->eiin ?? '—' }}
                        &nbsp;·&nbsp;
                        {{ $institution->type ? ucfirst($institution->type) : '' }}
                        @if(!$institution->type)
                            <span data-en="Type not set" data-bn="ধরন নির্ধারিত নয়">Type not set</span>
                        @endif
                        &nbsp;·&nbsp;
                        {{ $institution->mediumLabel() }}
                    </p>
                    <p class="text-secondary mb-0 mt-1" style="font-size:12px;">
                        <span class="material-icons-round" style="font-size:13px;vertical-align:middle;">location_on</span>
                        @php
                            $addressParts = collect([
                                $institution->address,
                                $institution->city,
                                $institution->district,
                                $divisionLabel,
                            ])->filter()->implode(', ');
                        @endphp
                        @if($addressParts)
                            {{ $addressParts }}
                        @else
                            <span data-en="Address not set" data-bn="ঠিকানা নির্ধারিত নয়">Address not set</span>
                        @endif
                    </p>
                </div>
            </div>

            <div class="row g-2 mt-3">
                <div class="col-6 col-md-3">
                    <div class="inst-mini-stat">
                        <span class="material-icons-round" style="font-size:16px;color:#4f46e5;">email</span>
                        <span class="text-truncate">{{ $institution->email ?? '—' }}</span>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="inst-mini-stat">
                        <span class="material-icons-round" style="font-size:16px;color:#059669;">call</span>
                        <span class="text-truncate">{{ $institution->phone ?? '—' }}</span>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="inst-mini-stat">
                        <span class="material-icons-round" style="font-size:16px;color:#d97706;">event</span>
                        <span class="text-truncate"><span data-en="Since" data-bn="যেই তারিখ থেকে">Since</span> {{ $institution->created_at->format('d M Y') }}</span>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="inst-mini-stat">
                        <span class="material-icons-round" style="font-size:16px;color:#7c3aed;">account_tree</span>
                        <span class="text-truncate">{{ $branches->count() }} <span data-en="Branch(es)" data-bn="শাখা">Branch(es)</span></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ══ Verification Panel ═════════════════════════════════════════════ --}}
    <div class="px-3 pt-3">
        <div class="dash-section-card">
            <div class="dash-section-title">
                <span class="material-icons-round text-primary" style="font-size:18px;">verified_user</span>
                <span data-en="Verification Status" data-bn="যাচাই অবস্থা">Verification Status</span>
            </div>

            @if($institution->verification_status !== \App\Models\Institution::VERIFICATION_VERIFIED && $institution->verification_note)
                <div class="inst-verify-note mb-3">
                    <strong data-en="Note:" data-bn="মন্তব্য:">Note:</strong> {{ $institution->verification_note }}
                </div>
            @endif

            @if($institution->verifiedBy)
                <p class="text-secondary mb-3" style="font-size:12px;">
                    <span data-en="Last updated by" data-bn="সর্বশেষ আপডেট করেছেন">Last updated by</span> {{ $institution->verifiedBy->name }} <span data-en="on" data-bn="তারিখ">on</span> {{ $institution->verified_at?->format('d M Y, h:i A') }}
                </p>
            @endif

            <div class="d-flex flex-wrap gap-2">
                @if($institution->verification_status === \App\Models\Institution::VERIFICATION_PENDING)
                    <button type="button" wire:click="verifyInstitution" class="btn btn-success btn-sm">
                        <span class="material-icons-round" style="font-size:14px;vertical-align:middle;">check_circle</span>
                        <span data-en="Verify" data-bn="যাচাই করুন">Verify</span>
                    </button>
                    <button type="button" wire:click="openRejectModal" class="btn btn-danger btn-sm">
                        <span class="material-icons-round" style="font-size:14px;vertical-align:middle;">cancel</span>
                        <span data-en="Reject" data-bn="প্রত্যাখ্যান">Reject</span>
                    </button>
                @elseif($institution->verification_status === \App\Models\Institution::VERIFICATION_VERIFIED)
                    <button type="button" wire:click="openSuspendModal" class="btn btn-warning btn-sm">
                        <span class="material-icons-round" style="font-size:14px;vertical-align:middle;">pause_circle</span>
                        <span data-en="Suspend" data-bn="স্থগিত করুন">Suspend</span>
                    </button>
                @elseif(in_array($institution->verification_status, [\App\Models\Institution::VERIFICATION_REJECTED, \App\Models\Institution::VERIFICATION_SUSPENDED]))
                    <button type="button" wire:click="reactivateInstitution" class="btn btn-success btn-sm">
                        <span class="material-icons-round" style="font-size:14px;vertical-align:middle;">restart_alt</span>
                        <span data-en="Reactivate (Mark Verified)" data-bn="পুনরায় সক্রিয় করুন (যাচাইকৃত হিসেবে চিহ্নিত)">Reactivate (Mark Verified)</span>
                    </button>
                @endif
            </div>
        </div>
    </div>

    {{-- ══ Stat Cards ══════════════════════════════════════════════════════ --}}
    <div class="px-3 pt-3">
        <div class="row g-3">
            <div class="col-6 col-md-4">
                <div class="dash-stat-card">
                    <div class="dash-stat-icon" style="background:#fef3c7;">
                        <span class="material-icons-round" style="color:#d97706;">school</span>
                    </div>
                    <p class="dash-stat-label" data-en="Total Students" data-bn="মোট শিক্ষার্থী">Total Students</p>
                    <h4 class="dash-stat-value">{{ number_format($totalStudents) }}</h4>
                </div>
            </div>
            <div class="col-6 col-md-4">
                <div class="dash-stat-card">
                    <div class="dash-stat-icon" style="background:#ede9fe;">
                        <span class="material-icons-round" style="color:#7c3aed;">badge</span>
                    </div>
                    <p class="dash-stat-label" data-en="Total Teachers" data-bn="মোট শিক্ষক">Total Teachers</p>
                    <h4 class="dash-stat-value">{{ number_format($totalTeachers) }}</h4>
                </div>
            </div>
            <div class="col-6 col-md-4">
                <div class="dash-stat-card">
                    <div class="dash-stat-icon" style="background:#ecfeff;">
                        <span class="material-icons-round" style="color:#0891b2;">how_to_reg</span>
                    </div>
                    <p class="dash-stat-label" data-en="Active Teachers" data-bn="সক্রিয় শিক্ষক">Active Teachers</p>
                    <h4 class="dash-stat-value">{{ number_format($activeTeachers) }}</h4>
                </div>
            </div>
        </div>
    </div>

    {{-- ══ Setup Progress ══════════════════════════════════════════════════ --}}
    <div class="px-3 pt-3">
        <div class="dash-section-card">
            <div class="dash-section-title">
                <span class="material-icons-round text-primary" style="font-size:18px;">checklist</span>
                <span data-en="Setup Progress" data-bn="সেটআপ অগ্রগতি">Setup Progress</span>
            </div>
            <div class="d-flex align-items-center gap-2 mb-2">
                <div class="inst-progress-bar flex-grow-1">
                    <div class="inst-progress-fill" style="width:{{ $institution->setupProgressPercent() }}%;"></div>
                </div>
                <span class="text-secondary" style="font-size:12px;">{{ $institution->setupProgressPercent() }}%</span>
            </div>
            @if($institution->setup_completed)
                <span class="inv-badge paid" data-en="Setup Completed" data-bn="সেটআপ সম্পন্ন">Setup Completed</span>
            @else
                <span class="inv-badge unpaid" data-en="Setup Incomplete" data-bn="সেটআপ অসম্পূর্ণ">Setup Incomplete</span>
            @endif
        </div>
    </div>

    {{-- ══ Branches ════════════════════════════════════════════════════════ --}}
    <div class="px-3 pt-3 pb-4">
        <div class="dash-section-card">
            <div class="dash-section-title">
                <span class="material-icons-round text-warning" style="font-size:18px;">account_tree</span>
                <span data-en="Branches" data-bn="শাখাসমূহ">Branches</span>
            </div>

            @forelse($branches as $branch)
                <div class="dash-notice-row">
                    <div class="flex-grow-1 min-w-0">
                        <p class="mb-0 text-dark fw-semibold text-truncate" style="font-size:13px;">
                            {{ $branch->name }}
                            @if($branch->is_main)
                                <span class="text-secondary" style="font-size:11px;" data-en="(Main)" data-bn="(প্রধান)">(Main)</span>
                            @endif
                        </p>
                        <small class="text-secondary" style="font-size:11px;">{{ $branch->code }}</small>
                    </div>
                    @if($branch->is_active)
                        <span class="inv-badge paid" data-en="Active" data-bn="সক্রিয়">Active</span>
                    @else
                        <span class="inv-badge unpaid" data-en="Inactive" data-bn="নিষ্ক্রিয়">Inactive</span>
                    @endif
                </div>
            @empty
                <p class="text-center text-secondary py-2 mb-0" style="font-size:13px;" data-en="No Branches Yet" data-bn="এখনো কোনো শাখা নেই">
                    No Branches Yet
                </p>
            @endforelse
        </div>
    </div>

    {{-- ══ Reject Modal ════════════════════════════════════════════════════ --}}
    @if($showRejectModal)
        <div class="inst-modal-overlay" wire:click.self="$set('showRejectModal', false)">
            <div class="inst-modal-box">
                <h6 class="fw-bold mb-3" data-en="Reject Institution" data-bn="প্রতিষ্ঠান প্রত্যাখ্যান করুন">Reject Institution</h6>
                <label class="inst-filter-label" data-en="Reason for rejection" data-bn="প্রত্যাখ্যানের কারণ">Reason for rejection</label>
                <textarea wire:model="rejectReason" class="form-control form-control-sm" rows="3"
                          data-en-ph="Write the reason..." data-bn-ph="কারণ লিখুন..."
                          placeholder="Write the reason..."></textarea>
                @error('rejectReason') <small class="text-danger">{{ $message }}</small> @enderror

                <div class="d-flex justify-content-end gap-2 mt-3">
                    <button type="button" wire:click="$set('showRejectModal', false)" class="btn btn-outline-secondary btn-sm" data-en="Cancel" data-bn="বাতিল">Cancel</button>
                    <button type="button" wire:click="confirmReject" class="btn btn-danger btn-sm" data-en="Confirm Reject" data-bn="প্রত্যাখ্যান নিশ্চিত করুন">Confirm Reject</button>
                </div>
            </div>
        </div>
    @endif

    {{-- ══ Suspend Modal ═══════════════════════════════════════════════════ --}}
    @if($showSuspendModal)
        <div class="inst-modal-overlay" wire:click.self="$set('showSuspendModal', false)">
            <div class="inst-modal-box">
                <h6 class="fw-bold mb-3" data-en="Suspend Institution" data-bn="প্রতিষ্ঠান স্থগিত করুন">Suspend Institution</h6>
                <label class="inst-filter-label" data-en="Reason for suspension" data-bn="স্থগিতের কারণ">Reason for suspension</label>
                <textarea wire:model="suspendReason" class="form-control form-control-sm" rows="3"
                          data-en-ph="Write the reason..." data-bn-ph="কারণ লিখুন..."
                          placeholder="Write the reason..."></textarea>
                @error('suspendReason') <small class="text-danger">{{ $message }}</small> @enderror

                <div class="d-flex justify-content-end gap-2 mt-3">
                    <button type="button" wire:click="$set('showSuspendModal', false)" class="btn btn-outline-secondary btn-sm" data-en="Cancel" data-bn="বাতিল">Cancel</button>
                    <button type="button" wire:click="confirmSuspend" class="btn btn-warning btn-sm" data-en="Confirm Suspend" data-bn="স্থগিত নিশ্চিত করুন">Confirm Suspend</button>
                </div>
            </div>
        </div>
    @endif

</div>

@push('styles')
<style>
    .inst-show-wrap { background: var(--body-bg); min-height: 100vh; }

    .inst-back-link {
        font-size: 12px; color: var(--lbl); text-decoration: none;
        display: inline-flex; align-items: center; gap: 4px;
    }

    .inst-show-header-card {
        background: var(--card); border: 1px solid var(--border);
        border-radius: var(--radius-card); padding: 16px;
        box-shadow: var(--shadow);
    }

    .inst-show-avatar {
        width: 52px; height: 52px; border-radius: 14px;
        background: linear-gradient(135deg, var(--primary), #7ba3ff);
        color: #fff; font-size: 20px; font-weight: 700;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }

    .inst-mini-stat {
        display: flex; align-items: center; gap: 6px;
        background: var(--section-bg); border-radius: 8px;
        padding: 8px 10px; font-size: 12px; color: var(--val);
    }

    .dash-stat-card {
        background: var(--card); border-radius: var(--radius-card);
        padding: 14px; box-shadow: var(--shadow); height: 100%;
        border: 1px solid var(--border);
    }
    .dash-stat-icon {
        width: 38px; height: 38px; border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        margin-bottom: 10px;
    }
    .dash-stat-icon .material-icons-round { font-size: 20px; }
    .dash-stat-label { font-size: 11px; color: var(--lbl); margin-bottom: 2px; }
    .dash-stat-value { font-size: 20px; font-weight: 700; color: var(--val); margin-bottom: 0; }

    .dash-section-card {
        background: var(--card); border: 1px solid var(--border);
        border-radius: var(--radius-card); padding: 16px; box-shadow: var(--shadow);
    }
    .dash-section-title {
        font-size: 14px; font-weight: 600; color: var(--val);
        display: flex; align-items: center; gap: 6px; margin-bottom: 12px;
    }

    .dash-notice-row {
        display: flex; align-items: center; padding: 11px 12px;
        border-radius: 10px; background: var(--section-bg);
        margin-bottom: 8px; gap: 10px;
    }
    .dash-notice-row:last-child { margin-bottom: 0; }

    .inst-progress-bar {
        height: 8px; border-radius: 999px; background: var(--section-bg);
        overflow: hidden;
    }
    .inst-progress-fill {
        height: 100%; background: var(--primary); border-radius: 999px;
        transition: width .3s;
    }

    .inv-badge {
        display: inline-block; padding: 3px 10px; border-radius: 4px;
        font-size: 11px; font-weight: 600; border: 1px solid transparent;
    }
    .inv-badge.paid    { background: transparent; border-color: #22c55e; color: #22c55e; }
    .inv-badge.unpaid  { background: transparent; border-color: #ef4444; color: #ef4444; }
    .inv-badge.pending { background: transparent; border-color: #d97706; color: #d97706; }

    .inst-verify-note {
        background: var(--section-bg); border-radius: 8px;
        padding: 10px 12px; font-size: 12px; color: var(--val);
    }

    .inst-modal-overlay {
        position: fixed; inset: 0; background: rgba(0,0,0,0.45);
        display: flex; align-items: center; justify-content: center;
        z-index: 1050; padding: 16px;
    }
    .inst-modal-box {
        background: var(--card); border-radius: var(--radius-card);
        padding: 20px; width: 100%; max-width: 420px;
        box-shadow: var(--shadow);
    }
</style>
@endpush