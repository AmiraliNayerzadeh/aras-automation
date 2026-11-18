<?php

use App\Models\FileManager\FileEntry;
use App\Models\Merchandising\Store;
use App\Models\Merchandising\Upload;
use App\Models\Merchandising\Visit;
use App\Models\Products\Product;
use App\Models\Products\ProductCategory;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;

beforeEach(function () {
    Carbon::setTestNow('2026-11-18 18:00:00');
    Storage::fake('public');
    $this->seed(RolePermissionSeeder::class);

    $this->admin = User::factory()->create(['status' => 'active']);
    $this->admin->assignRole('super-admin');
    $this->ani = User::factory()->create(['status' => 'active', 'name' => 'Ani']);
    $this->ani->assignRole('merchandiser');

    $food = ProductCategory::create(['title' => 'KETCHUP', 'division' => 'topsun', 'is_active' => true]);
    $home = ProductCategory::create(['title' => 'WASHING', 'division' => 'unilever', 'is_active' => true]);
    $ketchup = Product::create(['sku' => '30101', 'title' => 'TOP SUN KETCHUP', 'line' => 'TOP SUN KETCHUP', 'category_id' => $food->id]);
    $persil = Product::create(['sku' => '60101', 'title' => 'PERSIL 3KG', 'line' => 'PERSIL', 'category_id' => $home->id]);
    $this->store = Store::create(['name' => 'SAS Komitas', 'status' => 'active', 'region' => 'Arabkir', 'type' => 'supermarket']);

    $visit = Visit::create([
        'submission_key' => (string) Str::uuid(), 'user_id' => $this->ani->id, 'store_id' => $this->store->id,
        'visited_at' => '2026-11-18 10:00', 'divisions' => ['topsun', 'unilever'], 'actions' => ['rearranged'], 'store_feedback' => ['price_complaint'],
        'needs_order' => true, 'note' => 'Busy day',
    ]);
    $visit->items()->createMany([
        ['product_id' => $ketchup->id, 'division' => 'topsun', 'stock_status' => 'out_of_stock', 'price_tag' => 'missing', 'issues' => ['dirty'], 'needs_order' => true],
        ['product_id' => $persil->id, 'division' => 'unilever', 'stock_status' => 'in_stock', 'price_tag' => 'correct', 'shelf_position' => 'eye_level'],
    ]);

    $path = UploadedFile::fake()->image('before.jpg', 800, 600)->store('files/1', 'public');
    $entry = FileEntry::create(['owner_id' => $this->ani->id, 'original_name' => 'before.jpg', 'file_path' => $path, 'mime_type' => 'image/jpeg', 'size_bytes' => 1]);
    Upload::create(['token' => (string) Str::uuid(), 'user_id' => $this->ani->id, 'visit_id' => $visit->id, 'file_entry_id' => $entry->id, 'kind' => 'before', 'original_name' => 'before.jpg', 'mime_type' => 'image/jpeg', 'size_bytes' => 1, 'completed_at' => now()]);

    Visit::create(['submission_key' => (string) Str::uuid(), 'user_id' => $this->ani->id, 'store_id' => $this->store->id, 'visited_at' => '2026-11-10 10:00']);
});

afterEach(fn () => Carbon::setTestNow());

test('the report is for reviewers only', function () {
    $this->actingAs($this->ani)->get(route('merchandising.reports.index'))->assertForbidden();
});

test('the daily report shows today’s figures in English for everyone', function () {
    $this->actingAs($this->admin)->withSession(['locale' => 'fa'])->get(route('merchandising.reports.index'))
        ->assertOk()
        ->assertSee('Merchandising report')
        ->assertSee('Out-of-stock rate')
        ->assertViewHas('kpis', fn ($k) => $k['visits'] === 1 && $k['products_checked'] === 2 && $k['out_of_stock_rate'] === 50.0 && $k['price_tag_ok_rate'] === 50.0)
        ->assertViewHas('issueCounts', ['dirty' => 1])
        ->assertViewHas('topOut', fn ($rows) => $rows->first()['name'] === 'TOP SUN KETCHUP');
});

test('a range includes earlier visits', function () {
    $this->actingAs($this->admin)->get(route('merchandising.reports.index', ['date_from' => '2026-11-01', 'date_to' => '2026-11-18']))
        ->assertViewHas('kpis', fn ($k) => $k['visits'] === 2);
});

test('a division report counts only that division’s products', function () {
    $this->actingAs($this->admin)->get(route('merchandising.reports.index', ['division' => 'unilever']))
        ->assertOk()
        ->assertViewHas('kpis', fn ($k) => $k['visits'] === 1 && $k['products_checked'] === 1 && $k['out_of_stock'] === 0)
        ->assertViewHas('divisionLabel', 'UNILEVER');
});

test('the PDF downloads with photos and without', function () {
    $with = $this->actingAs($this->admin)->get(route('merchandising.reports.pdf'));
    $with->assertOk()->assertHeader('content-type', 'application/pdf');

    $without = $this->actingAs($this->admin)->get(route('merchandising.reports.pdf', ['photos' => 0]));
    $without->assertOk();

    expect(strlen($with->getContent()))->toBeGreaterThan(strlen($without->getContent()));
});

test('the Excel export has visits and one row per product check', function () {
    $response = $this->actingAs($this->admin)->get(route('merchandising.reports.excel'));
    $response->assertOk();

    $book = IOFactory::load($response->baseResponse->getFile()->getPathname());

    expect($book->getSheetNames())->toBe(['Summary', 'Visits', 'Product checks'])
        ->and($book->getSheetByName('Visits')->getCell('D2')->getValue())->toBe('SAS Komitas')
        ->and($book->getSheetByName('Product checks')->getHighestRow())->toBe(3)
        ->and($book->getSheetByName('Product checks')->getCell('H2')->getValue())->toBe('Out of stock');
});

test('an empty day still renders and exports', function () {
    $this->actingAs($this->admin)->get(route('merchandising.reports.index', ['date_from' => '2026-01-01', 'date_to' => '2026-01-01']))
        ->assertOk()->assertSee('No visits for these filters.');
    $this->actingAs($this->admin)->get(route('merchandising.reports.pdf', ['date_from' => '2026-01-01', 'date_to' => '2026-01-01']))->assertOk();
});

test('bad filters are refused', function () {
    $this->actingAs($this->admin)->get(route('merchandising.reports.index', ['date_from' => '2026-11-18', 'date_to' => '2026-11-01']))
        ->assertSessionHasErrors('date_to');
});
