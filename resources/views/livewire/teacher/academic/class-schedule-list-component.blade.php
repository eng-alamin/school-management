<div class="mat-card" style="padding-top:28px">

      <!-- floating header -->
      <div class="mat-card-header header-pink-gradient">
        <h5 id="cardHeaderTitleAllschedules">All Schedules</h5>
        <p id="cardHeaderSubtitle">A lightweight, extendable, dependency-free javascript HTML table plugin.</p>
      </div>

      <div class="row g-4 p-5">
        <div class="col-md-6">
            <div class="input-group input-group-outline">
                <label class="form-label">Class <span class="req">*</span></label>
                <select wire:model.live="class_id" class="form-select">
                    <option value="">Select Class</option>
                    @foreach ($classes as $c)
                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            @error('class_id') <span class="text-danger">{{ $message }}</span> @enderror
        </div>

        <div class="col-md-6">
            <div class="input-group input-group-outline">
                <label class="form-label">Section <span class="req">*</span></label>
                <select wire:model="section_id" class="form-select">
                    <option value="">{{ empty($availableSections) ? 'Select class first' : 'Select Section' }}</option>
                    @foreach ($availableSections as $s)
                        <option value="{{ $s['id'] }}">{{ $s['name'] }}</option>
                    @endforeach
                </select>
            </div>
            @error('section_id') <span class="text-danger">{{ $message }}</span> @enderror
        </div>
        <div class="col-md-12 text-center">
            <button wire:click="filter" class="btn-pink w-100 d-flex justify-content-center align-items-center" type="button" onclick="handleSave(this)">
                Filter
            </button>
        </div>
        <div class="col-md-12 text-center">
            <a href="{{ route('teacher.academic.class-schedule.create') }}" class="btn-pink w-100 d-flex justify-content-center align-items-center">
                <span class="material-icons-round" style="font-size:16px">add</span><span>New Schedule</span>
            </a>
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
                                @php
                                    // Start time Sunday বা যেকোনো available day থেকে নাও
                                    $anyItem = collect($row)->first(fn($i) => $i !== null);
                                @endphp
                                @if($anyItem)
                                <span class="sched-per-time">
                                    {{ \Carbon\Carbon::createFromFormat('H:i', $anyItem['start_time'])->format('g:i A') }}
                                    –
                                    {{ \Carbon\Carbon::createFromFormat('H:i', $anyItem['end_time'])->format('g:i A') }}
                                </span>
                                @endif
                            </div>
                        </td>

                        {{-- প্রতিটা day এর cell --}}
                        @foreach($days as $day)
                        @php $item = $row[$day] ?? null; @endphp
                        <td class="sched-td-cell {{ $item ? 'sched-c--science' : 'sched-c--empty' }}">
                            <div class="sched-cell-in">
                                @if($item)
                                    <div>
                                        <span class="sched-subj-name">{{ $item['subject'] }}</span>
                                    </div>
                                    <div>
                                        <span class="sched-tchr-name">{{ $item['teacher'] }}</span>
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
                            No schedule found
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @endif




    <!-- ═══════ DELETE CONFIRM MODAL ═══════ -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content text-center p-3">
            <div style="width:52px;height:52px;border-radius:50%;background:var(--pink-light);display:flex;align-items:center;justify-content:center;margin:12px auto">
                <span class="material-icons-round" style="color:var(--pink);font-size:26px">delete_outline</span>
            </div>
            <h6 style="font-weight:700;margin:8px 0 4px">Delete this category?</h6>
            <p style="font-size:.78rem;color:var(--muted);margin-bottom:16px" id="deleteName">This action cannot be undone.</p>
            <div style="display:flex;gap:8px;justify-content:center">
                <button class="btn-outline" data-bs-dismiss="modal">Cancel</button>
                <button class="btn-pink" onclick="confirmDelete()">Delete</button>
            </div>
            </div>
        </div>
    </div>

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
            body{font-family:var(--font-b);background:var(--paper);color:var(--ink);min-height:100vh;overflow-x:hidden}
            body::after{content:'';position:fixed;inset:0;z-index:9999;pointer-events:none;opacity:.022;
            background-image:url("data:image/svg+xml,%3Csvg viewBox='0 0 512 512' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='g'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.75' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23g)'/%3E%3C/svg%3E")}

            /* HEADER */
            #sched-header{background:var(--ink);color:var(--paper);padding:0 48px;position:relative;overflow:hidden;animation:sched-up .5s ease both}
            #sched-hdr-deco{position:absolute;top:-80px;right:-80px;width:320px;height:320px;border:1px solid rgba(255,255,255,.05);border-radius:50%;pointer-events:none}
            #sched-hdr-deco::after{content:'';position:absolute;inset:40px;border:1px solid rgba(255,255,255,.03);border-radius:50%}
            #sched-hdr-inner{max-width:1440px;margin:0 auto;display:flex;align-items:flex-end;justify-content:space-between;padding:34px 0 26px;gap:24px;flex-wrap:wrap;border-bottom:1px solid rgba(255,255,255,.09)}
            #sched-eyebrow{font-family:var(--font-m);font-size:.63rem;font-weight:400;letter-spacing:.18em;text-transform:uppercase;color:rgba(255,255,255,.38);margin-bottom:9px;display:flex;align-items:center;gap:10px}
            #sched-eyebrow::before{content:'';width:22px;height:1px;background:rgba(255,255,255,.28);display:inline-block}
            #sched-main-title{font-family:var(--font-d);font-size:clamp(1.8rem,3.5vw,3rem);font-weight:900;line-height:1;letter-spacing:-.02em;color:#fff}
            #sched-main-title em{font-style:italic;font-weight:400;color:rgba(255,255,255,.48)}
            .sched-hdr-right{display:flex;flex-direction:column;align-items:flex-end;gap:8px}
            #sched-school-name{font-family:var(--font-b);font-size:.76rem;font-weight:600;color:rgba(255,255,255,.42);letter-spacing:.02em}
            #sched-session-pill{font-family:var(--font-m);font-size:.66rem;font-weight:500;padding:5px 15px;border:1px solid rgba(255,255,255,.17);border-radius:99px;color:rgba(255,255,255,.62);letter-spacing:.08em}

            /* FILTER BAR — two rows */
            #sched-filter-bar{background:var(--ink);padding:0 48px;border-bottom:1px solid rgba(255,255,255,.06);animation:sched-up .5s .06s ease both;opacity:0;animation-fill-mode:forwards}
            #sched-filter-inner{max-width:1440px;margin:0 auto}

            /* Row 1 — class tabs */
            #sched-class-row{display:flex;align-items:center;overflow-x:auto;scrollbar-width:none;border-bottom:1px solid rgba(255,255,255,.05)}
            #sched-class-row::-webkit-scrollbar{display:none}
            .sched-cls-tab{font-family:var(--font-m);font-size:.68rem;font-weight:500;letter-spacing:.05em;padding:12px 18px;color:rgba(255,255,255,.3);cursor:pointer;border:none;background:transparent;border-bottom:2px solid transparent;transition:color .16s,border-color .16s;white-space:nowrap}
            .sched-cls-tab:hover{color:rgba(255,255,255,.7)}
            .sched-cls-tab.sched-cls-active{color:#fff;border-bottom-color:#fff}

            /* Row 2 — section pills */
            #sched-sec-row{display:flex;align-items:center;gap:8px;padding:9px 0;overflow-x:auto;scrollbar-width:none;min-height:44px}
            #sched-sec-row::-webkit-scrollbar{display:none}
            #sched-sec-label{font-family:var(--font-m);font-size:.57rem;font-weight:400;letter-spacing:.14em;text-transform:uppercase;color:rgba(255,255,255,.2);margin-right:4px;white-space:nowrap;flex-shrink:0}
            .sched-sec-pill{font-family:var(--font-m);font-size:.64rem;font-weight:500;letter-spacing:.05em;padding:5px 13px;border:1px solid rgba(255,255,255,.13);border-radius:99px;color:rgba(255,255,255,.38);background:transparent;cursor:pointer;transition:all .15s;white-space:nowrap;flex-shrink:0}
            .sched-sec-pill:hover{border-color:rgba(255,255,255,.38);color:rgba(255,255,255,.78)}
            .sched-sec-pill.sched-sec-active{background:#fff;border-color:#fff;color:var(--ink)}

            /* MAIN */
            #sched-main{max-width:1440px;margin:0 auto;padding:30px 48px 56px}
            #sched-meta-row{display:flex;align-items:center;justify-content:space-between;margin-bottom:22px;flex-wrap:wrap;gap:12px;animation:sched-up .5s ease both}
            #sched-cls-display{font-family:var(--font-d);font-size:1.35rem;font-weight:700;color:var(--ink);letter-spacing:-.01em;display:flex;align-items:baseline;gap:10px;flex-wrap:wrap}
            .sched-disp-sec{font-size:.85rem;font-weight:400;font-style:italic;color:var(--ink-muted)}
            .sched-disp-room{font-family:var(--font-m);font-size:.58rem;font-weight:500;padding:3px 9px;border:1px solid var(--rule);border-radius:3px;color:var(--ink-faint);letter-spacing:.05em;font-style:normal}

            .sched-legend{display:flex;align-items:center;gap:13px;flex-wrap:wrap}
            .sched-leg-item{display:flex;align-items:center;gap:6px;font-family:var(--font-m);font-size:.58rem;font-weight:400;color:var(--ink-muted);letter-spacing:.06em;text-transform:uppercase}
            .sched-leg-dot{width:9px;height:9px;border-radius:2px;flex-shrink:0}

            /* GRID */
            #sched-grid-wrap{animation:sched-up .45s .08s ease both;opacity:0;animation-fill-mode:forwards;overflow-x:auto}
            #sched-grid{width:100%;min-width:680px;border-collapse:collapse;border:1px solid var(--rule-dark);border-radius:var(--rlg);overflow:hidden;box-shadow:var(--shadow-lg)}

            .sched-thead-row th{background:var(--ink);color:#fff;padding:0;border:none}
            .sched-th-in{padding:13px 15px;border-right:1px solid rgba(255,255,255,.08)}
            .sched-th-day{font-family:var(--font-d);font-size:.95rem;font-weight:700;letter-spacing:-.01em;display:block;margin-bottom:2px}
            .sched-th-date{font-family:var(--font-m);font-size:.54rem;font-weight:400;color:rgba(255,255,255,.33);letter-spacing:.1em;text-transform:uppercase;display:block}
            .sched-th-time-hdr{background:var(--ink-soft)}

            .sched-td-per{background:var(--paper-off);border-right:1px solid var(--rule);border-bottom:1px solid var(--rule);width:80px;min-width:70px;text-align:center;padding:0;vertical-align:middle}
            .sched-per-inner{padding:11px 7px}
            .sched-per-num{font-family:var(--font-d);font-size:1.2rem;font-weight:900;color:var(--ink-faint);line-height:1;display:block}
            .sched-per-time{font-family:var(--font-m);font-size:.48rem;color:var(--ink-faint);letter-spacing:.04em;display:block;margin-top:4px}

            /* Break row */
            .sched-brk-row .sched-td-per{background:var(--ink)}
            .sched-brk-row .sched-per-num{color:rgba(255,255,255,.18)}
            .sched-brk-row .sched-per-time{color:rgba(255,255,255,.12)}
            .sched-td-brk{background:var(--ink);border-bottom:1px solid rgba(255,255,255,.04);text-align:center;vertical-align:middle;padding:7px}
            .sched-brk-lbl{font-family:var(--font-m);font-size:.56rem;font-weight:400;letter-spacing:.18em;text-transform:uppercase;color:rgba(255,255,255,.16)}

            /* Subject cells */
            .sched-td-cell{border-right:1px solid var(--rule);border-bottom:1px solid var(--rule);vertical-align:top;padding:0}
            .sched-td-cell:last-child{border-right:none}
            .sched-cell-in{padding:10px 13px;min-height:78px;display:flex;flex-direction:column;justify-content:space-between;gap:4px;position:relative;transition:background .13s}
            .sched-cell-in:hover{background:rgba(0,0,0,.02)}
            .sched-cell-in::before{content:'';position:absolute;top:9px;left:0;width:3px;height:calc(100% - 18px);border-radius:0 2px 2px 0}
            .sched-c--science  .sched-cell-in::before{background:#0a0a0a}
            .sched-c--lang     .sched-cell-in::before{background:#5a5a5a}
            .sched-c--math     .sched-cell-in::before{background:#2a2a2a}
            .sched-c--social   .sched-cell-in::before{background:#8a8a8a}
            .sched-c--religion .sched-cell-in::before{background:#c0c0c0}
            .sched-c--ict      .sched-cell-in::before{background:#1a1a1a}
            .sched-c--pe       .sched-cell-in::before{background:#404040}
            .sched-c--art      .sched-cell-in::before{background:#a0a0a0}
            .sched-c--empty    .sched-cell-in::before{display:none}

            .sched-subj-short{font-family:var(--font-m);font-size:.52rem;font-weight:500;color:var(--ink-faint);letter-spacing:.08em;text-transform:uppercase;display:block;margin-bottom:1px}
            .sched-subj-name{font-family:var(--font-d);font-size:.82rem;font-weight:700;color:var(--ink);line-height:1.2;letter-spacing:-.01em}
            .sched-tchr-name{font-family:var(--font-b);font-size:.64rem;font-weight:500;color:var(--ink-muted);margin-top:5px;display:flex;align-items:center;gap:5px}
            .sched-tchr-name::before{content:'';width:9px;height:1px;background:var(--ink-faint);display:inline-block;flex-shrink:0}
            .sched-room-tag{font-family:var(--font-m);font-size:.48rem;font-weight:500;padding:2px 6px;border:1px solid var(--rule);border-radius:3px;color:var(--ink-faint);letter-spacing:.05em;display:inline-block;margin-top:3px;align-self:flex-start}
            .sched-today-col{background:rgba(0,0,0,.015)}

            /* FOOTER */
            #sched-footer{max-width:1440px;margin:0 auto;padding:0 48px 42px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:14px;animation:sched-up .5s .18s ease both;opacity:0;animation-fill-mode:forwards}
            .sched-foot-note{font-family:var(--font-m);font-size:.58rem;color:var(--ink-faint);letter-spacing:.06em;display:flex;align-items:center;gap:8px}
            .sched-foot-note::before{content:'※';font-size:.66rem}
            .sched-foot-acts{display:flex;align-items:center;gap:9px}
            .sched-btn{font-family:var(--font-m);font-size:.63rem;font-weight:500;letter-spacing:.1em;text-transform:uppercase;padding:8px 17px;border-radius:var(--r);cursor:pointer;transition:opacity .14s,transform .14s;display:flex;align-items:center;gap:7px;border:none}
            .sched-btn:hover{opacity:.8;transform:translateY(-1px)}
            .sched-btn svg{width:12px;height:12px;flex-shrink:0}
            .sched-btn--dark{background:var(--ink);color:var(--paper)}
            .sched-btn--ghost{background:transparent;border:1px solid var(--rule-dark);color:var(--ink)}

            /* ANIMATIONS */
            @keyframes sched-up{from{opacity:0;transform:translateY(14px)}to{opacity:1;transform:translateY(0)}}

            /* ════ PRINT — LANDSCAPE A4 ════ */
            @media print{
            @page{size:A4 landscape;margin:8mm 10mm}
            body::after,#sched-filter-bar,#sched-footer{display:none!important}
            body{background:#fff;print-color-adjust:exact;-webkit-print-color-adjust:exact}
            #sched-header{animation:none;padding:0 10mm}
            #sched-hdr-inner{padding:8mm 0 6mm}
            #sched-main-title{font-size:1.75rem}
            #sched-school-name{font-size:.66rem}
            /* Print class/section info bar */
            #sched-print-bar{display:flex!important;align-items:center;justify-content:space-between;background:var(--ink);padding:5px 10mm;border-bottom:none}
            .sched-pbar-txt{font-family:var(--font-m);font-size:.56rem;color:rgba(255,255,255,.48);letter-spacing:.08em}
            #sched-main{padding:6mm 10mm 5mm}
            #sched-meta-row{margin-bottom:5mm;animation:none}
            #sched-cls-display{font-size:1.05rem}
            .sched-leg-item{font-size:.5rem}
            #sched-grid-wrap{animation:none;opacity:1;overflow:visible}
            #sched-grid{box-shadow:none;border:.5px solid #333;font-size:.75em}
            .sched-th-day{font-size:.88rem}
            .sched-th-date{font-size:.5rem}
            .sched-per-num{font-size:1rem}
            .sched-per-time{font-size:.42rem}
            .sched-subj-name{font-size:.72rem}
            .sched-tchr-name{font-size:.56rem}
            .sched-room-tag{font-size:.42rem}
            .sched-cell-in{min-height:60px;padding:7px 10px}
            .sched-td-per{width:58px;min-width:52px}
            .sched-per-inner{padding:8px 5px}
            .sched-th-in{padding:11px 12px}
            }
            #sched-print-bar{display:none}

            @media(max-width:820px){
            #sched-main,#sched-footer,#sched-header,#sched-filter-bar{padding-left:18px;padding-right:18px}
            #sched-main{padding-top:20px}
            }
        </style>
    @endpush


    @push('scripts')
    <script>
        document.addEventListener('livewire:initialized', () => {
            Livewire.hook('morph.updated', ({ el }) => {
                setTimeout(() => {

                    // ✅ Select re-init
                    el.querySelectorAll('.input-group-outline .form-select').forEach(function(select) {
                        if (!select.nextElementSibling || !select.nextElementSibling.classList.contains('custom-select-wrapper')) {
                            buildCustomSelect(select);
                        }
                    });

                    // ✅ Text/Time input — is-filled re-apply
                    el.querySelectorAll('.input-group-outline input').forEach(function(input) {
                        var group = input.closest('.input-group');
                        if (!group) return;

                        // value থাকলে is-filled দাও
                        if (input.value && input.value.trim() !== '') {
                            group.classList.add('is-filled');
                        } else {
                            group.classList.remove('is-filled');
                        }

                        // Duplicate listener এড়াতে flag চেক
                        if (input._materialInit) return;
                        input._materialInit = true;

                        input.addEventListener('focus', function() {
                            group.classList.add('is-focused');
                        });
                        input.addEventListener('blur', function() {
                            group.classList.remove('is-focused');
                            group.classList.toggle('is-filled', !!input.value.trim());
                        });
                        input.addEventListener('input', function() {
                            group.classList.toggle('is-filled', !!input.value.trim());
                        });
                    });

                    // ✅ Datepicker re-init
                    el.querySelectorAll('.input-group-outline input[type="date"]').forEach(function(input) {
                        if (!input._dpInit) {
                            _initDatepickers();
                        }
                    });

                }, 0);
            });
        });
    </script>
    @endpush