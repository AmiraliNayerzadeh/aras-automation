<?php

use App\Models\FileManager\FileEntry;
use App\Models\FileManager\Folder;
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

beforeEach(function () {
    Carbon::setTestNow('2026-11-18 12:00:00');
    Storage::fake('public');
    Storage::fake('local');
    $this->seed(RolePermissionSeeder::class);

    $this->merchandiser = User::factory()->create(['status' => 'active', 'name' => 'Ani']);
    $this->merchandiser->assignRole('merchandiser');
    $this->admin = User::factory()->create(['status' => 'active']);
    $this->admin->assignRole('super-admin');

    $food = ProductCategory::create(['title' => 'KETCHUP', 'division' => 'topsun', 'is_active' => true]);
    $home = ProductCategory::create(['title' => 'WASHING', 'division' => 'unilever', 'is_active' => true]);
    $this->ketchup = Product::create(['sku' => '30101', 'title' => 'TOP SUN KETCHUP 500G', 'line' => 'TOP SUN KETCHUP', 'category_id' => $food->id]);
    $this->persil = Product::create(['sku' => '60101', 'title' => 'PERSIL 3KG', 'line' => 'PERSIL', 'category_id' => $home->id]);
    $this->store = Store::create(['name' => 'SAS Komitas', 'food_account_code' => '11706001', 'status' => 'active', 'region' => 'Arabkir']);

    $this->upload = function (string $kind, ?User $user = null, string $content = null) {
        $user ??= $this->merchandiser;
        $content ??= UploadedFile::fake()->image('shelf.jpg', 40, 40)->get();
        $token = $this->actingAs($user)->postJson(route('merchandising.uploads.start'), ['kind' => $kind, 'name' => 'shelf.jpg', 'size' => strlen($content), 'type' => 'image/jpeg'])->json('token');
        $this->actingAs($user)->call('POST', route('merchandising.uploads.chunk', $token).'?offset=0', [], [], [], ['CONTENT_TYPE' => 'application/octet-stream', 'HTTP_ACCEPT' => 'application/json'], $content);

        return $token;
    };

    $this->payload = fn (array $overrides = []) => $overrides + [
        'submission_key' => (string) Str::uuid(),
        'visited_at' => '2026-11-18T11:30',
        'store_id' => $this->store->id,
        'items' => [
            ['product_id' => $this->ketchup->id, 'stock_status' => 'out_of_stock', 'shelf_position' => 'bottom', 'price_tag' => 'missing', 'issues' => ['dirty'], 'needs_order' => true],
            ['product_name' => 'Heinz ketchup (competitor)', 'stock_status' => 'in_stock'],
        ],
        'actions' => ['rearranged', 'fifo'],
        'store_feedback' => ['satisfied'],
        'needs_order' => true,
        'note' => 'Shelf was empty in the morning.',
        'uploads' => [($this->upload)('before'), ($this->upload)('after')],
    ];

    $this->submit = fn (array $payload, ?User $user = null) => $this->actingAs($user ?? $this->merchandiser)
        ->post(route('merchandising.visits.store'), ['payload' => json_encode($payload)]);
});

afterEach(fn () => Carbon::setTestNow());

test('the form offers only the products of the merchandiser’s divisions', function () {
    $this->merchandiser->forceFill(['merchandising_divisions' => ['topsun']])->save();

    $this->actingAs($this->merchandiser)->get(route('merchandising.visits.create'))
        ->assertOk()
        ->assertViewHas('catalog', fn (array $catalog) => collect($catalog)->pluck('sku')->all() === ['30101']);

    $this->merchandiser->forceFill(['merchandising_divisions' => []])->save();

    $this->actingAs($this->merchandiser)->get(route('merchandising.visits.create'))
        ->assertViewHas('catalog', fn (array $catalog) => count($catalog) === 2);
});

test('people without the role cannot reach the form', function () {
    $this->actingAs(User::factory()->create(['status' => 'active']))->get(route('merchandising.visits.create'))->assertForbidden();
});

test('a complete visit is saved with its products and filed into the file manager', function () {
    $response = ($this->submit)(($this->payload)());

    $visit = Visit::with('items', 'media.fileEntry')->first();
    $response->assertRedirect(route('merchandising.visits.show', $visit));

    expect($visit->items)->toHaveCount(2)
        ->and($visit->divisions)->toBe(['topsun'])
        ->and($visit->items->firstWhere('product_id', $this->ketchup->id))
        ->stock_status->toBe('out_of_stock')->issues->toBe(['dirty'])->needs_order->toBeTrue()->division->toBe('topsun')
        ->and($visit->items->firstWhere('product_id', null)->product_name)->toBe('Heinz ketchup (competitor)')
        ->and($visit->media)->toHaveCount(2)
        ->and(Storage::disk('public')->exists($visit->media->first()->fileEntry->file_path))->toBeTrue()
        ->and(Upload::whereNull('visit_id')->count())->toBe(0);

    $folder = Folder::find($visit->folder_id);
    expect($folder->name)->toBe('2026-11-18 11:30 - Ani')
        ->and($folder->parent->name)->toBe('SAS Komitas')
        ->and($folder->parent->parent->name)->toBe('2026-11')
        ->and($folder->parent->parent->parent->name)->toBe('Merchandising');
});

test('filed photos are private to the merchandiser and the reviewers', function () {
    ($this->submit)(($this->payload)());
    $file = FileEntry::first();
    $colleague = User::factory()->create(['status' => 'active']);
    $colleague->assignRole('employee');

    expect($file->isVisibleTo($this->merchandiser))->toBeTrue()
        ->and($file->isVisibleTo($this->admin))->toBeTrue()
        ->and($file->isVisibleTo($colleague))->toBeFalse()
        ->and(Folder::where('name', 'Merchandising')->first()->isVisibleTo($colleague))->toBeFalse();
});

test('a second visit to the same store reuses its folders', function () {
    ($this->submit)(($this->payload)());
    ($this->submit)(($this->payload)(['visited_at' => '2026-11-18T11:30']));

    expect(Folder::where('name', 'SAS Komitas')->count())->toBe(1)
        ->and(Folder::where('name', 'like', '2026-11-18 11:30 - Ani%')->count())->toBe(2);
});

test('submitting the same form twice creates one visit', function () {
    $payload = ($this->payload)();

    ($this->submit)($payload);
    ($this->submit)($payload)->assertRedirect(route('merchandising.visits.show', Visit::first()));

    expect(Visit::count())->toBe(1);
});

test('before and after photos are required and the form comes back intact', function () {
    $payload = ($this->payload)();
    $payload['uploads'] = [($this->upload)('before')];

    ($this->submit)($payload)->assertSessionHasErrors('uploads')->assertSessionHasInput('payload');

    expect(Visit::count())->toBe(0);
});

test('someone else’s uploads cannot be attached', function () {
    $other = User::factory()->create(['status' => 'active']);
    $other->assignRole('merchandiser');

    $payload = ($this->payload)(['uploads' => [($this->upload)('before', $other), ($this->upload)('after', $other)]]);

    ($this->submit)($payload)->assertSessionHasErrors('uploads');
});

test('a typed store is created unverified and reused for the same name', function () {
    ($this->submit)(($this->payload)(['store_id' => null, 'store_name' => 'Mini Market Ajapnyak']));
    ($this->submit)(($this->payload)(['store_id' => null, 'store_name' => '  mini market   ajapnyak ']));

    expect(Store::where('is_verified', false)->count())->toBe(1)
        ->and(Visit::count())->toBe(2);
});

test('bad answers are refused', function (array $overrides, string $field) {
    ($this->submit)(($this->payload)($overrides))->assertSessionHasErrors($field);
})->with([
    'no store' => [['store_id' => null, 'store_name' => null], 'store_name'],
    'future time' => [['visited_at' => '2026-11-19T09:00'], 'visited_at'],
    'unknown stock answer' => [['items' => [['product_id' => 1, 'stock_status' => 'plenty']]], 'items.0.stock_status'],
    'unknown issue' => [['items' => [['product_id' => 1, 'issues' => ['made_up']]]], 'items.0.issues.0'],
    'long note' => [['note' => str_repeat('a', 201)], 'note'],
]);

test('without "needs order" no product is marked for ordering', function () {
    ($this->submit)(($this->payload)(['needs_order' => false]));

    expect(Visit::first()->items->where('needs_order', true))->toHaveCount(0);
});

test('a merchandiser sees only their own visits; reviewers see all', function () {
    ($this->submit)(($this->payload)());
    $visit = Visit::first();
    $other = User::factory()->create(['status' => 'active']);
    $other->assignRole('merchandiser');

    $this->actingAs($this->merchandiser)->get(route('merchandising.visits.show', $visit))->assertOk()->assertSee('SAS Komitas');
    $this->actingAs($this->admin)->get(route('merchandising.visits.show', $visit))->assertOk();
    $this->actingAs($other)->get(route('merchandising.visits.show', $visit))->assertForbidden();
    $this->actingAs($other)->get(route('merchandising.visits.index'))->assertOk()->assertDontSee('SAS Komitas');
});

test('store search finds active stores by name or code', function () {
    Store::create(['name' => 'SAS Closed', 'status' => 'inactive']);

    $this->actingAs($this->merchandiser)->getJson(route('merchandising.stores.search', ['q' => 'SAS']))
        ->assertOk()->assertJsonCount(1)->assertJsonPath('0.name', 'SAS Komitas');
    $this->actingAs($this->merchandiser)->getJson(route('merchandising.stores.search', ['q' => '117060']))
        ->assertJsonPath('0.codes', '11706001');
});

test('only reviewers can delete a visit', function () {
    ($this->submit)(($this->payload)());
    $visit = Visit::first();

    $this->actingAs($this->merchandiser)->delete(route('merchandising.visits.destroy', $visit))->assertForbidden();
    $this->actingAs($this->admin)->delete(route('merchandising.visits.destroy', $visit))->assertRedirect();

    expect(Visit::count())->toBe(0);
});
