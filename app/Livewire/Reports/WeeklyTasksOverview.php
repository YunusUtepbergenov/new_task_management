<?php

namespace App\Livewire\Reports;

use Livewire\Attributes\Lazy;
use Livewire\Attributes\On;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\WeeklyTasksExport;
use App\Models\Task;
use Carbon\{Carbon, CarbonPeriod};

#[Lazy]
class WeeklyTasksOverview extends Component
{
    public function placeholder(): \Illuminate\Contracts\View\View
    {
        return view('livewire.placeholders.loading');
    }

    public $selectedWeek;
    public $weeks = [];

    #[On('task-updated')]
    public function refreshTasks(): void
    {
        // Re-render triggers fresh data from render()
    }

    public function mount(): void
    {
        $this->generateWeekOptions();
        $this->selectedWeek = now()->startOfWeek()->toDateString();
    }

    public function generateWeekOptions(): void
    {
        $start = now()->subWeeks(12)->startOfWeek();
        $end = now()->addWeek()->startOfWeek();
        $period = CarbonPeriod::create($start, '1 week', $end);

        foreach ($period as $weekStart) {
            $this->weeks[] = $weekStart->toDateString();
        }

        $this->weeks = array_reverse($this->weeks);
    }

    public function toggleProtocol($taskId): void
    {
        $task = Task::findOrFail($taskId);
        $newValue = !$task->for_protocol;

        if ($task->group_id) {
            Task::where('group_id', $task->group_id)->update(['for_protocol' => $newValue]);
        } else {
            $task->update(['for_protocol' => $newValue]);
        }
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
        
        $tasks = Task::with(['user', 'sector', 'score'])
            ->whereRaw('COALESCE(extended_deadline, deadline) BETWEEN ? AND ?', [$start, $end])
            ->whereIn('sector_id', $allowedSectors)
            ->orderBy('sector_id')
            ->get()
            ->groupBy(function ($task) {
                return $task->group_id ?? $task->id;
            });

        $groupedBySector = [];

        foreach ($tasks as $group) {
            $main = $group->first();
            $sectorName = $main->sector->name ?? 'Без сектора';
            $groupedBySector[$sectorName][] = $group->toArray(); // convert each group to array
        }

        return view('livewire.reports.weekly-tasks-overview', [
            'groupedTasks' => $groupedBySector,
        ]);
    }
}
