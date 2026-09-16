<?php

namespace App\Notifications;

use App\Models\FileManager\FileEntry;
use App\Models\FileManager\Folder;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class FileUploadedNotification extends Notification
{
    use Queueable;

    public function __construct(protected FileEntry $file, protected User $uploader) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'file_id' => $this->file->id,
            'file_name' => $this->file->title ?: $this->file->original_name,
            'folder_id' => $this->file->folder_id,
            'folder_path' => $this->file->folder ? $this->folderPath($this->file->folder) : null,
            'uploader_id' => $this->uploader->id,
            'uploader_name' => $this->uploader->name,
        ];
    }

    protected function folderPath(Folder $folder): string
    {
        return $folder->ancestors()->push($folder)->pluck('name')->implode(' / ');
    }
}
