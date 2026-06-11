<div>
    <div class="card">

        <!-- floating header -->
        <div class="mat-card-header header-pink-gradient">
            <h5 id="cardHeaderTitleProfileSetting">Profile Setting</h5>
        </div>

        <div class="container-xl mt-4">

            @include('livewire.admin.profile.navbar', ['user' => $user])

            <!-- START CONTENT -->

            {{-- Profile Details --}}
            <div class="card-custom profile-card p-4 mb-4">

                <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                    <span class="fw-bold fs-5">Profile Details</span>
                </div>

                <form wire:submit.prevent="updateProfile" enctype="multipart/form-data">

                    {{-- Avatar --}}
                    <div class="row mb-4 align-items-center">
                        <label class="col-lg-3 col-form-label text-muted fw-semibold" style="font-size:.88rem">Avatar</label>
                            <div class="col-lg-9">
                                <div class="photo-upload-box">
                                    @if($newAvatar)
                                        <span class="material-icons-round">check_circle</span>
                                        <span class="lbl">File selected</span>
                                    @elseif($avatar)
                                        <img src="{{ asset($avatar) }}" alt="Photo"
                                            style="max-height:80px;max-width:100%;object-fit:contain;margin-bottom:6px">
                                    @else
                                        <span class="material-icons-round">image</span>
                                        <span class="lbl">Click to upload</span>
                                    @endif
                                    <small style="color:#bbb;font-size:.7rem">PNG, JPG up to 2MB</small>
                                    <input type="file" wire:model="newAvatar" accept="image/*">
                                </div>
                                @error('newAvatar') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    {{-- Full Name --}}
                    <div class="row mb-4 align-items-center">
                        <label class="col-lg-3 col-form-label text-muted fw-semibold" style="font-size:.88rem">Full Name <span class="text-danger">*</span></label>
                        <div class="col-lg-9">
                            <input type="text" wire:model.defer="name" class="form-control" placeholder="Full name">
                            @error('name') <span class="text-danger" style="font-size:.78rem">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    {{-- Email --}}
                    <div class="row mb-4 align-items-center">
                        <label class="col-lg-3 col-form-label text-muted fw-semibold" style="font-size:.88rem">Email <span class="text-danger">*</span></label>
                        <div class="col-lg-9">
                            <input type="email" wire:model.defer="email" class="form-control" placeholder="Email address">
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
                            <span wire:loading wire:target="updateProfile" class="spinner-border spinner-border-sm me-1"></span>
                            Save Changes
                        </button>
                    </div>

                </form>
            </div>

            {{-- Sign-in Method / Change Password --}}
            <div class="card-custom profile-card p-4 mb-4">

                <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                    <span class="fw-bold fs-5">Sign-in Method</span>
                </div>

                {{-- Password row --}}
                <div class="d-flex flex-wrap align-items-center mb-4 pb-4 border-bottom" x-data="{ open: false }">
                    <div x-show="!open">
                        <div class="fw-semibold mb-1" style="font-size:.9rem">Password</div>
                        <div class="text-muted" style="font-size:.85rem;letter-spacing:2px">••••••••••••</div>
                    </div>
                    <div class="ms-auto" x-show="!open">
                        <button type="button" class="btn btn-sm btn-outline-secondary" @click="open = true">Reset Password</button>
                    </div>

                    <div class="w-100" x-show="open" x-cloak>
                        <form wire:submit.prevent="updatePassword">
                            <div class="row g-3 mb-3">
                                <div class="col-lg-4">
                                    <label class="form-label">Current Password</label>
                                    <input type="password" wire:model.defer="current_password" class="form-control" placeholder="Current password">
                                    @error('current_password') <span class="text-danger" style="font-size:.78rem">{{ $message }}</span> @enderror
                                </div>
                                <div class="col-lg-4">
                                    <label class="form-label">New Password</label>
                                    <input type="password" wire:model.defer="password" class="form-control" placeholder="New password">
                                    @error('password') <span class="text-danger" style="font-size:.78rem">{{ $message }}</span> @enderror
                                </div>
                                <div class="col-lg-4">
                                    <label class="form-label">Confirm Password</label>
                                    <input type="password" wire:model.defer="password_confirmation" class="form-control" placeholder="Confirm password">
                                </div>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn-outline bg-dark text-white btn-sm px-4">
                                    <span wire:loading wire:target="updatePassword" class="spinner-border spinner-border-sm me-1"></span>
                                    Update Password
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" @click="open = false">Cancel</button>
                            </div>
                        </form>
                    </div>
                </div>

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