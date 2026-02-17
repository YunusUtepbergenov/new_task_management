<?php

namespace App\Notifications;

use App\Models\User;
use App\Notifications\Channels\TelegramChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class CommentStoredNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private $comment;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($comment)
    {
        $this->comment = $comment;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable): array
    {
        $channels = ['database'];
        if ($notifiable->telegram_chat_id) {
            $channels[] = TelegramChannel::class;
        }
        return $channels;
    }

    public function toTelegram($notifiable): string
    {
        $user = User::find($this->comment->user_id);

        return "<b>Новый комментарий</b>\n\n"
            . "К заданию: {$this->comment->task->name}\n"
            . "От: {$user->short_name}";
    }

    public function toArray($notifiable)
    {
        $user = User::where('id', $this->comment->user_id)->first();
        return [
            'name' => $this->comment->task->name,
            'task_id' => $this->comment->task->id,
            'user_name' => $user->name,
            'user_id' => $user->id
        ];
    }
}
