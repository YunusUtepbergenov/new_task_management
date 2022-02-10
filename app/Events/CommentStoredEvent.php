<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CommentStoredEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $comment, $user_id;
    public function __construct($comment, $user_id)
    {
        $this->comment = $comment;
        $this->user_id = $user_id;
    }
}
