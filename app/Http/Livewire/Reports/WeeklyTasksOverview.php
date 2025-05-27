<?php

namespace App\Http\Livewire\Reports;

use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\WeeklyTasksExport;
use App\Models\Task;
use Carbon\{Carbon, CarbonPeriod};

class WeeklyTasksOverview extends Component
{

    public $sectors;
    public $selectedWeek;
    public $weeks = [];

    public function mount(){
        $this->generateWeekOptions();
        $this->selectedWeek = now()->startOfWeek()->toDateString();
    }

    public function generateWeekOptions()
    {
        $start = now()->subWeeks(12)->startOfWeek();
        $end = now()->startOfWeek();
        $period = CarbonPeriod::create($start, '1 week', $end);

        foreach ($period as $weekStart) {
            $this->weeks[] = $weekStart->toDateString();
        }

        $this->weeks = array_reverse($this->weeks);
    }

    public function getWeekRange()
    {
        $start = Carbon::parse($this->selectedWeek)->startOfWeek();
        $end = $start->copy()->endOfWeek();
        return [$start, $end];
    }

    public function export()
    {
        [$start, $end] = $this->getWeekRange();
        return Excel::download(new WeeklyTasksExport($start, $end), 'weekly_tasks.xlsx');
    }

    public function render()
    {
        [$start, $end] = $this->getWeekRange();
        $allowedSectors = [2, 3, 4, 5, 6, 7, 8, 9, 10, 12, 13, 14, 15, 16];

        $tasks = Task::with('user', 'sector')
            ->whereBetween('deadline', [$start, $end])
            ->whereIn('sector_id', $allowedSectors)
            ->get()
            ->groupBy(function ($task) {
                return $task->sector->name ?? 'Без сектора';
            });

        return view('livewire.reports.weekly-tasks-overview', [
            'groupedTasks' => $tasks,
        ]);
    }
}
