<?php

use App\Models\FileManager\FileEntry;
use App\Models\FileManager\Folder;
use App\Models\User;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    Permission::findOrCreate('files.create');

    $this->user = User::factory()->create();
    $this->user->givePermissionTo('files.create');
    $this->other = User::factory()->create();

    $folder = fn (string $name, ?Folder $parent = null, ?User $owner = null, bool $confidential = false) => Folder::forceCreate([
        'name' => $name,
        'parent_id' => $parent?->id,
        'owner_id' => ($owner ?? $this->user)->id,
        'is_confidential' => $confidential,
    ]);

    $this->projects = $folder('Projects');
    $this->archive = $folder('Archive');
    $this->alpha = $folder('Alpha', $this->projects);
    $this->deep = $folder('Deep', $this->alpha);
    $this->secret = $folder('Secret', null, $this->other, true);
    $this->hiddenChild = $folder('Hidden child', $this->archive, $this->other, true);

    FileEntry::forceCreate([
        'title' => 'Plan',
        'original_name' => 'plan.pdf',
        'file_path' => 'files/plan.pdf',
        'mime_type' => 'application/pdf',
        'folder_id' => $this->projects->id,
        'owner_id' => $this->user->id,
    ]);
});

test('the tree endpoint returns visible top-level folders sorted by name', function () {
    $response = $this->actingAs($this->user)->getJson(route('files.tree'));

    $response->assertOk();
    expect(collect($response->json('folders'))->pluck('name')->all())->toBe(['Archive', 'Projects']);
});

test('has_children only counts children the user can see', function () {
    $folders = collect($this->actingAs($this->user)->getJson(route('files.tree'))->json('folders'))->keyBy('name');

    expect($folders['Projects']['has_children'])->toBeTrue()
        ->and($folders['Archive']['has_children'])->toBeFalse();
});

test('the tree endpoint lists subfolders and files of a folder', function () {
    $response = $this->actingAs($this->user)->getJson(route('files.tree', ['folder' => $this->projects->id]));

    $response->assertOk()
        ->assertJsonPath('folders.0.name', 'Alpha')
        ->assertJsonPath('folders.0.has_children', true)
        ->assertJsonPath('files.0.name', 'Plan')
        ->assertJsonPath('files.0.url', route('files.entries.show', FileEntry::first()));
});

test('the tree endpoint refuses a folder the user cannot see', function () {
    $this->actingAs($this->user)->getJson(route('files.tree', ['folder' => $this->secret->id]))->assertForbidden();
    $this->actingAs($this->user)->getJson(route('files.tree', ['folder' => 999999]))->assertNotFound();
});

test('the index embeds the tree expanded along the current path', function () {
    $response = $this->actingAs($this->user)->get(route('files.index', ['folder' => $this->deep->id]));

    $response->assertOk();
    expect($response->viewData('treePath'))->toBe([$this->projects->id, $this->alpha->id, $this->deep->id])
        ->and(array_keys($response->viewData('treeLevels')))->toBe(['root', $this->projects->id, $this->alpha->id, $this->deep->id]);
});

test('the path stops at an ancestor the user cannot see', function () {
    $sharedInside = Folder::forceCreate(['name' => 'Inside', 'parent_id' => $this->secret->id, 'owner_id' => $this->user->id]);

    $response = $this->actingAs($this->user)->get(route('files.index', ['folder' => $sharedInside->id]));

    $response->assertOk();
    expect($response->viewData('treePath'))->toBe([])
        ->and(array_keys($response->viewData('treeLevels')))->toBe(['root']);
});

test('the tree panel remembers being hidden', function () {
    $this->actingAs($this->user)->get(route('files.index'))
        ->assertOk()
        ->assertViewHas('treeHidden', false)
        ->assertDontSee('is-tree-hidden" data-files-layout', false);

    $this->actingAs($this->user)->withUnencryptedCookie('files_tree', 'hidden')->get(route('files.index'))
        ->assertOk()
        ->assertViewHas('treeHidden', true)
        ->assertSee('files-layout is-tree-hidden', false);
});
