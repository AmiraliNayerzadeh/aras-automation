<?php

namespace App\Console\Commands\Hr;

use App\Models\Hr\FaceDeviceEvent;
use App\Services\TelegramNotifier;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

#[Signature('face-device:daily-report {date? : Y-m-d, defaults to yesterday}')]
#[Description('Send a daily summary of face recognition device events to Telegram.')]
class SendFaceDeviceDailyReport extends Command
{
    public function handle(TelegramNotifier $telegram): void
    {
        $date = $this->argument('date')
            ? Carbon::parse($this->argument('date'))->startOfDay()
            : now()->subDay()->startOfDay();

        $events = FaceDeviceEvent::query()
            ->with('user')
            ->whereBetween('event_time', [$date->copy()->startOfDay(), $date->copy()->endOfDay()])
            ->orderBy('event_time')
            ->get();

        $byUser = $events->whereNotNull('user_id')->groupBy('user_id');
        $unmatchedCount = $events->whereNull('user_id')->count();

        $lines = [];
        $lines[] = "📋 <b>گزارش روزانه دستگاه تشخیص چهره</b>";
        $lines[] = 'تاریخ: '.$date->format('Y-m-d');
        $lines[] = '';
        $lines[] = 'تعداد کل رویدادها: '.$events->count();
        $lines[] = 'تعداد کارمندان ثبت‌شده: '.$byUser->count();

        if ($unmatchedCount > 0) {
            $lines[] = 'رویدادهای بدون تطبیق کارمند: '.$unmatchedCount;
        }

        $lines[] = '';

        foreach ($byUser as $userEvents) {
            $user = $userEvents->first()->user;
            $first = $userEvents->first()->event_time?->format('H:i:s');
            $last = $userEvents->last()->event_time?->format('H:i:s');

            $lines[] = sprintf('👤 %s — ورود: %s | خروج: %s (تعداد: %d)', $user?->name ?? '—', $first, $last, $userEvents->count());
        }

        if ($byUser->isEmpty()) {
            $lines[] = 'رویدادی برای این تاریخ ثبت نشده است.';
        }

        $message = implode("\n", $lines);

        $sent = $telegram->sendMessage(config('services.telegram.report_chat_id'), $message);

        if ($sent) {
            Log::channel('telegram')->info('گزارش روزانه دستگاه تشخیص چهره ارسال شد', ['date' => $date->toDateString()]);
        } else {
            Log::channel('telegram')->error('ارسال گزارش روزانه دستگاه تشخیص چهره ناموفق بود', ['date' => $date->toDateString()]);
        }

        $this->info('Report sent for '.$date->toDateString().': '.($sent ? 'ok' : 'failed'));
    }
}
