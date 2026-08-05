<?php

namespace Database\Seeders;

use App\Models\Organization\Company;
use App\Models\Organization\Position;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::where('code', 'ARAS')->firstOrFail();
        $ceoPosition = Position::where('code', 'MGMT-CEO')->first();

        $admin = User::create([
            'name' => 'System Administrator',
            'email' => 'admin@aras-automation.test',
            'password' => 'password',
            'employee_number' => 'EMP-0001',
            'status' => 'active',
            'employment_type' => 'official',
            'company_id' => $company->id,
            'branch_id' => $ceoPosition?->department?->branch_id,
            'department_id' => $ceoPosition?->department_id,
            'position_id' => $ceoPosition?->id,
            'locale' => 'en',
        ]);

        $admin->forceFill(['email_verified_at' => Carbon::now()])->save();
        $admin->assignRole('super-admin');
    }
}
