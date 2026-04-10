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
        $locale = $notifiable->locale ?? 'ru';
        $user = User::find($this->task->user_id);

        return __('notifications.telegram.task_submitted', [], $locale) . "\n\n"
            . "📌 <b>{$this->task->name}</b>\n"
            . __('notifications.telegram.executor', [], $locale) . " {$user->short_name}\n\n"
            . __('notifications.telegram.please_check', [], $locale);
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
