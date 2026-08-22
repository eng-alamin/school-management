<div>

    <div class="card">

        <div class="mat-card-header header-primary-gradient">
            <h5 id="cardHeaderTitleAllschedules">Teacher Schedule</h5>
            <p id="cardHeaderSubtitle">View weekly class schedule assigned to a specific teacher.</p>
        </div>

        <div id="teacherSchedulePrintable">

            <div class="row g-4 p-5 no-print">

                {{-- Teacher Select --}}
                <div class="col-md-8 offset-md-2">
                    <div class="input-group input-group-outline" wire:ignore>
                        <label class="form-label">Teacher</label>
                        <select wire:model="teacher_id" class="form-select">
                            <option value="">Select Teacher</option>
                            @foreach ($teachers as $t)
                                <option value="{{ $t->id }}">{{ $t->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @error('teacher_id') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>

                {{-- Filter Button --}}
                <div class="col-md-8 offset-md-2 text-center">
                    <button wire:click="filter"
                            wire:loading.attr="disabled"
                            wire:target="filter"
                            class="btn btn-primary w-100 d-flex justify-content-center align-items-center"
                            type="button">
                        <span wire:loading.remove wire:target="filter">
                            View Schedule
                        </span>
                        <span wire:loading wire:target="filter">
                            <span class="material-icons-round" style="font-size:16px;animation:spin .7s linear infinite">sync</span>
                        </span>
                    </button>
                </div>

            </div>

            {{-- Print-only header (teacher name), shown only inside print window --}}
            <div class="print-only-header" style="display:none">
                <h2>Teacher Schedule</h2>
                @if($teacher_id)
                    <p>
                        Teacher:
                        {{ optional($teachers->firstWhere('id', (int) $teacher_id))->name }}
                    </p>
                @endif
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

            <div class="d-flex justify-content-end gap-2 px-5 pb-4 no-print">
                <button type="button"
                        class="btn btn-primary"
                        onclick="printTeacherSchedule()"
                        @if(!$hasSchedule) disabled @endif>
                    <span>
                        <span class="material-icons-round align-middle" style="font-size:1rem;">print</span>
                        <span>Print</span>
                    </span>
                </button>
            </div>
            @elseif($teacher_id)
                <div class="px-5 pb-4 no-print">
                    <div class="alert alert-warning py-2 mb-0" style="font-size:.82rem">
                        <span class="material-icons-round" style="font-size:16px;vertical-align:middle">info</span>
                        এই teacher এর কোনো schedule পাওয়া যায়নি।
                    </div>
                </div>
            @endif

        </div>

    </div>
    
</div>

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

    function printTeacherSchedule() {
        const printableEl = document.getElementById('teacherSchedulePrintable');

        if (!printableEl) {
            return;
        }

        const gridWrap = printableEl.querySelector('#sched-grid-wrap');

        if (!gridWrap) {
            alert('Print korar moto kono schedule pawa jayni. Age ekjon teacher select kore "View Schedule" chaap din.');
            return;
        }

        const printHeader = printableEl.querySelector('.print-only-header');
        const headerHtml = printHeader ? printHeader.innerHTML : '<h2>Teacher Schedule</h2>';
        const printContent = gridWrap.outerHTML;

        const printWindow = window.open('', '_blank', 'width=1000,height=700');

        if (!printWindow) {
            alert('Print window block hoye গেছে। Browser-er popup blocker check korun.');
            return;
        }

        printWindow.document.write(`
            <html>
                <head>
                    <title>Teacher Schedule</title>
                    <style>
                        * { box-sizing: border-box; }
                        body { font-family: Arial, Helvetica, sans-serif; padding: 28px; color: #222; }
                        .no-print { display: none !important; }

                        h2 { margin: 0 0 4px; font-size: 1.3rem; }
                        p { margin: 0 0 18px; color: #555; font-size: .9rem; }

                        #sched-grid-wrap { overflow-x: visible; padding: 0; }
                        #sched-grid { width: 100%; min-width: 0; border-collapse: collapse; border: 1px solid #1a1a1a; }

                        .sched-thead-row th { background: #0a0a0a; color: #fff; padding: 0; border: none; }
                        .sched-th-in { padding: 10px 12px; border-right: 1px solid rgba(255,255,255,.15); }
                        .sched-th-day { font-weight: 700; font-size: .85rem; display: block; }
                        .sched-th-time-hdr { background: #2a2a2a; }

                        .sched-td-per { background: #f0f0ee; border-right: 1px solid #e0e0de; border-bottom: 1px solid #e0e0de; width: 70px; text-align: center; padding: 0; vertical-align: middle; }
                        .sched-per-inner { padding: 8px 6px; }
                        .sched-per-num { font-weight: 900; font-size: 1rem; color: #888; display: block; }
                        .sched-per-time { font-size: .55rem; color: #888; display: block; margin-top: 3px; }

                        .sched-td-cell { border-right: 1px solid #e0e0de; border-bottom: 1px solid #e0e0de; vertical-align: top; padding: 0; }
                        .sched-td-cell:last-child { border-right: none; }
                        .sched-cell-in { padding: 8px 10px; min-height: 60px; }

                        .sched-subj-name { font-weight: 700; font-size: .8rem; color: #0a0a0a; }
                        .sched-tchr-name { font-size: .65rem; color: #555; display: block; margin-top: 4px; }
                        .sched-room-tag { font-size: .55rem; padding: 2px 6px; border: 1px solid #ddd; border-radius: 3px; color: #777; display: inline-block; margin-top: 3px; }

                        .material-icons-round, .bi { display: none; }
                    </style>
                </head>
                <body>
                    ${headerHtml}
                    ${printContent}
                </body>
            </html>
        `);

        printWindow.document.close();
        printWindow.focus();

        setTimeout(() => {
            printWindow.print();
            printWindow.close();
        }, 250);
    }
</script>
@endpush