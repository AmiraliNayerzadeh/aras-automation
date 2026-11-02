<?php

namespace App\Support;

use App\Notifications\FileUploadedNotification;
use App\Notifications\PaymentReminderDueNotification;
use Illuminate\Notifications\DatabaseNotification;

/**
 * Turns a stored notification into what the bell menu and the dashboard show,
 * so those views don't have to assume every notification is about a file.
 */
class NotificationPresenter
{
    /**
     * @return array{url: string, icon: string, text: string}|null  Null for a type this app doesn't know.
     */
    public static function present(DatabaseNotification $notification): ?array
    {
        $data = $notification->data;

        return match ($notification->type) {
            FileUploadedNotification::class => [
                'url' => route('files.entries.show', $data['file_id']),
                'icon' => 'ri-upload-cloud-2-line',
                'text' => __('notifications.file_uploaded', [
                    'uploader' => $data['uploader_name'],
                    'file' => $data['file_name'],
                    'folder' => $data['folder_path'] ?? __('notifications.root_folder'),
                ]),
            ],
            PaymentReminderDueNotification::class => [
                'url' => route('admin.payment-reminders.index'),
                'icon' => 'ri-bank-card-line',
                'text' => __('payments.notification_due', ['title' => $data['title'], 'date' => $data['due_date']]),
            ],
            default => null,
        };
    }
}
