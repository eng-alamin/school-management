<div class="mat-card" style="padding-top:28px">

    <!-- floating header -->
    <div class="mat-card-header header-pink-gradient">
        <h5 id="cardHeaderTitleAllschedules">Teacher Schedule</h5>
        <p id="cardHeaderSubtitle">View weekly class schedule assigned to a specific teacher.</p>
    </div>

    <div class="row g-4 p-5">

        {{-- Teacher Select --}}
        <div class="col-md-8 offset-md-2">
            <div class="input-group input-group-outline">
                <label class="form-label">Teacher <span class="req">*</span></label>
                <select wire:model.live="teacher_id" class="form-select">
                    <option value="">Select Teacher</option>
                    @foreach ($teachers as $t)
                        <option value="{{ $t->id }}">{{ $t->name }}</option>
                    @endforeach
                </select>
            </div>
            @error('teacher_id') <span class="text-danger small">{{ $message }}</span> @enderror
        </div>

        <div class="col-md-8 offset-md-2 text-center">
            <button wire:click="filter" class="btn-pink w-100 d-flex justify-content-center align-items-center" type="button">
                <span wire:loading wire:target="filter" class="spinner-border spinner-border-sm me-2"></span>
                View Schedule
            </button>
        </div>

    </div>

    @if($hasSchedule)
    <div id="sched-grid-wrap">
        <table id="sched-grid" role="grid">
            <thead>
                <tr class="sched-thead-row">
                    <th scope="col">
                        <div class="sched-th-in sched-th-time-hdr">
                            <span class="sched-th-day">Period</span>
                        </div>
                    </th>
                    @foreach($days as $day)
                    <th scope="col">
                        <div class="sched-th-in">
                            <span class="sched-th-day">{{ $day }}</span>
                        </div>
                    </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse($scheduleGrid as $periodIndex => $row)
                <tr>
                    {{-- Period column --}}
                    <td class="sched-td-per">
                        <div class="sched-per-inner">
                            <span class="sched-per-num">{{ $periodIndex + 1 }}</span>
                            @if($row['start_time'] && $row['end_time'])
                            <span class="sched-per-time">
                                {{ \Carbon\Carbon::createFromFormat('H:i', $row['start_time'])->format('g:i A') }}
                                –
                                {{ \Carbon\Carbon::createFromFormat('H:i', $row['end_time'])->format('g:i A') }}
                            </span>
                            @endif
                        </div>
                    </td>

                    {{-- Each day cell --}}
                    @foreach($days as $day)
                    @php $item = $row[$day] ?? null; @endphp
                    <td class="sched-td-cell {{ $item ? 'sched-c--science' : 'sched-c--empty' }}">
                        <div class="sched-cell-in">
                            @if($item)
                                <div>
                                    <span class="sched-subj-name">{{ $item['subject'] }}</span>
                                </div>
                                <div>
                                    <span class="sched-tchr-name">
                                        {{ $item['class'] }}
                                        @if(!empty($item['section']))
                                            · {{ $item['section'] }}
                                        @endif
                                    </span>
                                    @if(!empty($item['class_room']))
                                        <span class="sched-room-tag">{{ $item['class_room'] }}</span>
                                    @endif
                                </div>
                            @else
                                <span style="color:var(--ink-faint);font-size:.7rem">—</span>
                            @endif
                        </div>
                    </td>
                    @endforeach
                </tr>
                @empty
                <tr>
                    <td colspan="{{ count($days) + 1 }}" class="text-center p-4" style="color:var(--ink-faint)">
                        No schedule found for this teacher.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @endif

</div>

@push('styles')
    <style>
        html{scroll-behavior:smooth}
        :root{
        --ink:#0a0a0a;--ink-soft:#2a2a2a;--ink-muted:#6a6a6a;--ink-faint:#b0b0b0;
        --paper:#fafaf8;--paper-off:#f0f0ee;--rule:#e0e0de;--rule-dark:#1a1a1a;
        --shadow-lg:0 12px 40px rgba(0,0,0,0.14),0 4px 12px rgba(0,0,0,0.08);
        --font-d:'Playfair Display',Georgia,serif;
        --font-m:'DM Mono','Courier New',monospace;
        --font-b:'Instrument Sans',sans-serif;
        --r:4px;--rlg:14px;
        }

        /* GRID */
        #sched-grid-wrap{animation:sched-up .45s .08s ease both;opacity:0;animation-fill-mode:forwards;overflow-x:auto;padding: 0 20px 28px;}
        #sched-grid{width:100%;min-width:680px;border-collapse:collapse;border:1px solid var(--rule-dark);border-radius:var(--rlg);overflow:hidden;box-shadow:var(--shadow-lg)}

        .sched-thead-row th{background:var(--ink);color:#fff;padding:0;border:none}
        .sched-th-in{padding:13px 15px;border-right:1px solid rgba(255,255,255,.08)}
        .sched-th-day{font-family:var(--font-d);font-size:.95rem;font-weight:700;letter-spacing:-.01em;display:block;margin-bottom:2px}
        .sched-th-time-hdr{background:var(--ink-soft)}

        .sched-td-per{background:var(--paper-off);border-right:1px solid var(--rule);border-bottom:1px solid var(--rule);width:80px;min-width:70px;text-align:center;padding:0;vertical-align:middle}
        .sched-per-inner{padding:11px 7px}
        .sched-per-num{font-family:var(--font-d);font-size:1.2rem;font-weight:900;color:var(--ink-faint);line-height:1;display:block}
        .sched-per-time{font-family:var(--font-m);font-size:.48rem;color:var(--ink-faint);letter-spacing:.04em;display:block;margin-top:4px}

        /* Subject cells */
        .sched-td-cell{border-right:1px solid var(--rule);border-bottom:1px solid var(--rule);vertical-align:top;padding:0}
        .sched-td-cell:last-child{border-right:none}
        .sched-cell-in{padding:10px 13px;min-height:78px;display:flex;flex-direction:column;justify-content:space-between;gap:4px;position:relative;transition:background .13s}
        .sched-cell-in:hover{background:rgba(0,0,0,.02)}
        .sched-cell-in::before{content:'';position:absolute;top:9px;left:0;width:3px;height:calc(100% - 18px);border-radius:0 2px 2px 0}
        .sched-c--science .sched-cell-in::before{background:#0a0a0a}
        .sched-c--empty   .sched-cell-in::before{display:none}

        .sched-subj-name{font-family:var(--font-d);font-size:.82rem;font-weight:700;color:var(--ink);line-height:1.2;letter-spacing:-.01em}
        .sched-tchr-name{font-family:var(--font-b);font-size:.64rem;font-weight:500;color:var(--ink-muted);margin-top:5px;display:flex;align-items:center;gap:5px}
        .sched-tchr-name::before{content:'';width:9px;height:1px;background:var(--ink-faint);display:inline-block;flex-shrink:0}
        .sched-room-tag{font-family:var(--font-m);font-size:.48rem;font-weight:500;padding:2px 6px;border:1px solid var(--rule);border-radius:3px;color:var(--ink-faint);letter-spacing:.05em;display:inline-block;margin-top:3px;align-self:flex-start}

        @keyframes sched-up{from{opacity:0;transform:translateY(14px)}to{opacity:1;transform:translateY(0)}}
    </style>
@endpush

@push('scripts')
<script>
    document.addEventListener('livewire:initialized', () => {
        Livewire.hook('morph.updated', ({ el }) => {
            setTimeout(() => {
                el.querySelectorAll('.input-group-outline .form-select').forEach(function(select) {
                    if (!select.nextElementSibling || !select.nextElementSibling.classList.contains('custom-select-wrapper')) {
                        if (typeof buildCustomSelect === 'function') buildCustomSelect(select);
                    }
                });
            }, 0);
        });
    });
</script>
@endpush