<?php

namespace Database\Seeders;

use App\Models\Warehouse\Warehouse;
use Illuminate\Database\Seeder;

class WarehouseSeeder extends Seeder
{
    public function run(): void
    {
        Warehouse::updateOrCreate(
            ['code' => '0'],
            ['name' => 'Main Warehouse', 'is_default' => true, 'is_active' => true]
        );
    }
}
