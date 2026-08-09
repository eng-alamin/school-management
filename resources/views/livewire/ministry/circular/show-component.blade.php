{{-- resources/views/livewire/ministry/circular/show-component.blade.php --}}

<div class="notice-show-wrap">

    <div class="px-3 pt-3">
        <a href="{{ route('ministry.circulars.index') }}" class="inst-back-link">
            <span class="material-icons-round" style="font-size:16px;vertical-align:middle;">arrow_back</span>
            <span data-en="Back to Circulars" data-bn="পরিপত্রে ফিরে যান">Back to Circulars</span>
        </a>
    </div>

    <div class="px-3 pt-3">
        <div class="dash-section-card">
            <h5 class="fw-bold text-dark mb-1">{{ $circular->title }}</h5>
            <p class="text-secondary mb-3" style="font-size:12px;">
                <span data-en="Published by" data-bn="প্রকাশক">Published by</span> {{ $circular->creator->name ?? '—' }}
                <span data-en="on" data-bn="তারিখ">on</span> {{ $circular->published_at?->format('d M Y') ?? '—' }}
                @if($circular->expires_at)
                    · <span data-en="Expires" data-bn="মেয়াদ শেষ">Expires</span> {{ $circular->expires_at->format('d M Y') }}
                @endif
            </p>
            <p class="text-dark" style="font-size:14px;white-space:pre-line;">{{ $circular->description }}</p>

            @if($circular->attachment)
                <a href="{{ Storage::url($circular->attachment) }}" target="_blank" class="act-btn mt-2">
                    <span class="material-icons-round" style="font-size:16px;vertical-align:middle;">attach_file</span>
                    {{ $circular->attachment_name ?? 'View Attachment' }}
                </a>
            @endif
        </div>
    </div>

    <div class="px-3 pt-3 pb-4">
        <div class="inst-table-card">
            <div class="dash-section-title px-3 pt-3">
                <span data-en="Read Status by Institution" data-bn="প্রতিষ্ঠান অনুযায়ী পঠন অবস্থা">Read Status by Institution</span>
            </div>

            <div class="table-responsive">
                <table class="table inst-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th data-en="Institution" data-bn="প্রতিষ্ঠান">Institution</th>
                            <th data-en="Status" data-bn="অবস্থা">Status</th>
                            <th data-en="Read At" data-bn="পঠনের সময়">Read At</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reads as $read)
                            <tr wire:key="read-{{ $read->id }}">
                                <td class="fw-semibold text-dark" style="font-size:13px;">
                                    {{ $read->institution->name ?? '—' }}
                                </td>
                                <td>
                                    @if($read->read_at)
                                        <span class="inv-badge paid" data-en="Read" data-bn="পঠিত">Read</span>
                                    @else
                                        <span class="inv-badge unpaid" data-en="Unread" data-bn="অপঠিত">Unread</span>
                                    @endif
                                </td>
                                <td class="text-secondary" style="font-size:12px;">
                                    {{ $read->read_at?->format('d M Y, h:i A') ?? '—' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center text-secondary py-4" style="font-size:13px;" data-en="No target institutions found" data-bn="কোনো লক্ষ্য প্রতিষ্ঠান পাওয়া যায়নি">
                                    No target institutions found
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($reads->hasPages())
                <div class="px-3 py-3 border-top">{{ $reads->links() }}</div>
            @endif
        </div>
    </div>

</div>

@push('styles')
<style>
    .notice-show-wrap { background: var(--body-bg); min-height: 100vh; }
    .inst-back-link {
        font-size: 12px; color: var(--lbl); text-decoration: none;
        display: inline-flex; align-items: center; gap: 4px;
    }
    .dash-section-card {
        background: var(--card); border: 1px solid var(--border);
        border-radius: var(--radius-card); padding: 18px; box-shadow: var(--shadow);
    }
    .dash-section-title {
        font-size: 14px; font-weight: 600; color: var(--val);
        display: flex; align-items: center; gap: 6px; margin-bottom: 12px;
    }
    .act-btn {
        display: inline-flex; align-items: center; gap: 4px;
        background: var(--primary); color: #fff; border: none;
        border-radius: 8px; padding: 7px 14px; font-size: 13px;
        font-weight: 500; text-decoration: none;
    }
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
</style>
@endpush