<?php

namespace App\Exports;

use App\Models\Sector;
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
        $tasks = Task::with('user', 'sector')
            ->whereBetween('deadline', [$this->start, $this->end])
            ->where('for_protocol', true)
            ->get();

         $sectors = Sector::with(['tasks' => function ($query) {
                $query->whereBetween('deadline', [$this->start, $this->end])
                      ->with('user'); // optional, if you need user info
            }])
            ->whereIn('id', [2, 3, 4, 5, 6, 7, 8, 9, 10, 12, 13, 14, 15, 16])
            ->get();


        return view('exports.weekly_tasks', [
            'tasks' => $tasks,
            'sectors' => $sectors,
            'start' => $this->start,
            'end' => $this->end
        ]);
    }
}
