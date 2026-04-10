<?php

namespace App\Services;

use App\Models\DirectMessage;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

class DirectMessageService
{
    public function __construct(private TelegramBotService $telegram)
    {
    }

    /**
     * @param array<int> $recipientIds
     */
    public function send(User $sender, array $recipientIds, string $messageText, string $channel = 'web'): DirectMessage
    {
        if (!$sender->isDeputy() && !$sender->isDirector()) {
            throw new AuthorizationException(__('notifications.no_permission_send'));
        }

        $recipients = User::whereIn('id', $recipientIds)
            ->whereNotNull('telegram_chat_id')
            ->get();

        $directMessage = DirectMessage::create([
            'sender_id' => $sender->id,
            'message_text' => $messageText,
            'channel' => $channel,
        ]);

        $pivotData = [];
        foreach ($recipients as $recipient) {
            $pivotData[$recipient->id] = ['delivered' => false];
        }
        $directMessage->recipients()->attach($pivotData);

        $formattedMessage = "📨 <b>Сообщение от {$sender->short_name}:</b>\n\n{$messageText}";

        foreach ($recipients as $recipient) {
            $delivered = $this->telegram->sendMessage($recipient->telegram_chat_id, $formattedMessage);

            if ($delivered) {
                $directMessage->recipients()->updateExistingPivot($recipient->id, [
                    'delivered' => true,
                    'delivered_at' => now(),
                ]);
            }
        }

        return $directMessage;
    }
}
