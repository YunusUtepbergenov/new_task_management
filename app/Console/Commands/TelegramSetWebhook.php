<?php

namespace App\Console\Commands;

use App\Services\TelegramBotService;
use Illuminate\Console\Command;

class TelegramSetWebhook extends Command
{
    protected $signature = 'telegram:set-webhook {url? : The webhook URL. Defaults to APP_URL/api/telegram/webhook}';

    protected $description = 'Register the Telegram bot webhook URL';

    public function handle(TelegramBotService $telegram): int
    {
        $url = $this->argument('url') ?? rtrim(config('app.url'), '/') . '/api/telegram/webhook';
        $secret = config('services.telegram.webhook_secret');

        $this->info("Setting webhook to: {$url}");

        $result = $telegram->setWebhook($url, $secret);

        if (($result['ok'] ?? false) === true) {
            $this->info('Webhook set successfully.');
            return self::SUCCESS;
        }

        $this->error('Failed to set webhook: ' . ($result['description'] ?? 'Unknown error'));
        return self::FAILURE;
    }
}
