<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TelegramAuthController extends Controller
{
    public function verify(Request $request): JsonResponse
    {
        $request->validate([
            'token' => 'required|string',
            'chat_id' => 'required|integer',
        ]);

        $hashedToken = hash('sha256', $request->token);

        $user = User::where('telegram_token', $hashedToken)
            ->where('telegram_token_expires_at', '>', now())
            ->first();

        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'Invalid or expired token'], 401);
        }

        $user->update([
            'telegram_chat_id' => $request->chat_id,
            'telegram_token' => null,
            'telegram_token_expires_at' => null,
        ]);

        return response()->json(['status' => 'success', 'user_id' => $user->id, 'user_name' => $user->name]);
    }

    public function getTasks(Request $request): JsonResponse
    {
        $request->validate([
            'chat_id' => 'required|integer',
        ]);

        $user = User::where('telegram_chat_id', $request->chat_id)->first();

        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'User not found'], 404);
        }

        $tasks = Task::where('user_id', $user->id)
            ->whereIn('status', ['Не прочитано', 'Выполняется'])
            ->orderBy('deadline')
            ->get(['id', 'name', 'status', 'deadline', 'overdue']);

        return response()->json(['status' => 'success', 'tasks' => $tasks]);
    }
}
