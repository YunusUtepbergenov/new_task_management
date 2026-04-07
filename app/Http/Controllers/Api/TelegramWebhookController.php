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
            $this->telegram->sendMessage($chatId, "🤔 Неизвестная команда.\n\n📖 Введите /help для списка команд.");
        }

        return response()->json(['ok' => true]);
    }

    private function handleStart(int $chatId): void
    {
        $user = User::where('telegram_chat_id', $chatId)->first();

        if ($user) {
            $this->telegram->sendMessage($chatId, "👋 <b>С возвращением, {$user->short_name}!</b>\n\n📋 Введите /help для списка команд.");
            return;
        }

        $this->telegram->sendMessage($chatId, "👋 <b>Добро пожаловать в Ijro.cerr.uz!</b>\n\n🔑 Для привязки аккаунта сгенерируйте токен в настройках веб-приложения и отправьте его сюда.");
    }

    private function handleToken(int $chatId, string $token): void
    {
        $hashedToken = hash('sha256', $token);

        $user = User::where('telegram_token', $hashedToken)
            ->where('telegram_token_expires_at', '>', now())
            ->first();

        if (!$user) {
            $this->telegram->sendMessage($chatId, "❌ <b>Неверный или просроченный токен.</b>\n\n🔄 Пожалуйста, сгенерируйте новый токен в настройках.");
            return;
        }

        $user->update([
            'telegram_chat_id' => $chatId,
            'telegram_token' => null,
            'telegram_token_expires_at' => null,
        ]);

        $this->telegram->sendMessage($chatId, "✅ <b>Аккаунт успешно привязан!</b>\n\n🎉 Добро пожаловать, <b>{$user->short_name}</b>!\n\nТеперь вы будете получать уведомления о задачах.\n\n📋 Введите /help для списка команд.");
    }

    private function handleTasks(int $chatId): void
    {
        $user = User::where('telegram_chat_id', $chatId)->first();

        if (!$user) {
            $this->telegram->sendMessage($chatId, "🔒 Ваш аккаунт не привязан.\n\n🔑 Используйте /start для привязки.");
            return;
        }

        $tasks = Task::where('user_id', $user->id)
            ->whereIn('status', ['Не прочитано', 'Выполняется'])
            ->orderByRaw("FIELD(status, 'Не прочитано', 'Выполняется') ASC")
            ->orderBy('deadline')
            ->limit(20)
            ->get();

        if ($tasks->isEmpty()) {
            $this->telegram->sendMessage($chatId, "🎉 <b>У вас нет активных задач!</b>\n\n✨ Все задачи выполнены.");
            return;
        }

        $lines = ["📋 <b>Ваши активные задачи ({$tasks->count()}):</b>"];

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
            $num = $i + 1;
            $lines[] = "\n━━━━━━━━━━━━━━━━━━━\n<b>{$num}.</b> 📌 <b>{$task->name}</b>\n📅 Срок: {$deadline}\n{$statusEmoji} Статус: {$task->status}";
        }

        $lines[] = "\n━━━━━━━━━━━━━━━━━━━";

        $this->telegram->sendMessage($chatId, implode("", $lines));
    }

    private function handleKpi(int $chatId): void
    {
        $user = User::where('telegram_chat_id', $chatId)->first();

        if (!$user) {
            $this->telegram->sendMessage($chatId, "Ваш аккаунт не привязан. Используйте /start для привязки.");
            return;
        }

        $kpi = $user->kpiBoth();
        $month = Carbon::now()->translatedFormat('F Y');

        $message = "📊 <b>KPI за {$month}</b>\n\n"
            . "n━━━━━━━━━━━━━━━━━━━\n"
            . "🎯 Норма: <b>{$kpi['kpi']}</b> баллов\n"
            . "📈 Итого: <b>{$kpi['ovr_kpi']}</b> баллов\n"
            . "n━━━━━━━━━━━━━━━━━━━";

        $this->telegram->sendMessage($chatId, $message);
    }

    private function handleHelp(int $chatId): void
    {
        $user = User::where('telegram_chat_id', $chatId)->first();

        $message = "📖 <b>Доступные команды:</b>\n\n"
            . "📋 /tasks — Список активных задач\n"
            . "📊 /kpi — Текущий KPI за месяц\n"
            . "❓ /help — Список команд\n"
            . "🔓 /unlink — Отвязать Telegram от аккаунта";

        if ($user && ($user->isDeputy() || $user->isDirector())) {
            $message .= "\n✉️ /send — Отправить сообщение сотрудникам";
        }

        $this->telegram->sendMessage($chatId, $message);
    }

    private function handleSend(int $chatId): void
    {
        $sender = User::where('telegram_chat_id', $chatId)->first();

        if (!$sender) {
            $this->telegram->sendMessage($chatId, "🔒 Ваш аккаунт не привязан.\n\n🔑 Используйте /start для привязки.");
            return;
        }

        if (!$sender->isDeputy() && !$sender->isDirector()) {
            $this->telegram->sendMessage($chatId, "⛔ У вас нет прав для отправки сообщений.");
            return;
        }

        $users = User::whereNotNull('telegram_chat_id')
            ->where('id', '!=', $sender->id)
            ->orderBy('name')
            ->get(['id', 'name']);

        if ($users->isEmpty()) {
            $this->telegram->sendMessage($chatId, "😕 Нет пользователей с привязанным Telegram.");
            return;
        }

        $userMap = [];
        $lines = ["📋 <b>Список пользователей:</b>\n"];

        foreach ($users as $i => $user) {
            $num = $i + 1;
            $userMap[$num] = $user->id;
            $lines[] = "<b>{$num}.</b> {$user->name}";
        }

        $lines[] = "\n📝 <b>Ответьте в формате:</b>\n<code>1,3,5: Текст сообщения</code>";

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
            $this->telegram->sendMessage($chatId, "❌ Текст сообщения не может быть пустым.\n\n📝 Используйте /send чтобы начать заново.");
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
            $this->telegram->sendMessage($chatId, "❌ Не найдены пользователи по указанным номерам.\n\n📝 Используйте /send чтобы начать заново.");
            return;
        }

        $directMessage = $this->directMessageService->send($sender, $recipientIds, $messageText, 'telegram');
        $deliveredCount = $directMessage->recipients()->wherePivot('delivered', true)->count();

        $this->telegram->sendMessage($chatId, "✅ Сообщение отправлено <b>{$deliveredCount}</b> из <b>" . count($recipientIds) . "</b> пользователей.");
    }

    private function handleUnlink(int $chatId): void
    {
        $user = User::where('telegram_chat_id', $chatId)->first();

        if (!$user) {
            $this->telegram->sendMessage($chatId, "Ваш аккаунт не привязан.");
            return;
        }

        $user->update(['telegram_chat_id' => null]);

        $this->telegram->sendMessage($chatId, "✅ <b>Аккаунт успешно отвязан.</b>\n\n🔕 Вы больше не будете получать уведомления.\n\n🔑 Для повторной привязки используйте /start");
    }
}
