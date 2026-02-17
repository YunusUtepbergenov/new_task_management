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

        $users = User::whereNotNull('telegram_chat_id')->get();

        if ($users->isEmpty()) {
            $this->info('No users with linked Telegram accounts.');
            return self::SUCCESS;
        }

        $count = 0;

        foreach ($users as $user) {
            $tasks = Task::where('user_id', $user->id)
                ->whereRaw('DATE(COALESCE(extended_deadline, deadline)) = ?', [$today])
                ->whereIn('status', ['Не прочитано', 'Выполняется'])
                ->get();

            if ($tasks->isEmpty()) {
                continue;
            }

            $lines = ["⏰ <b>Напоминание: задачи на сегодня!</b>\n📅 {$today}\n"];

            foreach ($tasks as $i => $task) {
                $statusEmoji = $task->status === 'Не прочитано' ? '🆕' : '🔵';
                $lines[] = "{$statusEmoji} " . ($i + 1) . ". <b>{$task->name}</b>\n     Статус: {$task->status}";
            }

            $lines[] = "\n💪 Удачного рабочего дня!";

            $telegram->sendMessage($user->telegram_chat_id, implode("\n", $lines));
            $count++;
        }

        $this->info("Sent reminders to {$count} user(s).");

        return self::SUCCESS;
    }
}
