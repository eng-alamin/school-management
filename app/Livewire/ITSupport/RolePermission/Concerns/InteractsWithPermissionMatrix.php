<?php

namespace App\Livewire\ITSupport\RolePermission\Concerns;

use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Database\Seeders\InstitutionRolePermissionSeeder;

/**
 * Shared accordion-style permission matrix logic for RolePermission
 * CreateComponent and EditComponent.
 *
 * Grouping is driven by InstitutionRolePermissionSeeder::PARENT_GROUPS
 * (UI-layer only — does not touch permission names in the DB), so this
 * trait works against real, existing `permissions` rows exclusively.
 *
 * NAMING: every permission this trait reads/writes/matches lives under the
 * 'institution.' prefix (InstitutionRolePermissionSeeder::PREFIX) — e.g.
 * 'institution.branch.view'. PARENT_GROUPS / MODULE_LABELS keys themselves
 * stay UNPREFIXED (they're just UI grouping keys like 'branch',
 * 'inventory_unit'); the prefix is added only when building/matching the
 * actual DB permission name. Every LIKE query below is anchored with the
 * prefix so it can never accidentally match a 'ministry.*' permission that
 * happens to share an action name (e.g. both panels have a 'view' action).
 */
trait InteractsWithPermissionMatrix
{
    protected const ACTIONS = ['view', 'create', 'edit', 'delete'];

    public array $selectedPermissions = [];
    public array $expandedGroups = [];

    public function toggleGroupExpand(string $groupKey): void
    {
        if (in_array($groupKey, $this->expandedGroups, true)) {
            $this->expandedGroups = array_values(array_diff($this->expandedGroups, [$groupKey]));
        } else {
            $this->expandedGroups[] = $groupKey;
        }
    }

    public function isGroupExpanded(string $groupKey): bool
    {
        return in_array($groupKey, $this->expandedGroups, true);
    }

    private function childModuleKeys(string $groupKey): array
    {
        return InstitutionRolePermissionSeeder::PARENT_GROUPS[$groupKey]['children'] ?? [];
    }

    /** All permission names belonging to a single module (e.g. "institution.inventory_unit.*"). */
    private function permissionNamesForModule(string $moduleKey): array
    {
        return Permission::where('guard_name', 'web')
            ->where('name', 'like', InstitutionRolePermissionSeeder::PREFIX . "{$moduleKey}.%")
            ->pluck('name')
            ->all();
    }

    /** All permission names belonging to every module inside a parent group. */
    private function permissionNamesForGroup(string $groupKey): array
    {
        $names = [];
        foreach ($this->childModuleKeys($groupKey) as $moduleKey) {
            $names = array_merge($names, $this->permissionNamesForModule($moduleKey));
        }
        return $names;
    }

    /**
     * All permission names for a given action column across every module —
     * anchored to the 'institution.' prefix so this can never match a
     * Ministry permission sharing the same action word (e.g. 'view').
     */
    private function permissionNamesForAction(string $action): array
    {
        return Permission::where('guard_name', 'web')
            ->where('name', 'like', InstitutionRolePermissionSeeder::PREFIX . "%.{$action}")
            ->pluck('name')
            ->all();
    }

    public function isModuleFullySelected(string $moduleKey): bool
    {
        $names = $this->permissionNamesForModule($moduleKey);

        if (empty($names)) {
            return false;
        }

        return count(array_diff($names, $this->selectedPermissions)) === 0;
    }

    public function isGroupFullySelected(string $groupKey): bool
    {
        $names = $this->permissionNamesForGroup($groupKey);

        if (empty($names)) {
            return false;
        }

        return count(array_diff($names, $this->selectedPermissions)) === 0;
    }

    public function isGroupPartiallySelected(string $groupKey): bool
    {
        $names = $this->permissionNamesForGroup($groupKey);

        if (empty($names)) {
            return false;
        }

        $selectedCount = count(array_intersect($names, $this->selectedPermissions));

        return $selectedCount > 0 && $selectedCount < count($names);
    }

    public function isActionColumnFullySelected(string $action): bool
    {
        $names = $this->permissionNamesForAction($action);

        if (empty($names)) {
            return false;
        }

        return count(array_diff($names, $this->selectedPermissions)) === 0;
    }

    /** Toggle every permission belonging to one module row (used for single-child groups & expanded sub-rows). */
    public function toggleModule(string $moduleKey): void
    {
        $names = $this->permissionNamesForModule($moduleKey);

        if ($this->isModuleFullySelected($moduleKey)) {
            $this->selectedPermissions = array_values(array_diff($this->selectedPermissions, $names));
        } else {
            $this->selectedPermissions = array_values(array_unique(array_merge($this->selectedPermissions, $names)));
        }
    }

    /** Toggle every permission belonging to an entire parent group (all its child modules). */
    public function toggleGroup(string $groupKey): void
    {
        $names = $this->permissionNamesForGroup($groupKey);

        if ($this->isGroupFullySelected($groupKey)) {
            $this->selectedPermissions = array_values(array_diff($this->selectedPermissions, $names));
        } else {
            $this->selectedPermissions = array_values(array_unique(array_merge($this->selectedPermissions, $names)));
        }
    }

    /** Toggle every permission belonging to one action column (e.g. all "create"). */
    public function toggleActionColumn(string $action): void
    {
        $names = $this->permissionNamesForAction($action);

        if ($this->isActionColumnFullySelected($action)) {
            $this->selectedPermissions = array_values(array_diff($this->selectedPermissions, $names));
        } else {
            $this->selectedPermissions = array_values(array_unique(array_merge($this->selectedPermissions, $names)));
        }
    }

    public function resetPermissionMatrix(): void
    {
        $this->reset('selectedPermissions', 'expandedGroups');
    }

    /**
     * Builds the full grouped matrix structure consumed by the shared
     * permission-matrix Blade partial.
     */
    public function buildPermissionMatrix(): array
    {
        // Prefix-scoped on purpose: this matrix must only ever be able to
        // render/select 'institution.*' permissions, never a Ministry one.
        $allPermissions = Permission::where('guard_name', 'web')
            ->where('name', 'like', InstitutionRolePermissionSeeder::PREFIX . '%')
            ->get()
            ->keyBy('name');

        $matrix = [];

        foreach (InstitutionRolePermissionSeeder::PARENT_GROUPS as $groupKey => $group) {
            $children = [];

            foreach ($group['children'] as $moduleKey) {
                $row = [
                    'key'     => $moduleKey,
                    'label'   => InstitutionRolePermissionSeeder::MODULE_LABELS[$moduleKey] ?? Str::headline($moduleKey),
                    'actions' => [],
                ];

                foreach (self::ACTIONS as $action) {
                    $permName = InstitutionRolePermissionSeeder::PREFIX . "{$moduleKey}.{$action}";
                    $row['actions'][$action] = $allPermissions->get($permName); // null if module doesn't support this action
                }

                $children[] = $row;
            }

            $matrix[$groupKey] = [
                'label'     => $group['label'],
                'is_single' => count($children) === 1,
                'children'  => $children,
            ];
        }

        return $matrix;
    }
}