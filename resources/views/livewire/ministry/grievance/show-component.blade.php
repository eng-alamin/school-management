{{-- resources/views/livewire/ministry/grievance/show-component.blade.php --}}

<div class="grv-wrap">

    <div class="px-3 pt-3">
        <div class="dash-section-card">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                <div>
                    <h5 class="fw-bold text-dark mb-1">{{ $grievance->subject }}</h5>
                    <p class="text-secondary mb-0" style="font-size:12px;">
                        {{ $grievance->institution->name }} ·
                        {{ $grievance->complainantTypeLabel() }}: {{ $grievance->displayComplainantName() }}
                        @if ($grievance->student)
                            · <span data-en="Regarding:" data-bn="সম্পর্কিত:">Regarding:</span> {{ $grievance->student->name }}
                        @endif
                    </p>
                </div>
                @php
                    $statClass = match ($grievance->status) {
                        'resolved' => 'success',
                        'rejected' => 'muted',
                        'escalated' => 'danger',
                        default => 'warning',
                    };
                @endphp
                <span class="badge-soft {{ $statClass }}">{{ $grievance->statusLabel() }}</span>
            </div>
        </div>
    </div>

    <div class="px-3 pt-3">
        <div class="dash-section-card">
            <div class="dash-section-title">
                <span class="material-icons-round text-primary" style="font-size:18px;">description</span>
                <span data-en="Details" data-bn="বিস্তারিত">Details</span>
            </div>
            <p class="text-secondary mb-1" style="font-size:11px;text-transform:uppercase;letter-spacing:.03em;" data-en="Category" data-bn="বিভাগ">Category</p>
            <p class="text-dark mb-3" style="font-size:13px;">{{ $grievance->category }}</p>
            <p class="text-secondary mb-1" style="font-size:11px;text-transform:uppercase;letter-spacing:.03em;" data-en="Description" data-bn="বিবরণ">Description</p>
            <p class="text-dark mb-0" style="font-size:13px;white-space:pre-wrap;">{{ $grievance->description }}</p>
        </div>
    </div>

    @if ($grievance->resolution_note)
        <div class="px-3 pt-3">
            <div class="cm-info-note">
                <strong data-en="Resolution Note:" data-bn="সমাধান মন্তব্য:">Resolution Note:</strong> {{ $grievance->resolution_note }}
            </div>
        </div>
    @endif

    @if ($grievance->violation)
        <div class="px-3 pt-3">
            <div class="cm-warning-note">
                <strong data-en="Linked Violation:" data-bn="সংযুক্ত লঙ্ঘন:">Linked Violation:</strong>
                {{ $grievance->violation->severityLabel() }} — {{ \Illuminate\Support\Str::limit($grievance->violation->description, 100) }}
            </div>
        </div>
    @endif

    <div class="px-3 pt-3 pb-4">
        <div class="d-flex gap-2 flex-wrap">
            @if ($grievance->status === 'submitted')
                <button type="button" wire:click="startReview" class="act-btn">
                    <span class="material-icons-round" style="font-size:16px;">rate_review</span>
                    <span data-en="Start Review" data-bn="পর্যালোচনা শুরু করুন">Start Review</span>
                </button>
            @endif

            @if (!$grievance->isClosed())
                <button type="button" wire:click="openResolveModal('resolved')" class="btn btn-success btn-sm" data-en="Mark Resolved" data-bn="সমাধান হয়েছে চিহ্নিত করুন">Mark Resolved</button>
                <button type="button" wire:click="openResolveModal('rejected')" class="btn btn-outline-secondary btn-sm" data-en="Reject" data-bn="প্রত্যাখ্যান">Reject</button>
                <button type="button" wire:click="openResolveModal('escalated')" class="btn btn-outline-danger btn-sm" data-en="Escalate" data-bn="এসকেলেট করুন">Escalate</button>
            @endif

            @if (!$grievance->violation_id)
                <button type="button" wire:click="openViolationModal" class="btn btn-outline-danger btn-sm" data-en="Convert to Violation" data-bn="লঙ্ঘনে রূপান্তর করুন">Convert to Violation</button>
            @endif
        </div>
    </div>

    @if ($showResolveModal)
        <div class="cm-modal-overlay" wire:click.self="$set('showResolveModal', false)">
            <div class="cm-modal-box">
                <h6 class="fw-bold mb-3">
                    @if ($resolveAction === 'resolved')
                        <span data-en="Mark as Resolved" data-bn="সমাধান হয়েছে চিহ্নিত করুন">Mark as Resolved</span>
                    @elseif ($resolveAction === 'rejected')
                        <span data-en="Reject Grievance" data-bn="অভিযোগ প্রত্যাখ্যান করুন">Reject Grievance</span>
                    @else
                        <span data-en="Escalate Grievance" data-bn="অভিযোগ এসকেলেট করুন">Escalate Grievance</span>
                    @endif
                </h6>
                <textarea wire:model="resolutionNote" class="form-control form-control-sm" rows="3"
                          data-en-ph="Note..." data-bn-ph="মন্তব্য..."
                          placeholder="Note..."></textarea>
                @error('resolutionNote') <small class="text-danger">{{ $message }}</small> @enderror
                <div class="d-flex justify-content-end gap-2 mt-3">
                    <button type="button" wire:click="$set('showResolveModal', false)" class="btn btn-outline-secondary btn-sm" data-en="Close" data-bn="বন্ধ করুন">Close</button>
                    <button type="button" wire:click="confirmResolve" class="btn btn-primary btn-sm" data-en="Confirm" data-bn="নিশ্চিত করুন">Confirm</button>
                </div>
            </div>
        </div>
    @endif

    @if ($showViolationModal)
        <div class="cm-modal-overlay" wire:click.self="$set('showViolationModal', false)">
            <div class="cm-modal-box">
                <h6 class="fw-bold mb-3" data-en="Convert to Compliance Violation" data-bn="কমপ্লায়েন্স লঙ্ঘনে রূপান্তর করুন">Convert to Compliance Violation</h6>
                <select wire:model="violationSeverity" class="form-select form-select-sm mb-3">
                    <option value="minor" data-en="Minor" data-bn="সামান্য">Minor</option>
                    <option value="major" data-en="Major" data-bn="গুরুত্বপূর্ণ">Major</option>
                    <option value="critical" data-en="Critical" data-bn="গুরুতর">Critical</option>
                </select>
                <div class="d-flex justify-content-end gap-2">
                    <button type="button" wire:click="$set('showViolationModal', false)" class="btn btn-outline-secondary btn-sm" data-en="Close" data-bn="বন্ধ করুন">Close</button>
                    <button type="button" wire:click="convertToViolation" class="btn btn-danger btn-sm" data-en="Create Violation" data-bn="লঙ্ঘন তৈরি করুন">Create Violation</button>
                </div>
            </div>
        </div>
    @endif

</div>

@push('styles')
<style>
    .grv-wrap { background: var(--body-bg); min-height: 100vh; }

    .act-btn {
        display: inline-flex; align-items: center; gap: 6px;
        background: var(--primary); color: #fff; border: none;
        border-radius: 8px; padding: 7px 14px; font-size: 13px;
        font-weight: 500; text-decoration: none; cursor: pointer;
    }
    .act-btn.danger { background: #ef4444; }

    .inst-back-link {
        font-size: 12px; color: var(--lbl); text-decoration: none;
        display: inline-flex; align-items: center; gap: 4px;
    }

    .inst-filter-card {
        background: var(--card); border: 1px solid var(--border);
        border-radius: var(--radius-card); padding: 14px; box-shadow: var(--shadow);
    }
    .inst-filter-label { font-size: 11px; color: var(--lbl); margin-bottom: 2px; display: block; }

    .inst-table-card {
        background: var(--card); border: 1px solid var(--border);
        border-radius: var(--radius-card); box-shadow: var(--shadow); overflow: hidden;
    }
    .inst-table thead th {
        font-size: 11px; text-transform: uppercase; letter-spacing: .03em;
        color: var(--lbl); border-bottom: 1px solid var(--border);
        padding: 10px 14px; white-space: nowrap;
    }
    .inst-table tbody td { padding: 10px 14px; border-bottom: 1px solid var(--border); }
    .inst-table tbody tr:last-child td { border-bottom: none; }

    .dash-section-card {
        background: var(--card); border: 1px solid var(--border);
        border-radius: var(--radius-card); padding: 18px; box-shadow: var(--shadow);
    }
    .dash-section-title {
        font-size: 14px; font-weight: 600; color: var(--val);
        display: flex; align-items: center; gap: 6px; margin-bottom: 14px;
    }
    .dash-notice-row {
        display: flex; align-items: center; padding: 11px 12px;
        border-radius: 10px; background: var(--section-bg);
        margin-bottom: 8px; gap: 10px;
    }
    .dash-notice-row:last-child { margin-bottom: 0; }

    .inv-badge {
        display: inline-block; padding: 3px 10px; border-radius: 4px;
        font-size: 11px; font-weight: 600; border: 1px solid transparent;
    }
    .inv-badge.paid   { background: transparent; border-color: #22c55e; color: #22c55e; }
    .inv-badge.unpaid { background: transparent; border-color: #ef4444; color: #ef4444; }

    .badge-soft {
        display: inline-block; padding: 3px 10px; border-radius: 4px;
        font-size: 11px; font-weight: 600; border: 1px solid transparent;
    }
    .badge-soft.success { border-color: #22c55e; color: #22c55e; }
    .badge-soft.danger  { border-color: #ef4444; color: #ef4444; }
    .badge-soft.warning { border-color: #d97706; color: #d97706; }
    .badge-soft.muted   { border-color: #6b7280; color: #6b7280; }
    .badge-soft.info    { border-color: #3b6fed; color: #3b6fed; }

    .icon-btn-sm {
        display: inline-flex; align-items: center; justify-content: center;
        width: 30px; height: 30px; border-radius: 8px; border: 1px solid var(--border);
        background: var(--card); color: var(--val); cursor: pointer; transition: var(--transition);
    }
    .icon-btn-sm:hover   { background: var(--section-bg); }
    .icon-btn-sm.primary { color: var(--primary); border-color: var(--primary); }
    .icon-btn-sm.success { color: #22c55e; border-color: #22c55e; }
    .icon-btn-sm.danger  { color: #ef4444; border-color: #ef4444; }

    .cm-suggest-list {
        position: absolute; z-index: 10; width: 100%;
        background: var(--card); border: 1px solid var(--border);
        border-radius: 10px; box-shadow: var(--select-shadow);
        margin-top: 4px; max-height: 220px; overflow-y: auto;
    }
    .cm-suggest-item {
        display: block; width: 100%; text-align: left; background: transparent;
        border: none; border-bottom: 1px solid var(--border); padding: 8px 12px;
        font-size: 12px; color: var(--val); cursor: pointer;
    }
    .cm-suggest-item:last-child { border-bottom: none; }
    .cm-suggest-item:hover { background: var(--select-item-hover); }

    .cm-info-note {
        background: var(--section-bg); border-radius: 8px;
        padding: 10px 12px; font-size: 12px; color: var(--val);
    }
    .cm-warning-note {
        background: rgba(217, 119, 6, 0.1); border: 1px solid rgba(217, 119, 6, 0.3);
        border-radius: 8px; padding: 10px 12px; font-size: 12px; color: var(--val);
    }

    .cm-modal-overlay {
        position: fixed; inset: 0; background: rgba(0,0,0,0.45);
        display: flex; align-items: center; justify-content: center;
        z-index: 1050; padding: 16px;
    }
    .cm-modal-box {
        background: var(--card); border-radius: var(--radius-card);
        padding: 20px; width: 100%; max-width: 440px; box-shadow: var(--shadow);
    }
</style>
@endpush