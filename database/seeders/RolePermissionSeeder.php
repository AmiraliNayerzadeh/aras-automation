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
        'leaves.create', 'leaves.view_all', 'leaves.approve', 'leaves.approve_any', 'leaves.cancel_any',
        'missions.create', 'missions.view_all', 'missions.approve', 'missions.approve_any', 'missions.cancel_any',
        'business-partners.view', 'business-partners.create', 'business-partners.edit', 'business-partners.delete',
        'orders.view', 'orders.create', 'orders.edit', 'orders.delete', 'orders.advance', 'orders.advance_any',
        'products.view', 'products.create', 'products.edit', 'products.delete',
        'warehouse.view', 'warehouse.manage',
        'stock.view', 'stock.record',
        'tasks.create', 'tasks.view_all', 'tasks.manage',
        'files.create', 'files.view_all', 'files.manage',
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
            'leaves.view_all', 'leaves.approve_any', 'leaves.cancel_any',
            'missions.view_all', 'missions.approve_any', 'missions.cancel_any',
            'business-partners.view', 'business-partners.create', 'business-partners.edit', 'business-partners.delete',
            'orders.view', 'orders.create', 'orders.edit', 'orders.delete', 'orders.advance', 'orders.advance_any',
            'products.view', 'products.create', 'products.edit', 'products.delete',
            'warehouse.view', 'warehouse.manage',
            'stock.view', 'stock.record',
            'tasks.create', 'tasks.view_all', 'tasks.manage',
            'files.create', 'files.manage',
        ]);

        $hr = Role::findOrCreate('hr', 'web');
        $hr->syncPermissions([
            'users.view', 'users.create', 'users.edit', 'organization.view',
            'leaves.view_all', 'leaves.create', 'leaves.approve',
            'missions.view_all', 'missions.create', 'missions.approve',
            'tasks.create', 'files.create',
        ]);

        $warehouse = Role::findOrCreate('warehouse', 'web');
        $warehouse->syncPermissions([
            'organization.view', 'leaves.create', 'missions.create',
            'orders.view', 'orders.advance',
            'products.view', 'warehouse.view', 'stock.view', 'stock.record',
            'tasks.create', 'files.create',
        ]);

        $employee = Role::findOrCreate('employee', 'web');
        $employee->syncPermissions(['leaves.create', 'leaves.approve', 'missions.create', 'missions.approve', 'tasks.create', 'files.create']);
    }
}
