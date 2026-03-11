<?php

namespace App\Console\Commands;

use App\Models\Task;
use App\Models\User;
use App\Services\TelegramBotService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendDeadlineReminders extends Command
{
    protected $signature = 'telegram:deadline-reminders';

    protected $description = 'Send Telegram reminders to users about tasks due today';

    public function handle(TelegramBotService $telegram): int
    {
        $today = Carbon::today()->toDateString();

        $tasksByUser = Task::with('user:id,telegram_chat_id')
            ->whereHas('user', fn ($q) => $q->whereNotNull('telegram_chat_id'))
            ->whereRaw('DATE(COALESCE(extended_deadline, deadline)) = ?', [$today])
            ->whereIn('status', ['Не прочитано', 'Выполняется'])
            ->get()
            ->groupBy('user_id');

        if ($tasksByUser->isEmpty()) {
            $this->info('No deadline tasks for today.');
            return self::SUCCESS;
        }

        $count = 0;

        foreach ($tasksByUser as $userId => $tasks) {
            $chatId = $tasks->first()->user->telegram_chat_id;

            $lines = ["⏰ <b>Напоминание: задачи на сегодня!</b>\n📅 {$today}\n"];

            foreach ($tasks as $i => $task) {
                $statusEmoji = $task->status === 'Не прочитано' ? '🆕' : '🔵';
                $lines[] = "{$statusEmoji} " . ($i + 1) . ". <b>{$task->name}</b>\n     Статус: {$task->status}";
            }

            $lines[] = "\n💪 Удачного рабочего дня!";

            $telegram->sendMessage($chatId, implode("\n", $lines));
            $count++;
        }

        $this->info("Sent reminders to {$count} user(s).");

        return self::SUCCESS;
    }
}
