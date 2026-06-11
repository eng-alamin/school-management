<div class="mat-card" style="padding-top:28px">

    {{-- Floating Header --}}
    <div class="mat-card-header header-pink-gradient">
        <h5>
            <span class="material-icons-round" style="font-size:18px;vertical-align:middle;margin-right:6px">payments</span>
            Salary Assign
        </h5>
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
                        class="btn-pink d-flex align-items-center gap-1"
                        type="button">
                    <span wire:loading.remove wire:target="filter">
                        <span class="material-icons-round" style="font-size:16px;vertical-align:middle;margin-right:4px">filter_alt</span> Filter
                    </span>
                    <span wire:loading wire:target="filter">
                        <span class="material-icons-round" style="font-size:16px;animation:spin .7s linear infinite">sync</span> Filtering...
                    </span>
                </button>
            </div>

        </div>
    </div>

    {{-- Employee List --}}
    @if($hasFiltered)
    <div class="form-section">
        <div class="section-heading">
            <span class="material-icons-round">people</span> Employee Salary Assign
        </div>

        @if(count($employees) > 0)
        <div class="table-responsive mt-3">
            <table class="table-loader">
                <thead>
                    <tr>
                        <th>Sl</th>
                        <th>Staff Id</th>
                        <th>Name</th>
                        <th>Designation</th>
                        <th>Department</th>
                        <th>Salary Grade (GPA)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($employees as $i => $employee)
                    <tr wire:key="employee-{{ $employee['id'] }}">
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $employee['staff_id'] ?? '—' }}</td>
                        <td>{{ $employee['name'] }}</td>
                        <td>{{ $employee['designation']['name'] ?? '—' }}</td>
                        <td>{{ $employee['department']['name'] ?? '—' }}</td>
                        <td style="width:260px">
                            <select wire:model.live="salaryTemplate.{{ $employee['id'] }}"
                                    class="schedule-input">
                                <option value="">Select</option>
                                @foreach($salaryTemplates as $template)
                                    <option value="{{ $template->id }}">{{ $template->salary_grade }}</option>
                                @endforeach
                            </select>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Footer --}}
        <div class="form-footer">
            <button class="btn-outline" type="button" wire:click="resetForm">
                <span class="material-icons-round" style="font-size:16px">refresh</span> Reset
            </button>
            <button class="btn-pink" type="button"
                    wire:click="save"
                    wire:loading.attr="disabled"
                    wire:target="save">
                <span wire:loading.remove wire:target="save">
                    <span class="material-icons-round">save</span> Save
                </span>
                <span wire:loading wire:target="save">
                    <span class="material-icons-round" style="font-size:16px;animation:spin .7s linear infinite">sync</span> Saving...
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

@push('styles')
<style>
    .table-loader {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
    }
    .table-loader thead th {
        padding: 10px;
        text-align: left;
        font-weight: 600;
        font-size: 12px;
        white-space: nowrap;
    }
    .table-loader tbody td {
        padding: 8px 10px;
        vertical-align: middle;
        font-size: 13px;
    }
    .table-loader tbody tr {
        transition: background .15s;
    }
    .table-loader tbody tr:hover {
        background: rgba(255,255,255,.03);
    }
    .schedule-input {
        border: 1px solid #3d3d3d;
        padding: 6px 10px;
        border-radius: 4px;
        font-size: 12px;
        outline: none;
        width: 100%;
        transition: border-color 0.2s;
        background: transparent;
        color: inherit;
    }
    .schedule-input:focus {
        border-color: #e05252;
    }
    .form-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding-top: 20px;
        margin-top: 16px;
        flex-wrap: wrap;
        gap: 12px;
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
                        if (typeof buildCustomSelect === 'function') buildCustomSelect(select);
                    }
                });
            }, 0);
        });
    });
</script>
@endpush