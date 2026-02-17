<?php

namespace App\Exports;
use App\Models\Task;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Carbon\Carbon;

class WeeklyTasksExport implements FromView
{
    protected $start;
    protected $end;

    public function __construct(Carbon $start, Carbon $end)
    {
        $this->start = $start;
        $this->end = $end;
    }

    public function view(): View
    {
        $rawTasks = Task::with(['user', 'sector', 'score'])
            ->whereRaw('COALESCE(extended_deadline, deadline) BETWEEN ? AND ?', [$this->start, $this->end])
            ->where('for_protocol', true)
            ->get()
            ->groupBy(function ($task) {
                return $task->group_id ?? $task->id;
            });

        $grouped = [];

        foreach ($rawTasks as $group) {
            $main = $group->first();
            $scoreName = $main->score->name ?? 'Без категории';
            $responsibles = $group->pluck('user')->filter()->map(fn ($u) => $u->short_name)->unique()->join(', ');

            $main->merged_responsibles = $responsibles;

            $grouped[$scoreName][] = $main;

        }

        return view('exports.weekly_tasks', [
            'tasks' => $grouped,
            'start' => $this->start,
            'end' => $this->end,
        ]);
    }
}
