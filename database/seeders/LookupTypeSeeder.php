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
        'order_stage' => 'Order Stage',
        'transport_method' => 'Transport Method',
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

        $leaveType = LookupType::where('code', 'leave_type')->firstOrFail();

        $leaveTypes = [
            ['code' => 'annual', 'label' => ['en' => 'Annual Leave', 'hy' => 'Ամենամյա արձակուրդ', 'fa' => 'مرخصی سالانه'], 'color' => 'primary', 'sort_order' => 1],
            ['code' => 'sick', 'label' => ['en' => 'Sick Leave', 'hy' => 'Հիվանդության արձակուրդ', 'fa' => 'مرخصی استعلاجی'], 'color' => 'danger', 'sort_order' => 2],
            ['code' => 'unpaid', 'label' => ['en' => 'Unpaid Leave', 'hy' => 'Չվճարվող արձակուրդ', 'fa' => 'مرخصی بدون حقوق'], 'color' => 'secondary', 'sort_order' => 3],
            ['code' => 'emergency', 'label' => ['en' => 'Emergency Leave', 'hy' => 'Արտակարգ արձակուրդ', 'fa' => 'مرخصی اضطراری'], 'color' => 'warning', 'sort_order' => 4],
            ['code' => 'bereavement', 'label' => ['en' => 'Bereavement Leave', 'hy' => 'Սգո արձակուրդ', 'fa' => 'مرخصی فوت بستگان'], 'color' => 'dark', 'sort_order' => 5],
        ];

        foreach ($leaveTypes as $type) {
            $leaveType->values()->updateOrCreate(
                ['code' => $type['code']],
                [
                    'label' => $type['label'],
                    'color' => $type['color'],
                    'sort_order' => $type['sort_order'],
                    'is_active' => true,
                ]
            );
        }

        $missionType = LookupType::where('code', 'mission_type')->firstOrFail();

        $missionTypes = [
            ['code' => 'domestic', 'label' => ['en' => 'Domestic', 'hy' => 'Ներքին', 'fa' => 'داخلی'], 'color' => 'info', 'sort_order' => 1],
            ['code' => 'international', 'label' => ['en' => 'International', 'hy' => 'Միջազգային', 'fa' => 'خارجی'], 'color' => 'primary', 'sort_order' => 2],
            ['code' => 'client_visit', 'label' => ['en' => 'Client Visit', 'hy' => 'Հաճախորդի այց', 'fa' => 'بازدید مشتری'], 'color' => 'success', 'sort_order' => 3],
        ];

        foreach ($missionTypes as $type) {
            $missionType->values()->updateOrCreate(
                ['code' => $type['code']],
                [
                    'label' => $type['label'],
                    'color' => $type['color'],
                    'sort_order' => $type['sort_order'],
                    'is_active' => true,
                ]
            );
        }

        $fileCategory = LookupType::where('code', 'file_category')->firstOrFail();

        $fileCategories = [
            ['code' => 'id_card', 'label' => ['en' => 'ID Card', 'hy' => 'Անձնագիր', 'fa' => 'کارت شناسایی'], 'color' => 'primary', 'sort_order' => 1],
            ['code' => 'contract', 'label' => ['en' => 'Contract', 'hy' => 'Պայմանագիր', 'fa' => 'قرارداد'], 'color' => 'info', 'sort_order' => 2],
            ['code' => 'certificate', 'label' => ['en' => 'Certificate', 'hy' => 'Վկայական', 'fa' => 'گواهی'], 'color' => 'success', 'sort_order' => 3],
            ['code' => 'other', 'label' => ['en' => 'Other', 'hy' => 'Այլ', 'fa' => 'سایر'], 'color' => 'secondary', 'sort_order' => 4],
        ];

        foreach ($fileCategories as $category) {
            $fileCategory->values()->updateOrCreate(
                ['code' => $category['code']],
                [
                    'label' => $category['label'],
                    'color' => $category['color'],
                    'sort_order' => $category['sort_order'],
                    'is_active' => true,
                ]
            );
        }

        $orderStage = LookupType::where('code', 'order_stage')->firstOrFail();

        $orderStages = [
            ['code' => 'order_registered', 'label' => ['en' => 'Order Registered', 'hy' => 'Պատվերի գրանցում', 'fa' => 'ثبت سفارش'], 'color' => 'neutral', 'sort_order' => 10],
            ['code' => 'approved', 'label' => ['en' => 'Approved', 'hy' => 'Հաստատված', 'fa' => 'تأیید شده'], 'color' => 'success', 'sort_order' => 20],
            ['code' => 'payment_recorded', 'label' => ['en' => 'Payment Recorded', 'hy' => 'Վճարումը գրանցված է', 'fa' => 'پرداخت ثبت شد'], 'color' => 'info', 'sort_order' => 30],
            ['code' => 'preparation', 'label' => ['en' => 'Preparation', 'hy' => 'Պատրաստում', 'fa' => 'آماده‌سازی'], 'color' => 'warning', 'sort_order' => 40],
            ['code' => 'shipping', 'label' => ['en' => 'Shipping', 'hy' => 'Առաքում', 'fa' => 'حمل و نقل'], 'color' => 'primary', 'sort_order' => 50],
            ['code' => 'customs', 'label' => ['en' => 'Customs', 'hy' => 'Մաքսային ձևակերպում', 'fa' => 'گمرک'], 'color' => 'cyan', 'sort_order' => 60],
            ['code' => 'clearance', 'label' => ['en' => 'Clearance', 'hy' => 'Մաքսային ազատում', 'fa' => 'ترخیص کالا'], 'color' => 'lilac', 'sort_order' => 70],
            ['code' => 'warehouse', 'label' => ['en' => 'Warehouse', 'hy' => 'Պահեստ', 'fa' => 'انبار'], 'color' => 'primary', 'sort_order' => 80],
            ['code' => 'quality_control', 'label' => ['en' => 'Quality Control', 'hy' => 'Որակի հսկողություն', 'fa' => 'کنترل کیفیت'], 'color' => 'warning', 'sort_order' => 90],
            ['code' => 'distribution', 'label' => ['en' => 'Distribution', 'hy' => 'Բաշխում', 'fa' => 'توزیع'], 'color' => 'info', 'sort_order' => 100],
            ['code' => 'delivered', 'label' => ['en' => 'Delivered', 'hy' => 'Առաքված', 'fa' => 'تحویل داده شد'], 'color' => 'success', 'sort_order' => 110],
            ['code' => 'closed', 'label' => ['en' => 'Closed', 'hy' => 'Փակված', 'fa' => 'بسته شده'], 'color' => 'success', 'sort_order' => 120],
        ];

        foreach ($orderStages as $stage) {
            $orderStage->values()->updateOrCreate(
                ['code' => $stage['code']],
                [
                    'label' => $stage['label'],
                    'color' => $stage['color'],
                    'sort_order' => $stage['sort_order'],
                    'is_active' => true,
                ]
            );
        }

        $transportMethod = LookupType::where('code', 'transport_method')->firstOrFail();

        $transportMethods = [
            ['code' => 'road', 'label' => ['en' => 'Road', 'hy' => 'Ցամաքային', 'fa' => 'جاده‌ای'], 'color' => 'primary', 'sort_order' => 1],
            ['code' => 'air', 'label' => ['en' => 'Air', 'hy' => 'Օդային', 'fa' => 'هوایی'], 'color' => 'info', 'sort_order' => 2],
            ['code' => 'sea', 'label' => ['en' => 'Sea', 'hy' => 'Ծովային', 'fa' => 'دریایی'], 'color' => 'cyan', 'sort_order' => 3],
            ['code' => 'rail', 'label' => ['en' => 'Rail', 'hy' => 'Երկաթուղային', 'fa' => 'ریلی'], 'color' => 'warning', 'sort_order' => 4],
        ];

        foreach ($transportMethods as $method) {
            $transportMethod->values()->updateOrCreate(
                ['code' => $method['code']],
                [
                    'label' => $method['label'],
                    'color' => $method['color'],
                    'sort_order' => $method['sort_order'],
                    'is_active' => true,
                ]
            );
        }

        $taskPriority = LookupType::where('code', 'task_priority')->firstOrFail();

        $taskPriorities = [
            ['code' => 'low', 'label' => ['en' => 'Low', 'hy' => 'Ցածր', 'fa' => 'کم'], 'color' => 'neutral', 'sort_order' => 1],
            ['code' => 'medium', 'label' => ['en' => 'Medium', 'hy' => 'Միջին', 'fa' => 'متوسط'], 'color' => 'info', 'sort_order' => 2],
            ['code' => 'high', 'label' => ['en' => 'High', 'hy' => 'Բարձր', 'fa' => 'زیاد'], 'color' => 'warning', 'sort_order' => 3],
            ['code' => 'urgent', 'label' => ['en' => 'Urgent', 'hy' => 'Հրատապ', 'fa' => 'فوری'], 'color' => 'danger', 'sort_order' => 4],
        ];

        foreach ($taskPriorities as $priority) {
            $taskPriority->values()->updateOrCreate(
                ['code' => $priority['code']],
                [
                    'label' => $priority['label'],
                    'color' => $priority['color'],
                    'sort_order' => $priority['sort_order'],
                    'is_active' => true,
                ]
            );
        }
    }
}
