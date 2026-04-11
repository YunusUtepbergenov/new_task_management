<?php

namespace App\Console\Commands;

use App\Models\TurnstileLog;
use App\Models\User;
use App\Services\TelegramBotService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class SendTurnstileNotifications extends Command
{
    protected $signature = 'telegram:turnstile-notifications';

    protected $description = 'Send Telegram notifications when users enter or exit via turnstile';

    protected array $entryNames = ['Турникет 1', 'Ð¢ÑƒÑ€Ð½Ð¸ÐºÐµÑ‚ 1'];
    protected array $exitNames = ['Турникет 2', 'Ð¢ÑƒÑ€Ð½Ð¸ÐºÐµÑ‚ 2'];

    public function handle(TelegramBotService $telegram): int
    {
        $users = User::whereNotNull('log_id')
            ->whereNotNull('telegram_chat_id')
            ->get()
            ->keyBy('log_id');

        if ($users->isEmpty()) {
            $this->info('No users with both log_id and telegram_chat_id.');
            return self::SUCCESS;
        }

        $since = Carbon::now()->subMinutes(3);

        $logs = TurnstileLog::on('turnstile')
            ->whereIn('id', $users->keys()->toArray())
            ->where('auth_datetime', '>=', $since)
            ->orderBy('auth_datetime')
            ->get();

        if ($logs->isEmpty()) {
            $this->info('No new turnstile entries.');
            return self::SUCCESS;
        }

        $count = 0;

        foreach ($logs as $log) {
            $cacheKey = "turnstile_notified:{$log->id}_{$log->auth_datetime}";

            if (Cache::has($cacheKey)) {
                continue;
            }

            $user = $users->get($log->id);

            if (! $user) {
                continue;
            }

            $locale = $user->locale ?? 'ru';
            $time = Carbon::parse($log->auth_time)->format('H:i');

            if (in_array($log->device_name, $this->entryNames, true)) {
                $message = '🟢 ' . __('notifications.turnstile.entry', ['time' => $time], $locale);
            } elseif (in_array($log->device_name, $this->exitNames, true)) {
                $message = '🔴 ' . __('notifications.turnstile.exit', ['time' => $time], $locale);
            } else {
                continue;
            }

            $telegram->sendMessage($user->telegram_chat_id, $message);
            Cache::put($cacheKey, true, now()->addMinutes(10));
            $count++;
        }

        $this->info("Sent {$count} turnstile notification(s).");

        return self::SUCCESS;
    }
}
