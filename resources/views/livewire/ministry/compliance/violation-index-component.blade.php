{{-- resources/views/livewire/ministry/compliance/violation-index-component.blade.php --}}

<div class="cm-wrap">

    <div class="px-3 pt-3 pb-2 d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <h5 class="fw-bold mb-0 text-dark" data-en="Compliance Violations" data-bn="কমপ্লায়েন্স লঙ্ঘন">Compliance Violations</h5>
            <p class="text-secondary mb-0" style="font-size:12px;" data-en="Track and resolve institution compliance violations" data-bn="প্রতিষ্ঠানের কমপ্লায়েন্স লঙ্ঘন ট্র্যাক ও সমাধান করুন">Track and resolve institution compliance violations</p>
        </div>
        <button type="button" wire:click="openCreateModal" class="btn btn-danger">
            <span>
                <span class="material-icons-round">report</span> 
                <span data-en="Report Violation" data-bn="লঙ্ঘন রিপোর্ট করুন">Report Violation</span>
            </span>
        </button>
    </div>

    <div class="px-3 pt-2">
        <div class="inst-filter-card">
            <div class="row g-2 align-items-end">
                <div class="col-12 col-md-3">
                    <input type="text" wire:model.live.debounce.300ms="search"
                           class="form-control form-control-sm"
                           data-en-ph="Search institution..." data-bn-ph="প্রতিষ্ঠান খুঁজুন..."
                           placeholder="Search institution...">
                </div>
                <div class="col-6 col-md-2">
                    <div class="input-group input-group-outline">
                        <select wire:model.live="severity" class="form-select form-select-sm">
                            <option value="" data-en="All Severities" data-bn="সকল তীব্রতা">All Severities</option>
                            @foreach ($this->severities as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-6 col-md-2">
                    <div class="input-group input-group-outline">
                        <select wire:model.live="status" class="form-select form-select-sm">
                            <option value="" data-en="All Statuses" data-bn="সকল অবস্থা">All Statuses</option>
                            @foreach ($this->statuses as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="input-group input-group-outline">
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
    </div>

    <div class="px-3 pt-3 pb-4">
        <div class="inst-table-card">
            <div class="table-responsive">
                <table class="table inst-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th data-en="Institution" data-bn="প্রতিষ্ঠান">Institution</th>
                            <th role="button" wire:click="sortBy('severity')" data-en="Severity" data-bn="তীব্রতা">Severity</th>
                            <th data-en="Description" data-bn="বিবরণ">Description</th>
                            <th role="button" wire:click="sortBy('status')" data-en="Status" data-bn="অবস্থা">Status</th>
                            <th data-en="Source" data-bn="উৎস">Source</th>
                            <th class="text-end" data-en="Action" data-bn="কার্যক্রম">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($violations as $violation)
                            <tr wire:key="violation-{{ $violation->id }}">
                                <td class="fw-semibold text-dark" style="font-size:13px;">{{ $violation->institution->name }}</td>
                                <td>
                                    @php
                                        $sevClass = match ($violation->severity) {
                                            'critical' => 'danger',
                                            'major' => 'warning',
                                            default => 'muted',
                                        };
                                    @endphp
                                    <span class="badge-soft {{ $sevClass }}">{{ $violation->severityLabel() }}</span>
                                </td>
                                <td class="text-secondary" style="font-size:12px;">{{ \Illuminate\Support\Str::limit($violation->description, 60) }}</td>
                                <td>
                                    @php
                                        $statClass = match ($violation->status) {
                                            'resolved' => 'success',
                                            'escalated' => 'danger',
                                            default => 'warning',
                                        };
                                    @endphp
                                    <span class="badge-soft {{ $statClass }}">{{ $violation->statusLabel() }}</span>
                                </td>
                                <td class="text-secondary" style="font-size:12px;">
                                    @if ($violation->inspection_id)
                                        <a href="{{ route('ministry.compliance.inspections.show', $violation->inspection_id) }}" class="text-decoration-none" data-en="Inspection" data-bn="পরিদর্শন">Inspection</a>
                                    @else
                                        <span data-en="Direct Report" data-bn="সরাসরি রিপোর্ট">Direct Report</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    @if ($violation->isOpen())
                                        <div class="d-inline-flex gap-1">
                                            <button type="button" wire:click="openResolveModal({{ $violation->id }}, 'resolved')" class="icon-btn-sm success" title="Resolve">
                                                <span class="material-icons-round" style="font-size:16px;">check_circle</span>
                                            </button>
                                            <button type="button" wire:click="openResolveModal({{ $violation->id }}, 'escalated')" class="icon-btn-sm danger" title="Escalate">
                                                <span class="material-icons-round" style="font-size:16px;">trending_up</span>
                                            </button>
                                        </div>
                                    @else
                                        <span class="text-secondary" style="font-size:11px;">{{ $violation->resolved_at?->format('d M, Y') }}</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-secondary py-4" style="font-size:13px;" data-en="No violations found." data-bn="কোনো লঙ্ঘন পাওয়া যায়নি।">No violations found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $violations->links() }}
        </div>
    </div>

    @if ($showCreateModal)
        <div class="cm-modal-overlay" wire:click.self="$set('showCreateModal', false)">
            <div class="cm-modal-box position-relative">
                <h6 class="fw-bold mb-3" data-en="Report Violation" data-bn="লঙ্ঘন রিপোর্ট করুন">Report Violation</h6>

                <label class="inst-filter-label" data-en="Institution" data-bn="প্রতিষ্ঠান">Institution</label>
                <input type="text" wire:model.live.debounce.300ms="institutionSearch" class="form-control form-control-sm"
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
                            <div class="cm-suggest-item text-secondary" data-en="No matching institutions." data-bn="কোনো মিলযুক্ত প্রতিষ্ঠান নেই।">No matching institutions.</div>
                        @endforelse
                    </div>
                @endif

                <label class="inst-filter-label mt-2" data-en="Severity" data-bn="তীব্রতা">Severity</label>
                <select wire:model="newSeverity" class="form-select form-select-sm">
                    <option value="minor" data-en="Minor" data-bn="সামান্য">Minor</option>
                    <option value="major" data-en="Major" data-bn="গুরুত্বপূর্ণ">Major</option>
                    <option value="critical" data-en="Critical" data-bn="গুরুতর">Critical</option>
                </select>

                <label class="inst-filter-label mt-2" data-en="Description" data-bn="বিবরণ">Description</label>
                <textarea wire:model="newDescription" class="form-control form-control-sm" rows="3"></textarea>
                @error('newDescription') <small class="text-danger">{{ $message }}</small> @enderror

                <div class="d-flex justify-content-end gap-2 mt-3">
                    <button type="button" wire:click="$set('showCreateModal', false)" class="btn btn-outline-secondary btn-sm" data-en="Cancel" data-bn="বাতিল">Cancel</button>
                    <button type="button" wire:click="createViolation" class="btn btn-danger btn-sm" data-en="Submit" data-bn="জমা দিন">Submit</button>
                </div>
            </div>
        </div>
    @endif

    @if ($showResolveModal)
        <div class="cm-modal-overlay" wire:click.self="$set('showResolveModal', false)">
            <div class="cm-modal-box">
                <h6 class="fw-bold mb-3">
                    @if($resolveAction === 'resolved')
                        <span data-en="Resolve Violation" data-bn="লঙ্ঘন সমাধান করুন">Resolve Violation</span>
                    @else
                        <span data-en="Escalate Violation" data-bn="লঙ্ঘন এসকেলেট করুন">Escalate Violation</span>
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

</div>

@push('styles')
<style>
    .custom-select-trigger {
        min-height: 30px !important;
        height: 36px !important;
    }
    .cm-wrap { background: var(--body-bg); min-height: 100vh; }

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