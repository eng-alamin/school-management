{{-- livewire/admin/role-permission/create-component.blade.php --}}

<div>

    <div class="card">

        <div class="mat-card-header header-primary-gradient d-flex justify-content-between align-items-center">
            <div>
                <h5 id="role-add-header-title">Role Registration</h5>
                <p id="role-add-header-subtitle" class="mb-0">Create a new role and assign permissions</p>
            </div>
            <a href="{{ route('admin.role-permission.index') }}" wire:navigate class="btn btn-light">
                <span>
                    <span class="material-icons-round align-middle">arrow_back</span>
                    <span> Back to List</span>
                </span>
            </a>
        </div>

        <!-- ══ ROLE DETAILS ══ -->
        <div class="form-section" style="padding-top:40px">
            <div class="section-heading">
                <span class="material-icons-round">admin_panel_settings</span> Role Details
            </div>

            <div class="row g-4">
                <div class="col-md-6">
                    <div class="input-group input-group-outline">
                        <label class="form-label"><span id="role-add-lbl-name">Role Name</span> <span class="req">*</span></label>
                        <input type="text" wire:model.live.debounce.500ms="name" class="form-control" placeholder=" " autocomplete="off" onfocus="focused(this)" onfocusout="defocused(this)">
                    </div>
                    @error('name') <span class="text-danger">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>

        <!-- ══ PERMISSIONS ══ -->
        <div class="form-section">
            <div class="section-heading">
                <span class="material-icons-round">verified_user</span> Permissions
            </div>

            <div class="table-responsive">
                @include('livewire.admin.role-permission.partials.permission-matrix')
            </div>
            @error('selectedPermissions') <span class="text-danger">{{ $message }}</span> @enderror
            {{-- Array-indexed rule failures land under keys like
                 'selectedPermissions.0', not 'selectedPermissions' — the
                 wildcard pattern below is required to surface them,
                 otherwise a failed selection fails validate() silently
                 with no visible error. --}}
            @error('selectedPermissions.*') <span class="text-danger">{{ $message }}</span> @enderror
        </div>

        <!-- FORM FOOTER -->
        <div class="form-footer">
            <button class="btn btn-secondary" type="button" wire:click="resetForm">
                <span>
                    <span class="material-icons-round" style="font-size:16px">refresh</span>
                    <span>Reset</span>
                </span>
            </button>

            <button class="btn btn-primary" type="button" wire:click="save" wire:loading.attr="disabled" wire:target="save">
                <span wire:loading.remove wire:target="save" style="display: inline-flex;align-items: center;gap: 6px">
                    <span class="material-icons-round">save</span>
                    <span>Save</span>
                </span>

                <span wire:loading wire:target="save">
                    <span class="material-icons-round" style="font-size:16px;animation:spin .7s linear infinite">sync</span>
                    <span>Saving...</span>
                </span>
            </button>
        </div>

    </div>

</div>

@push('styles')
    <style>
        .permission-matrix { width: 100%; border-collapse: collapse; }
        .permission-matrix th, .permission-matrix td {
            padding: 0.65rem 1rem;
            border-bottom: 1px solid #eef0f2;
            vertical-align: middle;
        }
        .permission-matrix thead th {
            background: #f8f9fb;
            font-weight: 600;
            font-size: 0.9rem;
            color: #344054;
        }
        .permission-matrix tbody tr.group-row { cursor: pointer; background: #fcfcfd; }
        .permission-matrix tbody tr.group-row:hover { background: #f5f6f8; }
        .permission-matrix tbody tr.submodule-row td { background: #fff; }
        .permission-check { width: 22px; height: 22px; cursor: pointer; }
        .permission-check:disabled { opacity: 0.15; cursor: not-allowed; }
        .module-name { font-weight: 600; color: #1d2939; }
        .group-caret {
            font-size: 20px;
            color: #667085;
            transition: transform 0.15s ease;
        }
        .group-caret.expanded { transform: rotate(90deg); }
    </style>
@endpush