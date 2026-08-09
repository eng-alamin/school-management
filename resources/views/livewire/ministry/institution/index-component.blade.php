{{-- resources/views/livewire/ministry/institution/index-component.blade.php --}}

<div class="inst-wrap">

    {{-- ══ Header ══════════════════════════════════════════════════════════ --}}
    <div class="px-3 pt-3 pb-2">
        <h5 class="fw-bold mb-0 text-dark" data-en="Institution Directory" data-bn="প্রতিষ্ঠান তালিকা">Institution Directory</h5>
        <p class="text-secondary mb-0" style="font-size:12px;" data-en="All registered institutions nationwide" data-bn="দেশব্যাপী নিবন্ধিত সকল প্রতিষ্ঠান">All registered institutions nationwide</p>
    </div>

    {{-- ══ Division Quick Filter Pills ═════════════════════════════════════ --}}
    <div class="px-3 pt-2">
        <div class="inst-pill-row">
            @foreach($this->divisions as $key => $label)
                <button
                    type="button"
                    wire:click="filterByDivision('{{ $key }}')"
                    class="inst-pill {{ $division === $key ? 'active' : '' }}"
                >
                    {{ $label }}
                    <span class="inst-pill-count">{{ $this->divisionCounts[$key] ?? 0 }}</span>
                </button>
            @endforeach
        </div>
    </div>

    {{-- ══ Filters ═════════════════════════════════════════════════════════ --}}
    <div class="px-3 pt-3">
        <div class="inst-filter-card">
            <div class="row g-2 align-items-end">

                <div class="col-12 col-md-3">
                    <label class="inst-filter-label" data-en="Search" data-bn="খুঁজুন">Search</label>
                    <input type="text" wire:model.live.debounce.400ms="search"
                           class="form-control form-control-sm"
                           data-en-ph="Name, EIIN, email..." data-bn-ph="নাম, ইআইআইএন, ইমেইল..."
                           placeholder="Name, EIIN, email...">
                </div>

                <div class="col-6 col-md-2">
                    <label class="inst-filter-label" data-en="Division" data-bn="বিভাগ">Division</label>
                    <select wire:model.live="division" class="form-select form-select-sm">
                        <option value="" data-en="All" data-bn="সকল">All</option>
                        @foreach($this->divisions as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-6 col-md-2">
                    <label class="inst-filter-label" data-en="District" data-bn="জেলা">District</label>
                    <select wire:model.live="district" class="form-select form-select-sm">
                        <option value="" data-en="All" data-bn="সকল">All</option>
                        @foreach($this->districts as $d)
                            <option value="{{ $d }}">{{ $d }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-6 col-md-2">
                    <label class="inst-filter-label" data-en="Type" data-bn="ধরন">Type</label>
                    <select wire:model.live="type" class="form-select form-select-sm">
                        <option value="" data-en="All" data-bn="সকল">All</option>
                        @foreach($this->types as $t)
                            <option value="{{ $t }}">{{ ucfirst($t) }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-6 col-md-2">
                    <label class="inst-filter-label" data-en="Medium" data-bn="মাধ্যম">Medium</label>
                    <select wire:model.live="medium" class="form-select form-select-sm">
                        <option value="" data-en="All" data-bn="সকল">All</option>
                        @foreach($this->mediums as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-6 col-md-1">
                    <label class="inst-filter-label" data-en="Status" data-bn="অবস্থা">Status</label>
                    <select wire:model.live="status" class="form-select form-select-sm">
                        <option value="" data-en="All" data-bn="সকল">All</option>
                        <option value="1" data-en="Active" data-bn="সক্রিয়">Active</option>
                        <option value="0" data-en="Inactive" data-bn="নিষ্ক্রিয়">Inactive</option>
                    </select>
                </div>

                <div class="col-6 col-md-2">
                    <label class="inst-filter-label" data-en="Verification" data-bn="যাচাই">Verification</label>
                    <select wire:model.live="verificationStatus" class="form-select form-select-sm">
                        <option value="" data-en="All" data-bn="সকল">All</option>
                        @foreach($this->verificationStatuses as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

            </div>

            @if($search || $division || $district || $type || $medium || $status !== '' || $verificationStatus)
                <div class="mt-2">
                    <button type="button" wire:click="resetFilters" class="inst-reset-btn">
                        <span class="material-icons-round" style="font-size:14px;vertical-align:middle;">close</span>
                        <span data-en="Reset filters" data-bn="ফিল্টার রিসেট করুন">Reset filters</span>
                    </button>
                </div>
            @endif
        </div>
    </div>

    {{-- ══ Table ═══════════════════════════════════════════════════════════ --}}
    <div class="px-3 pt-3 pb-4">
        <div class="inst-table-card">

            <div class="table-responsive">
                <table class="table inst-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th data-en="Institution" data-bn="প্রতিষ্ঠান">Institution</th>
                            <th data-en="EIIN" data-bn="ইআইআইএন">EIIN</th>
                            <th data-en="Type" data-bn="ধরন">Type</th>
                            <th data-en="Medium" data-bn="মাধ্যম">Medium</th>
                            <th data-en="Location" data-bn="অবস্থান">Location</th>
                            <th data-en="Contact" data-bn="যোগাযোগ">Contact</th>
                            <th data-en="Status" data-bn="অবস্থা">Status</th>
                            <th data-en="Verification" data-bn="যাচাই">Verification</th>
                            <th data-en="Registered" data-bn="নিবন্ধিত">Registered</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($institutions as $institution)
                            <tr wire:key="inst-{{ $institution->id }}">
                                <td>
                                    <a href="{{ route('ministry.institutions.show', $institution) }}" class="d-flex align-items-center gap-2 text-decoration-none">
                                        <div class="inst-avatar">
                                            {{ strtoupper(substr($institution->name, 0, 1)) }}
                                        </div>
                                        <span class="fw-semibold text-dark" style="font-size:13px;">
                                            {{ $institution->name }}
                                        </span>
                                    </a>
                                </td>
                                <td class="text-secondary" style="font-size:12px;">
                                    {{ $institution->eiin ?? '—' }}
                                </td>
                                <td class="text-secondary" style="font-size:12px;">
                                    {{ $institution->type ? ucfirst($institution->type) : '—' }}
                                </td>
                                <td class="text-secondary" style="font-size:12px;">
                                    {{ $institution->mediumLabel() }}
                                </td>
                                <td class="text-secondary" style="font-size:12px;">
                                    {{ collect([
                                        $institution->district,
                                        $this->divisions[$institution->division] ?? $institution->division,
                                    ])->filter()->implode(', ') ?: '—' }}
                                </td>
                                <td class="text-secondary" style="font-size:12px;">
                                    {{ $institution->phone ?? $institution->email ?? '—' }}
                                </td>
                                <td>
                                    @if($institution->status)
                                        <span class="inv-badge paid" data-en="Active" data-bn="সক্রিয়">Active</span>
                                    @else
                                        <span class="inv-badge unpaid" data-en="Inactive" data-bn="নিষ্ক্রিয়">Inactive</span>
                                    @endif
                                </td>
                                <td>
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
                                </td>
                                <td class="text-secondary" style="font-size:12px;">
                                    {{ $institution->created_at->format('d M Y') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-secondary py-4" style="font-size:13px;" data-en="No institutions match the selected filters" data-bn="নির্বাচিত ফিল্টারের সাথে কোনো প্রতিষ্ঠান মিলেনি">
                                    No institutions match the selected filters
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($institutions->hasPages())
                <div class="px-3 py-3 border-top">
                    {{ $institutions->links() }}
                </div>
            @endif

        </div>
    </div>

</div>

@push('styles')
<style>
    .inst-wrap { background: var(--body-bg); min-height: 100vh; }

    .inst-pill-row {
        display: flex; flex-wrap: wrap; gap: 8px;
    }
    .inst-pill {
        display: inline-flex; align-items: center; gap: 6px;
        background: var(--card); border: 1px solid var(--border);
        border-radius: 999px; padding: 6px 12px;
        font-size: 12px; font-weight: 500; color: var(--val);
        cursor: pointer;
    }
    .inst-pill.active {
        background: var(--primary); border-color: var(--primary); color: #fff;
    }
    .inst-pill-count {
        background: rgba(0,0,0,0.06); border-radius: 999px;
        padding: 1px 7px; font-size: 11px; font-weight: 600;
    }
    .inst-pill.active .inst-pill-count { background: rgba(255,255,255,0.25); }

    .inst-filter-card {
        background: var(--card); border: 1px solid var(--border);
        border-radius: var(--radius-card); padding: 14px;
        box-shadow: var(--shadow);
    }
    .inst-filter-label {
        font-size: 11px; color: var(--lbl); margin-bottom: 2px; display: block;
    }
    .inst-reset-btn {
        background: transparent; border: none; color: #ef4444;
        font-size: 12px; font-weight: 500; padding: 0;
    }

    .inst-table-card {
        background: var(--card); border: 1px solid var(--border);
        border-radius: var(--radius-card); box-shadow: var(--shadow);
        overflow: hidden;
    }
    .inst-table thead th {
        font-size: 11px; text-transform: uppercase; letter-spacing: .03em;
        color: var(--lbl); border-bottom: 1px solid var(--border);
        padding: 10px 14px; white-space: nowrap;
    }
    .inst-table tbody td {
        padding: 10px 14px; border-bottom: 1px solid var(--border);
    }
    .inst-table tbody tr:last-child td { border-bottom: none; }

    .inst-avatar {
        width: 30px; height: 30px; border-radius: 8px;
        background: linear-gradient(135deg, var(--primary), #7ba3ff);
        color: #fff; font-size: 12px; font-weight: 700;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }

    .inv-badge {
        display: inline-block; padding: 3px 10px; border-radius: 4px;
        font-size: 11px; font-weight: 600; border: 1px solid transparent;
    }
    .inv-badge.paid    { background: transparent; border-color: #22c55e; color: #22c55e; }
    .inv-badge.unpaid  { background: transparent; border-color: #ef4444; color: #ef4444; }
    .inv-badge.pending { background: transparent; border-color: #d97706; color: #d97706; }
</style>
@endpush