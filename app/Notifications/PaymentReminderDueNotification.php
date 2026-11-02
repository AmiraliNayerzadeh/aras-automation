<?php

namespace App\Notifications;

use App\Models\Finance\PaymentReminder;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PaymentReminderDueNotification extends Notification
{
    use Queueable;

    public function __construct(protected PaymentReminder $reminder) {}

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
            'reminder_id' => $this->reminder->id,
            'title' => $this->reminder->title,
            'due_date' => $this->reminder->next_due_date->toDateString(),
        ];
    }
}
