<?php

namespace App\Exports;

use App\Models\Task;
use Maatwebsite\Excel\Concerns\FromCollection;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class WeeklyTasksExport implements FromView
{
    public function view(): View
    {
        $startOfWeek = now()->startOfWeek(); // Monday
        $endOfWeek = now()->endOfWeek();     // Sunday

        $tasks = Task::with(['user', 'score'])
            ->whereBetween('deadline', [$startOfWeek, $endOfWeek])
            ->orderBy('deadline')
            ->get();

        return view('exports.weekly_tasks', [
            'tasks' => $tasks
        ]);
    }
}
