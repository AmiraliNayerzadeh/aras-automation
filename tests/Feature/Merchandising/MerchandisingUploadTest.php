<?php

use App\Models\Merchandising\Upload;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('local');
    $this->seed(RolePermissionSeeder::class);

    $this->user = User::factory()->create(['status' => 'active']);
    $this->user->assignRole('merchandiser');

    $this->start = fn (string $kind, int $size, ?User $user = null) => $this->actingAs($user ?? $this->user)
        ->postJson(route('merchandising.uploads.start'), ['kind' => $kind, 'name' => 'file.bin', 'size' => $size]);

    $this->chunk = fn (string $token, int $offset, string $bytes, ?User $user = null) => $this->actingAs($user ?? $this->user)
        ->call('POST', route('merchandising.uploads.chunk', $token).'?offset='.$offset, [], [], [], ['CONTENT_TYPE' => 'application/octet-stream', 'HTTP_ACCEPT' => 'application/json'], $bytes);
});

test('a photo arrives in chunks and is checked when complete', function () {
    $image = UploadedFile::fake()->image('a.jpg', 300, 300)->get();
    $token = ($this->start)('before', strlen($image))->assertCreated()->json('token');
    $half = intdiv(strlen($image), 2);

    ($this->chunk)($token, 0, substr($image, 0, $half))->assertOk()->assertJson(['received' => $half, 'complete' => false]);
    ($this->chunk)($token, $half, substr($image, $half))->assertOk()->assertJson(['complete' => true]);

    $upload = Upload::first();
    expect($upload->mime_type)->toStartWith('image/')
        ->and(Storage::disk('local')->get($upload->temp_path))->toBe($image);
});

test('a repeated or out-of-order chunk is answered with the real position', function () {
    $image = UploadedFile::fake()->image('a.jpg', 200, 200)->get();
    $token = ($this->start)('before', strlen($image))->json('token');

    ($this->chunk)($token, 0, substr($image, 0, 100));
    ($this->chunk)($token, 0, substr($image, 0, 100))->assertStatus(409)->assertJson(['received' => 100]);
    ($this->chunk)($token, 500, 'x')->assertStatus(409)->assertJson(['received' => 100]);

    $this->actingAs($this->user)->getJson(route('merchandising.uploads.status', $token))->assertJson(['received' => 100, 'complete' => false]);
});

test('a non-image is refused for before/after photos', function () {
    $token = ($this->start)('before', 11)->json('token');

    ($this->chunk)($token, 0, 'hello world')->assertStatus(422);

    expect(Upload::count())->toBe(0);
});

test('videos are accepted only among the extra media', function () {
    $video = "\x00\x00\x00\x18ftypmp42\x00\x00\x00\x00mp42isom".str_repeat("\x00", 64);

    $extra = ($this->start)('extra', strlen($video))->json('token');
    ($this->chunk)($extra, 0, $video)->assertOk()->assertJson(['complete' => true]);
    expect(Upload::where('token', $extra)->value('mime_type'))->toStartWith('video/');

    $before = ($this->start)('before', strlen($video))->json('token');
    ($this->chunk)($before, 0, $video)->assertStatus(422);
});

test('files over the limit are refused up front', function () {
    ($this->start)('extra', Upload::MAX_BYTES + 1)->assertStatus(422);
});

test('more bytes than announced are refused and trimmed', function () {
    $token = ($this->start)('extra', 5)->json('token');

    ($this->chunk)($token, 0, 'too many bytes')->assertStatus(422);

    expect(Storage::disk('local')->size(Upload::first()->temp_path))->toBe(0);
});

test('nobody else can touch an upload', function () {
    $token = ($this->start)('before', 10)->json('token');
    $other = User::factory()->create(['status' => 'active']);
    $other->assignRole('merchandiser');

    ($this->chunk)($token, 0, '0123456789', $other)->assertNotFound();
    $this->actingAs($other)->deleteJson(route('merchandising.uploads.destroy', $token))->assertNotFound();
});

test('an upload can be discarded before submitting', function () {
    $token = ($this->start)('before', 10)->json('token');

    $this->actingAs($this->user)->deleteJson(route('merchandising.uploads.destroy', $token))->assertOk();

    expect(Upload::count())->toBe(0);
});

test('abandoned uploads are cleaned up', function () {
    $token = ($this->start)('before', 10)->json('token');
    Upload::where('token', $token)->update(['updated_at' => now()->subDays(4)]);

    $this->artisan('merchandising:clean-uploads')->assertSuccessful();

    expect(Upload::count())->toBe(0);
});
