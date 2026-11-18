<?php

use App\Models\Merchandising\Store;
use App\Models\Products\Product;
use App\Models\Products\ProductCategory;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Http\UploadedFile;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);

    $this->admin = User::factory()->create(['status' => 'active']);
    $this->admin->assignRole('admin');
    $this->employee = User::factory()->create(['status' => 'active', 'name' => 'Narek']);
    $this->employee->assignRole('employee');
});

test('store pages are for admins only', function () {
    $this->actingAs($this->employee)->get(route('merchandising.stores.index'))->assertForbidden();
    $this->actingAs($this->employee)->get(route('merchandising.setup'))->assertForbidden();
    $this->actingAs($this->admin)->get(route('merchandising.stores.index'))->assertOk();
    $this->actingAs($this->admin)->get(route('merchandising.setup'))->assertOk();
});

test('a store is added and its codes must be unique', function () {
    Store::create(['name' => 'Existing', 'food_account_code' => '11700001', 'status' => 'active']);

    $this->actingAs($this->admin)->post(route('merchandising.stores.store'), ['name' => 'New', 'food_account_code' => '11700001', 'status' => 'active'])
        ->assertSessionHasErrors('food_account_code');
    $this->actingAs($this->admin)->post(route('merchandising.stores.store'), ['name' => 'New', 'food_account_code' => '11700002', 'city' => 'Gyumri', 'status' => 'active', 'is_verified' => '1'])
        ->assertRedirect(route('merchandising.stores.index'));

    expect(Store::where('name', 'New')->first())->city->toBe('Gyumri')->is_verified->toBeTrue();
});

test('the stores list is searchable by code and flags typed-in stores', function () {
    Store::create(['name' => 'Komitas', 'food_account_code' => '11706001', 'status' => 'active']);
    Store::create(['name' => 'Typed one', 'status' => 'active', 'is_verified' => false]);

    $this->actingAs($this->admin)->get(route('merchandising.stores.index', ['q' => '117060']))
        ->assertSee('Komitas')->assertDontSee('Typed one')
        ->assertSee(__('merchandising.unverified_stores', ['count' => 1]));
});

test('stores import from the panel and report the result', function () {
    $csv = UploadedFile::fake()->createWithContent('stores.csv', "name,food_code,cleaning_code,city\nShop A,11700001,11800001,Gyumri\nShop B,11700002,,Vanadzor\n");

    $this->actingAs($this->admin)->post(route('merchandising.stores.import'), ['file' => $csv])
        ->assertSessionHas('import_result', fn ($r) => $r['created'] === 2 && $r['format'] === 'template');

    expect(Store::count())->toBe(2);
});

test('an unrecognised file is refused with a message', function () {
    $csv = UploadedFile::fake()->createWithContent('x.csv', "foo,bar\n1,2\n");

    $this->actingAs($this->admin)->post(route('merchandising.stores.import'), ['file' => $csv])->assertSessionHasErrors('file');
});

test('the template downloads', function () {
    $this->actingAs($this->admin)->get(route('merchandising.stores.template'))->assertOk()->assertDownload('stores-template.xlsx');
});

test('a user becomes a merchandiser with chosen divisions and can be removed', function () {
    $this->actingAs($this->admin)->post(route('merchandising.setup.merchandisers.store'), ['user_id' => $this->employee->id])->assertRedirect();
    expect($this->employee->fresh()->hasRole('merchandiser'))->toBeTrue();

    $this->actingAs($this->admin)->put(route('merchandising.setup.merchandisers.update', $this->employee), ['divisions' => ['unilever']]);
    expect($this->employee->fresh()->merchandising_divisions)->toBe(['unilever']);

    $this->actingAs($this->admin)->delete(route('merchandising.setup.merchandisers.destroy', $this->employee));
    expect($this->employee->fresh()->hasRole('merchandiser'))->toBeFalse();
});

test('product groups can be moved between divisions', function () {
    $group = ProductCategory::create(['title' => 'PLASTIC', 'is_active' => true, 'division' => 'unilever']);

    $this->actingAs($this->admin)->put(route('merchandising.setup.categories'), ['divisions' => [$group->id => '']])->assertRedirect();

    expect($group->fresh()->division)->toBeNull();
});

test('products import from the panel', function () {
    $csv = UploadedFile::fake()->createWithContent('items.csv', "item code,item name,ItemGroupDesc,Item Main group Desc\n30101,TOP SUN KETCHUP,TOP SUN KETCHUP,KETCHUP & TOMATO PASTA\n");

    $this->actingAs($this->admin)->post(route('merchandising.setup.products.import'), ['file' => $csv])
        ->assertSessionHas('product_import_result', fn ($r) => $r['created'] === 1);

    expect(Product::where('sku', '30101')->first()->line)->toBe('TOP SUN KETCHUP');
});
