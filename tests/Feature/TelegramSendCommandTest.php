<?php

namespace Tests\Feature;

use App\Models\DirectMessage;
use App\Models\User;
use App\Services\TelegramBotService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class TelegramSendCommandTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        config(['services.telegram.webhook_secret' => 'test-secret']);
    }

    private function webhookRequest(int $chatId, string $text): \Illuminate\Testing\TestResponse
    {
        return $this->postJson('/api/telegram/webhook', [
            'message' => [
                'chat' => ['id' => $chatId],
                'text' => $text,
            ],
        ], [
            'X-Telegram-Bot-Api-Secret-Token' => 'test-secret',
        ]);
    }

    public function test_send_command_shows_user_list_for_deputy(): void
    {
        $deputy = User::factory()->deputy()->withTelegram()->create();
        User::factory()->withTelegram()->create(['name' => 'Тестовый Пользователь']);

        $this->mock(TelegramBotService::class, function ($mock) {
            $mock->shouldReceive('sendMessage')
                ->once()
                ->withArgs(function ($chatId, $text) {
                    return str_contains($text, 'Список пользователей') && str_contains($text, 'Тестовый Пользователь');
                })
                ->andReturn(true);
        });

        $this->webhookRequest($deputy->telegram_chat_id, '/send')
            ->assertStatus(200);

        $this->assertTrue(Cache::has("tg_send_{$deputy->telegram_chat_id}"));
    }

    public function test_send_command_denied_for_regular_user(): void
    {
        $user = User::factory()->withTelegram()->create();

        $this->mock(TelegramBotService::class, function ($mock) {
            $mock->shouldReceive('sendMessage')
                ->once()
                ->withArgs(function ($chatId, $text) {
                    return str_contains($text, 'нет прав');
                })
                ->andReturn(true);
        });

        $this->webhookRequest($user->telegram_chat_id, '/send')
            ->assertStatus(200);
    }

    public function test_send_reply_delivers_messages(): void
    {
        $director = User::factory()->director()->withTelegram()->create();
        $recipient1 = User::factory()->withTelegram()->create();
        $recipient2 = User::factory()->withTelegram()->create();

        Cache::put("tg_send_{$director->telegram_chat_id}", [
            1 => $recipient1->id,
            2 => $recipient2->id,
        ], now()->addMinutes(10));

        $this->mock(TelegramBotService::class, function ($mock) {
            // 2 messages to recipients + 1 confirmation to sender
            $mock->shouldReceive('sendMessage')->times(3)->andReturn(true);
        });

        $this->webhookRequest($director->telegram_chat_id, '1,2: Прошу подготовить отчёт')
            ->assertStatus(200);

        $this->assertDatabaseHas('direct_messages', [
            'sender_id' => $director->id,
            'message_text' => 'Прошу подготовить отчёт',
            'channel' => 'telegram',
        ]);

        $message = DirectMessage::first();
        $this->assertEquals(2, $message->recipients()->count());
        $this->assertFalse(Cache::has("tg_send_{$director->telegram_chat_id}"));
    }
}
