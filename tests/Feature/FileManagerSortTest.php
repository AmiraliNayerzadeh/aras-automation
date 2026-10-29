<?php

use App\Models\FileManager\FileEntry;
use App\Models\FileManager\Folder;
use App\Models\User;
use Illuminate\Support\Carbon;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    Permission::findOrCreate('files.create');

    $this->user = User::factory()->create();
    $this->user->givePermissionTo('files.create');

    $folder = fn (string $name, string $createdAt) => Folder::forceCreate([
        'name' => $name,
        'owner_id' => $this->user->id,
        'created_at' => Carbon::parse($createdAt),
        'updated_at' => Carbon::parse($createdAt),
    ]);
    $file = fn (?string $title, string $originalName, string $createdAt) => FileEntry::forceCreate([
        'title' => $title,
        'original_name' => $originalName,
        'file_path' => 'files/'.$originalName,
        'owner_id' => $this->user->id,
        'created_at' => Carbon::parse($createdAt),
        'updated_at' => Carbon::parse($createdAt),
    ]);

    $folder('Bravo folder', '2026-01-02');
    $folder('Alpha folder', '2026-01-01');
    $folder('Charlie folder', '2026-01-03');

    // An empty title falls back to the original name, as in the views.
    $file('Bravo file', 'zzz.pdf', '2026-02-03');
    $file('', 'alpha-file.pdf', '2026-02-01');
    $file(null, 'charlie-file.pdf', '2026-02-02');
});

function listedNames($response): array
{
    $folders = $response->viewData('folders')->pluck('name')->all();
    $files = $response->viewData('files')->map(fn ($f) => $f->title ?: $f->original_name)->all();

    return [$folders, $files];
}

test('items are sorted newest first by default', function () {
    $response = $this->actingAs($this->user)->get(route('files.index', ['tab' => 'mine']));

    $response->assertOk()
        ->assertSee('name="sort"', false)
        ->assertSee('<option value="newest" selected>', false);
    expect(listedNames($response))->toBe([
        ['Charlie folder', 'Bravo folder', 'Alpha folder'],
        ['Bravo file', 'charlie-file.pdf', 'alpha-file.pdf'],
    ]);
});

test('items can be sorted oldest first', function () {
    $response = $this->actingAs($this->user)->get(route('files.index', ['tab' => 'mine', 'sort' => 'oldest']));

    expect(listedNames($response))->toBe([
        ['Alpha folder', 'Bravo folder', 'Charlie folder'],
        ['alpha-file.pdf', 'charlie-file.pdf', 'Bravo file'],
    ]);
});

test('items can be sorted by name in both directions', function () {
    $asc = $this->actingAs($this->user)->get(route('files.index', ['tab' => 'mine', 'sort' => 'name_asc']));
    expect(listedNames($asc))->toBe([
        ['Alpha folder', 'Bravo folder', 'Charlie folder'],
        ['alpha-file.pdf', 'Bravo file', 'charlie-file.pdf'],
    ]);

    $desc = $this->actingAs($this->user)->get(route('files.index', ['tab' => 'mine', 'sort' => 'name_desc']));
    expect(listedNames($desc))->toBe([
        ['Charlie folder', 'Bravo folder', 'Alpha folder'],
        ['charlie-file.pdf', 'Bravo file', 'alpha-file.pdf'],
    ]);
});

test('sorting applies inside a folder', function () {
    $parent = Folder::where('name', 'Alpha folder')->first();
    Folder::query()->where('name', '!=', 'Alpha folder')->update(['parent_id' => $parent->id]);
    FileEntry::query()->update(['folder_id' => $parent->id]);

    $response = $this->actingAs($this->user)->get(route('files.index', ['folder' => $parent->id, 'sort' => 'name_desc']));

    $response->assertOk();
    expect(listedNames($response))->toBe([
        ['Charlie folder', 'Bravo folder'],
        ['charlie-file.pdf', 'Bravo file', 'alpha-file.pdf'],
    ]);
});

test('the chosen sort is remembered in a cookie and invalid values fall back to newest', function () {
    $this->actingAs($this->user)->get(route('files.index', ['sort' => 'name_asc']))
        ->assertCookie('files_sort', 'name_asc');

    $response = $this->actingAs($this->user)->get(route('files.index', ['tab' => 'mine', 'sort' => 'bogus']));
    $response->assertOk();
    expect($response->viewData('sort'))->toBe('newest');
});
