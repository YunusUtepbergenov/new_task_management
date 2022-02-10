<?php

namespace App\Providers;

use App\Events\CommentStoredEvent;
use App\Events\TaskConfirmedEvent;
use App\Events\TaskCreatedEvent;
use App\Events\TaskRejectedEvent;
use App\Events\TaskSubmittedEvent;
use App\Listeners\SendCommentStoredNotification;
use App\Listeners\SendNewTaskNotification;
use App\Listeners\SendTaskConfirmedNotification;
use App\Listeners\SendTaskRejectedNotification;
use App\Listeners\SendTaskSubmittedNotification;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        TaskCreatedEvent::class => [
            SendNewTaskNotification::class,
        ],
        TaskSubmittedEvent::class => [
            SendTaskSubmittedNotification::class
        ],
        TaskConfirmedEvent::class => [
            SendTaskConfirmedNotification::class
        ],
        TaskRejectedEvent::class => [
            SendTaskRejectedNotification::class
        ],
        CommentStoredEvent::class => [
            SendCommentStoredNotification::class
        ]
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
