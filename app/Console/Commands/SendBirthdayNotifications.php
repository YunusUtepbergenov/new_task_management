<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\TelegramBotService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

/**
 * Announce today's employee birthdays to everyone else so colleagues can send
 * their congratulations. The celebrant is never notified about their own
 * birthday; each recipient receives a single message listing every colleague
 * whose birthday falls on today (usually just one).
 */
class SendBirthdayNotifications extends Command
{
    protected $signature = 'telegram:birthday-notifications';

    protected $description = 'Notify staff on Telegram about colleagues whose birthday is today so they can congratulate them';

    public function handle(TelegramBotService $telegram): int
    {
        $today = Carbon::today();

        $celebrants = User::query()
            ->where('leave', 0)
            ->whereNotNull('birth_date')
            ->whereMonth('birth_date', $today->month)
            ->whereDay('birth_date', $today->day)
            ->orderBy('name')
            ->get(['id', 'name']);

        if ($celebrants->isEmpty()) {
            $this->info('No birthdays today.');

            return self::SUCCESS;
        }

        $recipients = User::query()
            ->where('leave', 0)
            ->whereNotNull('telegram_chat_id')
            ->get(['id', 'telegram_chat_id', 'locale']);

        $sent = 0;

        foreach ($recipients as $recipient) {
            // A colleague should not be told to congratulate themselves.
            $others = $celebrants->where('id', '!=', $recipient->id);

            if ($others->isEmpty()) {
                continue;
            }

            $message = $this->buildMessage($recipient->locale ?? 'ru', $others);

            if ($telegram->sendMessage($recipient->telegram_chat_id, $message)) {
                $sent++;
            }

            // Keep well under Telegram's bulk rate limit when broadcasting to everyone.
            usleep(50_000);
        }

        $this->info("Notified {$sent} user(s) about {$celebrants->count()} birthday(s) today.");

        return self::SUCCESS;
    }

    /**
     * Build the localized announcement listing every celebrant. Names are
     * HTML-escaped because the bot sends with parse_mode=HTML.
     *
     * @param  Collection<int, User>  $celebrants
     */
    private function buildMessage(string $locale, Collection $celebrants): string
    {
        $count = $celebrants->count();

        $lines = [
            trans_choice('notifications.birthday.intro', $count, [], $locale),
        ];

        foreach ($celebrants as $celebrant) {
            $lines[] = '🎂 <b>'.e($celebrant->name).'</b>';
        }

        $lines[] = '';
        $lines[] = trans_choice('notifications.birthday.congratulate', $count, [], $locale);

        return implode("\n", $lines);
    }
}
