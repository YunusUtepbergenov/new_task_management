<?php

namespace App\Livewire\Reports;

use App\Models\Sector;
use App\Traits\HasTaskDeletion;
use App\Traits\HasTaskView;
use Illuminate\Support\Facades\Auth;
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
    use HasTaskView, HasTaskDeletion;

    public function placeholder(): \Illuminate\Contracts\View\View
    {
        return view('livewire.placeholders.loading');
    }

    public $selectedWeek;
    public $weeks = [];

    #[On('task-updated')]
    #[On('task-created')]
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

        $weeks = [];
        foreach ($period as $weekStart) {
            $weeks[$weekStart->toDateString()] =
                $weekStart->format('d M Y') . ' – ' .
                $weekStart->copy()->endOfWeek()->format('d M Y');
        }

        $this->weeks = array_reverse($weeks, true);
    }

    public function toggleProtocol($taskId): void
    {
        $task = Task::findOrFail($taskId);
        $user = Auth::user();

        if (!$user->isDeputy() && !$user->isHR()) {
            if (!$user->isHead() || $user->sector_id !== $task->sector_id) {
                return;
            }
        }

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

    public function render(): \Illuminate\Contracts\View\View
    {
        [$start, $end] = $this->getWeekRange();
        $allowedSectors = [2, 3, 4, 5, 6, 7, 8, 9, 10, 12, 13, 14, 15, 16];

        $sectorNames = Sector::whereIn('id', $allowedSectors)->pluck('name', 'id');

        $tasks = Task::with([
                'user:id,name',
                'score:id,name',
            ])
            ->select(['id', 'name', 'status', 'deadline', 'extended_deadline', 'for_protocol', 'creator_id', 'sector_id', 'score_id', 'user_id', 'group_id', 'repeat_id'])
            ->where(function ($query) use ($start, $end) {
                $query->whereRaw('COALESCE(extended_deadline, deadline) BETWEEN ? AND ?', [$start, $end])
                    ->orWhere(function ($query) use ($start) {
                        $query->whereRaw('COALESCE(extended_deadline, deadline) < ?', [$start])
                            ->whereIn('status', ['Не прочитано', 'Выполняется', 'Дорабатывается']);
                    });
            })
            ->whereIn('sector_id', $allowedSectors)
            ->orderBy('id')
            ->get()
            ->groupBy(fn ($task) => $task->group_id ?? $task->id);

        $groupedBySector = [];
        foreach ($sectorNames as $id => $name) {
            $groupedBySector[$name] = [];
        }

        foreach ($tasks as $group) {
            $main = $group->first();
            $sectorName = $sectorNames[$main->sector_id] ?? 'Без сектора';
            $groupedBySector[$sectorName][] = $group->toArray();
        }

        $user = Auth::user();
        if ($user->isHead() && $user->sector_id) {
            $ownSector = $sectorNames[$user->sector_id] ?? null;
            if ($ownSector && isset($groupedBySector[$ownSector])) {
                $groupedBySector = [$ownSector => $groupedBySector[$ownSector]] + $groupedBySector;
            }
        }

        return view('livewire.reports.weekly-tasks-overview', [
            'groupedTasks' => $groupedBySector,
        ]);
    }
}
