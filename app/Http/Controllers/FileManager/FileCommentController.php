<?php

namespace App\Http\Controllers\FileManager;

use App\Http\Controllers\Controller;
use App\Models\FileManager\FileEntry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class FileCommentController extends Controller
{
    public function store(Request $request, FileEntry $file): RedirectResponse
    {
        $this->authorize('view', $file);

        $data = $request->validate(['body' => ['required', 'string', 'max:5000']]);

        $file->comments()->create([
            'user_id' => $request->user()->id,
            'body' => $data['body'],
        ]);

        return redirect()->route('files.entries.show', $file)->with('status', 'file-commentadded');
    }
}
