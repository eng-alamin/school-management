<div class="table-responsive" style="max-height: 560px; overflow-y: auto;">
    <table class="permission-matrix">
        <thead>
            <tr>
                <th style="width: 34%;">Module</th>
                @foreach($actions as $action)
                    <th class="text-center">
                        <div class="form-check d-inline-flex align-items-center gap-2 justify-content-center">
                            <input type="checkbox" class="form-check-input permission-check"
                                   wire:click="toggleActionColumn('{{ $action }}')"
                                   @checked($this->isActionColumnFullySelected($action))>
                            <span>{{ ucfirst($action === 'view' ? 'Read' : ($action === 'edit' ? 'Update' : $action)) }}</span>
                        </div>
                    </th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($matrix as $groupKey => $group)
                @if($group['is_single'])
                    {{-- Standalone module — no sibling to group under, render flat --}}
                    @php($module = $group['children'][0])
                    <tr wire:key="group-{{ $groupKey }}">
                        <td>
                            <div class="form-check d-inline-flex align-items-center gap-2">
                                <input type="checkbox" class="form-check-input permission-check"
                                       wire:click="toggleModule('{{ $module['key'] }}')"
                                       @checked($this->isModuleFullySelected($module['key']))>
                                <span class="module-name">{{ $group['label'] }}</span>
                            </div>
                        </td>
                        @foreach($actions as $action)
                            <td class="text-center">
                                @if($module['actions'][$action])
                                    <input type="checkbox" class="form-check-input permission-check"
                                           wire:model="selectedPermissions"
                                           value="{{ $module['actions'][$action]->name }}">
                                @else
                                    <input type="checkbox" class="form-check-input permission-check" disabled>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @else
                    {{-- Parent module — click row to expand/collapse its sub-modules --}}
                    <tr wire:key="group-{{ $groupKey }}" class="group-row" wire:click="toggleGroupExpand('{{ $groupKey }}')">
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <i class="material-icons-round group-caret {{ $this->isGroupExpanded($groupKey) ? 'expanded' : '' }}">chevron_right</i>
                                <div class="form-check d-inline-flex align-items-center gap-2" onclick="event.stopPropagation();">
                                    <input type="checkbox" class="form-check-input permission-check"
                                           wire:click="toggleGroup('{{ $groupKey }}')"
                                           @checked($this->isGroupFullySelected($groupKey))>
                                    <span class="module-name">{{ $group['label'] }}</span>
                                    @if($this->isGroupPartiallySelected($groupKey))
                                        <span class="badge bg-light text-dark border">partial</span>
                                    @endif
                                </div>
                            </div>
                        </td>
                        @foreach($actions as $action)
                            <td></td>
                        @endforeach
                    </tr>

                    @if($this->isGroupExpanded($groupKey))
                        @foreach($group['children'] as $module)
                            <tr wire:key="module-{{ $module['key'] }}" class="submodule-row">
                                <td class="ps-5">
                                    <div class="form-check d-inline-flex align-items-center gap-2">
                                        <input type="checkbox" class="form-check-input permission-check"
                                               wire:click="toggleModule('{{ $module['key'] }}')"
                                               @checked($this->isModuleFullySelected($module['key']))>
                                        <span>{{ $module['label'] }}</span>
                                    </div>
                                </td>
                                @foreach($actions as $action)
                                    <td class="text-center">
                                        @if($module['actions'][$action])
                                            <input type="checkbox" class="form-check-input permission-check"
                                                   wire:model="selectedPermissions"
                                                   value="{{ $module['actions'][$action]->name }}">
                                        @else
                                            <input type="checkbox" class="form-check-input permission-check" disabled>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    @endif
                @endif
            @endforeach
        </tbody>
    </table>
</div>