<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\User;
use App\Services\DirectMessageService;
use App\Services\TelegramBotService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class TelegramWebhookController extends Controller
{
    public function __construct(
        private TelegramBotService $telegram,
        private DirectMessageService $directMessageService,
    ) {
    }

    public function handle(Request $request): JsonResponse
    {
        $secret = config('services.telegram.webhook_secret');
        if ($secret && $request->header('X-Telegram-Bot-Api-Secret-Token') !== $secret) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $update = $request->all();
        $message = $update['message'] ?? null;

        if (!$message || !isset($message['text'])) {
            return response()->json(['ok' => true]);
        }

        $chatId = $message['chat']['id'];
        $text = trim($message['text']);

        if ($text === '/start') {
            $this->handleStart($chatId);
        } elseif ($text === '/tasks') {
            $this->handleTasks($chatId);
        } elseif ($text === '/kpi') {
            $this->handleKpi($chatId);
        } elseif ($text === '/help') {
            $this->handleHelp($chatId);
        } elseif ($text === '/unlink') {
            $this->handleUnlink($chatId);
        } elseif ($text === '/send') {
            $this->handleSend($chatId);
        } elseif (!str_starts_with($text, '/') && preg_match('/^\d[\d,\s]*:/', $text) && Cache::has("tg_send_{$chatId}")) {
            $this->handleSendReply($chatId, $text);
        } elseif (!str_starts_with($text, '/')) {
            $this->handleToken($chatId, $text);
        } else {
            $locale = $this->getUserLocale($chatId);
            $this->telegram->sendMessage($chatId, __('notifications.bot.unknown_command', [], $locale) . "\n\n" . __('notifications.bot.enter_help', [], $locale));
        }

        return response()->json(['ok' => true]);
    }

    private function getUserLocale(int $chatId): string
    {
        $user = User::where('telegram_chat_id', $chatId)->first();

        return $user->locale ?? 'ru';
    }

    private function handleStart(int $chatId): void
    {
        $user = User::where('telegram_chat_id', $chatId)->first();

        if ($user) {
            $locale = $user->locale ?? 'ru';
            $this->telegram->sendMessage($chatId, __('notifications.bot.welcome_back', ['name' => $user->short_name], $locale) . "\n\n" . __('notifications.bot.help_hint', [], $locale));
            return;
        }

        $this->telegram->sendMessage($chatId, __('notifications.bot.welcome', [], 'ru') . "\n\n" . __('notifications.bot.link_instructions', [], 'ru'));
    }

    private function handleToken(int $chatId, string $token): void
    {
        $hashedToken = hash('sha256', $token);

        $user = User::where('telegram_token', $hashedToken)
            ->where('telegram_token_expires_at', '>', now())
            ->first();

        if (!$user) {
            $this->telegram->sendMessage($chatId, __('notifications.bot.invalid_token', [], 'ru') . "\n\n" . __('notifications.bot.generate_new_token', [], 'ru'));
            return;
        }

        $user->update([
            'telegram_chat_id' => $chatId,
            'telegram_token' => null,
            'telegram_token_expires_at' => null,
        ]);

        $locale = $user->locale ?? 'ru';
        $this->telegram->sendMessage($chatId, __('notifications.bot.account_linked', [], $locale) . "\n\n" . __('notifications.bot.welcome_name', ['name' => $user->short_name], $locale) . "\n\n" . __('notifications.bot.will_receive', [], $locale) . "\n\n" . __('notifications.bot.help_hint', [], $locale));
    }

    private function handleTasks(int $chatId): void
    {
        $user = User::where('telegram_chat_id', $chatId)->first();

        if (!$user) {
            $this->telegram->sendMessage($chatId, __('notifications.bot.not_linked', [], 'ru') . "\n\n" . __('notifications.bot.use_start', [], 'ru'));
            return;
        }

        $locale = $user->locale ?? 'ru';

        $tasks = Task::where('user_id', $user->id)
            ->whereIn('status', ['Не прочитано', 'Выполняется'])
            ->orderByRaw("FIELD(status, 'Не прочитано', 'Выполняется') ASC")
            ->orderBy('deadline')
            ->limit(20)
            ->get();

        if ($tasks->isEmpty()) {
            $this->telegram->sendMessage($chatId, __('notifications.bot.no_active_tasks', [], $locale) . "\n\n" . __('notifications.bot.all_completed', [], $locale));
            return;
        }

        $lines = [__('notifications.bot.active_tasks_count', ['count' => $tasks->count()], $locale)];

        $statusUnread = __('notifications.bot.status_unread', [], $locale);
        $statusInProgress = __('notifications.bot.status_in_progress', [], $locale);

        foreach ($tasks as $i => $task) {
            $deadline = Carbon::parse($task->extended_deadline ?? $task->deadline)->format('Y-m-d');
            $statusEmoji = match($task->status) {
                'Не прочитано' => '🆕',
                'Выполняется' => '🔵',
                default => '⚪',
            };
            if ($task->overdue) {
                $statusEmoji = '🔴';
            }
            $statusLabel = $task->status === 'Не прочитано' ? $statusUnread : $statusInProgress;
            $num = $i + 1;
            $lines[] = "\n━━━━━━━━━━━━━━━━━━━\n<b>{$num}.</b> 📌 <b>{$task->name}</b>\n" . __('notifications.telegram.deadline', [], $locale) . " {$deadline}\n{$statusEmoji} {$statusLabel}";
        }

        $lines[] = "\n━━━━━━━━━━━━━━━━━━━";

        $this->telegram->sendMessage($chatId, implode("", $lines));
    }

    private function handleKpi(int $chatId): void
    {
        $user = User::where('telegram_chat_id', $chatId)->first();

        if (!$user) {
            $this->telegram->sendMessage($chatId, __('notifications.bot.not_linked', [], 'ru') . " " . __('notifications.bot.use_start', [], 'ru'));
            return;
        }

        $locale = $user->locale ?? 'ru';
        $kpi = $user->kpiBoth();
        $month = Carbon::now()->translatedFormat('F Y');

        $message = __('notifications.bot.kpi_title', ['month' => $month], $locale) . "\n\n"
            . "n━━━━━━━━━━━━━━━━━━━\n"
            . __('notifications.bot.kpi_norm', [], $locale) . " <b>{$kpi['kpi']}</b> " . __('notifications.bot.kpi_points', [], $locale) . "\n"
            . __('notifications.bot.kpi_total', [], $locale) . " <b>{$kpi['ovr_kpi']}</b> " . __('notifications.bot.kpi_points', [], $locale) . "\n"
            . "n━━━━━━━━━━━━━━━━━━━";

        $this->telegram->sendMessage($chatId, $message);
    }

    private function handleHelp(int $chatId): void
    {
        $user = User::where('telegram_chat_id', $chatId)->first();
        $locale = $user->locale ?? 'ru';

        $message = __('notifications.bot.available_commands', [], $locale) . "\n\n"
            . __('notifications.bot.cmd_tasks', [], $locale) . "\n"
            . __('notifications.bot.cmd_kpi', [], $locale) . "\n"
            . __('notifications.bot.cmd_help', [], $locale) . "\n"
            . __('notifications.bot.cmd_unlink', [], $locale);

        if ($user && ($user->isDeputy() || $user->isDirector())) {
            $message .= "\n" . __('notifications.bot.cmd_send', [], $locale);
        }

        $this->telegram->sendMessage($chatId, $message);
    }

    private function handleSend(int $chatId): void
    {
        $sender = User::where('telegram_chat_id', $chatId)->first();

        if (!$sender) {
            $this->telegram->sendMessage($chatId, __('notifications.bot.not_linked', [], 'ru') . "\n\n" . __('notifications.bot.use_start', [], 'ru'));
            return;
        }

        $locale = $sender->locale ?? 'ru';

        if (!$sender->isDeputy() && !$sender->isDirector()) {
            $this->telegram->sendMessage($chatId, __('notifications.bot.no_permission', [], $locale));
            return;
        }

        $users = User::whereNotNull('telegram_chat_id')
            ->where('id', '!=', $sender->id)
            ->orderBy('name')
            ->get(['id', 'name']);

        if ($users->isEmpty()) {
            $this->telegram->sendMessage($chatId, __('notifications.bot.no_telegram_users', [], $locale));
            return;
        }

        $userMap = [];
        $lines = [__('notifications.bot.user_list', [], $locale) . "\n"];

        foreach ($users as $i => $user) {
            $num = $i + 1;
            $userMap[$num] = $user->id;
            $lines[] = "<b>{$num}.</b> {$user->name}";
        }

        $lines[] = "\n" . __('notifications.bot.reply_format', [], $locale) . "\n<code>1,3,5: Текст сообщения</code>";

        Cache::put("tg_send_{$chatId}", $userMap, now()->addMinutes(10));

        $this->telegram->sendMessage($chatId, implode("\n", $lines));
    }

    private function handleSendReply(int $chatId, string $text): void
    {
        $sender = User::where('telegram_chat_id', $chatId)->first();

        if (!$sender || (!$sender->isDeputy() && !$sender->isDirector())) {
            Cache::forget("tg_send_{$chatId}");
            return;
        }

        $userMap = Cache::pull("tg_send_{$chatId}");

        if (!$userMap) {
            return;
        }

        $colonPos = strpos($text, ':');
        $numbersPart = trim(substr($text, 0, $colonPos));
        $messageText = trim(substr($text, $colonPos + 1));

        if (empty($messageText)) {
            $locale = $sender->locale ?? 'ru';
            $this->telegram->sendMessage($chatId, __('notifications.bot.empty_message', [], $locale) . "\n\n" . __('notifications.bot.use_send_again', [], $locale));
            return;
        }

        $numbers = array_map('intval', array_filter(preg_split('/[\s,]+/', $numbersPart)));
        $recipientIds = [];

        foreach ($numbers as $num) {
            if (isset($userMap[$num])) {
                $recipientIds[] = $userMap[$num];
            }
        }

        if (empty($recipientIds)) {
            $locale = $sender->locale ?? 'ru';
            $this->telegram->sendMessage($chatId, __('notifications.bot.no_users_found', [], $locale) . "\n\n" . __('notifications.bot.use_send_again', [], $locale));
            return;
        }

        $directMessage = $this->directMessageService->send($sender, $recipientIds, $messageText, 'telegram');
        $deliveredCount = $directMessage->recipients()->wherePivot('delivered', true)->count();

        $locale = $sender->locale ?? 'ru';
        $this->telegram->sendMessage($chatId, __('notifications.bot.message_sent', ['delivered' => $deliveredCount, 'total' => count($recipientIds)], $locale));
    }

    private function handleUnlink(int $chatId): void
    {
        $user = User::where('telegram_chat_id', $chatId)->first();

        if (!$user) {
            $this->telegram->sendMessage($chatId, __('notifications.bot.not_linked', [], 'ru'));
            return;
        }

        $locale = $user->locale ?? 'ru';
        $user->update(['telegram_chat_id' => null]);

        $this->telegram->sendMessage($chatId, __('notifications.bot.unlinked', [], $locale) . "\n\n" . __('notifications.bot.no_more_notifications', [], $locale) . "\n\n" . __('notifications.bot.use_start_relink', [], $locale));
    }
}
