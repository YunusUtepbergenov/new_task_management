<?php

namespace App\Notifications;

use App\Models\User;
use App\Notifications\Channels\TelegramChannel;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class NewTaskNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private $task;

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
        $deadline = Carbon::parse($this->task->extended_deadline ?? $this->task->deadline)->format('d.m.Y');

        return __('notifications.telegram.new_task', [], $locale) . "\n\n"
            . "📌 <b>{$this->task->name}</b>\n"
            . __('notifications.telegram.from', [], $locale) . " {$creator->short_name}\n"
            . __('notifications.telegram.deadline', [], $locale) . " {$deadline}";
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
