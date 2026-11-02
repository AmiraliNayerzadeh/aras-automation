<?php

namespace App\Console\Commands\Finance;

use App\Enums\UserStatus;
use App\Models\Finance\PaymentReminder;
use App\Models\User;
use App\Notifications\PaymentReminderDueNotification;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

#[Signature('payments:send-reminders')]
#[Description('Notify site admins once about each payment whose reminder window has opened.')]
class SendPaymentReminders extends Command
{
    public function handle(): void
    {
        $recipients = User::permission('payment-reminders.manage')
            ->where('status', UserStatus::Active->value)
            ->get();

        if ($recipients->isEmpty()) {
            $this->warn('No active admins to notify.');

            return;
        }

        $sent = 0;

        foreach (PaymentReminder::needingAttention() as $reminder) {
            if ($reminder->notified_due_date?->equalTo($reminder->next_due_date)) {
                continue;
            }

            Notification::send($recipients, new PaymentReminderDueNotification($reminder));
            $reminder->update(['notified_due_date' => $reminder->next_due_date]);
            $sent++;
        }

        $this->info("Payment reminders sent: {$sent}");
    }
}
