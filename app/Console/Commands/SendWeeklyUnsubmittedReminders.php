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
                'user:id,short_name,telegram_chat_id',
                'creator:id,short_name,telegram_chat_id',
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
            $chatId = $userTasks->first()->user?->telegram_chat_id;
            if (!$chatId) {
                continue;
            }

            $lines = ["📋 <b>Итоги недели: незавершённые задачи</b>\n📅 Неделя: {$weekStart} – {$weekEnd}\n"];

            foreach ($userTasks as $i => $task) {
                $statusEmoji = $task->status === 'Не прочитано' ? '🆕' : '🔵';
                $deadline    = $task->extended_deadline ?? $task->deadline;
                $lines[]     = "{$statusEmoji} " . ($i + 1) . ". <b>{$task->name}</b>\n     Срок: {$deadline}";
            }

            $lines[] = "\n⚠️ Пожалуйста, сдайте задачи на проверку или продлите срок на следующую неделю.";

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

            $lines = ["👤 <b>Отчёт руководителя: незавершённые поручения</b>\n📅 Неделя: {$weekStart} – {$weekEnd}\n"];

            foreach ($tasksToReport as $i => $task) {
                $statusEmoji  = $task->status === 'Не прочитано' ? '🆕' : '🔵';
                $deadline     = $task->extended_deadline ?? $task->deadline;
                $assigneeName = $task->user?->short_name ?? '—';
                $lines[]      = "{$statusEmoji} " . ($i + 1) . ". <b>{$task->name}</b>\n     Ответственный: {$assigneeName}\n     Срок: {$deadline}\n     Статус: {$task->status}";
            }

            $lines[] = "\n⚠️ Задачи не сданы на проверку. Пожалуйста, выясните ситуацию и при необходимости продлите срок.";

            $telegram->sendMessage($chatId, implode("\n", $lines));
            $creatorCount++;
        }

        $this->info("Sent reminders to {$assigneeCount} assignee(s) and {$creatorCount} creator(s).");

        return self::SUCCESS;
    }
}
