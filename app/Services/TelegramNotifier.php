<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class TelegramNotifier
{
    public function sendMessage(?string $chatId, string $text): bool
    {
        $token = config('services.telegram.bot_token');

        if (! $token || ! $chatId) {
            return false;
        }

        try {
            $response = Http::timeout(10)->post("https://api.telegram.org/bot{$token}/sendMessage", [
                'chat_id' => $chatId,
                'text' => $text,
                'parse_mode' => 'HTML',
            ]);

            return $response->successful();
        } catch (\Throwable) {
            return false;
        }
    }

    public function sendDocument(?string $chatId, string $filename, string $binaryContent, ?string $caption = null): bool
    {
        $token = config('services.telegram.bot_token');

        if (! $token || ! $chatId) {
            return false;
        }

        try {
            $response = Http::timeout(30)
                ->attach('document', $binaryContent, $filename)
                ->post("https://api.telegram.org/bot{$token}/sendDocument", array_filter([
                    'chat_id' => $chatId,
                    'caption' => $caption,
                ]));

            return $response->successful();
        } catch (\Throwable) {
            return false;
        }
    }
}
