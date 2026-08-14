<?php

namespace App\Logging;

use Monolog\Logger;

class TelegramLogger
{
    /**
     * Create a custom Monolog instance that ships log records to Telegram.
     */
    public function __invoke(array $config): Logger
    {
        $logger = new Logger('telegram');

        $handler = new TelegramLogHandler($config['level'] ?? 'debug');

        $logger->pushHandler($handler);

        return $logger;
    }
}
