<?php

namespace App\Providers;

use App\Models\FileManager\FileEntry;
use App\Models\FileManager\FileShare;
use App\Support\DashboardWidgetRegistry;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;

class FileServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(DashboardWidgetRegistry $registry): void
    {
        $registry->register('files.shared_with_me', function () {
            $user = Auth::user();

            if (! $user) {
                return [];
            }

            $roleIds = $user->roles->pluck('id');

            $shares = FileShare::query()
                ->where('shareable_type', 'file')
                ->where(function ($q) use ($user, $roleIds) {
                    $q->where('grantee_type', 'everyone')
                        ->orWhere(fn ($qq) => $qq->where('grantee_type', 'user')->where('grantee_id', $user->id))
                        ->orWhere(fn ($qq) => $qq->where('grantee_type', 'role')->whereIn('grantee_id', $roleIds));
                })
                ->where('created_by_id', '!=', $user->id)
                ->latest()
                ->limit(5)
                ->get();

            $files = FileEntry::whereIn('id', $shares->pluck('shareable_id'))->get()->keyBy('id');

            return $shares
                ->map(fn ($share) => $files->get($share->shareable_id))
                ->filter()
                ->map(fn (FileEntry $file) => [
                    'label' => __('app.file_shared_with_you', ['title' => $file->title ?: $file->original_name]),
                    'url' => route('files.entries.show', $file),
                    'count' => 1,
                ])
                ->values()
                ->all();
        });
    }
}
