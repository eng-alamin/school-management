{{-- resources/views/livewire/admin/biometric/create-user-mapping-component.blade.php --}}
<div>

    <div class="card">

        <div class="mat-card-header header-primary-gradient">
            <h5>Add Mapping</h5>
            <p data-en="Manage students, filter by Device, type, class and section."
               data-bn="Manage students, filter by Device, type, class and section.">
                Manage students, filter by device, type, class and section.
            </p>
        </div>

        {{-- ===== FILTER GROUND: Device / Type / Class / Section ===== --}}
        <div class="form-section" style="padding:32px 28px 20px">
            <div class="section-heading">
                <span class="material-icons-round">filter_alt</span> Filter Ground
            </div>

            <div class="row g-4">
                <div class="col-md-3">
                    <div class="input-group input-group-outline" wire:ignore>
                        <label class="form-label">Select Device <span class="req">*</span></label>
                        <select wire:model.live="deviceId" class="form-select no-custom-select">
                            <option value="">-- Select Device --</option>
                            @foreach($devices as $d)
                                <option value="{{ $d->id }}">{{ $d->device_name }} ({{ $d->device_serial }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="input-group input-group-outline">
                        <label class="form-label">Type <span class="req">*</span></label>
                        <select wire:model.live="attendable_type" class="form-select no-custom-select" @disabled(!$device)>
                            <option value="student">Student</option>
                            <option value="employee">Employee</option>
                        </select>
                    </div>
                </div>

                @if($attendable_type === 'student')
                    <div class="col-md-3">
                        <div class="input-group input-group-outline">
                            <label class="form-label">Class <span class="req">*</span></label>
                            <select wire:model.live="filterClassId" class="form-select no-custom-select @error('filterClassId') is-invalid @enderror" @disabled(!$device)>
                                <option value="">Select Class</option>
                                @foreach($filterClasses as $c)
                                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        @error('filterClassId') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>

                    <div class="col-md-3">
                        <div class="input-group input-group-outline">
                            <label class="form-label">Section @if($filterClassHasSection)<span class="req">*</span>@endif</label>
                            <select wire:model="filterSectionId" class="form-select no-custom-select @error('filterSectionId') is-invalid @enderror"
                                    @disabled(!$device || !$filterClassId || !$filterClassHasSection)>
                                @if(!$filterClassId)
                                    <option value="">Select class first</option>
                                @elseif(!$filterClassHasSection)
                                    <option value="">N/A — this class has no sections</option>
                                @else
                                    <option value="">Select Section</option>
                                    @if($filterSections->count() > 1)
                                        <option value="all">All Section</option>
                                    @endif
                                    @foreach($filterSections as $s)
                                        <option value="{{ $s->id }}">{{ $s->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        @error('filterSectionId') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                @else
                    <div class="col-md-6">
                        <div class="input-group input-group-outline">
                            <label class="form-label">Search Employee</label>
                            <input type="text" class="form-control" wire:model="employeeSearch" @disabled(!$device)
                                   placeholder="Search by name or Employee ID..."
                                   data-en-placeholder="Search by name or Employee ID..."
                                   data-bn-placeholder="নাম বা Employee ID দিয়ে সার্চ করো...">
                        </div>
                    </div>
                @endif

                <div class="col-md-12 text-center">
                    <button wire:click="filter"
                            wire:loading.attr="disabled"
                            wire:target="filter"
                            class="btn btn-primary w-100 d-flex justify-content-center align-items-center"
                            type="button"
                            @disabled(!$device)>
                        <span wire:loading.remove wire:target="filter">
                            <span class="material-icons-round" style="font-size:16px;vertical-align:middle;margin-right:4px">filter_alt</span> Filter
                        </span>
                        <span wire:loading wire:target="filter">
                            <span class="material-icons-round" style="font-size:16px;animation:spin .7s linear infinite">sync</span> Loading...
                        </span>
                    </button>
                </div>

            </div>
        </div>

        {{-- ===== FILTER RESULTS TABLE (bulk mapping) ===== --}}
        @if($hasResults)
            <div style="margin:0 28px 20px;padding:14px 18px;background:#fff8e1;border-left:4px solid #f59e0b;border-radius:6px">
                <span style="font-size:.78rem;color:#555">
                    <span class="material-icons-round" style="font-size:16px;vertical-align:middle;margin-right:4px">info</span>
                    Check the checkboxes of the ones you want to map, then enter the Device User ID (required) and Card Number (optional) for each one.
                </span>
            </div>

            <div style="margin:0 28px 28px;overflow-x:auto">
                <table class="table table-hover mb-0" style="font-size:.78rem;min-width:900px">
                    <thead style="background:#1a1a1a;color:#fff">
                        <tr>
                            <th style="width:40px">
                                <input type="checkbox" wire:model.live="selectAll" class="form-check-input">
                            </th>
                            <th id="th-sl">SL</th>
                            <th id="th-name">Name</th>
                            <th id="th-idno">ID No.</th>
                            <th id="th-extra">{{ $attendable_type === 'employee' ? 'Designation' : 'Class / Section' }}</th>
                            <th id="th-device-user-id" style="min-width:150px">Device User ID <span class="req">*</span></th>
                            <th id="th-card-number" style="min-width:150px">Card Number</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($people as $i => $person)
                        <tr wire:key="filter-person-{{ $person['id'] }}">
                            <td>
                                <input type="checkbox"
                                       class="form-check-input"
                                       value="{{ $person['id'] }}"
                                       wire:model.live="selectedStudents">
                            </td>
                            <td>{{ $i + 1 }}</td>
                            <td>{{ $person['name'] }}</td>
                            <td>{{ $person['idNo'] }}</td>
                            <td>{{ $person['extra'] }}</td>
                            <td>
                                <input type="text"
                                    wire:key="dui-{{ $device->id }}-{{ $person['id'] }}"
                                    class="form-control form-control-sm"
                                    wire:model="people.{{ $i }}.device_user_id"
                                    placeholder="e.g. 1001">
                            </td>
                            <td>
                                <input type="text"
                                    wire:key="card-{{ $device->id }}-{{ $person['id'] }}"
                                    inputmode="numeric"
                                    class="form-control form-control-sm"
                                    wire:model="people.{{ $i }}.card_number"
                                    placeholder="Optional">
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                No matching student/employee found.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="form-footer d-flex justify-content-end gap-2" style="padding:16px 28px;border-top:1px solid var(--border)">
                <button type="button" class="btn btn-light" onclick="history.back()">
                    <span class="material-icons-round" style="font-size:16px;vertical-align:middle;margin-right:4px">arrow_back</span>
                    Cancel
                </button>
               
                <button type="button"
                        class="btn btn-primary d-flex align-items-center gap-1"
                        wire:click="save"
                        wire:loading.attr="disabled"
                        wire:target="save">
                    <span wire:loading wire:target="save" class="spinner-border spinner-border-sm"></span>
                    <span class="material-icons-round" style="font-size:16px" wire:loading.remove wire:target="save">verified</span>
                    Save Mapping{{ count($selectedStudents) > 1 ? 's' : '' }}
                    @if(count($selectedStudents))
                        ({{ count($selectedStudents) }})
                    @endif
                </button>
            </div>

        @endif

    </div>
</div>


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