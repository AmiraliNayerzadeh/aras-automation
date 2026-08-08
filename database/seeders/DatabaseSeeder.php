<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            OrgStructureSeeder::class,
            AdminUserSeeder::class,
            SettingsSeeder::class,
            LookupTypeSeeder::class,
            WarehouseSeeder::class,
        ]);
    }
}
