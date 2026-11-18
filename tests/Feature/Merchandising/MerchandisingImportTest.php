<?php

use App\Enums\MerchandisingDivision;
use App\Models\Merchandising\Store;
use App\Models\Products\Product;
use App\Models\Products\ProductCategory;
use App\Services\Merchandising\ImportRows;
use App\Services\Merchandising\ProductCatalogImporter;
use App\Services\Merchandising\StoreImporter;
use Database\Seeders\MerchandisingSeeder;
use Illuminate\Support\Facades\Storage;

$product = fn (string $code, string $name, string $group, ?string $barcode = null, string $line = 'LINE') => [
    'code' => $code, 'barcode' => $barcode, 'name' => $name, 'unit' => 'HAT', 'line' => $line, 'main_group' => $group,
];

test('products are created, mapped to divisions and updated on re-import', function () use ($product) {
    $importer = app(ProductCatalogImporter::class);
    $rows = [
        $product('10101', 'LIPTON YELLOW', 'TEA & COFFEE & SWEET', '111'),
        $product('60101', 'ACTIVE SOFTENER', 'WASHING', '222'),
        $product('90001', 'OLD ITEM', 'DELIST'),
    ];

    expect($importer->import($rows))->toMatchArray(['created' => 3, 'updated' => 0]);
    expect($importer->import($rows))->toMatchArray(['created' => 0, 'updated' => 0]);

    expect(ProductCategory::where('title', 'TEA & COFFEE & SWEET')->value('division'))->toBe(MerchandisingDivision::TopSun->value)
        ->and(ProductCategory::where('title', 'WASHING')->value('division'))->toBe(MerchandisingDivision::Unilever->value)
        ->and(Product::where('sku', '90001')->value('is_active'))->toBeFalse();

    $rows[0]['name'] = 'LIPTON YELLOW LABEL';
    expect($importer->import($rows))->toMatchArray(['updated' => 1])
        ->and(Product::where('sku', '10101')->value('title'))->toBe('LIPTON YELLOW LABEL');
});

test('an existing product keeps its price and brand, and its category matches regardless of case', function () use ($product) {
    $category = ProductCategory::create(['title' => 'Tea & Coffee & Sweet', 'is_active' => true]);
    Product::create(['sku' => '10101', 'title' => 'Old name', 'price' => 580, 'category_id' => null]);

    app(ProductCatalogImporter::class)->import([$product('10101', 'LIPTON', 'TEA & COFFEE & SWEET')]);

    $updated = Product::where('sku', '10101')->first();
    expect($updated->price)->toEqual('580.00')
        ->and($updated->category_id)->toBe($category->id)
        ->and(ProductCategory::count())->toBe(1);
});

test('an admin division choice is not overwritten and deleted products stay deleted', function () use ($product) {
    ProductCategory::create(['title' => 'PLASTIC', 'is_active' => true, 'division' => 'topsun']);
    Product::create(['sku' => '10010', 'title' => 'Gone'])->delete();

    $result = app(ProductCatalogImporter::class)->import([$product('10010', 'PLASTIC ROLL', 'PLASTIC')]);

    expect(ProductCategory::where('title', 'PLASTIC')->value('division'))->toBe('topsun')
        ->and($result['skipped'])->toBe(1)
        ->and(Product::withTrashed()->where('sku', '10010')->first()->title)->toBe('Gone');
});

test('a barcode already used by another product is not reassigned', function () use ($product) {
    Product::create(['sku' => '1', 'title' => 'First', 'barcode' => '999']);

    app(ProductCatalogImporter::class)->import([$product('2', 'Second', 'WASHING', '999')]);

    expect(Product::where('sku', '2')->value('barcode'))->toBeNull();
});

test('rows without a code are reported, not imported', function () use ($product) {
    $result = app(ProductCatalogImporter::class)->import([['name' => 'No code', 'main_group' => 'WASHING']]);

    expect($result['skipped'])->toBe(1)->and($result['errors'][0])->toContain('Row 2');
});

test('the shipped catalog seeds once and changes nothing the second time', function () {
    Storage::fake('public');

    $this->seed(MerchandisingSeeder::class);
    $count = Product::count();
    $this->seed(MerchandisingSeeder::class);

    expect($count)->toBe(1143)
        ->and(Product::count())->toBe($count)
        ->and(Product::where('is_active', true)->count())->toBe(1129)
        ->and(Product::whereNotNull('image_path')->count())->toBe(770)
        ->and(Storage::disk('public')->exists(Product::whereNotNull('image_path')->value('image_path')))->toBeTrue();
});

// ---------------------------------------------------------------- stores

$visitorRow = fn (int $id, string $name, ?string $food, ?string $cleaning, array $extra = []) => $extra + [
    'id' => (string) $id, 'name' => $name, 'address' => null, 'phone' => null, 'neighborhood_id' => '4', 'account_type_id' => '5',
    'tax_code' => '123', 'status' => 'active', 'food_acc_code' => $food, 'cleaning_acc_code' => $cleaning,
    'latitude' => '40.1', 'longitude' => '44.5', 'is_vip' => '1',
];

$erpRow = fn (string $code, string $name, array $extra = []) => $extra + [
    'acc_code' => $code, 'account_name' => $name, 'acc_type' => 'x', 'tax_code' => null, 'tel1' => null, 'address1' => null, 'address2' => null,
];

test('a visitor-app export creates stores with mapped region, type and codes', function () use ($visitorRow) {
    $result = app(StoreImporter::class)->import([$visitorRow(1, 'SAS KOMITAS', '11706001', '11810001')]);

    expect($result)->toMatchArray(['format' => 'visitor', 'created' => 1])
        ->and(Store::first())
        ->region->toBe('Kentron')
        ->type->toBe('supermarket')
        ->is_vip->toBeTrue()
        ->food_account_code->toBe('11706001')
        ->cleaning_account_code->toBe('11810001')
        ->legacy_account_id->toBe(1);
});

test('the ERP list only adds unknown customer accounts and fills gaps', function () use ($visitorRow, $erpRow) {
    $importer = app(StoreImporter::class);
    $importer->import([$visitorRow(1, 'SAS KOMITAS', '11706001', '11810001')]);

    $result = $importer->import([
        $erpRow('11706001', 'SAS KOMITAS LLC', ['tel1' => '010 111']),
        $erpRow('11707777', 'NEW SHOP ARABKIR'),
        $erpRow('11819999', 'NEW SHOP ARABKIR'),
        $erpRow('11600001', 'OIL COMPANY'),
        $erpRow('11101001', 'BANK'),
    ]);

    $existing = Store::where('food_account_code', '11706001')->first();
    $new = Store::where('name', 'NEW SHOP ARABKIR')->first();

    expect($result)->toMatchArray(['format' => 'erp', 'created' => 1, 'updated' => 1])
        ->and($existing->name)->toBe('SAS KOMITAS')
        ->and($existing->phone)->toBe('010 111')
        ->and($new->food_account_code)->toBe('11707777')
        ->and($new->cleaning_account_code)->toBe('11819999')
        ->and(Store::count())->toBe(2);
});

test('ERP chain accounts are placed by their code rules', function () use ($erpRow) {
    app(StoreImporter::class)->import([
        $erpRow('12206026', 'ANCUM MARKET'),
        $erpRow('12216026', 'ANCUM HOUSEHOLD'),
        $erpRow('12101001', 'AREV VIP'),
        $erpRow('12401001', 'MAXOIL AVAN'),
    ]);

    expect(Store::where('food_account_code', '12206026')->exists())->toBeTrue()
        ->and(Store::where('cleaning_account_code', '12216026')->exists())->toBeTrue()
        ->and(Store::where('name', 'AREV VIP')->first())->food_account_code->toBe('12101001')->cleaning_account_code->toBe('12101001')
        ->and(Store::where('name', 'MAXOIL AVAN')->value('type'))->toBe('gas_station');
});

test('common names are never merged by guesswork', function () use ($erpRow) {
    app(StoreImporter::class)->import([
        $erpRow('11814318', 'E. XANZADYAN'),
        $erpRow('11814359', 'E. XANZADYAN'),
        $erpRow('11714000', 'E. XANZADYAN'),
    ]);

    expect(Store::count())->toBe(3);
});

test('a file giving one code to two shops is reported, and re-importing changes nothing', function () use ($visitorRow) {
    $rows = [
        $visitorRow(1, 'GYULBENKYAN', '12101006', '12101106'),
        $visitorRow(2, 'KORONA', '12101106', '12101107'),
    ];
    $importer = app(StoreImporter::class);

    $first = $importer->import($rows);
    $second = $importer->import($rows);

    expect($first['conflicts'])->toHaveCount(1)
        ->and($first['conflicts'][0])->toContain('KORONA')
        ->and($second)->toMatchArray(['created' => 0, 'updated' => 0])
        ->and(Store::count())->toBe(1);
});

test('the template updates by code and matches code-less rows by exact name', function () {
    $importer = app(StoreImporter::class);
    $importer->import([['name' => 'Corner Shop', 'food_code' => null, 'cleaning_code' => null, 'city' => 'Gyumri', 'type' => 'Grocery']]);
    $importer->import([['name' => 'Corner Shop', 'food_code' => '11700001', 'cleaning_code' => null, 'city' => 'Gyumri', 'type' => 'grocery']]);

    expect(Store::count())->toBe(1)
        ->and(Store::first())->food_account_code->toBe('11700001')->type->toBe('grocery')->city->toBe('Gyumri');
});

test('unknown columns are refused', function () {
    expect(app(StoreImporter::class)->import([['foo' => 'bar']])['format'])->toBe('unknown');
});

test('CSV headings are normalised the same way as spreadsheet headings', function () {
    $path = tempnam(sys_get_temp_dir(), 'csv');
    file_put_contents($path, "\xEF\xBB\xBF\"Acc code\",\" Account Name\",\"Address2\"\n\"11706001\",\"SHOP\",\"40.1, 44.5\"\n");

    $rows = ImportRows::fromCsv($path);
    @unlink($path);

    expect($rows[0])->toMatchArray(['acc_code' => '11706001', 'account_name' => 'SHOP', 'address2' => '40.1, 44.5']);
});
