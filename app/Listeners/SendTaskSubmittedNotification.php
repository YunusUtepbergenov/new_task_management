<?php

namespace App\Listeners;

use App\Events\TaskSubmittedEvent;
use App\Models\User;
use App\Notifications\TaskSubmittedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Notification;

class SendTaskSubmittedNotification
{
    public function handle($event)
    {
        $creator = User::where('id', $event->task->creator_id)->first();
        Notification::send($creator, new TaskSubmittedNotification($event->task));
    }
}
