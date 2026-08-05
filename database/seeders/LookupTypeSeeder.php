<?php

namespace Database\Seeders;

use App\Models\Settings\LookupType;
use Illuminate\Database\Seeder;

class LookupTypeSeeder extends Seeder
{
    /**
     * @var array<int, string>
     */
    protected array $types = [
        'leave_type' => 'Leave Type',
        'request_type' => 'Request Type',
        'mission_type' => 'Mission Type',
        'task_priority' => 'Task Priority',
        'expense_type' => 'Expense Type',
        'file_category' => 'File Category',
        'generic_status' => 'Generic Status',
    ];

    public function run(): void
    {
        foreach ($this->types as $code => $name) {
            LookupType::updateOrCreate(
                ['code' => $code],
                ['name' => $name, 'is_system' => true]
            );
        }

        $genericStatus = LookupType::where('code', 'generic_status')->firstOrFail();

        $statuses = [
            ['code' => 'active', 'label' => ['en' => 'Active', 'hy' => 'Ակտիվ', 'fa' => 'فعال'], 'color' => 'success', 'sort_order' => 1],
            ['code' => 'inactive', 'label' => ['en' => 'Inactive', 'hy' => 'Ոչ ակտիվ', 'fa' => 'غیرفعال'], 'color' => 'secondary', 'sort_order' => 2],
            ['code' => 'pending', 'label' => ['en' => 'Pending', 'hy' => 'Սպասման մեջ', 'fa' => 'در انتظار'], 'color' => 'warning', 'sort_order' => 3],
            ['code' => 'archived', 'label' => ['en' => 'Archived', 'hy' => 'Արխիվացված', 'fa' => 'بایگانی‌شده'], 'color' => 'dark', 'sort_order' => 4],
        ];

        foreach ($statuses as $status) {
            $genericStatus->values()->updateOrCreate(
                ['code' => $status['code']],
                [
                    'label' => $status['label'],
                    'color' => $status['color'],
                    'sort_order' => $status['sort_order'],
                    'is_active' => true,
                ]
            );
        }
    }
}
