{{-- resources/views/livewire/ministry/compliance/inspection-form-component.blade.php --}}

<div class="cm-wrap">

    <div class="px-3 pt-3">
        <a href="{{ route('ministry.compliance.inspections.index') }}" class="inst-back-link">
            <span class="material-icons-round" style="font-size:16px;vertical-align:middle;">arrow_back</span>
            <span data-en="Back to Inspections" data-bn="পরিদর্শনে ফিরে যান">Back to Inspections</span>
        </a>
    </div>

    <div class="px-3 pt-3 pb-4">
        <div class="dash-section-card" style="max-width:640px;">

            <div class="dash-section-title">
                <span data-en="Schedule New Inspection" data-bn="নতুন পরিদর্শন নির্ধারণ করুন">Schedule New Inspection</span>
            </div>

            <div class="mb-3 position-relative">
                <label class="inst-filter-label" data-en="Institution" data-bn="প্রতিষ্ঠান">Institution</label>
                <input type="text" wire:model.live.debounce.300ms="institutionSearch"
                       class="form-control form-control-sm"
                       data-en-ph="Search by name or EIIN..." data-bn-ph="নাম বা ইআইআইএন দিয়ে খুঁজুন..."
                       placeholder="Search by name or EIIN...">
                @error('institutionId') <small class="text-danger">{{ $message }}</small> @enderror

                @if ($institutionSearch && !$institutionId)
                    <div class="cm-suggest-list">
                        @forelse ($this->institutionOptions as $opt)
                            <button type="button" wire:click="selectInstitution({{ $opt->id }}, '{{ addslashes($opt->name) }}')" class="cm-suggest-item">
                                {{ $opt->name }} <span class="text-secondary" style="font-size:11px;">({{ $opt->eiin }})</span>
                            </button>
                        @empty
                            <div class="cm-suggest-item text-secondary" data-en="No matching verified institutions." data-bn="কোনো মিলযুক্ত যাচাইকৃত প্রতিষ্ঠান নেই।">No matching verified institutions.</div>
                        @endforelse
                    </div>
                @endif
            </div>

            <div class="mb-3">
                <label class="inst-filter-label" data-en="Scheduled Date" data-bn="নির্ধারিত তারিখ">Scheduled Date</label>
                <input type="date" wire:model="scheduledAt" class="form-control form-control-sm">
                @error('scheduledAt') <small class="text-danger">{{ $message }}</small> @enderror
            </div>

            <div class="mb-3">
                <label class="inst-filter-label" data-en="Notes (optional)" data-bn="মন্তব্য (ঐচ্ছিক)">Notes (optional)</label>
                <textarea wire:model="notes" class="form-control form-control-sm" rows="3"></textarea>
            </div>

            <button type="button" wire:click="save" class="act-btn">
                <span class="material-icons-round" style="font-size:16px;">event_available</span>
                <span data-en="Schedule" data-bn="নির্ধারণ করুন">Schedule</span>
            </button>

        </div>
    </div>

</div>

@push('styles')
<style>
    .cm-wrap { background: var(--body-bg); min-height: 100vh; }

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