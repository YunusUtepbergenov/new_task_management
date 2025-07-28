<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;
use Illuminate\Http\Request;
use App\Models\User;

class TelegramAuthController extends Controller
{
    public function verify(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
        ]);

        $user = User::select(['id', 'name', 'sector_id', 'role_id', 'log_id'])->where('telegram_token', $request->token)->first();

        if (!$user) {
            return response()->json(['error' => 'Invalid token'], 403);
        }

        return response()->json([
            'message' => 'Authenticated',
            'user' => $user
        ]);
    }

    public function getTasks(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
        ]);

        $user = User::where('telegram_token', $request->token)->first();

        $tasks = Task::with('user:id,name,sector_id,role_id')
                ->where('user_id', $user->id)
                ->where('status', '<>', 'Выполнено')
                ->whereNull('project_id')
                ->orderByRaw('COALESCE(extended_deadline, deadline)')
                ->get();

        return response()->json([
            'message' => 'Success',
            'tasks' => $tasks
        ]);
    }
}
