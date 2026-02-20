<?php

namespace App\Notifications\Channels;

use App\Services\TelegramBotService;
use Illuminate\Notifications\Notification;

class TelegramChannel
{
    public function __construct(private TelegramBotService $telegram)
    {
    }

    public function send(object $notifiable, Notification $notification): void
    {
        $chatId = $notifiable->routeNotificationFor('telegram');

        if (!$chatId) {
            return;
        }

        $message = $notification->toTelegram($notifiable);

        $this->telegram->sendMessage($chatId, $message);
    }
}
