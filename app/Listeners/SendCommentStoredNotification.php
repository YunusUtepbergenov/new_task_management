<?php

namespace App\Listeners;

use App\Models\User;
use App\Notifications\CommentStoredNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Notification;

class SendCommentStoredNotification
{
    public function handle($event)
    {
        $user = User::where('id', $event->user_id)->first();
        Notification::send($user, new CommentStoredNotification($event->comment));
    }
}
