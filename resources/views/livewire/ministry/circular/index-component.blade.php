{{-- resources/views/livewire/ministry/circular/index-component.blade.php --}}

<div class="notice-wrap">

    <div class="px-3 pt-3 pb-2 d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <h5 class="fw-bold mb-0 text-dark" data-en="Circulars" data-bn="পরিপত্রসমূহ">Circulars</h5>
            <p class="text-secondary mb-0" style="font-size:12px;" data-en="Broadcast circulars to registered institutions" data-bn="নিবন্ধিত প্রতিষ্ঠানসমূহে পরিপত্র প্রচার করুন">Broadcast circulars to registered institutions</p>
        </div>
        <a href="{{ route('ministry.circulars.create') }}" class="act-btn">
            <span class="material-icons-round" style="font-size:16px;">add</span> <span data-en="Publish Circular" data-bn="পরিপত্র প্রকাশ করুন">Publish Circular</span>
        </a>
    </div>

    <div class="px-3 pt-2">
        <div class="inst-filter-card">
            <div class="row g-2 align-items-end">
                <div class="col-8 col-md-4">
                    <label class="inst-filter-label" data-en="Search" data-bn="খুঁজুন">Search</label>
                    <input type="text" wire:model.live.debounce.400ms="search"
                           class="form-control form-control-sm"
                           data-en-ph="Search by title..." data-bn-ph="শিরোনাম দিয়ে খুঁজুন..."
                           placeholder="Search by title...">
                </div>
                <div class="col-4 col-md-2">
                    <label class="inst-filter-label" data-en="Status" data-bn="অবস্থা">Status</label>
                    <select wire:model.live="status" class="form-select form-select-sm">
                        <option value="" data-en="All" data-bn="সকল">All</option>
                        <option value="active" data-en="Active" data-bn="সক্রিয়">Active</option>
                        <option value="inactive" data-en="Inactive" data-bn="নিষ্ক্রিয়">Inactive</option>
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
                            <th data-en="Title" data-bn="শিরোনাম">Title</th>
                            <th data-en="Audience" data-bn="প্রাপক">Audience</th>
                            <th data-en="Published By" data-bn="প্রকাশক">Published By</th>
                            <th data-en="Published At" data-bn="প্রকাশের তারিখ">Published At</th>
                            <th data-en="Status" data-bn="অবস্থা">Status</th>
                            <th class="text-end" data-en="Action" data-bn="কার্যক্রম">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($circulars as $circular)
                            <tr wire:key="circular-{{ $circular->id }}">
                                <td>
                                    <a href="{{ route('ministry.circulars.show', $circular) }}"
                                       class="fw-semibold text-dark text-decoration-none" style="font-size:13px;">
                                        {{ $circular->title }}
                                    </a>
                                </td>
                                <td class="text-secondary" style="font-size:12px;">
                                    @if($circular->audience === 'institution')
                                        {{ $circular->institution->name ?? '—' }}
                                    @elseif($circular->audience === 'division')
                                        {{ ucfirst($circular->division) }} <span data-en="Division" data-bn="বিভাগ">Division</span>
                                    @elseif($circular->audience === 'district')
                                        {{ $circular->district }} <span data-en="District" data-bn="জেলা">District</span>
                                    @else
                                        <span data-en="All Institutions" data-bn="সকল প্রতিষ্ঠান">All Institutions</span>
                                    @endif
                                </td>
                                <td class="text-secondary" style="font-size:12px;">
                                    {{ $circular->creator->name ?? '—' }}
                                </td>
                                <td class="text-secondary" style="font-size:12px;">
                                    {{ $circular->published_at?->format('d M Y') ?? '—' }}
                                </td>
                                <td>
                                    @if($circular->status === 'active')
                                        <span class="inv-badge paid" data-en="Active" data-bn="সক্রিয়">Active</span>
                                    @else
                                        <span class="inv-badge unpaid" data-en="Inactive" data-bn="নিষ্ক্রিয়">Inactive</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('ministry.circulars.show', $circular) }}" class="icon-btn-sm primary" title="View">
                                        <span class="material-icons-round" style="font-size:16px;">visibility</span>
                                    </a>
                                    <button type="button"
                                            wire:click="delete({{ $circular->id }})"
                                            wire:confirm="Are you sure you want to delete this circular?"
                                            class="icon-btn-sm danger" title="Delete">
                                        <span class="material-icons-round" style="font-size:16px;">delete</span>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-secondary py-4" style="font-size:13px;" data-en="No circulars published yet" data-bn="এখনো কোনো পরিপত্র প্রকাশিত হয়নি">
                                    No circulars published yet
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($circulars->hasPages())
                <div class="px-3 py-3 border-top">{{ $circulars->links() }}</div>
            @endif
        </div>
    </div>

</div>

@push('styles')
<style>
    .notice-wrap { background: var(--body-bg); min-height: 100vh; }
    .act-btn {
        display: inline-flex; align-items: center; gap: 6px;
        background: var(--primary); color: #fff; border: none;
        border-radius: 8px; padding: 7px 14px; font-size: 13px;
        font-weight: 500; text-decoration: none;
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
    .inv-badge {
        display: inline-block; padding: 3px 10px; border-radius: 4px;
        font-size: 11px; font-weight: 600; border: 1px solid transparent;
    }
    .inv-badge.paid   { background: transparent; border-color: #22c55e; color: #22c55e; }
    .inv-badge.unpaid { background: transparent; border-color: #ef4444; color: #ef4444; }
    .icon-btn-sm {
        display: inline-flex; align-items: center; justify-content: center;
        width: 30px; height: 30px; border-radius: 8px; border: 1px solid var(--border);
        background: var(--card); color: var(--val); cursor: pointer; transition: var(--transition);
    }
    .icon-btn-sm:hover   { background: var(--section-bg); }
    .icon-btn-sm.primary { color: var(--primary); border-color: var(--primary); }
    .icon-btn-sm.success { color: #22c55e; border-color: #22c55e; }
    .icon-btn-sm.danger  { color: #ef4444; border-color: #ef4444; }
</style>
@endpush