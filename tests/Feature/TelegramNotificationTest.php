<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use App\Notifications\Channels\TelegramChannel;
use App\Notifications\NewTaskNotification;
use App\Services\TelegramBotService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TelegramNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_notification_includes_telegram_channel_when_linked(): void
    {
        $this->seed();

        $user = User::first();
        $user->update(['telegram_chat_id' => 123456]);

        $task = Task::factory()->create(['creator_id' => $user->id, 'user_id' => $user->id]);
        $notification = new NewTaskNotification($task);

        $channels = $notification->via($user);

        $this->assertContains('database', $channels);
        $this->assertContains(TelegramChannel::class, $channels);
    }

    public function test_notification_excludes_telegram_channel_when_not_linked(): void
    {
        $this->seed();

        $user = User::first();
        $user->update(['telegram_chat_id' => null]);

        $task = Task::factory()->create(['creator_id' => $user->id, 'user_id' => $user->id]);
        $notification = new NewTaskNotification($task);

        $channels = $notification->via($user);

        $this->assertContains('database', $channels);
        $this->assertNotContains(TelegramChannel::class, $channels);
    }

    public function test_telegram_channel_sends_message(): void
    {
        $this->seed();

        $user = User::first();
        $user->update(['telegram_chat_id' => 123456]);

        $task = Task::factory()->create(['creator_id' => $user->id, 'user_id' => $user->id]);
        $notification = new NewTaskNotification($task);

        $mock = $this->mock(TelegramBotService::class, function ($mock) {
            $mock->shouldReceive('sendMessage')
                ->once()
                ->with(123456, \Mockery::type('string'), 'HTML')
                ->andReturn(true);
        });

        $channel = new TelegramChannel($mock);
        $channel->send($user, $notification);
    }

    public function test_telegram_channel_skips_when_no_chat_id(): void
    {
        $this->seed();

        $user = User::first();
        $user->update(['telegram_chat_id' => null]);

        $task = Task::factory()->create(['creator_id' => $user->id, 'user_id' => $user->id]);
        $notification = new NewTaskNotification($task);

        $mock = $this->mock(TelegramBotService::class, function ($mock) {
            $mock->shouldNotReceive('sendMessage');
        });

        $channel = new TelegramChannel($mock);
        $channel->send($user, $notification);
    }
}
