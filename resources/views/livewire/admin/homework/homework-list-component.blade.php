<div class="mat-card" style="padding-top:28px">

      <!-- floating header -->
      <div class="mat-card-header header-pink-gradient">
        <h5 id="cardHeaderTitleAlldesignations">All Homeworks</h5>
        <p id="cardHeaderSubtitle">A lightweight, extendable, dependency-free javascript HTML table plugin.</p>
      </div>

      <div class="row g-4 p-5">

        <div class="col-md-4">
          <div wire:ignore class="input-group input-group-outline">
            <label class="form-label">Class</label>
            <select wire:model="class_id" class="form-select">
              <option value="">Select</option>
              @foreach($classes as $class)
                <option value="{{ $class->id }}">{{ $class->name }}</option>
              @endforeach
            </select>
          </div>
          @error('class_id') <span class="text-danger">{{ $message }}</span> @enderror
        </div>
        
        <div class="col-md-4">
          <div wire:ignore class="input-group input-group-outline">
            <label class="form-label">Section</label>
            <select wire:model="section_id" class="form-select">
              <option value="">Select</option>
              @foreach($sections as $section)
                <option value="{{ $section->id }}">{{ $section->name }}</option>
              @endforeach
            </select>
          </div>
          @error('section_id') <span class="text-danger">{{ $message }}</span> @enderror
        </div>

        <div class="col-md-4">
          <div wire:ignore class="input-group input-group-outline">
            <label class="form-label">Subject</label>
            <select wire:model="subject_id" class="form-select">
              <option value="">Select</option>
              @foreach($subjects as $subject)
                <option value="{{ $subject->id }}">{{ $subject->name }}</option>
              @endforeach
            </select>
          </div>
          @error('subject_id') <span class="text-danger">{{ $message }}</span> @enderror
        </div>

        <div class="col-md-12 text-center">
            <button wire:click="filter" class="btn-pink w-100 d-flex justify-content-center align-items-center" type="button">
                Filter
            </button>
        </div>

        <div class="col-md-12 text-center">
            <a href="{{ route('admin.homework.add') }}" class="btn-pink w-100 d-flex justify-content-center align-items-center">
                <span class="material-icons-round" style="font-size:16px">add</span><span>New Homework</span>
            </a>
        </div>
      </div>

        <!-- Table Card -->
        @if($hasHomework)
            <div class="table-card">
                <!-- toolbar -->
                <div class="card-toolbar">
                <div class="card-toolbar-title">
                    <!-- search in table -->
                    <div style="position:relative;display:inline-flex;align-items:center">
                    <span class="material-icons-round" style="position:absolute;left:10px;font-size:17px;color:var(--muted);pointer-events:none">search</span>
                    <input type="text" id="tableSearch" placeholder="Search pages…" style="border:1px solid rgba(0,0,0,.1);border-radius:8px;padding:7px 12px 7px 32px;font-size:.78rem;font-family:inherit;color:var(--dark);outline:none;background:#f8f9fa;width:220px"/>
                    </div>
                </div>

                <!-- buttons right -->

                    <a href="{{ route('admin.homework.add') }}" target="_blank" id="newHomeworkBtn" class="btn-outline btn-outline bg-dark text-white">
                    <span class="material-icons-round">add</span> <span id="newHomeworkBtn">New Homework</span>
                </a>

                </div>

                <!-- table -->
                <div class="table-responsive">
                <table class="mat-table" id="productsTable">
                    <thead>
                    <tr>
                        <th onclick="sortTable(0)" id="sl"><span id="th-sl-lbl">SL</span> <span class="sort-icon"></span></th>
                        <th onclick="sortTable(2)" id="th-subject"><span id="th-subject-lbl">Subject</span> <span class="sort-icon"></span></th>
                        <th onclick="sortTable(3)" id="th-class"><span id="th-class-lbl">Class</span> <span class="sort-icon"></span></th>
                        <th onclick="sortTable(4)" id="th-section"><span id="th-section-lbl">Section</span> <span class="sort-icon"></span></th>
                        <th onclick="sortTable(1)" id="th-title"><span id="th-title-lbl">Title</span> <span class="sort-icon"></span></th>
                        <th onclick="sortTable(5)" id="th-homework-date"><span id="th-homework-date-lbl">Homework Date</span> <span class="sort-icon"></span></th>
                        <th onclick="sortTable(6)" id="th-submission-date"><span id="th-submission-date-lbl">Submission Date</span> <span class="sort-icon"></span></th>
                        <th onclick="sortTable(7)" id="th-status"><span id="th-status-lbl">Status</span> <span class="sort-icon"></span></th>
                        <th id="th-actions"><span id="th-actions-lbl">Action</span></th>
                    </tr>
                    </thead>
                    <tbody id="tableBody"></tbody>
                </table>
                </div>

                <!-- pagination -->
                <div style="display:flex;align-items:center;justify-content:space-between;padding:14px 18px 18px;flex-wrap:wrap;gap:10px">
                <span style="font-size:.75rem;color:var(--muted)" id="pageInfo"></span>
                <div style="display:flex;gap:4px" id="paginationBtns"></div>
                </div>
            </div>
        @endif

    <!-- ═══════ Open Create Modal ═══════ -->
    <div wire:ignore.self class="modal fade" id="openCreateModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title">Add New Parent</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4" style="display:flex;flex-direction:column;gap:14px">
                    <div>
                        <label style="font-size:.75rem;font-weight:600;color:var(--dark);display:block;margin-bottom:4px">Name *</label>
                        <input wire:model="name" type="text" placeholder="e.g. Science" style="width:100%;border:1px solid rgba(0,0,0,.12);border-radius:8px;padding:8px 12px;font-size:.82rem;font-family:inherit;outline:none"/>
                        @error('name') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="modal-footer border-0 gap-2">
                    <button class="btn-outline" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" wire:click="save" class="btn-pink"><span class="material-icons-round" style="font-size:16px">add</span> Save</button>
                </div>
            </div>
        </div>
    </div>

    <!-- ═══════ Open Edit Modal ═══════ -->
    <div wire:ignore.self class="modal fade" id="openEditModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">

                <div class="modal-header border-0">
                    <h5 class="modal-title">Edit Parent</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body p-4" style="display:flex;flex-direction:column;gap:14px">
                    
                    <input type="hidden" wire:model="homework_id"/>

                    <div>
                        <label style="font-size:.75rem;font-weight:600;color:var(--dark);display:block;margin-bottom:4px">Name *</label>
                        <input wire:model="name" type="text" placeholder="e.g. A" style="width:100%;border:1px solid rgba(0,0,0,.12);border-radius:8px;padding:8px 12px;font-size:.82rem;font-family:inherit;outline:none"/>
                        @error('name') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>

                </div>

                <div class="modal-footer border-0">
                    <button class="btn-outline" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" wire:click="update({{ $homework_id }})" class="btn-pink">Update</button>
                </div>

            </div>
        </div>
    </div>

    <!-- ═══════ DELETE CONFIRM MODAL ═══════ -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content text-center p-3">
            <div style="width:52px;height:52px;border-radius:50%;background:var(--pink-light);display:flex;align-items:center;justify-content:center;margin:12px auto">
                <span class="material-icons-round" style="color:var(--pink);font-size:26px">delete_outline</span>
            </div>
            <h6 style="font-weight:700;margin:8px 0 4px">Delete this parent?</h6>
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
            // Filter এর পর data update
            Livewire.on('homeworkFiltered', (data) => {
                const newData = data[0] ?? [];
                homeworks.length = 0;
                newData.forEach(d => homeworks.push(d));
                filteredData = [...homeworks];
                currentPage = 1;

                // Table render হওয়ার জন্য wait
                setTimeout(() => {
                    renderTable();

                    // Search re-attach
                    const el = document.getElementById('tableSearch');
                    if (el && !el._searchInit) {
                        el._searchInit = true;
                        el.addEventListener('input', function() {
                            const q = this.value.toLowerCase();
                            filteredData = homeworks.filter(p =>
                                String(p.subject?.name ?? '').toLowerCase().includes(q) ||
                                String(p.class?.name ?? '').toLowerCase().includes(q) ||
                                String(p.section?.name ?? '').toLowerCase().includes(q) ||
                                String(p.title ?? '').toLowerCase().includes(q) ||
                                String(p.homework_date ?? '').toLowerCase().includes(q) ||
                                String(p.submission_date ?? '').toLowerCase().includes(q) ||
                                String(p.status ?? '').toLowerCase().includes(q)
                            );
                            currentPage = 1;
                            renderTable();
                        });
                    }
                }, 150);
            });
        });
    </script>
    
    {{-- Datatble এর জন্য custom JS (search, pagination, sort) --}}
    <script>
        const homeworks = @json($homeworks);
        const perPage = 10;
        let currentPage = 1;
        let sortCol = -1, sortAsc = true;
        let filteredData = [...homeworks];
        let deleteTargetId = null;

        function getVal(p, col) {
            switch(col) {
                case 0: return p.id;
                case 1: return p.subject?.name ?? '';
                case 2: return p.class?.name ?? '';
                case 3: return p.section?.name ?? '';
                case 4: return p.title ?? '';
                case 5: return p.homework_date ?? '';
                case 6: return p.submission_date ?? '';
                case 7: return p.status ?? '';
                default: return '';
            }
        }

        function renderTable() {
            const start = (currentPage - 1) * perPage;
            const rows  = filteredData.slice(start, start + perPage);
            const tbody = document.getElementById('tableBody');

            tbody.innerHTML = rows.map((p, index) => `
                <tr id="row-${p.id}">
                    <td data-label="SL"><span>${start + index + 1}</span></td>
                    <td data-label="Subject"><span>${p.subject?.name ?? ''}</span></td>
                    <td data-label="Class"><span>${p.class?.name ?? ''}</span></td>
                    <td data-label="Section"><span>${p.section?.name ?? ''}</span></td>
                    <td data-label="Title"><span>${p.title ?? ''}</span></td>
                    <td data-label="Homework Date"><span>${p.homework_date ?? ''}</span></td>
                    <td data-label="Submission Date"><span>${p.submission_date ?? ''}</span></td>
                    <td data-label="Status"><span>${p.status ?? ''}</span></td>
                    <td data-label="Actions">
                        <div class="action-btns">
                            <a href="/homework/edit/${p.id}" target="_blank" class="act-btn edit" title="Edit">
                                <span class="material-icons-round">drive_file_rename_outline</span>
                            </a>
                            <button class="act-btn delete" title="Delete" onclick="openDeleteModal(${p.id})">
                                <span class="material-icons-round">delete</span>
                            </button>
                        </div>
                    </td>
                </tr>
            `).join('');

            renderPagination();
        }

        function renderPagination() {
            const total = Math.ceil(filteredData.length / perPage);
            const info  = document.getElementById('pageInfo');
            const wrap  = document.getElementById('paginationBtns');
            const s = (currentPage-1)*perPage+1, e = Math.min(currentPage*perPage, filteredData.length);
            info.textContent = filteredData.length ? `Showing ${s}–${e} of ${filteredData.length}` : 'No results';

            const btnStyle = (active) => `
                display:inline-flex;align-items:center;justify-content:center;
                width:32px;height:32px;border-radius:8px;border:1px solid rgba(0,0,0,.1);
                font-size:.78rem;font-weight:600;cursor:pointer;font-family:inherit;
                background:${active?'linear-gradient(195deg,#ec407a,#d81b60)':'#fff'};
                color:${active?'#fff':'var(--dark)'};
                box-shadow:${active?'0 4px 12px var(--pink-shadow)':'none'};
            `;

            let html = `<button style="${btnStyle(false)}" onclick="changePage(${currentPage-1})" ${currentPage===1?'disabled':''}>‹</button>`;
            for (let i=1;i<=total;i++) {
                html += `<button style="${btnStyle(i===currentPage)}" onclick="changePage(${i})">${i}</button>`;
            }
            html += `<button style="${btnStyle(false)}" onclick="changePage(${currentPage+1})" ${currentPage===total||total===0?'disabled':''}>›</button>`;
            wrap.innerHTML = html;
        }

        function changePage(p) {
            const total = Math.ceil(filteredData.length / perPage);
            if (p < 1 || p > total) return;
            currentPage = p;
            renderTable();
        }

        /* Search */
        document.getElementById('tableSearch').addEventListener('input', function() {
            const q = this.value.toLowerCase();
            filteredData = homeworks.filter(p =>
                String(p.id ?? '').toLowerCase().includes(q) ||
                String(p.subject?.name ?? '').toLowerCase().includes(q) ||
                String(p.class?.name ?? '').toLowerCase().includes(q) ||
                String(p.section?.name ?? '').toLowerCase().includes(q) ||
                String(p.title ?? '').toLowerCase().includes(q) ||
                String(p.homework_date ?? '').toLowerCase().includes(q) ||
                String(p.submission_date ?? '').toLowerCase().includes(q) ||
                String(p.status ?? '').toLowerCase().includes(q)
            );
            currentPage = 1;
            renderTable();
        });

        /* Sort */
        function sortTable(col) { 
            const ths = document.querySelectorAll('.mat-table th');
            ths.forEach(th => th.classList.remove('sorted-asc','sorted-desc'));

            if (sortCol === col) sortAsc = !sortAsc;
            else { sortCol = col; sortAsc = true; }

            ths[col].classList.add(sortAsc ? 'sorted-asc' : 'sorted-desc');

            filteredData.sort((a, b) => {
                const av = getVal(a, col);
                const bv = getVal(b, col);
                if (typeof av === 'number' && typeof bv === 'number') {
                    return sortAsc ? av - bv : bv - av;
                }
                return sortAsc
                    ? String(av).localeCompare(String(bv))
                    : String(bv).localeCompare(String(av));
            });

            renderTable();
        }

        // Open Edit Modal
        function openEditModal(id) {
            @this.call('edit', id);
            new bootstrap.Modal(document.getElementById('openEditModal')).show();
        }

        // Open Delete Modal
        function openDeleteModal(id) {
            deleteTargetId = id;
            const p = homeworks.find(x => x.id === id);
            if (!p) return;
            const displayName = p.name ?? p.name ?? 'This record';
            document.getElementById('deleteName').textContent = `"${displayName}" will be permanently deleted.`;
            new bootstrap.Modal(document.getElementById('deleteModal')).show();
        }

        // Confirm delete
        function confirmDelete() {
            Livewire.dispatch('deleteConfirmed', { id: deleteTargetId });

            const idx = homeworks.findIndex(x => x.id === deleteTargetId);
            if (idx > -1) {
                homeworks.splice(idx, 1);
                filteredData = [...homeworks];
                renderTable();
            }
            bootstrap.Modal.getInstance(document.getElementById('deleteModal')).hide();
        }

        // Save/Update এর পর refresh
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('refresh-list', (data) => {
                const newData = data[0];
                homeworks.length = 0;
                newData.forEach(d => homeworks.push(d));
                filteredData = [...homeworks];
                renderTable();

                document.querySelectorAll('.modal.show').forEach(m => {
                    bootstrap.Modal.getInstance(m)?.hide();
                });
            });
        });

        /* init */
        renderTable();
    </script>
@endpush