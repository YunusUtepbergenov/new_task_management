<?php

namespace App\Livewire\Reports;

use App\Events\TaskCreatedEvent;
use App\Models\Repeat;
use App\Models\Sector;
use App\Models\User;
use App\Services\TaskService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
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

    public $task_score = null;
    public $task_name;
    public $deadline;
    public $task_employee = [];
    public $task_plan = 1;
    public $sectors;
    public $scoresGrouped;
    public $is_repeating = false;
    public $repeat_type = null;
    public $repeat_day = null;

    #[On('task-updated')]
    public function refreshTasks(): void
    {
        // Re-render triggers fresh data from render()
    }

    public function mount(): void
    {
        $this->generateWeekOptions();
        $this->selectedWeek = now()->startOfWeek()->toDateString();
        $this->sectors = Sector::with('users')->get();
        $this->scoresGrouped = ['Категории' => (new TaskService())->scoresList()];
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

    public function taskStore(): void
    {
        $this->validate([
            'task_name' => 'required|string|max:255',
            'task_score' => 'required|integer',
            'task_employee' => 'required|array|min:1',
            'deadline' => $this->is_repeating ? 'nullable' : 'required|date',
            'repeat_type' => $this->is_repeating ? 'required|in:weekly,monthly,quarterly' : 'nullable',
            'repeat_day' => $this->is_repeating ? 'required|integer|min:1|max:31' : 'nullable',
        ]);

        $isMultiple = count($this->task_employee) > 1;
        $groupId = $isMultiple ? Str::uuid() : null;

        foreach ($this->task_employee as $userId) {
            $user = User::find($userId);

            if ($isMultiple) {
                $creator = Auth::id();
            } elseif ($user->role_id == 2) {
                $creator = 2;
            } else {
                $creator = Auth::id();
            }

            if (!$user || $user->leave) {
                continue;
            }

            DB::transaction(function () use ($user, $groupId, $creator) {
                $repeatId = null;

                $deadline = $this->is_repeating
                    ? $this->calculateInitialRepeatDeadline($this->repeat_type, (int) $this->repeat_day)
                    : $this->deadline;

                if ($this->is_repeating) {
                    $repeat = Repeat::create([
                        'task_id' => null,
                        'repeat' => $this->repeat_type,
                        'day' => $this->repeat_day,
                    ]);
                    $repeatId = $repeat->id;
                }

                $task = Task::create([
                    'creator_id' => $creator,
                    'user_id' => $user->id,
                    'sector_id' => $user->sector->id,
                    'project_id' => null,
                    'type_id' => 1,
                    'priority_id' => 1,
                    'score_id' => $this->task_score,
                    'name' => $this->task_name,
                    'description' => null,
                    'deadline' => $deadline,
                    'status' => 'Не прочитано',
                    'planning_type' => $this->task_plan,
                    'repeat_id' => $repeatId,
                    'group_id' => $groupId,
                ]);

                if ($this->is_repeating) {
                    $repeat->update(['task_id' => $task->id]);
                }

                event(new TaskCreatedEvent($task));
            });
        }

        $this->reset([
            'task_score', 'task_name', 'task_employee',
            'deadline', 'task_plan', 'is_repeating',
            'repeat_type', 'repeat_day',
        ]);

        $this->dispatch('form-reset');
        $this->dispatch('toastr:success', message: 'Задача успешно создана');
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

    private function calculateInitialRepeatDeadline(string $type, int $day): ?Carbon
    {
        $today = now();

        if ($type === 'weekly') {
            $daysUntil = $day - $today->dayOfWeekIso;
            if ($daysUntil <= 0) {
                $daysUntil += 7;
            }
            return $today->copy()->addDays($daysUntil)->startOfDay();
        }

        if ($type === 'monthly') {
            $daysInMonth = $today->daysInMonth;
            if ($day >= $today->day && $day <= $daysInMonth) {
                return $today->copy()->startOfMonth()->addDays($day - 1)->startOfDay();
            } else {
                $next = $today->copy()->addMonth();
                $safeDay = min($day, $next->daysInMonth);
                return $next->startOfMonth()->addDays($safeDay - 1)->startOfDay();
            }
        }

        if ($type === 'quarterly') {
            $nextQuarterStart = $today->firstOfQuarter()->addMonths(3);
            return $nextQuarterStart->copy()->addDays($day)->startOfDay();
        }

        return null;
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
