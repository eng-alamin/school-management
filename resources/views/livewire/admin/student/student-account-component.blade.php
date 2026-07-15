<div>
    
    <div class="card border-0 bg-transparent">

        <div class="container-xl mt-4">

            @include('livewire.admin.student.student-navbar', ['student' => $student])

            <!-- START CONTENT -->

            {{-- Account Details --}}
            <div class="card account-card p-4 mb-4">

                <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                    <span class="fw-bold fs-5">Account Details</span>
                </div>

                <form wire:submit.prevent="updateAccount">

                    {{-- Name --}}
                    <div class="row mb-4 align-items-center">
                        <label class="col-lg-3 col-form-label text-muted fw-semibold" style="font-size:.88rem">Name <span class="text-danger">*</span></label>
                        <div class="col-lg-9">
                            <input type="text" wire:model.defer="name" class="form-control" placeholder="Full name">
                            @error('name') <span class="text-danger" style="font-size:.78rem">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    {{-- Username --}}
                    <div class="row mb-4 align-items-center">
                        <label class="col-lg-3 col-form-label text-muted fw-semibold" style="font-size:.88rem">Username <span class="text-danger">*</span></label>
                        <div class="col-lg-9">
                            <input type="text" wire:model.defer="username" class="form-control" placeholder="User Name">
                            @error('username') <span class="text-danger" style="font-size:.78rem">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    {{-- Email --}}
                    <div class="row mb-4 align-items-center">
                        <label class="col-lg-3 col-form-label text-muted fw-semibold" style="font-size:.88rem">Email <span class="text-danger">*</span></label>
                        <div class="col-lg-9">
                            <input type="email" wire:model.defer="email" class="form-control" placeholder="Email Address">
                            @error('email') <span class="text-danger" style="font-size:.78rem">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    {{-- Phone --}}
                    <div class="row mb-4 align-items-center">
                        <label class="col-lg-3 col-form-label text-muted fw-semibold" style="font-size:.88rem">Phone</label>
                        <div class="col-lg-9">
                            <input type="tel" wire:model.defer="phone" class="form-control" placeholder="Phone number">
                            @error('phone') <span class="text-danger" style="font-size:.78rem">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="d-flex justify-content-end pt-2 border-top">
                        <button type="submit" class="btn-outline bg-dark text-white btn-sm px-4">
                            <span wire:loading wire:target="updateAccount" class="spinner-border spinner-border-sm me-1"></span>
                            Save Changes
                        </button>
                    </div>

                </form>
            </div>

            {{-- Password Reset --}}
            <div class="card account-card p-4 mb-4">

                <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                    <span class="fw-bold fs-5">Reset Password</span>
                </div>

                <form wire:submit.prevent="updatePassword">
                    <div class="row g-3 mb-3">
                        <div class="col-lg-6">
                            <label class="form-label">New Password</label>
                            <input type="password" wire:model.defer="password" class="form-control" placeholder="New password">
                            @error('password') <span class="text-danger" style="font-size:.78rem">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-lg-6">
                            <label class="form-label">Confirm Password</label>
                            <input type="password" wire:model.defer="password_confirmation" class="form-control" placeholder="Confirm password">
                        </div>
                    </div>
                    <div class="d-flex justify-content-end pt-2 border-top">
                        <button type="submit" class="btn-outline bg-dark text-white btn-sm px-4">
                            <span wire:loading wire:target="updatePassword" class="spinner-border spinner-border-sm me-1"></span>
                            Reset Password
                        </button>
                    </div>
                </form>

            </div>

            <!-- END CONTENT -->

        </div>

    </div>
</div>

@push('styles')
    <style>
        :root {
            --primary: rgba(33, 37, 41);
            --primary-light: rgba(239,84,84,.12);
        }

        .card { border: 1px solid var(--border); border-radius: 12px; box-shadow: 0 1px 4px rgba(0,0,0,.04); }

        .form-label { font-size: .8rem; font-weight: 600; color: var(--text-muted); margin-bottom: 4px; }
        .form-control, .form-select {
            border-radius: 8px; border: 1px solid var(--border);
            font-size: .875rem; padding: .45rem .75rem;
            transition: border-color .2s, box-shadow .2s;
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--primary); box-shadow: 0 0 0 3px var(--primary-light);
        }
        .btn-sm { font-size: .78rem; padding: .3rem .65rem; border-radius: 6px; }
    </style>
@endpush