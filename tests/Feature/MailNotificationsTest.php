<?php

namespace Tests\Feature;

use App\Livewire\Mails\MailShow;
use App\Livewire\Notifications;
use App\Models\MailDeadline;
use App\Models\MailDocument;
use App\Models\User;
use App\Notifications\MailAssignedNotification;
use App\Notifications\MailStatusChangedNotification;
use App\Services\MailService;
use App\Services\TelegramBotService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\TestCase;

class MailNotificationsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    /**
     * @param  list<array<string, mixed>>  $items
     */
    private function saveDocument(User $author, array $items, ?MailDocument $document = null): MailDocument
    {
        return app(MailService::class)->save($document?->only(['type', 'document_number', 'document_date', 'title']) ?? MailDocument::factory()->raw(), $items, $author, $document);
    }

    /**
     * @param  list<User>  $coExecutors
     * @return array<string, mixed>
     */
    private function item(?User $main, array $coExecutors = [], string $clause = '4-банд', ?int $id = null, ?string $deadline = null): array
    {
        return [
            'id' => $id,
            'clause' => $clause,
            'content' => 'Тест топшириғи',
            'main_executor_id' => $main?->id,
            'co_executor_ids' => array_map(fn (User $user) => $user->id, $coExecutors),
            'deadlines' => [['deadline' => $deadline ?? today()->addWeek()->toDateString()]],
        ];
    }

    public function test_executors_are_told_about_a_new_document_with_their_own_items(): void
    {
        Notification::fake();
        $mailer = User::factory()->mailer()->create();
        $main = User::factory()->create(['sector_id' => 2, 'locale' => 'uz']);
        $co = User::factory()->create(['sector_id' => 3, 'locale' => 'ru']);
        $other = User::factory()->create(['sector_id' => 4]);

        $document = $this->saveDocument($mailer, [
            $this->item($main, [$co], '4-банд'),
            $this->item($other, [], '5-банд'),
        ]);
        $first = $document->items->firstWhere('clause', '4-банд');

        Notification::assertSentTo($main, MailAssignedNotification::class, function (MailAssignedNotification $notification) use ($main, $first, $document) {
            $text = $notification->toTelegram($main);

            return $notification->itemIds === [$first->id]
                && str_contains($text, $document->document_number)
                && str_contains($text, '4-банд')
                && ! str_contains($text, '5-банд')
                && str_contains($text, __('notifications.mails.as_main', [], 'uz'));
        });
        Notification::assertSentTo($co, MailAssignedNotification::class, fn (MailAssignedNotification $notification) => str_contains($notification->toTelegram($co), __('notifications.mails.as_co', [], 'ru')));
        Notification::assertSentToTimes($other, MailAssignedNotification::class, 1);
        Notification::assertNotSentTo($mailer, MailAssignedNotification::class);
    }

    public function test_editing_tells_only_the_newly_added_executors(): void
    {
        $mailer = User::factory()->mailer()->create();
        $main = User::factory()->create(['sector_id' => 2]);
        $document = $this->saveDocument($mailer, [$this->item($main)]);
        $item = $document->items->sole();

        Notification::fake();
        $added = User::factory()->create(['sector_id' => 3]);
        $this->saveDocument($mailer, [$this->item($main, [$added], '4-банд', $item->id)], $document);

        Notification::assertSentTo($added, MailAssignedNotification::class);
        Notification::assertNotSentTo($main, MailAssignedNotification::class);
    }

    public function test_the_author_and_people_who_left_are_not_notified(): void
    {
        Notification::fake();
        $mailer = User::factory()->mailer()->create();
        $gone = User::factory()->create(['sector_id' => 2, 'leave' => 1]);

        $this->saveDocument($mailer, [$this->item($mailer, [$gone])]);

        Notification::assertNothingSent();
    }

    public function test_a_status_change_by_the_mailer_reaches_the_items_executors(): void
    {
        $mailer = User::factory()->mailer()->create();
        $main = User::factory()->create(['sector_id' => 2, 'locale' => 'uz']);
        $co = User::factory()->create(['sector_id' => 3]);
        $document = $this->saveDocument($mailer, [$this->item($main, [$co])]);
        $deadline = $document->deadlines()->sole();

        Notification::fake();
        $page = Livewire::actingAs($mailer)->test(MailShow::class, ['mailDocument' => $document]);

        $page->set("statusForms.{$deadline->id}.status", MailDeadline::STATUS_RETURNED)
            ->set("statusForms.{$deadline->id}.note", 'Илова етишмайди')
            ->call('updateStatus', $deadline->id)
            ->assertHasNoErrors();

        foreach ([$main, $co] as $executor) {
            Notification::assertSentTo($executor, MailStatusChangedNotification::class, function (MailStatusChangedNotification $notification) use ($executor, $document) {
                $text = $notification->toTelegram($executor);

                return $notification->previousStatus === MailDeadline::STATUS_PENDING
                    && str_contains($text, $document->document_number)
                    && str_contains($text, __('mails.statuses.returned', [], $executor->locale))
                    && str_contains($text, 'Илова етишмайди');
            });
        }
        Notification::assertNotSentTo($mailer, MailStatusChangedNotification::class);

        // Saving the same status again, or resetting to "not sent", is not news.
        $page->call('updateStatus', $deadline->id)
            ->set("statusForms.{$deadline->id}.status", MailDeadline::STATUS_PENDING)
            ->call('updateStatus', $deadline->id);

        Notification::assertSentToTimes($main, MailStatusChangedNotification::class, 1);
    }

    public function test_reminders_list_open_deadlines_per_executor(): void
    {
        $mailer = User::factory()->mailer()->create();
        $first = User::factory()->withTelegram()->create(['sector_id' => 2, 'locale' => 'uz']);
        $second = User::factory()->withTelegram()->create(['sector_id' => 3, 'locale' => 'ru']);
        $offline = User::factory()->create(['sector_id' => 4]);

        $soon = $this->saveDocument($mailer, [$this->item($first, [], '1-банд', null, today()->addDay()->toDateString())]);
        $overdue = $this->saveDocument($mailer, [$this->item($second, [$first], '2-банд', null, today()->subDays(2)->toDateString())]);
        $overdue->deadlines()->update(['status' => MailDeadline::STATUS_RETURNED]);
        $sent = $this->saveDocument($mailer, [$this->item($first, [], '3-банд', null, today()->toDateString())]);
        $sent->deadlines()->update(['status' => MailDeadline::STATUS_IN_REVIEW]);
        $far = $this->saveDocument($mailer, [$this->item($first, [], '4-банд', null, today()->addDays(10)->toDateString())]);
        $this->saveDocument($mailer, [$this->item($offline, [], '5-банд', null, today()->toDateString())]);

        $messages = [];
        $this->mock(TelegramBotService::class, function ($mock) use (&$messages) {
            $mock->shouldReceive('sendMessage')->andReturnUsing(function ($chatId, string $text) use (&$messages): bool {
                $messages[$chatId] = $text;

                return true;
            });
        });

        $this->artisan('mails:deadline-reminders')
            ->expectsOutputToContain('Sent reminders to 2 user(s).')
            ->assertSuccessful();

        $this->assertEqualsCanonicalizing([$first->telegram_chat_id, $second->telegram_chat_id], array_keys($messages));

        $firstText = $messages[$first->telegram_chat_id];
        $this->assertStringContainsString($soon->document_number, $firstText);
        $this->assertStringContainsString($overdue->document_number, $firstText);
        $this->assertStringContainsString(__('notifications.mails.days_overdue', ['days' => 2], 'uz'), $firstText);
        $this->assertStringNotContainsString($sent->document_number, $firstText);
        $this->assertStringNotContainsString($far->document_number, $firstText);

        $this->assertStringContainsString(__('notifications.mails.overdue', [], 'ru'), $messages[$second->telegram_chat_id]);
        $this->assertStringNotContainsString($soon->document_number, $messages[$second->telegram_chat_id]);
    }

    public function test_the_bell_lists_mail_notifications_with_a_link_to_the_document(): void
    {
        $mailer = User::factory()->mailer()->create();
        $main = User::factory()->create(['sector_id' => 2]);
        $document = $this->saveDocument($mailer, [$this->item($main)]);

        Livewire::actingAs($main)
            ->test(Notifications::class)
            ->assertSee($mailer->short_name)
            ->assertSee($document->document_number)
            ->assertSee(route('mails.index', ['document' => $document->id]))
            ->assertSee(__('notifications.assigned_mail'));
    }
}
