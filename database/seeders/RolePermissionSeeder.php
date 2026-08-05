<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    /**
     * @var array<int, string>
     */
    protected array $permissions = [
        'users.view', 'users.create', 'users.edit', 'users.delete',
        'organization.view', 'organization.create', 'organization.edit', 'organization.delete',
        'roles.manage',
        'settings.manage',
        'activitylog.view',
    ];

    public function run(): void
    {
        foreach ($this->permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        $superAdmin = Role::findOrCreate('super-admin', 'web');
        $superAdmin->update(['is_system' => true]);
        $superAdmin->syncPermissions($this->permissions);

        $admin = Role::findOrCreate('admin', 'web');
        $admin->update(['is_system' => true]);
        $admin->syncPermissions([
            'users.view', 'users.create', 'users.edit', 'users.delete',
            'organization.view', 'organization.create', 'organization.edit', 'organization.delete',
            'roles.manage', 'settings.manage', 'activitylog.view',
        ]);

        $hr = Role::findOrCreate('hr', 'web');
        $hr->syncPermissions(['users.view', 'users.create', 'users.edit', 'organization.view']);

        $warehouse = Role::findOrCreate('warehouse', 'web');
        $warehouse->syncPermissions(['organization.view']);

        Role::findOrCreate('employee', 'web');
    }
}
