<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\TelegramBotService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SendBirthdayNotificationsCommandTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Record every Telegram message the command would send.
     *
     * @param  array<int, array{chatId: mixed, text: string}>  $sent
     */
    private function spyOnTelegram(array &$sent): void
    {
        $this->mock(TelegramBotService::class, function ($mock) use (&$sent) {
            $mock->shouldReceive('sendMessage')
                ->andReturnUsing(function ($chatId, $text) use (&$sent) {
                    $sent[] = ['chatId' => $chatId, 'text' => $text];

                    return true;
                });
        });
    }

    public function test_notifies_colleagues_about_todays_birthday(): void
    {
        $this->seed();

        $celebrant = User::factory()->create([
            'name' => 'Zzz Celebrant Unique',
            'birth_date' => now()->subYears(30)->format('Y-m-d'),
            'telegram_chat_id' => null,
            'leave' => 0,
        ]);

        $colleague = User::factory()->create([
            'name' => 'Colleague One',
            'telegram_chat_id' => 556001,
            'locale' => 'ru',
            'leave' => 0,
        ]);

        $sent = [];
        $this->spyOnTelegram($sent);

        $this->artisan('telegram:birthday-notifications')->assertSuccessful();

        $toColleague = collect($sent)->firstWhere('chatId', 556001);
        $this->assertNotNull($toColleague, 'Colleague with linked Telegram should be notified.');
        $this->assertStringContainsString($celebrant->name, $toColleague['text']);
        $this->assertStringContainsString('наш коллега', $toColleague['text']);
    }

    public function test_celebrant_is_not_told_to_congratulate_themselves(): void
    {
        $this->seed();

        $celebrant = User::factory()->create([
            'name' => 'Zzz Self Celebrant',
            'birth_date' => now()->subYears(25)->format('Y-m-d'),
            'telegram_chat_id' => 556100,
            'leave' => 0,
        ]);

        $sent = [];
        $this->spyOnTelegram($sent);

        $this->artisan('telegram:birthday-notifications')->assertSuccessful();

        $selfMessage = collect($sent)->first(
            fn ($m) => $m['chatId'] === 556100 && str_contains($m['text'], $celebrant->name)
        );

        $this->assertNull($selfMessage, 'The birthday person must not be told to congratulate themselves.');
    }

    public function test_message_is_localized_to_recipient_locale(): void
    {
        $this->seed();

        User::factory()->create([
            'name' => 'Zzz Uz Celebrant',
            'birth_date' => now()->subYears(40)->format('Y-m-d'),
            'telegram_chat_id' => null,
            'leave' => 0,
        ]);

        User::factory()->create([
            'name' => 'Uz Colleague',
            'telegram_chat_id' => 556200,
            'locale' => 'uz',
            'leave' => 0,
        ]);

        $sent = [];
        $this->spyOnTelegram($sent);

        $this->artisan('telegram:birthday-notifications')->assertSuccessful();

        $toUz = collect($sent)->firstWhere('chatId', 556200);
        $this->assertNotNull($toUz);
        $this->assertStringContainsString('ҳамкасбимиз', $toUz['text']);
    }

    public function test_left_employees_do_not_trigger_notifications(): void
    {
        $this->seed();

        // Ensure no seeded user shares today's birthday so the assertion is clean.
        User::query()->update(['birth_date' => now()->addDay()->format('Y-m-d')]);

        $leftCelebrant = User::factory()->create([
            'name' => 'Zzz Left Celebrant',
            'birth_date' => now()->format('Y-m-d'),
            'telegram_chat_id' => null,
            'leave' => 1,
        ]);

        User::factory()->create([
            'name' => 'Active Colleague',
            'birth_date' => now()->addDays(2)->format('Y-m-d'),
            'telegram_chat_id' => 556300,
            'leave' => 0,
        ]);

        $sent = [];
        $this->spyOnTelegram($sent);

        $this->artisan('telegram:birthday-notifications')->assertSuccessful();

        $mentioningLeft = collect($sent)->first(fn ($m) => str_contains($m['text'], $leftCelebrant->name));
        $this->assertNull($mentioningLeft, 'A user on leave should not be announced as a birthday.');
    }
}
