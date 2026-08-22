{{-- resources/views/livewire/admin/setting/feature-component.blade.php --}}
<div>
    <div class="card">

        {{-- floating header --}}
        <div class="mat-card-header header-primary-gradient">
            <h5 id="cardHeaderTitleAllsections">Feature Control</h5>
            <p id="cardHeaderSubtitle">Manage system feature toggles and configurations.</p>
        </div>

        <div class="card-body">
            <div class="row g-4 my-4 px-4">
                @foreach ($features as $key => $feature)
                    <div class="col-md-4 col-sm-6">
                        <div class="d-flex align-items-center gap-2">
                            <div class="form-check form-switch mb-0">
                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    role="switch"
                                    id="feature-{{ $key }}"
                                    wire:click="toggleFeature('{{ $key }}')"
                                    @checked($feature['is_active'])
                                    style="width: 2.5em; height: 1.3em; cursor: pointer;"
                                >
                            </div>
                            <label class="form-check-label mb-0" for="feature-{{ $key }}">
                                {{ $feature['label'] }}
                            </label>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>