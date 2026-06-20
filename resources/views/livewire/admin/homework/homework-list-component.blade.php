<div class="mat-card" style="padding-top:28px">

    <!-- floating header -->
    <div class="mat-card-header header-pink-gradient">
        <h5 id="cardHeaderTitleAllHomeworks">All Homeworks</h5>
        <p id="cardHeaderSubtitle">Filter and manage homework assigned to each class.</p>
    </div>

    <div class="row g-4 p-5">

        <div class="col-md-4">
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

        <div class="col-md-4">
            <div class="input-group input-group-outline">
                <label class="form-label">Section</label>
                <select wire:model="section_id" class="form-select">
                    <option value="">{{ empty($availableSections) ? 'Select class first' : 'Select Section' }}</option>
                    @foreach ($availableSections as $s)
                        <option value="{{ $s['id'] }}">{{ $s['name'] }}</option>
                    @endforeach
                </select>
            </div>
            @error('section_id') <span class="text-danger">{{ $message }}</span> @enderror
        </div>

        <div class="col-md-4">
            <div class="input-group input-group-outline">
                <label class="form-label">Subject <span class="req">*</span></label>
                <select wire:model="subject_id" class="form-select">
                    <option value="">Select Subject</option>
                    @foreach ($subjects as $subject)
                        <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                    @endforeach
                </select>
            </div>
            @error('subject_id') <span class="text-danger">{{ $message }}</span> @enderror
        </div>

        <div class="col-md-12 text-center">
            <button wire:click="filter" class="btn-pink w-100 d-flex justify-content-center align-items-center" type="button">
                <span wire:loading.remove wire:target="filter">Filter</span>
                <span wire:loading wire:target="filter">Loading...</span>
            </button>
        </div>

        <div class="col-md-12 text-center">
            <a href="{{ route('admin.homework.add') }}" class="btn-pink w-100 d-flex justify-content-center align-items-center">
                <span class="material-icons-round" style="font-size:16px">add</span><span>New Homework</span>
            </a>
        </div>
    </div>

    @if($hasHomework)
    <div class="table-responsive p-4">
        <table class="mat-table w-100">
            <thead>
                <tr>
                    <th>SL</th>
                    <th>Subject</th>
                    <th>Class</th>
                    <th>Section</th>
                    <th>Title</th>
                    <th>Homework Date</th>
                    <th>Submission Date</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($homeworks as $index => $h)
                <tr wire:key="hw-{{ $h['id'] }}">
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $h['subject']['name'] ?? '-' }}</td>
                    <td>{{ $h['class']['name'] ?? '-' }}</td>
                    <td>{{ $h['section']['name'] ?? '-' }}</td>
                    <td>{{ $h['title'] ?? '-' }}</td>
                    <td>{{ $h['homework_date'] ?? '-' }}</td>
                    <td>{{ $h['submission_date'] ?? '-' }}</td>
                    <td>{{ $h['status'] ?? '-' }}</td>
                    <td>
                        <div class="action-btns">
                            @if ($h['attachment'])
                                <a href="{{ Storage::url($h['attachment']) }}" target="_blank" class="act-btn"><span class="material-icons-round">attachment</span></a>
                            @endif
                            <a href="/homework/edit/{{ $h['id'] }}" class="act-btn edit" title="Edit">
                                <span class="material-icons-round">drive_file_rename_outline</span>
                            </a>
                            <button type="button" class="act-btn delete" title="Delete"
                                onclick="openDeleteModal({{ $h['id'] }}, '{{ addslashes($h['title'] ?? '') }}')">
                                <span class="material-icons-round">delete</span>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center p-4" style="color:var(--ink-faint)">No homework found</td>
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
                <h6 style="font-weight:700;margin:8px 0 4px">Delete this homework?</h6>
                <p style="font-size:.78rem;color:var(--muted);margin-bottom:16px" id="deleteName">This action cannot be undone.</p>
                <div style="display:flex;gap:8px;justify-content:center">
                    <button class="btn-outline" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn-pink" onclick="confirmDelete()">Delete</button>
                </div>
            </div>
        </div>
    </div>

</div>

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
<script>
    let deleteTargetId = null;

    function openDeleteModal(id, title) {
        deleteTargetId = id;
        document.getElementById('deleteName').textContent = `"${title}" will be permanently deleted.`;
        new bootstrap.Modal(document.getElementById('deleteModal')).show();
    }

    function confirmDelete() {
        @this.call('deleteConfirmed', deleteTargetId);
        bootstrap.Modal.getInstance(document.getElementById('deleteModal')).hide();
    }
</script>
@endpush