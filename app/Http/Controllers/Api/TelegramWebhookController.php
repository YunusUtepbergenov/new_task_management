<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\User;
use App\Services\TelegramBotService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TelegramWebhookController extends Controller
{
    public function __construct(private TelegramBotService $telegram)
    {
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

        if (str_starts_with($text, '/start')) {
            $this->handleStart($chatId, $text);
        } elseif ($text === '/tasks') {
            $this->handleTasks($chatId);
        } elseif ($text === '/kpi') {
            $this->handleKpi($chatId);
        } elseif ($text === '/help') {
            $this->handleHelp($chatId);
        } elseif ($text === '/unlink') {
            $this->handleUnlink($chatId);
        } else {
            $this->telegram->sendMessage($chatId, "Неизвестная команда. Введите /help для списка команд.");
        }

        return response()->json(['ok' => true]);
    }

    private function handleStart(int $chatId, string $text): void
    {
        $parts = explode(' ', $text, 2);
        $token = $parts[1] ?? null;

        if (!$token) {
            $this->telegram->sendMessage($chatId, "Добро пожаловать! Для привязки аккаунта сгенерируйте токен в настройках веб-приложения и отправьте:\n\n<code>/start ваш_токен</code>");
            return;
        }

        $hashedToken = hash('sha256', $token);

        $user = User::where('telegram_token', $hashedToken)
            ->where('telegram_token_expires_at', '>', now())
            ->first();

        if (!$user) {
            $this->telegram->sendMessage($chatId, "Неверный или просроченный токен. Пожалуйста, сгенерируйте новый токен в настройках.");
            return;
        }

        $user->update([
            'telegram_chat_id' => $chatId,
            'telegram_token' => null,
            'telegram_token_expires_at' => null,
        ]);

        $this->telegram->sendMessage($chatId, "Аккаунт успешно привязан! Добро пожаловать, <b>{$user->short_name}</b>.\n\nВведите /help для списка команд.");
    }

    private function handleTasks(int $chatId): void
    {
        $user = User::where('telegram_chat_id', $chatId)->first();

        if (!$user) {
            $this->telegram->sendMessage($chatId, "Ваш аккаунт не привязан. Используйте /start для привязки.");
            return;
        }

        $tasks = Task::where('user_id', $user->id)
            ->whereIn('status', ['Не прочитано', 'Выполняется'])
            ->orWhere(function ($q) use ($user) {
                $q->where('user_id', $user->id)->where('overdue', 1);
            })
            ->orderByRaw("FIELD(status, 'Не прочитано', 'Выполняется') ASC")
            ->orderBy('deadline')
            ->limit(20)
            ->get();

        if ($tasks->isEmpty()) {
            $this->telegram->sendMessage($chatId, "У вас нет активных задач.");
            return;
        }

        $lines = [];

        foreach ($tasks as $task) {
            $deadline = Carbon::parse($task->extended_deadline ?? $task->deadline)->format('Y-m-d');
            $lines[] = "📌 {$task->name}\n📅 Deadline: {$deadline}\nStatus: {$task->status}";
        }

        $this->telegram->sendMessage($chatId, implode("\n\n", $lines));
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

        $message = "<b>KPI за {$month}:</b>\n\n"
            . "KPI (норма): <b>{$kpi['kpi']}</b> баллов\n"
            . "KPI (итого): <b>{$kpi['ovr_kpi']}</b> баллов";

        $this->telegram->sendMessage($chatId, $message);
    }

    private function handleHelp(int $chatId): void
    {
        $message = "<b>Доступные команды:</b>\n\n"
            . "/tasks — Список активных задач\n"
            . "/kpi — Текущий KPI за месяц\n"
            . "/help — Список команд\n"
            . "/unlink — Отвязать Telegram от аккаунта";

        $this->telegram->sendMessage($chatId, $message);
    }

    private function handleUnlink(int $chatId): void
    {
        $user = User::where('telegram_chat_id', $chatId)->first();

        if (!$user) {
            $this->telegram->sendMessage($chatId, "Ваш аккаунт не привязан.");
            return;
        }

        $user->update(['telegram_chat_id' => null]);

        $this->telegram->sendMessage($chatId, "Аккаунт успешно отвязан. Вы больше не будете получать уведомления.");
    }
}
