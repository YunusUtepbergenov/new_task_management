<?php

namespace Tests\Feature;

use App\Models\DirectMessage;
use App\Models\User;
use App\Services\TelegramBotService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DirectMessageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_director_can_access_direct_messages_page(): void
    {
        $user = User::factory()->director()->create();

        $this->actingAs($user)
            ->get(route('direct.messages'))
            ->assertStatus(200);
    }

    public function test_deputy_can_access_direct_messages_page(): void
    {
        $user = User::factory()->deputy()->create();

        $this->actingAs($user)
            ->get(route('direct.messages'))
            ->assertStatus(200);
    }

    public function test_regular_user_cannot_access_direct_messages_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('direct.messages'))
            ->assertStatus(403);
    }

    public function test_can_send_message_to_users(): void
    {
        $this->mock(TelegramBotService::class, function ($mock) {
            $mock->shouldReceive('sendMessage')->twice()->andReturn(true);
        });

        $sender = User::factory()->director()->create();
        $recipient1 = User::factory()->withTelegram()->create();
        $recipient2 = User::factory()->withTelegram()->create();

        Livewire::actingAs($sender)
            ->test(\App\Livewire\DirectMessages::class)
            ->set('selectedUserIds', [(string) $recipient1->id, (string) $recipient2->id])
            ->set('messageText', 'Тестовое сообщение')
            ->call('sendMessage')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('direct_messages', [
            'sender_id' => $sender->id,
            'message_text' => 'Тестовое сообщение',
            'channel' => 'web',
        ]);

        $message = DirectMessage::first();
        $this->assertEquals(2, $message->recipients()->count());
    }

    public function test_users_without_telegram_are_excluded(): void
    {
        $this->mock(TelegramBotService::class, function ($mock) {
            $mock->shouldReceive('sendMessage')->once()->andReturn(true);
        });

        $sender = User::factory()->deputy()->create();
        $withTelegram = User::factory()->withTelegram()->create();
        $withoutTelegram = User::factory()->create(['telegram_chat_id' => null]);

        Livewire::actingAs($sender)
            ->test(\App\Livewire\DirectMessages::class)
            ->set('selectedUserIds', [(string) $withTelegram->id, (string) $withoutTelegram->id])
            ->set('messageText', 'Тестовое сообщение')
            ->call('sendMessage')
            ->assertHasNoErrors();

        $message = DirectMessage::first();
        $this->assertEquals(1, $message->recipients()->count());
        $this->assertTrue($message->recipients->contains($withTelegram));
    }

    public function test_validation_requires_recipients_and_message(): void
    {
        $sender = User::factory()->director()->create();

        Livewire::actingAs($sender)
            ->test(\App\Livewire\DirectMessages::class)
            ->set('selectedUserIds', [])
            ->set('messageText', '')
            ->call('sendMessage')
            ->assertHasErrors(['selectedUserIds', 'messageText']);
    }
}
