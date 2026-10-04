<?php

namespace App\Console\Commands;

use App\Models\MailDeadline;
use App\Models\User;
use App\Services\TelegramBotService;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

/**
 * One Telegram message per executor listing their edo.ijro.uz deadlines that are overdue,
 * due today or due within the next days. Only deadlines still waiting on the executor
 * (not sent, or returned) are included.
 */
class SendMailDeadlineReminders extends Command
{
    protected $signature = 'mails:deadline-reminders {--days=3 : Also remind about deadlines due within this many days}';

    protected $description = 'Send Telegram reminders to executors about edo.ijro.uz deadlines that are due soon or overdue';

    public function handle(TelegramBotService $telegram): int
    {
        $until = today()->addDays(max(0, (int) $this->option('days')));

        $deadlines = MailDeadline::query()
            ->whereIn('status', [MailDeadline::STATUS_PENDING, MailDeadline::STATUS_RETURNED])
            ->whereDate('deadline', '<=', $until)
            ->with([
                'item.document',
                'item.executors' => fn ($executors) => $executors->whereNotNull('telegram_chat_id')->where('leave', 0),
            ])
            ->orderBy('deadline')
            ->get();

        /** @var array<int, array{user: User, deadlines: list<MailDeadline>}> $byUser */
        $byUser = [];

        foreach ($deadlines as $deadline) {
            foreach ($deadline->item->executors as $user) {
                $byUser[$user->id]['user'] = $user;
                $byUser[$user->id]['deadlines'][] = $deadline;
            }
        }

        foreach ($byUser as ['user' => $user, 'deadlines' => $userDeadlines]) {
            $telegram->sendMessage($user->telegram_chat_id, $this->message($user, collect($userDeadlines)));
        }

        $this->info('Sent reminders to '.count($byUser).' user(s).');

        return self::SUCCESS;
    }

    /**
     * @param  Collection<int, MailDeadline>  $deadlines
     */
    private function message(User $user, Collection $deadlines): string
    {
        $locale = $user->locale ?? 'ru';
        $t = fn (string $key, array $replace = []): string => __("notifications.mails.{$key}", $replace, $locale);

        $groups = [
            'overdue' => $deadlines->filter(fn (MailDeadline $deadline): bool => $deadline->deadline->lt(today())),
            'due_today' => $deadlines->filter(fn (MailDeadline $deadline): bool => $deadline->deadline->isToday()),
            'due_soon' => $deadlines->filter(fn (MailDeadline $deadline): bool => $deadline->deadline->gt(today())),
        ];

        $lines = [$t('reminder_title')];

        foreach ($groups as $key => $group) {
            if ($group->isEmpty()) {
                continue;
            }

            $lines[] = '';
            $lines[] = $t($key).':';

            foreach ($group->values() as $index => $deadline) {
                $document = $deadline->item->document;
                $when = $deadline->deadline->format('d.m.Y');

                if ($key === 'overdue') {
                    $when .= ' · '.$t('days_overdue', ['days' => (int) $deadline->deadline->diffInDays(today())]);
                }

                if ($deadline->status === MailDeadline::STATUS_RETURNED) {
                    $when .= ' · '.__('mails.statuses.returned', [], $locale);
                }

                $lines[] = ($index + 1).'. <b>'.e($document->document_number ?: '#'.$document->id).'</b> — '.e($deadline->item->clause ?: __('mails.fields.item', [], $locale));
                $lines[] = '   📅 '.$when;
            }
        }

        $lines[] = '';
        $lines[] = __('notifications.reminders.good_day', [], $locale);

        return implode("\n", $lines);
    }
}
