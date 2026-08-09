{{-- resources/views/livewire/ministry/compliance/inspection-show-component.blade.php --}}

<div class="cm-wrap">

    <div class="px-3 pt-3">
        <a href="{{ route('ministry.compliance.inspections.index') }}" class="inst-back-link">
            <span class="material-icons-round" style="font-size:16px;vertical-align:middle;">arrow_back</span>
            <span data-en="Back to Inspections" data-bn="পরিদর্শনে ফিরে যান">Back to Inspections</span>
        </a>
    </div>

    <div class="px-3 pt-3">
        <div class="dash-section-card">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                <div>
                    <h5 class="fw-bold text-dark mb-1">{{ $inspection->institution->name }}</h5>
                    <p class="text-secondary mb-0" style="font-size:12px;">
                        EIIN: {{ $inspection->institution->eiin }} · {{ $inspection->institution->division }}
                    </p>
                </div>
                @php
                    $badgeClass = match ($inspection->status) {
                        'completed' => 'success',
                        'cancelled' => 'muted',
                        default => 'warning',
                    };
                @endphp
                <span class="badge-soft {{ $badgeClass }}">{{ $inspection->statusLabel() }}</span>
            </div>

            @if ($inspection->overall_score !== null)
                <div class="cm-info-note mt-3">
                    <strong data-en="Overall Score:" data-bn="সামগ্রিক স্কোর:">Overall Score:</strong> {{ $inspection->overall_score }}%
                </div>
            @endif
        </div>
    </div>

    @if ($inspection->isScheduled())

        <div class="px-3 pt-3">
            <button type="button" wire:click="openCancelModal" class="btn btn-outline-danger btn-sm" data-en="Cancel Inspection" data-bn="পরিদর্শন বাতিল করুন">Cancel Inspection</button>
        </div>

        <div class="px-3 pt-3">
            <div class="inst-table-card">
                <div class="dash-section-title px-3 pt-3">
                    <span class="material-icons-round text-primary" style="font-size:18px;">checklist</span>
                    <span data-en="Checklist Scoring" data-bn="চেকলিস্ট স্কোরিং">Checklist Scoring</span>
                </div>
                <div class="table-responsive">
                    <table class="table inst-table align-middle mb-0">
                        <thead>
                            <tr>
                                <th data-en="Category" data-bn="বিভাগ">Category</th>
                                <th data-en="Criterion" data-bn="মানদণ্ড">Criterion</th>
                                <th style="width:140px;" data-en="Score" data-bn="স্কোর">Score</th>
                                <th data-en="Remarks" data-bn="মন্তব্য">Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($this->checklistItems as $item)
                                <tr wire:key="score-{{ $item->id }}">
                                    <td class="text-secondary" style="font-size:12px;">{{ $item->category }}</td>
                                    <td class="text-secondary" style="font-size:12px;">{{ $item->criterion }}</td>
                                    <td>
                                        <input type="number" step="0.5" min="0" max="{{ $item->max_score }}"
                                               wire:model="scores.{{ $item->id }}.score" class="form-control form-control-sm">
                                        <span class="text-secondary" style="font-size:11px;">/ {{ $item->max_score }}</span>
                                        @error("scores.{$item->id}.score") <div class="text-danger" style="font-size:11px;">{{ $message }}</div> @enderror
                                    </td>
                                    <td>
                                        <input type="text" wire:model="scores.{{ $item->id }}.remarks" class="form-control form-control-sm">
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="px-3 py-3 border-top">
                    <button type="button" wire:click="conductInspection" class="act-btn">
                        <span class="material-icons-round" style="font-size:16px;">task_alt</span>
                        <span data-en="Submit &amp; Complete Inspection" data-bn="জমা দিন এবং পরিদর্শন সম্পন্ন করুন">Submit &amp; Complete Inspection</span>
                    </button>
                </div>
            </div>
        </div>

    @else

        <div class="px-3 pt-3">
            <div class="inst-table-card">
                <div class="dash-section-title px-3 pt-3">
                    <span class="material-icons-round text-primary" style="font-size:18px;">grading</span>
                    <span data-en="Scores" data-bn="স্কোর">Scores</span>
                </div>
                <div class="table-responsive">
                    <table class="table inst-table align-middle mb-0">
                        <thead>
                            <tr>
                                <th data-en="Category" data-bn="বিভাগ">Category</th>
                                <th data-en="Criterion" data-bn="মানদণ্ড">Criterion</th>
                                <th data-en="Score" data-bn="স্কোর">Score</th>
                                <th data-en="Remarks" data-bn="মন্তব্য">Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($inspection->results as $result)
                                <tr wire:key="result-{{ $result->id }}">
                                    <td class="text-secondary" style="font-size:12px;">{{ $result->checklistItem->category }}</td>
                                    <td class="text-secondary" style="font-size:12px;">{{ $result->checklistItem->criterion }}</td>
                                    <td class="text-secondary" style="font-size:12px;">{{ $result->score }} / {{ $result->checklistItem->max_score }}</td>
                                    <td class="text-secondary" style="font-size:12px;">{{ $result->remarks }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-secondary py-3" style="font-size:13px;" data-en="No scores recorded." data-bn="কোনো স্কোর রেকর্ড করা হয়নি।">No scores recorded.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    @endif

    <div class="px-3 pt-3 pb-4">
        <div class="dash-section-card">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="dash-section-title mb-0">
                    <span class="material-icons-round text-warning" style="font-size:18px;">report</span>
                    <span data-en="Related Violations" data-bn="সম্পর্কিত লঙ্ঘন">Related Violations</span>
                </div>
                <button type="button" wire:click="openViolationModal" class="btn btn-outline-danger btn-sm">
                    <span class="material-icons-round" style="font-size:14px;vertical-align:middle;">add</span>
                    <span data-en="Raise Violation" data-bn="লঙ্ঘন উত্থাপন করুন">Raise Violation</span>
                </button>
            </div>

            @forelse ($inspection->violations as $violation)
                <div class="dash-notice-row align-items-start">
                    <div class="flex-grow-1 min-w-0">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            @php
                                $sevClass = match ($violation->severity) {
                                    'critical' => 'danger',
                                    'major' => 'warning',
                                    default => 'muted',
                                };
                            @endphp
                            <span class="badge-soft {{ $sevClass }}">{{ $violation->severityLabel() }}</span>
                            <span class="badge-soft info">{{ $violation->statusLabel() }}</span>
                        </div>
                        <p class="mb-0 text-dark" style="font-size:13px;">{{ $violation->description }}</p>
                    </div>
                </div>
            @empty
                <p class="text-center text-secondary py-2 mb-0" style="font-size:13px;" data-en="No violations linked to this inspection." data-bn="এই পরিদর্শনের সাথে কোনো লঙ্ঘন যুক্ত নেই।">No violations linked to this inspection.</p>
            @endforelse
        </div>
    </div>

    @if ($showCancelModal)
        <div class="cm-modal-overlay" wire:click.self="$set('showCancelModal', false)">
            <div class="cm-modal-box">
                <h6 class="fw-bold mb-3" data-en="Cancel Inspection" data-bn="পরিদর্শন বাতিল করুন">Cancel Inspection</h6>
                <textarea wire:model="cancelReason" class="form-control form-control-sm" rows="3"
                          data-en-ph="Reason for cancellation..." data-bn-ph="বাতিলের কারণ..."
                          placeholder="Reason for cancellation..."></textarea>
                @error('cancelReason') <small class="text-danger">{{ $message }}</small> @enderror
                <div class="d-flex justify-content-end gap-2 mt-3">
                    <button type="button" wire:click="$set('showCancelModal', false)" class="btn btn-outline-secondary btn-sm" data-en="Close" data-bn="বন্ধ করুন">Close</button>
                    <button type="button" wire:click="confirmCancel" class="btn btn-danger btn-sm" data-en="Confirm Cancel" data-bn="বাতিল নিশ্চিত করুন">Confirm Cancel</button>
                </div>
            </div>
        </div>
    @endif

    @if ($showViolationModal)
        <div class="cm-modal-overlay" wire:click.self="$set('showViolationModal', false)">
            <div class="cm-modal-box">
                <h6 class="fw-bold mb-3" data-en="Raise Violation" data-bn="লঙ্ঘন উত্থাপন করুন">Raise Violation</h6>
                <label class="inst-filter-label" data-en="Severity" data-bn="তীব্রতা">Severity</label>
                <select wire:model="violationSeverity" class="form-select form-select-sm mb-2">
                    <option value="minor" data-en="Minor" data-bn="সামান্য">Minor</option>
                    <option value="major" data-en="Major" data-bn="গুরুত্বপূর্ণ">Major</option>
                    <option value="critical" data-en="Critical" data-bn="গুরুতর">Critical</option>
                </select>
                <label class="inst-filter-label" data-en="Description" data-bn="বিবরণ">Description</label>
                <textarea wire:model="violationDescription" class="form-control form-control-sm" rows="3"
                          data-en-ph="Describe the violation..." data-bn-ph="লঙ্ঘনের বিবরণ দিন..."
                          placeholder="Describe the violation..."></textarea>
                @error('violationDescription') <small class="text-danger">{{ $message }}</small> @enderror
                <div class="d-flex justify-content-end gap-2 mt-3">
                    <button type="button" wire:click="$set('showViolationModal', false)" class="btn btn-outline-secondary btn-sm" data-en="Close" data-bn="বন্ধ করুন">Close</button>
                    <button type="button" wire:click="raiseViolation" class="btn btn-danger btn-sm" data-en="Submit" data-bn="জমা দিন">Submit</button>
                </div>
            </div>
        </div>
    @endif

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