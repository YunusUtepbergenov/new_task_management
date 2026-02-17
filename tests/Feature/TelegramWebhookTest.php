<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\TelegramBotService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class TelegramWebhookTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.telegram.webhook_secret' => 'test-secret']);
    }

    public function test_webhook_rejects_invalid_secret(): void
    {
        $response = $this->postJson('/api/telegram/webhook', [], [
            'X-Telegram-Bot-Api-Secret-Token' => 'wrong-secret',
        ]);

        $response->assertStatus(403);
    }

    public function test_webhook_accepts_valid_secret(): void
    {
        $this->mock(TelegramBotService::class, function ($mock) {
            $mock->shouldReceive('sendMessage')->once()->andReturn(true);
        });

        $response = $this->postJson('/api/telegram/webhook', [
            'message' => [
                'chat' => ['id' => 123456],
                'text' => '/help',
            ],
        ], [
            'X-Telegram-Bot-Api-Secret-Token' => 'test-secret',
        ]);

        $response->assertStatus(200);
    }

    public function test_start_with_valid_token_links_account(): void
    {
        $this->seed();

        $plainToken = Str::random(32);
        $user = User::first();
        $user->update([
            'telegram_token' => hash('sha256', $plainToken),
            'telegram_token_expires_at' => now()->addMinutes(10),
        ]);

        $this->mock(TelegramBotService::class, function ($mock) {
            $mock->shouldReceive('sendMessage')->once()->andReturn(true);
        });

        $this->postJson('/api/telegram/webhook', [
            'message' => [
                'chat' => ['id' => 999888],
                'text' => "/start {$plainToken}",
            ],
        ], [
            'X-Telegram-Bot-Api-Secret-Token' => 'test-secret',
        ]);

        $user->refresh();
        $this->assertEquals(999888, $user->telegram_chat_id);
        $this->assertNull($user->telegram_token);
    }

    public function test_start_with_expired_token_fails(): void
    {
        $this->seed();

        $plainToken = Str::random(32);
        $user = User::first();
        $user->update([
            'telegram_token' => hash('sha256', $plainToken),
            'telegram_token_expires_at' => now()->subMinutes(1),
        ]);

        $this->mock(TelegramBotService::class, function ($mock) {
            $mock->shouldReceive('sendMessage')->once()->andReturn(true);
        });

        $this->postJson('/api/telegram/webhook', [
            'message' => [
                'chat' => ['id' => 999888],
                'text' => "/start {$plainToken}",
            ],
        ], [
            'X-Telegram-Bot-Api-Secret-Token' => 'test-secret',
        ]);

        $user->refresh();
        $this->assertNull($user->telegram_chat_id);
    }

    public function test_unlink_removes_chat_id(): void
    {
        $this->seed();

        $user = User::first();
        $user->update(['telegram_chat_id' => 123456]);

        $this->mock(TelegramBotService::class, function ($mock) {
            $mock->shouldReceive('sendMessage')->once()->andReturn(true);
        });

        $this->postJson('/api/telegram/webhook', [
            'message' => [
                'chat' => ['id' => 123456],
                'text' => '/unlink',
            ],
        ], [
            'X-Telegram-Bot-Api-Secret-Token' => 'test-secret',
        ]);

        $user->refresh();
        $this->assertNull($user->telegram_chat_id);
    }
}
