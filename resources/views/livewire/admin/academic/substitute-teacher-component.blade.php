<div>
    <div class="card">

        {{-- Floating Header --}}
        <div class="mat-card-header header-primary-gradient">
            <h5>Substitute Teacher Assignment</h5>
            <p>Find absent teacher periods and assign substitutes</p>
        </div>

        <div class="row g-4 p-5">
            <div class="col-md-8 offset-md-2">
                 <div class="input-group input-group-outline" wire:ignore>
                    <label class="form-label">Date</label>
                    <input wire:model="date" type="date" class="form-control" data-dp-value="{{ $date }}"
                        onfocus="focused(this)" onfocusout="defocused(this)">
                </div>
                @error('date') <span class="text-danger small">{{ $message }}</span> @enderror
            </div>

            {{-- Filter Button --}}
            <div class="col-md-8 offset-md-2 text-center">
                <button wire:click="loadPeriods"
                        wire:loading.attr="disabled"
                        wire:target="loadPeriods"
                        class="btn btn-primary w-100 d-flex justify-content-center align-items-center"
                        type="button">
                    <span wire:loading.remove wire:target="loadPeriods">
                        <span class="material-icons-round" style="font-size:16px;vertical-align:middle;margin-right:4px">search</span> Find absent teacher periods
                    </span>
                    <span wire:loading wire:target="loadPeriods">
                        <span class="material-icons-round" style="font-size:16px;animation:spin .7s linear infinite">sync</span> Finding...
                    </span>
                </button>
            </div>

        </div>

        {{-- Periods List --}}
        @if($loaded)
        <div class="form-section">
            <div class="section-heading d-flex align-items-center justify-content-between flex-wrap gap-2">
                <span>Absent Teacher Periods</span>
                @if(!empty($periods))
                    <span class="att-count-badge">{{ count($periods) }} period(s)</span>
                @endif
            </div>

            @if(empty($periods))
                <p class="text-muted mt-3 mb-0">Selected date-e kono teacher absent nei, ba oi din tar kono class routine nei.</p>
            @else

                {{-- ══ DESKTOP/TABLET: Table view (hidden on mobile) ══ --}}
                <div class="table-responsive mt-3 att-table-wrap d-none d-md-block">
                    <table class="table table-hover mb-0 att-table">
                        <thead>
                            <tr>
                                <th>Class</th>
                                <th>Time</th>
                                <th>Subject</th>
                                <th>Original Teacher</th>
                                <th>Substitute Teacher</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($periods as $key => $row)
                            <tr wire:key="sub-row-{{ $key }}">
                                <td><span class="att-section-pill">{{ $row['class_name'] }} {{ $row['section_name'] }}</span></td>
                                <td class="text-muted fw-500">{{ $row['start_time'] }} - {{ $row['end_time'] }}</td>
                                <td>{{ $row['subject_name'] }}</td>
                                <td>{{ $row['teacher_name'] }}</td>
                                <td style="min-width:200px;">
                                    @if($row['existing'] && $row['existing']->status !== 'cancelled')
                                        <span class="fw-600" style="font-size:.85rem">{{ $row['substitute_name'] }}</span>
                                    @else
                                        <select class="form-select form-select-sm" wire:model="selected.{{ $key }}">
                                            <option value="">-- Select --</option>
                                            @foreach($suggestions[$key] ?? [] as $t)
                                                <option value="{{ $t['id'] }}">{{ $t['name'] }}</option>
                                            @endforeach
                                        </select>
                                        @if(empty($suggestions[$key]))
                                            <div class="text-danger small mt-1">Ei somoy free teacher paoya jayni.</div>
                                        @endif
                                    @endif
                                </td>
                                <td>
                                    @if($row['existing'])
                                        <span class="status-pill status-pill--{{ $row['existing']->status === 'cancelled' ? 'cancelled' : 'assigned' }}">
                                            {{ ucfirst($row['existing']->status) }}
                                        </span>
                                    @else
                                        <span class="status-pill status-pill--pending">Pending</span>
                                    @endif
                                </td>
                                <td>
                                    @if($row['existing'] && $row['existing']->status !== 'cancelled')
                                        <button type="button" class="row-action-btn row-action-btn--danger" wire:click="cancel('{{ $key }}')">
                                            <span class="material-icons-round" style="font-size:15px">close</span> Cancel
                                        </button>
                                    @else
                                        <button type="button" class="row-action-btn row-action-btn--success" wire:click="assign('{{ $key }}')">
                                            <span class="material-icons-round" style="font-size:15px">check</span> Assign
                                        </button>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- ══ MOBILE: Card view (hidden on desktop/tablet) ══ --}}
                <div class="att-mobile-list d-md-none mt-3">
                    @foreach($periods as $key => $row)
                    <div class="att-mcard" wire:key="sub-card-{{ $key }}">
                        <div class="att-mcard-top">
                            <div class="att-mcard-info">
                                <span class="fw-600 d-block" style="font-size:.85rem">
                                    {{ $row['class_name'] }} {{ $row['section_name'] }} — {{ $row['subject_name'] }}
                                </span>
                                <div class="d-flex align-items-center gap-1 flex-wrap mt-1">
                                    <span class="att-section-pill">{{ $row['start_time'] }} - {{ $row['end_time'] }}</span>
                                    <span class="att-roll-pill">{{ $row['teacher_name'] }}</span>
                                </div>
                            </div>
                            <div>
                                @if($row['existing'])
                                    <span class="status-pill status-pill--{{ $row['existing']->status === 'cancelled' ? 'cancelled' : 'assigned' }}">
                                        {{ ucfirst($row['existing']->status) }}
                                    </span>
                                @else
                                    <span class="status-pill status-pill--pending">Pending</span>
                                @endif
                            </div>
                        </div>

                        <div class="mt-2">
                            @if($row['existing'] && $row['existing']->status !== 'cancelled')
                                <div class="fw-600 mb-2" style="font-size:.82rem">Substitute: {{ $row['substitute_name'] }}</div>
                                <button type="button" class="btn-outline w-100 d-flex justify-content-center align-items-center gap-1"
                                        wire:click="cancel('{{ $key }}')">
                                    <span class="material-icons-round" style="font-size:16px">close</span> Cancel
                                </button>
                            @else
                                <select class="form-select form-select-sm mb-2" wire:model="selected.{{ $key }}">
                                    <option value="">-- Select --</option>
                                    @foreach($suggestions[$key] ?? [] as $t)
                                        <option value="{{ $t['id'] }}">{{ $t['name'] }}</option>
                                    @endforeach
                                </select>
                                @if(empty($suggestions[$key]))
                                    <div class="text-danger small mb-2">Ei somoy free teacher paoya jayni.</div>
                                @endif
                                <button type="button" class="btn-primary w-100 d-flex justify-content-center align-items-center gap-1"
                                        wire:click="assign('{{ $key }}')">
                                    <span class="material-icons-round" style="font-size:16px">check</span> Assign
                                </button>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>

            @endif
        </div>
        @endif

    </div>
</div>

@push('styles')
<style>
    /* ── Attendance count badge ── */
    .att-count-badge {
        font-size: .72rem;
        font-weight: 600;
        color: #6a6a6a;
        background: #f2f2f2;
        padding: 4px 12px;
        border-radius: 20px;
        white-space: nowrap;
    }

    /* ── Table polish (desktop) ── */
    .att-table-wrap { border-radius: 10px; }
    .att-table thead th {
        font-size: .7rem;
        text-transform: uppercase;
        letter-spacing: .04em;
        color: #9a9a9a;
        font-weight: 700;
        border-bottom: 2px solid #eee;
        padding: 10px 12px;
        white-space: nowrap;
    }
    .att-table tbody td {
        padding: 12px;
        vertical-align: middle;
        border-bottom: 1px solid #f0f0f0;
    }
    .att-table tbody tr { transition: background .15s; }
    .att-table tbody tr:hover { background: #fafafa; }
    .att-table tbody tr:last-child td { border-bottom: none; }

    /* ── Section / Roll pill (re-used for class/teacher) ── */
    .att-section-pill, .att-roll-pill {
        font-size: .68rem;
        font-weight: 600;
        color: #4a4a4a;
        background: #f0f0f0;
        padding: 3px 9px;
        border-radius: 20px;
        white-space: nowrap;
    }

    /* ── Status pill (Pending / Assigned / Cancelled) ── */
    .status-pill {
        display: inline-block;
        font-size: .7rem;
        font-weight: 700;
        padding: 4px 11px;
        border-radius: 20px;
        white-space: nowrap;
    }
    .status-pill--pending   { background: #fff6e0; color: #9c6d05; }
    .status-pill--assigned  { background: #e8f8ee; color: #1a9c46; }
    .status-pill--cancelled { background: #f2f2f2; color: #6a6a6a; }

    /* ── Row action buttons (Assign / Cancel) ── */
    .row-action-btn {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: .74rem;
        font-weight: 700;
        padding: 6px 12px;
        border-radius: 20px;
        border: 1px solid transparent;
        background: #fff;
        transition: all .15s;
        white-space: nowrap;
    }
    .row-action-btn--success {
        color: #1a9c46;
        border-color: #34c759;
        background: #e8f8ee;
    }
    .row-action-btn--success:hover { background: #d7f2e0; }
    .row-action-btn--danger {
        color: #c0362f;
        border-color: #e0524f;
        background: #fdecec;
    }
    .row-action-btn--danger:hover { background: #fbdada; }

    /* ══════════ MOBILE CARD LIST ══════════ */
    .att-mobile-list {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }
    .att-mcard {
        border: 1px solid #eee;
        border-radius: 12px;
        padding: 12px;
        background: #fff;
        box-shadow: 0 1px 4px rgba(0,0,0,.04);
    }
    .att-mcard-top {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 10px;
        margin-bottom: 6px;
    }
    .att-mcard-info { min-width: 0; flex: 1; }

    /* ══════════ RESPONSIVE TWEAKS ══════════ */
    @media (max-width: 767.98px) {
        .form-section { padding-left: 14px !important; padding-right: 14px !important; }
        .section-heading { font-size: .85rem; }
        .att-count-badge { font-size: .68rem; padding: 3px 10px; }
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('livewire:initialized', () => {
        Livewire.hook('morph.updated', ({ el }) => {
            setTimeout(() => {
                el.querySelectorAll('.input-group-outline .form-select').forEach(function(select) {
                    if (!select.nextElementSibling || !select.nextElementSibling.classList.contains('custom-select-wrapper')) {
                        buildCustomSelect(select);
                    }
                });

                el.querySelectorAll('.input-group-outline input').forEach(function(input) {
                    var group = input.closest('.input-group');
                    if (!group) return;
                    if (input.value && input.value.trim() !== '') {
                        group.classList.add('is-filled');
                    } else {
                        group.classList.remove('is-filled');
                    }
                    if (input._materialInit) return;
                    input._materialInit = true;
                    input.addEventListener('focus', function() { group.classList.add('is-focused'); });
                    input.addEventListener('blur', function() {
                        group.classList.remove('is-focused');
                        group.classList.toggle('is-filled', !!input.value.trim());
                    });
                    input.addEventListener('input', function() {
                        group.classList.toggle('is-filled', !!input.value.trim());
                    });
                });

                el.querySelectorAll('.input-group-outline input[type="date"]').forEach(function(input) {
                    if (!input._dpInit) { _initDatepickers(); }
                });
            }, 0);
        });
    });
</script>
@endpush