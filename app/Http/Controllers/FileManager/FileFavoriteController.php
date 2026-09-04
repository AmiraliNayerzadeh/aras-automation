<?php

namespace App\Http\Controllers\FileManager;

use App\Http\Controllers\Controller;
use App\Models\FileManager\FileEntry;
use App\Models\FileManager\FileFavorite;
use App\Models\FileManager\Folder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FileFavoriteController extends Controller
{
    public function toggle(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'favoritable_type' => ['required', Rule::in(['folder', 'file'])],
            'favoritable_id' => ['required', 'integer'],
        ]);

        $model = $data['favoritable_type'] === 'folder'
            ? Folder::findOrFail($data['favoritable_id'])
            : FileEntry::findOrFail($data['favoritable_id']);

        $this->authorize('view', $model);

        $user = $request->user();

        $favorite = FileFavorite::where('user_id', $user->id)
            ->where('favoritable_type', $data['favoritable_type'])
            ->where('favoritable_id', $data['favoritable_id'])
            ->first();

        if ($favorite) {
            $favorite->delete();
        } else {
            FileFavorite::create([
                'user_id' => $user->id,
                'favoritable_type' => $data['favoritable_type'],
                'favoritable_id' => $data['favoritable_id'],
            ]);
        }

        return back();
    }
}
