<?php

namespace App\Console\Commands;

use App\Models\Task;
use App\Services\TelegramBotService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendWeeklyUnsubmittedReminders extends Command
{
    protected $signature = 'telegram:weekly-unsubmitted-reminders';

    protected $description = 'Send Telegram reminders on Saturday to assignees and creators of unsubmitted tasks this week';

    public function handle(TelegramBotService $telegram): int
    {
        $weekStart = Carbon::now()->startOfWeek()->toDateString();
        $weekEnd   = Carbon::now()->endOfWeek()->toDateString();

        $tasks = Task::with([
                'user:id,short_name,telegram_chat_id,locale',
                'creator:id,short_name,telegram_chat_id,locale',
            ])
            ->whereRaw('DATE(COALESCE(extended_deadline, deadline)) BETWEEN ? AND ?', [$weekStart, $weekEnd])
            ->whereIn('status', ['Не прочитано', 'Выполняется'])
            ->get();

        if ($tasks->isEmpty()) {
            $this->info('No unsubmitted tasks found for this week.');
            return self::SUCCESS;
        }

        // --- Notify assignees ---
        $assigneeCount = 0;
        foreach ($tasks->groupBy('user_id') as $userTasks) {
            $user = $userTasks->first()->user;
            $chatId = $user?->telegram_chat_id;
            if (!$chatId) {
                continue;
            }

            $locale = $user->locale ?? 'ru';
            $lines = [__('notifications.reminders.weekly_title', [], $locale) . "\n" . __('notifications.reminders.week_label', [], $locale) . " {$weekStart} – {$weekEnd}\n"];

            foreach ($userTasks as $i => $task) {
                $statusEmoji = $task->status === 'Не прочитано' ? '🆕' : '🔵';
                $deadline    = $task->extended_deadline ?? $task->deadline;
                $lines[]     = "{$statusEmoji} " . ($i + 1) . ". <b>{$task->name}</b>\n     " . __('notifications.telegram.deadline', [], $locale) . " {$deadline}";
            }

            $lines[] = "\n" . __('notifications.reminders.submit_or_extend', [], $locale);

            $telegram->sendMessage($chatId, implode("\n", $lines));
            $assigneeCount++;
        }

        // --- Notify creators (skip if creator is also the assignee of all their tasks) ---
        $creatorCount = 0;
        foreach ($tasks->groupBy('creator_id') as $creatorId => $creatorTasks) {
            $creator = $creatorTasks->first()->creator;
            $chatId  = $creator?->telegram_chat_id;
            if (!$chatId) {
                continue;
            }

            // Exclude tasks where the creator is also the assignee (already notified above)
            $tasksToReport = $creatorTasks->filter(fn ($t) => $t->user_id !== $creatorId);
            if ($tasksToReport->isEmpty()) {
                continue;
            }

            $locale = $creator->locale ?? 'ru';
            $statusUnread = __('notifications.bot.status_unread', [], $locale);
            $statusInProgress = __('notifications.bot.status_in_progress', [], $locale);

            $lines = ["👤 " . __('notifications.reminders.supervisor_title', [], $locale) . "\n" . __('notifications.reminders.week_label', [], $locale) . " {$weekStart} – {$weekEnd}\n"];

            foreach ($tasksToReport as $i => $task) {
                $statusEmoji  = $task->status === 'Не прочитано' ? '🆕' : '🔵';
                $statusLabel  = $task->status === 'Не прочитано' ? $statusUnread : $statusInProgress;
                $deadline     = $task->extended_deadline ?? $task->deadline;
                $assigneeName = $task->user?->short_name ?? '—';
                $lines[]      = "{$statusEmoji} " . ($i + 1) . ". <b>{$task->name}</b>\n     {$assigneeName}\n     " . __('notifications.telegram.deadline', [], $locale) . " {$deadline}\n     {$statusLabel}";
            }

            $lines[] = "\n" . __('notifications.reminders.tasks_not_submitted', [], $locale);

            $telegram->sendMessage($chatId, implode("\n", $lines));
            $creatorCount++;
        }

        $this->info("Sent reminders to {$assigneeCount} assignee(s) and {$creatorCount} creator(s).");

        return self::SUCCESS;
    }
}
