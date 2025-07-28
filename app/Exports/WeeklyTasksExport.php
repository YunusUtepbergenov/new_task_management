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
        $tasks = Task::with(['user', 'sector', 'score'])
                 ->whereRaw('COALESCE(extended_deadline, deadline) BETWEEN ? AND ?', [$this->start, $this->end])
                ->where('for_protocol', true)
                ->get()
                ->groupBy(function ($task) {
                    return $task->group_id ?? $task->id;
                })
                ->map(function ($group) {
                    $main = $group->first();
                    $responsibles = $group->pluck('user')->filter()->map(fn($u) => $u->employee_name())->unique()->join(', ');

        return view('exports.weekly_tasks', [
            'tasks' => $tasks,
            'start' => $this->start,
            'end' => $this->end
        ]);
    }
}
