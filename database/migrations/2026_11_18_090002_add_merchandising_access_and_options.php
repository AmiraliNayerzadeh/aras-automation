<?php

use App\Models\Settings\LookupType;
use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Data the merchandising module needs to work right after `migrate`, so a
 * deploy doesn't depend on remembering to run a seeder. Idempotent.
 */
return new class extends Migration
{
    public const OPTIONS = [
        'merch_issue' => ['Merchandising: Product Issue', [
            'near_expiry' => ['Near expiry', 'Մոտ է պիտանելիության ժամկետը', 'نزدیک به انقضا'],
            'expired' => ['Expired', 'Ժամկետանց', 'منقضی‌شده'],
            'damaged' => ['Damaged packaging', 'Վնասված փաթեթավորում', 'بسته‌بندی آسیب‌دیده'],
            'dirty' => ['Dirty / dusty', 'Կեղտոտ / փոշոտ', 'کثیف / خاک‌گرفته'],
            'competitor_space' => ['Space taken by competitor', 'Տեղը զբաղեցրել է մրցակիցը', 'جای محصول را رقیب گرفته'],
            'wrong_place' => ['Wrong shelf / category', 'Սխալ դարակ / բաժին', 'قفسه یا بخش اشتباه'],
            'hidden' => ['Hidden / not facing', 'Թաքնված / ճակատով չէ', 'دیده نمی‌شود / رو به مشتری نیست'],
            'low_facings' => ['Too few facings', 'Քիչ ճակատներ', 'تعداد ردیف کم'],
        ]],
        'merch_action' => ['Merchandising: Action Taken', [
            'rearranged' => ['Rearranged shelf', 'Վերադասավորել է դարակը', 'چیدمان مجدد'],
            'fifo' => ['FIFO rotation (older stock to front)', 'FIFO (հինը՝ առջև)', 'جابه‌جایی FIFO (کالای قدیمی جلو)'],
            'cleaned' => ['Cleaned shelf', 'Մաքրել է դարակը', 'تمیز کردن قفسه'],
            'price_tags' => ['Placed price tags', 'Տեղադրել է գնապիտակներ', 'نصب برچسب قیمت'],
            'removed_expired' => ['Removed expired stock', 'Հեռացրել է ժամկետանցը', 'جمع کردن کالای منقضی'],
            'pos_material' => ['Installed stand / poster', 'Տեղադրել է ստենդ / պաստառ', 'نصب استند یا پوستر'],
            'more_space' => ['Gained more shelf space', 'Ավելի շատ տեղ է ստացել', 'گرفتن جای بیشتر در قفسه'],
            'restocked' => ['Restocked from storage', 'Լրացրել է պահեստից', 'پر کردن قفسه از انبار'],
        ]],
        'merch_feedback' => ['Merchandising: Store Feedback', [
            'satisfied' => ['Satisfied', 'Գոհ է', 'راضی'],
            'delivery_complaint' => ['Delivery complaint', 'Բողոք առաքումից', 'شکایت از تحویل'],
            'price_complaint' => ['Price complaint', 'Բողոք գնից', 'شکایت از قیمت'],
            'discount_request' => ['Asked for a discount', 'Զեղչ է խնդրել', 'درخواست تخفیف'],
            'wants_new_products' => ['Wants new products', 'Նոր ապրանքներ է ուզում', 'درخواست محصول جدید'],
        ]],
    ];

    public function up(): void
    {
        $create = Permission::findOrCreate('merchandising.create', 'web');
        $viewAll = Permission::findOrCreate('merchandising.view_all', 'web');
        $stores = Permission::findOrCreate('stores.manage', 'web');

        Role::findOrCreate('merchandiser', 'web')->givePermissionTo($create);

        Role::whereIn('name', ['super-admin', 'admin'])->get()
            ->each(fn (Role $role) => $role->givePermissionTo([$create, $viewAll, $stores]));

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (self::OPTIONS as $code => [$name, $values]) {
            // withTrashed: a soft-deleted row still holds its unique code.
            $type = LookupType::withTrashed()->updateOrCreate(['code' => $code], ['name' => $name, 'is_system' => true]);
            $type->trashed() && $type->restore();
            $order = 0;

            foreach ($values as $valueCode => [$en, $hy, $fa]) {
                // Never overwrite labels an admin has since edited.
                $type->values()->withTrashed()->firstOrCreate(['code' => $valueCode], [
                    'label' => ['en' => $en, 'hy' => $hy, 'fa' => $fa],
                    'color' => 'primary',
                    'sort_order' => ++$order,
                    'is_active' => true,
                ]);
            }
        }
    }

    public function down(): void
    {
        LookupType::whereIn('code', array_keys(self::OPTIONS))->get()->each(function (LookupType $type) {
            $type->values()->forceDelete();
            $type->forceDelete();
        });

        Role::where('name', 'merchandiser')->delete();
        Permission::whereIn('name', ['merchandising.create', 'merchandising.view_all', 'stores.manage'])->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
