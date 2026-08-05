<?php

namespace Database\Seeders;

use App\Models\Organization\Branch;
use App\Models\Organization\Company;
use App\Models\Organization\Department;
use App\Models\Organization\Position;
use App\Models\Organization\Unit;
use Illuminate\Database\Seeder;

class OrgStructureSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::create([
            'name' => 'Aras Automation',
            'code' => 'ARAS',
            'timezone' => 'Asia/Yerevan',
            'default_locale' => 'en',
            'is_active' => true,
        ]);

        $branch = Branch::create([
            'company_id' => $company->id,
            'name' => 'Head Office',
            'code' => 'HQ',
            'is_active' => true,
        ]);

        $management = Department::create([
            'branch_id' => $branch->id,
            'name' => 'Management',
            'code' => 'MGMT',
            'is_active' => true,
        ]);

        $hr = Department::create([
            'branch_id' => $branch->id,
            'name' => 'Human Resources',
            'code' => 'HR',
            'is_active' => true,
        ]);

        $warehouse = Department::create([
            'branch_id' => $branch->id,
            'name' => 'Warehouse',
            'code' => 'WH',
            'is_active' => true,
        ]);

        $warehouseUnit = Unit::create([
            'department_id' => $warehouse->id,
            'name' => 'Inventory',
            'code' => 'WH-INV',
            'is_active' => true,
        ]);

        Position::create([
            'department_id' => $management->id,
            'title' => 'Chief Executive Officer',
            'code' => 'MGMT-CEO',
            'is_active' => true,
        ]);

        Position::create([
            'department_id' => $hr->id,
            'title' => 'HR Manager',
            'code' => 'HR-MGR',
            'is_active' => true,
        ]);

        Position::create([
            'department_id' => $warehouse->id,
            'unit_id' => $warehouseUnit->id,
            'title' => 'Warehouse Clerk',
            'code' => 'WH-CLERK',
            'is_active' => true,
        ]);
    }
}
