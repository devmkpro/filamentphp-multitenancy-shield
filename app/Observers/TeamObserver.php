<?php

namespace App\Observers;

use App\Models\Permission;
use App\Models\Role;
use App\Models\Team;
use Spatie\Permission\PermissionRegistrar;
use Illuminate\Support\Facades\Auth;
use Filament\Facades\Filament;

class TeamObserver
{
    /**
     * Handle the Team "created" event.
     */
    public function created(Team $team): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $rolesAndPermissions = $this->getStandardRolesAndPermissions();

        foreach ($rolesAndPermissions as $roleName => $data) {
            $role = Role::firstOrCreate([
                'name'       => $roleName,
                'guard_name' => 'web',
                'team_id'    => $team->id,
            ]);

            $permissionsForRole = $this->getPermissionsForRole($data);

            foreach ($permissionsForRole as $permissionName) {
                Permission::firstOrCreate([
                    'name'       => $permissionName,
                    'guard_name' => 'web',
                ]);
            }

            $role->givePermissionTo($permissionsForRole);
        }
    
    }

    /**
     * Retorna a estrutura padrão de roles e permissões.
     * Extraído do seu ShieldSeeder para reutilização.
     */
    private function getStandardRolesAndPermissions(): array
    {
        return [
            'super_admin' => [
                'models'             => ['User', 'Role', 'Task', 'Label', 'Team'],
                'custom_permissions' => [
                    'page_ManageSetting',
                    'page_MyProfilePage',
                ],
            ],
        ];
    }

    /**
     * Gera e retorna a lista de nomes de permissões para uma role.
     */
    private function getPermissionsForRole(array $roleData): array
    {
        $models = $roleData['models'];
        if (in_array('all', $models, true)) {
            $models = ['User', 'Role', 'Task', 'Label', 'Team'];
        }

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

        $dynamicPermissions = [];
        foreach ($models as $model) {
            $modelName = strtolower(class_basename($model));
            foreach ($actions as $action) {
                $dynamicPermissions[] = "{$action}_{$modelName}";
            }
        }

        return array_unique(array_merge($dynamicPermissions, $roleData['custom_permissions']));
    }

    /**
     * Handle the Team "updated" event.
     */
    public function updated(Team $team): void
    {
        //
    }

    /**
     * Handle the Team "deleted" event.
     */
    public function deleted(Team $team): void
    {
        //
    }

    /**
     * Handle the Team "restored" event.
     */
    public function restored(Team $team): void
    {
        //
    }

    /**
     * Handle the Team "force deleted" event.
     */
    public function forceDeleted(Team $team): void
    {
        //
    }
}
