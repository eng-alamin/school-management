<div>
    <div class="card">

        {{-- Floating Header --}}
        <div class="mat-card-header header-primary-gradient">
            <h5> Salary Assign</h5>
            <p>Assign salary grades to employees by role and designation</p>
        </div>

        {{-- Select Ground --}}
        <div class="form-section" style="padding-top:40px; padding-bottom:20px">
            <div class="section-heading">
                <span class="material-icons-round">tune</span> Select Ground
            </div>
            <div class="row g-4">

                {{-- Role --}}
                <div class="col-md-6">
                    <div wire:ignore class="input-group input-group-outline">
                        <label class="form-label">Role <span class="req">*</span></label>
                        <select wire:model.live="role" class="form-select">
                            <option value="">Select Role</option>
                            @foreach($roles as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    @error('role') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>

                {{-- Designation --}}
                <div class="col-md-6">
                    <div class="input-group input-group-outline">
                        <label class="form-label">Designation</label>
                        <select wire:model.live="designation_id"
                                class="form-select"
                                @if(!$role) disabled @endif>
                            <option value="">Select Designation</option>
                            @foreach($designations as $designation)
                                <option value="{{ $designation['id'] }}">{{ $designation['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    @error('designation_id') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>

                {{-- Filter Button --}}
                <div class="col-md-12 d-flex justify-content-end">
                    <button wire:click="filter"
                            wire:loading.attr="disabled"
                            wire:target="filter"
                            class="btn btn-primary d-flex align-items-center gap-1"
                            type="button">
                        <span wire:loading.remove wire:target="filter">
                            <span class="material-icons-round">filter_alt</span> Filter
                        </span>
                        <span wire:loading wire:target="filter">
                            <span class="material-icons-round" style="animation:spin .7s linear infinite">sync</span> Filtering...
                        </span>
                    </button>
                </div>

            </div>
        </div>

        {{-- Employee List --}}
        @if($hasFiltered)
        <div class="form-section">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
                <div class="section-heading mb-0">
                    <span class="material-icons-round">people</span> Employee Salary Assign
                </div>

                @if(count($employees) > 0)
                    <div style="position:relative;display:inline-flex;align-items:center">
                        <span class="material-icons-round" style="position:absolute;left:10px;font-size:16px;color:var(--muted);pointer-events:none">search</span>
                        <input type="text"
                            wire:model.live.debounce.300ms="employeeSearch"
                            placeholder="Search by name or ID"
                            style="border:1px solid rgba(0,0,0,.1);border-radius:8px;padding:6px 12px 6px 32px;font-size:.78rem;font-family:inherit;outline:none;background:#f8f9fa;width:220px" />
                    </div>
                @endif
            </div>

            @if(count($employees) > 0)
            <div class="table-responsive mt-3">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Sl</th>
                            <th>Staff Id</th>
                            <th>Name</th>
                            <th>Designation</th>
                            <th>Department</th>
                            <th>Salary Template</th>
                            <th>Net Salary (Preview)</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($filteredEmployees as $i => $employee)
                        <tr wire:key="employee-{{ $employee['id'] }}">
                            <td>{{ $i + 1 }}</td>
                            <td><a class="text-primary" href="{{ route('admin.employee.view', ['id' => $employee['id']]) }}" target="_blank">{{ $employee['employee_id'] ?? '—' }}</a></td>
                            <td>{{ $employee['name'] }}</td>
                            <td>{{ $employee['designation']['name'] ?? '—' }}</td>
                            <td>{{ $employee['department']['name'] ?? '—' }}</td>
                            <td style="width:240px">
                                <div class="input-group input-group-outline">
                                    <select wire:model.live="salaryTemplate.{{ $employee['id'] }}"
                                            class="form-select form-select-sm @if(empty($salaryTemplate[$employee['id']])) schedule-input-empty @endif">
                                        <option value="">Select</option>
                                        @foreach($salaryTemplates as $template)
                                            <option value="{{ $template->id }}">{{ \Illuminate\Support\Str::limit($template->name, 12) }} ({{ $template->salary_grade }})</option>
                                        @endforeach
                                    </select>
                                </div>
                            </td>
                            <td>
                                @if(!empty($templateAmounts[$employee['id']]))
                                    <span style="font-weight:600;color:#16a34a">
                                        ৳{{ number_format($templateAmounts[$employee['id']]['net'], 0) }}
                                    </span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                @if(in_array($employee['id'], $alreadyAssignedIds))
                                    <span class="badge" style="background:#dbeafe;color:#1e40af;font-weight:600;font-size:.68rem;padding:5px 8px;border-radius:6px;">
                                        Already Assigned
                                    </span>
                                @else
                                    <span class="badge" style="background:#fef3c7;color:#92400e;font-weight:600;font-size:.68rem;padding:5px 8px;border-radius:6px;">
                                        Not Assigned
                                    </span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">
                                No employees match "{{ $employeeSearch }}".
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Footer --}}
            <div class="form-footer">
                <button class="btn btn-secondary" type="button" wire:click="resetForm">
                    <span>
                        <span class="material-icons-round" style="font-size:16px">refresh</span>
                        <span>Reset</span>
                    </span>
                </button>
                <button class="btn btn-primary" type="button"
                        wire:click="save"
                        wire:loading.attr="disabled"
                        wire:target="save">
                    <span wire:loading.remove wire:target="save" style="display: inline-flex;align-items: center;gap: 6px">
                        <span class="material-icons-round">save</span>
                        <span>Save</span>
                    </span>
                    <span wire:loading wire:target="save">
                        <span class="material-icons-round" style="animation:spin .7s linear infinite">sync</span>
                        <span>Saving...</span>
                    </span>
                </button>
            </div>

            @else
            <div class="text-center py-5 text-muted">
                <i class="bi bi-inbox display-5 d-block mb-2 opacity-25"></i>
                No employees found for selected role/designation.
            </div>
            @endif
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
                        if (typeof buildCustomSelect === 'function') buildCustomSelect(select);
                    }
                });
            }, 0);
        });
    });
</script>
@endpush