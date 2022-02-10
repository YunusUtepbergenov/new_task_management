<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
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
    public function via($notifiable)
    {
        return ['database'];
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
