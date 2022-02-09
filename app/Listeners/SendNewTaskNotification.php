<?php

namespace App\Listeners;

use App\Notifications\NewTaskNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Notification;

class SendNewTaskNotification
{

    public function handle($event)
    {
        Notification::send($event->task->user, new NewTaskNotification($event->task));
    }
}
