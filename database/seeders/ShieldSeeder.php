<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use BezhanSalleh\FilamentShield\Support\Utils;
use Spatie\Permission\PermissionRegistrar;

/**
 * ShieldSeeder
 *
 * This seeder is responsible for creating default roles and permissions for the system.
 * It generates dynamic permissions based on the defined models and associates them with the specified roles.
 * Additionally, it removes obsolete permissions that are no longer defined in the seeder.
 */
class ShieldSeeder extends Seeder
{
    /**
     * List of models for which default permissions will be generated.
     */
    private array $models = ['User', 'Role', 'Task', 'Label', 'Team'];

    /**
     * Roles definition:
     * - 'models': models for which to generate default permissions. Use ['all'] for all models in $this->models.
     * - 'custom_permissions': additional required permissions.
     */
    private array $roles = [
        'super_admin' => [
            'models'             => ['all'],
            'custom_permissions' => [
                'page_ManageSetting',
                'page_MyProfilePage',
            ],
        ],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if ($this->command) {
            $this->command->info(__('🛡️  Starting ShieldSeeder...'));
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $definedPermissionsNames = $this->createRolesAndPermissions();

        $permissionModel = Utils::getPermissionModel();
        $allExistingPermissionNames = $permissionModel::pluck('name')->toArray();
        $permissionsToRemove = array_diff($allExistingPermissionNames, $definedPermissionsNames);

        if (!empty($permissionsToRemove)) {
            if ($this->command) {
                $this->command->info(__('🧹 Removing undefined permissions from ShieldSeeder...'));
            }
            $permissionModel::whereIn('name', $permissionsToRemove)->delete();
            if ($this->command) {
                $this->command->info(__(count($permissionsToRemove) . ' obsolete permission(s) removed.'));
            }
        } else {
            if ($this->command) {
                $this->command->info(__('✨ No obsolete permissions to remove.'));
            }
        }

        // Clear the cache again after any modifications
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        if ($this->command) {
            $this->command->info(__('✅ ShieldSeeder executed successfully!'));
        }
    }

    /**
     * Creates roles and associates permissions (dynamic and custom).
     * Returns an array with all permission names defined by the seeder.
     */
    protected function createRolesAndPermissions(): array
    {
        $roleModel       = Utils::getRoleModel();
        $permissionModel = Utils::getPermissionModel();
        $allSeederDefinedPermissions = [];

        $teams = \App\Models\Team::all();

        if ($teams->isEmpty()) {
            return [];
        }

        foreach ($this->roles as $roleName => $data) {
            $modelsForRole = ($data['models'] === ['all']) ? $this->models : $data['models'];
            $dynamicPermissions = $this->generatePermissions($modelsForRole);
            $currentRolePermissions = array_unique(array_merge($dynamicPermissions, $data['custom_permissions']));
            $allSeederDefinedPermissions = array_merge($allSeederDefinedPermissions, $currentRolePermissions);

            // Garante que as permissions existam
            $permissionModelsForRole = collect($currentRolePermissions)->map(function ($permissionName) use ($permissionModel) {
                return $permissionModel::firstOrCreate([
                    'name'       => $permissionName,
                    'guard_name' => 'web',
                ]);
            });

            foreach ($teams as $team) {
                $role = $roleModel::firstOrCreate([
                    'name'       => $roleName,
                    'guard_name' => 'web',
                    'team_id'    => $team->id, // IMPORTANTE para tenancy!
                ]);

                $role->syncPermissions($permissionModelsForRole);
            }
        }

        return array_unique($allSeederDefinedPermissions);
    }


    /**
     * Generates default permissions for the provided models.
     */
    private function generatePermissions(array $models): array
    {
        $actions = [
            'view',
            'view_any',
            'create',
            'update',
            'restore',
            'restore_any',
            'replicate',
            'reorder',
            'delete',
            'delete_any',
            'force_delete',
            'force_delete_any'
        ];

        $permissions = [];
        foreach ($models as $model) {
            $modelName = strtolower(class_basename($model));
            foreach ($actions as $action) {
                $permissions[] = "{$action}_{$modelName}";
            }
        }
        return $permissions;
    }
}
