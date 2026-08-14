<?php

namespace App\Logging;

use App\Services\TelegramNotifier;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\LogRecord;

class TelegramLogHandler extends AbstractProcessingHandler
{
    protected function write(LogRecord $record): void
    {
        $chatId = config('services.telegram.log_chat_id');

        if (! $chatId) {
            return;
        }

        $emoji = match (true) {
            $record->level->value >= 500 => '🔴',
            $record->level->value >= 400 => '🟠',
            $record->level->value >= 300 => '🟡',
            default => 'ℹ️',
        };

        $text = sprintf(
            "%s <b>[%s] %s</b>\n%s",
            $emoji,
            $record->level->getName(),
            e($record->channel),
            e($record->message)
        );

        if (! empty($record->context)) {
            $context = json_encode($record->context, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            $text .= "\n<pre>".e(mb_substr($context, 0, 2500))."</pre>";
        }

        try {
            app(TelegramNotifier::class)->sendMessage($chatId, mb_substr($text, 0, 4000));
        } catch (\Throwable) {
            // never let logging failures bubble up
        }
    }
}
