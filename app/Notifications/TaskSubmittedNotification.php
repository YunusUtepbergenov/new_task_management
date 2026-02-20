<?php

namespace App\Notifications;

use App\Models\User;
use App\Notifications\Channels\TelegramChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class TaskSubmittedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private $task;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($task)
    {
        $this->task = $task;
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
        $user = User::find($this->task->user_id);

        return "📬 <b>Задание выполнено!</b>\n\n"
            . "📌 <b>{$this->task->name}</b>\n"
            . "👤 Исполнитель: {$user->short_name}\n\n"
            . "✏️ Пожалуйста, проверьте задание.";
    }

    public function toArray($notifiable)
    {
        $user = User::where('id', $this->task->user_id)->first();
        return [
            'name' => $this->task->name,
            'task_id' => $this->task->id,
            'user_name' => $user->name,
            'creator_id' => $user->id,
            'created_at' => $this->task->created_at
        ];
    }
}
