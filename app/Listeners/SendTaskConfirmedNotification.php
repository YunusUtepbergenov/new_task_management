<?php

namespace App\Listeners;

use App\Notifications\TaskConfirmedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Notification;

class SendTaskConfirmedNotification
{
    public function handle($event)
    {
        Notification::send($event->task->user, new TaskConfirmedNotification($event->task));
    }
}
