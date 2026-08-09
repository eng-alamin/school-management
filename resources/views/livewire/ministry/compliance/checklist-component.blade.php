{{-- resources/views/livewire/ministry/compliance/checklist-component.blade.php --}}

<div class="cm-wrap">

    <div class="px-3 pt-3 pb-2 d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <h5 class="fw-bold mb-0 text-dark" data-en="Inspection Checklist" data-bn="পরিদর্শন চেকলিস্ট">Inspection Checklist</h5>
            <p class="text-secondary mb-0" style="font-size:12px;" data-en="Criteria used to evaluate institutions during inspection" data-bn="পরিদর্শনের সময় প্রতিষ্ঠান মূল্যায়নের মানদণ্ড">Criteria used to evaluate institutions during inspection</p>
        </div>
        <button type="button" wire:click="openCreateModal" class="act-btn">
            <span class="material-icons-round" style="font-size:16px;">add</span> <span data-en="Add Criterion" data-bn="মানদণ্ড যোগ করুন">Add Criterion</span>
        </button>
    </div>

    <div class="px-3 pt-2">
        <div class="inst-filter-card">
            <div class="row g-2 align-items-end">
                <div class="col-12 col-md-6">
                    <label class="inst-filter-label" data-en="Search" data-bn="খুঁজুন">Search</label>
                    <input type="text" wire:model.live.debounce.300ms="search"
                           class="form-control form-control-sm"
                           data-en-ph="Search category or criterion..." data-bn-ph="বিভাগ বা মানদণ্ড খুঁজুন..."
                           placeholder="Search category or criterion...">
                </div>
                <div class="col-6 col-md-3">
                    <label class="inst-filter-label" data-en="Status" data-bn="অবস্থা">Status</label>
                    <select wire:model.live="statusFilter" class="form-select form-select-sm">
                        <option value="active" data-en="Active" data-bn="সক্রিয়">Active</option>
                        <option value="inactive" data-en="Inactive" data-bn="নিষ্ক্রিয়">Inactive</option>
                        <option value="all" data-en="All" data-bn="সকল">All</option>
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
                            <th role="button" wire:click="sortBy('category')" data-en="Category" data-bn="বিভাগ">Category</th>
                            <th role="button" wire:click="sortBy('criterion')" data-en="Criterion" data-bn="মানদণ্ড">Criterion</th>
                            <th role="button" wire:click="sortBy('max_score')" data-en="Max Score" data-bn="সর্বোচ্চ স্কোর">Max Score</th>
                            <th data-en="Status" data-bn="অবস্থা">Status</th>
                            <th data-en="Used In" data-bn="ব্যবহৃত হয়েছে">Used In</th>
                            <th class="text-end" data-en="Actions" data-bn="কার্যক্রম">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($items as $item)
                            <tr wire:key="checklist-{{ $item->id }}">
                                <td class="fw-semibold text-dark" style="font-size:13px;">{{ $item->category }}</td>
                                <td class="text-secondary" style="font-size:12px;">{{ $item->criterion }}</td>
                                <td class="text-secondary" style="font-size:12px;">{{ $item->max_score }}</td>
                                <td>
                                    @if ($item->is_active)
                                        <span class="inv-badge paid" data-en="Active" data-bn="সক্রিয়">Active</span>
                                    @else
                                        <span class="inv-badge unpaid" data-en="Inactive" data-bn="নিষ্ক্রিয়">Inactive</span>
                                    @endif
                                </td>
                                <td class="text-secondary" style="font-size:12px;">
                                    {{ $item->results_count }} <span data-en="inspections" data-bn="পরিদর্শন">inspections</span>
                                </td>
                                <td class="text-end">
                                    <div class="d-inline-flex gap-1">
                                        <button type="button" wire:click="openEditModal({{ $item->id }})" class="icon-btn-sm primary" title="Edit">
                                            <span class="material-icons-round" style="font-size:16px;">edit</span>
                                        </button>
                                        <button type="button" wire:click="toggleActive({{ $item->id }})" class="icon-btn-sm" title="Toggle Status">
                                            <span class="material-icons-round" style="font-size:16px;">sync_alt</span>
                                        </button>
                                        @if ($item->results_count === 0)
                                            <button type="button" wire:click="delete({{ $item->id }})"
                                                    wire:confirm="Are you sure you want to delete this criterion?"
                                                    class="icon-btn-sm danger" title="Delete">
                                                <span class="material-icons-round" style="font-size:16px;">delete</span>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-secondary py-4" style="font-size:13px;" data-en="No checklist items found." data-bn="কোনো চেকলিস্ট আইটেম পাওয়া যায়নি।">
                                    No checklist items found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($items->hasPages())
                <div class="px-3 py-3 border-top">{{ $items->links() }}</div>
            @endif
        </div>
    </div>

    @if ($showModal)
        <div class="cm-modal-overlay" wire:click.self="closeModal">
            <div class="cm-modal-box">
                <h6 class="fw-bold mb-3">
                    @if($editingId)
                        <span data-en="Edit Criterion" data-bn="মানদণ্ড সম্পাদনা">Edit Criterion</span>
                    @else
                        <span data-en="Add Criterion" data-bn="মানদণ্ড যোগ করুন">Add Criterion</span>
                    @endif
                </h6>

                <div class="mb-2">
                    <label class="inst-filter-label" data-en="Category" data-bn="বিভাগ">Category</label>
                    <input type="text" wire:model="category" class="form-control form-control-sm">
                    @error('category') <small class="text-danger">{{ $message }}</small> @enderror
                </div>

                <div class="mb-2">
                    <label class="inst-filter-label" data-en="Criterion" data-bn="মানদণ্ড">Criterion</label>
                    <textarea wire:model="criterion" class="form-control form-control-sm" rows="2"></textarea>
                    @error('criterion') <small class="text-danger">{{ $message }}</small> @enderror
                </div>

                <div class="row g-2">
                    <div class="col-6 mb-2">
                        <label class="inst-filter-label" data-en="Max Score" data-bn="সর্বোচ্চ স্কোর">Max Score</label>
                        <input type="number" wire:model="maxScore" class="form-control form-control-sm" min="1" max="100">
                        @error('maxScore') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>
                    <div class="col-6 mb-2">
                        <label class="inst-filter-label" data-en="Sort Order" data-bn="ক্রম">Sort Order</label>
                        <input type="number" wire:model="sortOrder" class="form-control form-control-sm" min="0">
                    </div>
                </div>

                <div class="form-check mb-3 mt-1">
                    <input type="checkbox" wire:model="isActive" class="form-check-input" id="isActiveCheck">
                    <label class="form-check-label" for="isActiveCheck" style="font-size:13px;" data-en="Active" data-bn="সক্রিয়">Active</label>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <button type="button" wire:click="closeModal" class="btn btn-outline-secondary btn-sm" data-en="Cancel" data-bn="বাতিল">Cancel</button>
                    <button type="button" wire:click="save" class="btn btn-primary btn-sm" data-en="Save" data-bn="সংরক্ষণ">Save</button>
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