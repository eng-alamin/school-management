<div>
    @if($isImpersonating)
    <div class="child-switcher d-flex align-items-center gap-2">

        @if(session('error'))
            <div class="alert alert-danger alert-sm py-1 px-2 mb-0" style="font-size:12px;">
                {{ session('error') }}
            </div>
        @endif

        @if(count($siblings) > 1)
            <div class="dropdown">
                <button class="btn btn-sm btn-outline dropdown-toggle child-switcher-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <span class="material-icons-round" style="font-size:15px;vertical-align:middle;">group</span>
                    Switch Child
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    @foreach($siblings as $sibling)
                        <li>
                            <button
                                type="button"
                                class="dropdown-item d-flex align-items-center gap-2 {{ $sibling->id === $currentStudentId ? 'active' : '' }}"
                                wire:click="switchChild({{ $sibling->id }})"
                                wire:loading.attr="disabled"
                                wire:target="switchChild({{ $sibling->id }})"
                            >
                                <span class="material-icons-round" style="font-size:16px;">person</span>
                                <span>
                                    {{ $sibling->name }}
                                    @if($sibling->id === $currentStudentId)
                                        <small class="text-muted">(current)</small>
                                    @endif
                                </span>
                            </button>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        <button
            type="button"
            wire:click="backToParent"
            wire:loading.attr="disabled"
            wire:target="backToParent"
            class="btn btn-sm btn-outline child-switcher-btn"
        >
            <span wire:loading.remove wire:target="backToParent" class="d-flex align-items-center gap-1">
                <span class="material-icons-round" style="font-size:15px;">arrow_back</span>
                Back to Parent
            </span>
        </button>

    </div>

    @push('scripts')
        <script>
            document.addEventListener('livewire:init', () => {
                Livewire.hook('request', ({ fail }) => {
                    fail(({ status, content }) => {
                        console.error('Livewire request failed:', status, content);
                    });
                });
            });
        </script>
    @endpush

    @push('styles')
        <style>
            .child-switcher-btn {
                border-radius: 8px;
                font-size: 12.5px;
                font-weight: 500;
                padding: 6px 12px;
            }
            .dropdown-item.active, .dropdown-item:active {
                background-color: #1a1a1a !important;
            }
        </style>
    @endpush
@endif
</div>