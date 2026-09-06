<div>

    <div class="card">

      <!-- floating header -->
      <div class="mat-card-header header-primary-gradient">
        <h5 id="cardHeaderTitleAllsections">Grades Range</h5>
        <p id="cardHeaderSubtitle">Manage exam grades, create, update, and organize academic grades easily.</p>
      </div>

        <div class="card-header border-0">
            <!-- toolbar -->
            <div class="card-toolbar">
                {{-- Left side --}}
                <div class="card-toolbar-title">
                    <!-- search in table -->
                    <div style="position:relative;display:inline-flex;align-items:center">
                        <span class="material-icons-round" style="position:absolute;left:10px;font-size:17px;color:var(--muted);pointer-events:none">search</span>
                        <input type="text" wire:model.live.debounce.300ms="search" id="tableSearch" placeholder="Search" class="tb-search"/>
                    </div>
                </div>

                <!-- Right Side -->
                @if($grades->total() > 10)
                    <div class="col-md-2">
                        <div class="input-group input-group-outline">
                            <select class="form-select form-select-sm" wire:model.live="perPage">
                                <option value="10">10 / page</option>
                                <option value="25">25 / page</option>
                                <option value="50">50 / page</option>
                            </select>
                        </div>
                    </div>
                @endif
                <button class="btn btn-primary" wire:click="openCreate">
                    <span>
                        <span class="material-icons-round">add</span> 
                        <span id="newSectionBtn">Add Grade</span>
                    </span>
                </button>

            </div>
        </div>

        <div class="card-body pt-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>SL</th>
                            <th wire:click="sortBy('name')" style="cursor:pointer">Name @if($sortField === 'name') {!! $sortDirection === 'asc' ? '↑' : '↓' !!} @endif </th>
                            <th wire:click="sortBy('grade_point')" style="cursor:pointer">Grade Point @if($sortField === 'grade_point') {!! $sortDirection === 'asc' ? '↑' : '↓' !!} @endif </th>
                            <th wire:click="sortBy('min_percentage')" style="cursor:pointer">Min Percentage @if($sortField === 'min_percentage') {!! $sortDirection === 'asc' ? '↑' : '↓' !!} @endif </th>
                            <th wire:click="sortBy('max_percentage')" style="cursor:pointer">Max Percentage @if($sortField === 'max_percentage') {!! $sortDirection === 'asc' ? '↑' : '↓' !!} @endif </th>
                            <th wire:click="sortBy('remarks')" style="cursor:pointer">Remarks @if($sortField === 'remarks') {!! $sortDirection === 'asc' ? '↑' : '↓' !!} @endif </th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($grades as $i => $grade)
                        <tr>
                            <td class="text-muted">{{ $grades->firstItem() + $i }}</td>
                            <td> {{ $grade->name }} </td>
                            <td> {{ $grade->grade_point }} </td>
                            <td> {{ $grade->min_percentage }} </td>
                            <td> {{ $grade->max_percentage }} </td>
                            <td> {{ $grade->remarks }} </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <button class="act-btn edit" title="Edit" wire:click="openEdit({{ $grade->id }})">
                                        <span class="material-icons-round">drive_file_rename_outline</span>
                                    </button>
                                    <button class="act-btn delete" title="Delete" wire:click="confirmDeleteRecord({{ $grade->id }})">
                                        <span class="material-icons-round">delete</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox display-5 d-block mb-2 opacity-25"></i>
                                No grades found. <a href="#" wire:click.prevent="openCreate">Create one now</a>.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card-footer border-0 bg-white d-flex align-items-center justify-content-between flex-wrap gap-2 py-2 px-3">
            <small class="text-muted">Showing {{ $grades->firstItem() ?? 0 }}–{{ $grades->lastItem() ?? 0 }} of {{ $grades->total() }}</small>
           {{ $grades->links('vendor.pagination.custom') }}
        </div>
        
    </div>

    {{-- ===== CREATE/EDIT MODAL ===== --}}
    @if($showModal)
        <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,.5);" wire:ignore.self>
            <div class="modal-dialog modal-md modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header border-0">
                        <h5 class="modal-title">
                            {{ $editId ? 'Edit' : 'Create' }} Exam Grade
                        </h5>
                        <button type="button" class="btn-close" wire:click="$set('showModal',false)"></button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit.prevent="save">
                            <div class="row g-3">
                                <div class="col-md-12">
                                    <div class="input-group input-group-outline">
                                        <label class="form-label">Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('name') is-invalid @enderror" wire:model.defer="name">
                                    </div>
                                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-12">
                                    <div class="input-group input-group-outline">
                                        <label class="form-label">Grade Point <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control @error('grade_point') is-invalid @enderror" wire:model.defer="grade_point">
                                    </div>
                                    @error('grade_point') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-12">
                                    <div class="input-group input-group-outline">
                                        <label class="form-label">Min Percentage <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control @error('min_percentage') is-invalid @enderror" wire:model.defer="min_percentage">
                                    </div>
                                    @error('min_percentage') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-12">
                                    <div class="input-group input-group-outline">
                                        <label class="form-label">Max Percentage <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control @error('max_percentage') is-invalid @enderror" wire:model.defer="max_percentage">
                                    </div>
                                    @error('max_percentage') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-12">
                                    <div class="input-group input-group-outline">
                                        <label class="form-label">Remarks</label>
                                        <input type="text" class="form-control @error('remarks') is-invalid @enderror" wire:model.defer="remarks">
                                    </div>
                                    @error('remarks') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-light" wire:click="$set('showModal',false)">Cancel</button>
                        <button type="button" class="btn btn-primary" wire:click="save" wire:loading.attr="disabled">
                            <span wire:loading wire:target="save" class="spinner-border spinner-border-sm me-1"></span>
                            {{ $editId ? 'Update' : 'Create' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- ===== DELETE CONFIRM ===== --}}
    @if($confirmDelete)
        <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,.5);">
            <div class="modal-dialog modal-sm">
                <div class="modal-content">
                    <div class="modal-body text-center py-4">
                        <div style="width:56px;height:56px;border-radius:50%;background:#fee2e2;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
                            <i class="bi bi-exclamation-triangle text-danger" style="font-size:1.5rem;"></i>
                        </div>
                        <h6 class="fw-700">Delete Grade?</h6>
                        <p class="text-muted small">This action cannot be undone.</p>
                    </div>
                    <div class="modal-footer justify-content-center border-0 pt-0">
                        <button class="btn btn-light btn-sm" wire:click="$set('confirmDelete',false)">Cancel</button>
                        <button class="btn btn-danger btn-sm" wire:click="deleteRecord">
                            <span wire:loading wire:target="deleteRecord" class="spinner-border spinner-border-sm me-1"></span>
                            Delete
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>