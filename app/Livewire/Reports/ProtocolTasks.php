<?php

namespace App\Livewire\Reports;

use App\Exports\WeeklyTasksExport;
use App\Models\Sector;
use App\Models\Task;
use App\Models\TaskLog;
use App\Traits\HasTaskView;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Lazy;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

#[Lazy]
class ProtocolTasks extends Component
{
    use HasTaskView;

    public function placeholder(): \Illuminate\Contracts\View\View
    {
        return view('livewire.placeholders.loading');
    }

    public $selectedWeek;
    public $weeks = [];

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

    public function getWeekRange(): array
    {
        $start = Carbon::parse($this->selectedWeek)->startOfWeek();
        $end = $start->copy()->endOfWeek();
        return [$start, $end];
    }

    public function removeFromProtocol(int $taskId): void
    {
        $task = Task::findOrFail($taskId);
        $user = Auth::user();

        if (!$user->isDeputy() && !$user->isHR()) {
            return;
        }

        if ($task->group_id) {
            Task::where('group_id', $task->group_id)->update(['for_protocol' => false]);
        } else {
            $task->update(['for_protocol' => false]);
        }
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
            ->select(['id', 'name', 'status', 'deadline', 'extended_deadline', 'for_protocol', 'creator_id', 'sector_id', 'score_id', 'user_id', 'group_id'])
            ->selectRaw('(SELECT COUNT(*) FROM tasks AS t2 WHERE t2.group_id = tasks.group_id AND tasks.group_id IS NOT NULL) as group_member_count')
            ->whereRaw('COALESCE(extended_deadline, deadline) BETWEEN ? AND ?', [$start, $end])
            ->whereIn('sector_id', $allowedSectors)
            ->where('for_protocol', true)
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

        // Remove empty sectors
        $groupedBySector = array_filter($groupedBySector);

        return view('livewire.reports.protocol-tasks', [
            'groupedTasks' => $groupedBySector,
        ]);
    }
}
