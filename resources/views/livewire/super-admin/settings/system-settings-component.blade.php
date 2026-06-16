{{-- resources/views/livewire/super-admin/settings/system-settings-component.blade.php --}}

<div>
    <div class="page-header-area">
        <div class="container-fluid">
            <h4>{{ __('System Settings') }}</h4>
        </div>
    </div>

    <div class="container-fluid mt-4">

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        {{-- Tabs --}}
        <ul class="nav nav-tabs mb-4">
            @foreach([
                'general'     => ['icon' => 'fa-gear',          'label' => 'App'],
                'smtp'        => ['icon' => 'fa-envelope',      'label' => 'SMTP'],
                'gateway'     => ['icon' => 'fa-credit-card',   'label' => 'Gateway'],
                'register'    => ['icon' => 'fa-user-plus',     'label' => 'Register'],
                'features'    => ['icon' => 'fa-toggle-on',     'label' => 'Feature Control'],
                'maintenance' => ['icon' => 'fa-screwdriver-wrench', 'label' => 'Maintenance'],
            ] as $tab => $info)
                <li class="nav-item">
                    <button
                        wire:click="$set('activeTab', '{{ $tab }}')"
                        class="nav-link {{ $activeTab === $tab ? 'active' : '' }}"
                    >
                        <i class="fas {{ $info['icon'] }} me-1"></i> {{ $info['label'] }}
                    </button>
                </li>
            @endforeach
        </ul>

        <div class="card">
            <div class="card-body">

                {{-- ===== TAB: APP ===== --}}
                @if($activeTab === 'general')
                    <h5 class="mb-4">{{ __('App Settings') }}</h5>

                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="form-label">{{ __('App Name') }}</label>
                            <input type="text" wire:model="app_name" class="form-control" placeholder="App Name">
                            @error('app_name') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">{{ __('Logo') }}</label>

                            @if($existing_logo && !$logo)
                                <div class="mb-2">
                                    <img src="{{ Storage::url($existing_logo) }}" alt="Current Logo" style="height:60px;" class="rounded border p-1">
                                </div>
                            @endif

                            @if($logo)
                                <div class="mb-2">
                                    <img src="{{ $logo->temporaryUrl() }}" alt="New Logo Preview" style="height:60px;" class="rounded border p-1">
                                </div>
                            @endif

                            <input type="file" wire:model="logo" accept="image/*" class="form-control">
                            <div wire:loading wire:target="logo" class="form-text">{{ __('Uploading...') }}</div>
                            @error('logo') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>
                    </div>
                @endif

                {{-- ===== TAB: SMTP ===== --}}
                @if($activeTab === 'smtp')
                    <h5 class="mb-4">{{ __('SMTP Settings') }}</h5>

                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="form-label">{{ __('SMTP Host') }}</label>
                            <input wire:model="smtp_host" class="form-control" placeholder="smtp.example.com">
                            @error('smtp_host') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('SMTP Port') }}</label>
                            <input type="number" wire:model="smtp_port" class="form-control" placeholder="587">
                            @error('smtp_port') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('SMTP User') }}</label>
                            <input wire:model="smtp_user" class="form-control" placeholder="username@example.com">
                            @error('smtp_user') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('SMTP Password') }}</label>
                            <input type="password" wire:model="smtp_pass" class="form-control" autocomplete="new-password">
                            @error('smtp_pass') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>
                    </div>
                @endif

                {{-- ===== TAB: GATEWAY ===== --}}
                @if($activeTab === 'gateway')
                    <h5 class="mb-4">{{ __('Gateway Settings') }}</h5>

                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="form-label">{{ __('SMS Gateway') }}</label>
                            <input wire:model="sms_gateway" class="form-control" placeholder="SMS Gateway">
                            @error('sms_gateway') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Payment Gateway') }}</label>
                            <input wire:model="payment_gateway" class="form-control" placeholder="Payment Gateway">
                            @error('payment_gateway') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>
                    </div>
                @endif

                {{-- ===== TAB: REGISTER ===== --}}
                @if($activeTab === 'register')
                    <h5 class="mb-4">{{ __('Registration Settings') }}</h5>

                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Registration Type') }}</label>
                            <select wire:model="register_type" class="form-select">
                                <option value="">{{ __('-- select --') }}</option>
                                <option value="free">{{ __('Free') }}</option>
                                <option value="paid">{{ __('Paid') }}</option>
                            </select>
                            @error('register_type') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Registration Fee') }}</label>
                            <input type="number" wire:model="register_fee" class="form-control" placeholder="5000">
                            @error('register_fee') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>
                    </div>
                @endif

                {{-- ===== TAB: FEATURE CONTROL ===== --}}
                @if($activeTab === 'features')
                    <h5 class="mb-4">{{ __('Feature Control') }}</h5>

                    <div class="row g-4">
                        <div class="col-md-4">
                            <div class="form-check form-switch">
                                <input type="checkbox" wire:model="feature_student" class="form-check-input" id="featureStudent">
                                <label class="form-check-label" for="featureStudent">{{ __('Student Module') }}</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check form-switch">
                                <input type="checkbox" wire:model="feature_teacher" class="form-check-input" id="featureTeacher">
                                <label class="form-check-label" for="featureTeacher">{{ __('Teacher Module') }}</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check form-switch">
                                <input type="checkbox" wire:model="feature_fee" class="form-check-input" id="featureFee">
                                <label class="form-check-label" for="featureFee">{{ __('Fee Module') }}</label>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- ===== TAB: MAINTENANCE ===== --}}
                @if($activeTab === 'maintenance')
                    <h5 class="mb-4">{{ __('Maintenance Mode') }}</h5>

                    <div class="form-check form-switch">
                        <input type="checkbox" wire:model="maintenance_mode" class="form-check-input" id="maintenanceToggle">
                        <label class="form-check-label" for="maintenanceToggle">{{ __('Enable Maintenance Mode') }}</label>
                    </div>
                    <small class="text-muted">{{ __('When enabled, the public site will show a maintenance page to visitors.') }}</small>
                @endif

            </div>
        </div>

        {{-- Action Buttons --}}
        <div class="d-flex gap-3 mt-4 mb-5">
            <button wire:click="save" class="btn btn-outline bg-dark text-white px-5">
                <span wire:loading.remove wire:target="save">
                    <i class="fas fa-save me-2"></i>{{ __('Save Changes') }}
                </span>
                <span wire:loading wire:target="save">
                    <i class="fas fa-spinner fa-spin me-2"></i>{{ __('Saving...') }}
                </span>
            </button>
            <button
                wire:click="resetToDefault"
                wire:confirm="{{ __('Are you sure? All system settings will reset to default!') }}"
                class="btn btn-outline-danger"
            >
                <i class="fas fa-undo me-2"></i>{{ __('Reset to Default') }}
            </button>
        </div>

    </div>
</div>