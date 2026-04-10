<?php

namespace App\Notifications;

use App\Models\User;
use App\Notifications\Channels\TelegramChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class TaskRejectedNotification extends Notification implements ShouldQueue
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
        $creator = User::find($this->task->creator_id);

        return __('notifications.telegram.task_rejected', [], $locale) . "\n\n"
            . "📌 <b>{$this->task->name}</b>\n"
            . "👤 " . __('notifications.telegram.supervisor_rejected', ['name' => $creator->short_name], $locale) . "\n\n"
            . __('notifications.telegram.please_check_comments', [], $locale);
    }

    public function toArray($notifiable)
    {
        $user = User::where('id', $this->task->creator_id)->first();
        return [
            'name' => $this->task->name,
            'task_id' => $this->task->id,
            'creator_name' => $user->name,
            'creator_id' => $user->id,
        ];
    }
}
