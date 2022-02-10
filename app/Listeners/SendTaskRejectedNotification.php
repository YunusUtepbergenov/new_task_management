<?php

namespace App\Listeners;

use App\Notifications\TaskRejectedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Notification;

class SendTaskRejectedNotification
{

    public function handle($event)
    {
        Notification::send($event->task->user, new TaskRejectedNotification($event->task));
    }
}
