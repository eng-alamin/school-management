{{-- resources/views/livewire/ministry/grievance/index-component.blade.php --}}

<div class="grv-wrap">

    <div class="px-3 pt-3 pb-2">
        <h5 class="fw-bold mb-0 text-dark" data-en="Grievances" data-bn="অভিযোগসমূহ">Grievances</h5>
        <p class="text-secondary mb-0" style="font-size:12px;" data-en="Complaints submitted by guardians, students, or staff" data-bn="অভিভাবক, শিক্ষার্থী বা কর্মচারী কর্তৃক জমাকৃত অভিযোগ">Complaints submitted by guardians, students, or staff</p>
    </div>

    <div class="px-3 pt-2">
        <div class="inst-filter-card">
            <div class="row g-2 align-items-end">
                <div class="col-12 col-md-3">
                    <label class="inst-filter-label" data-en="Search" data-bn="খুঁজুন">Search</label>
                    <input type="text" wire:model.live.debounce.300ms="search"
                           class="form-control form-control-sm"
                           data-en-ph="Search subject, category, institution..." data-bn-ph="বিষয়, বিভাগ, প্রতিষ্ঠান খুঁজুন..."
                           placeholder="Search subject, category, institution...">
                </div>
                <div class="col-6 col-md-2">
                    <label class="inst-filter-label" data-en="Status" data-bn="অবস্থা">Status</label>
                    <select wire:model.live="status" class="form-select form-select-sm">
                        <option value="" data-en="All Statuses" data-bn="সকল অবস্থা">All Statuses</option>
                        @foreach ($this->statuses as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label class="inst-filter-label" data-en="Type" data-bn="ধরন">Type</label>
                    <select wire:model.live="complainantType" class="form-select form-select-sm">
                        <option value="" data-en="All Types" data-bn="সকল ধরন">All Types</option>
                        @foreach ($this->complainantTypes as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-3">
                    <label class="inst-filter-label" data-en="Division" data-bn="বিভাগ">Division</label>
                    <select wire:model.live="division" class="form-select form-select-sm">
                        <option value="" data-en="All Divisions" data-bn="সকল বিভাগ">All Divisions</option>
                        @foreach ($this->divisions as $div)
                            <option value="{{ $div }}">{{ $div }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="px-3 pt-3 pb-4">
        <div class="inst-table-card">
            <div class="table-responsive">
                <table class="table inst-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th data-en="Institution" data-bn="প্রতিষ্ঠান">Institution</th>
                            <th data-en="Subject" data-bn="বিষয়">Subject</th>
                            <th data-en="Type" data-bn="ধরন">Type</th>
                            <th role="button" wire:click="sortBy('status')" data-en="Status" data-bn="অবস্থা">Status</th>
                            <th data-en="Assigned To" data-bn="নিযুক্ত">Assigned To</th>
                            <th role="button" wire:click="sortBy('created_at')" data-en="Submitted" data-bn="জমাকৃত">Submitted</th>
                            <th class="text-end" data-en="Action" data-bn="কার্যক্রম">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($grievances as $grievance)
                            <tr wire:key="grievance-{{ $grievance->id }}">
                                <td class="text-secondary" style="font-size:12px;">{{ $grievance->institution->name }}</td>
                                <td class="fw-semibold text-dark" style="font-size:13px;">{{ $grievance->subject }}</td>
                                <td class="text-secondary" style="font-size:12px;">{{ $grievance->complainantTypeLabel() }}</td>
                                <td>
                                    @php
                                        $statClass = match ($grievance->status) {
                                            'resolved' => 'success',
                                            'rejected' => 'muted',
                                            'escalated' => 'danger',
                                            default => 'warning',
                                        };
                                    @endphp
                                    <span class="badge-soft {{ $statClass }}">{{ $grievance->statusLabel() }}</span>
                                </td>
                                <td class="text-secondary" style="font-size:12px;">
                                    {{ $grievance->assignedTo->name ?? '—' }}
                                    @if (!$grievance->assigned_to)
                                        <button type="button" wire:click="assignToMe({{ $grievance->id }})" class="grv-assign-link" data-en="Assign to me" data-bn="আমাকে নিযুক্ত করুন">Assign to me</button>
                                    @endif
                                </td>
                                <td class="text-secondary" style="font-size:12px;">{{ $grievance->created_at->format('d M, Y') }}</td>
                                <td class="text-end">
                                    <a href="{{ route('ministry.grievances.show', $grievance) }}" class="icon-btn-sm primary" title="View">
                                        <span class="material-icons-round" style="font-size:16px;">visibility</span>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-secondary py-4" style="font-size:13px;" data-en="No grievances found." data-bn="কোনো অভিযোগ পাওয়া যায়নি।">No grievances found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $grievances->links() }}
        </div>
    </div>

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

    .grv-assign-link {
        background: transparent; border: none; color: var(--primary);
        font-size: 11px; font-weight: 600; padding: 0; margin-left: 6px;
        text-decoration: underline; cursor: pointer;
    }

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