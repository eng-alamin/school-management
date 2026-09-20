<div>
    <div class="card">

        {{-- Floating Header --}}
        <div class="mat-card-header header-primary-gradient">
            <h5 id="cHTSubstituteTeacher">Substitute Teacher Assignment</h5>
            <p id="cHSSubstituteTeacher">Find absent teacher periods and assign substitutes</p>
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
                <p class="text-muted mt-3 mb-0">On the selected date, the teacher has no absence recorded, or does not have a class routine for that day.</p>
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