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
use Livewire\Attributes\Computed;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\On;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\WeeklyTasksExport;
use App\Models\Task;
use App\Models\TaskLog;
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
    public $is_repeating = false;
    public $repeat_type = null;
    public $repeat_day = null;

    #[Computed]
    public function sectors(): \Illuminate\Database\Eloquent\Collection
    {
        $sectors = TaskService::cachedSectorsWithUsers();

        if (Auth::user()->isHead()) {
            $ownSectorId = Auth::user()->sector_id;
            $sectors = $sectors->sortBy(fn ($s) => $s->id === $ownSectorId ? 0 : 1)->values();
        }

        return $sectors;
    }

    #[Computed]
    public function scoresGrouped(): array
    {
        return ['Категории' => (new TaskService())->scoresList()];
    }

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

        $weeks = [];
        foreach ($period as $weekStart) {
            $weeks[$weekStart->toDateString()] =
                $weekStart->format('d M Y') . ' – ' .
                $weekStart->copy()->endOfWeek()->format('d M Y');
        }

        $this->weeks = array_reverse($weeks, true);
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

        $users = User::whereIn('id', $this->task_employee)->get()->keyBy('id');

        foreach ($this->task_employee as $userId) {
            $user = $users->get($userId);

            if (!$user || $user->leave) {
                continue;
            }

            $creator = ($isMultiple || $user->role_id != 2) ? Auth::id() : 2;

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
                    'sector_id' => $user->sector_id,
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

                TaskLog::create([
                    'task_id' => $task->id,
                    'user_id' => Auth::id(),
                    'action' => 'created',
                    'description' => 'Задача создана',
                ]);

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

    public function view(int $taskId): void
    {
        $this->dispatch('taskClicked', id: $taskId);
    }

    public function deleteTask(int $taskId): void
    {
        $task = Task::where('id', $taskId)
            ->where('creator_id', Auth::id())
            ->first();

        if (!$task) {
            return;
        }

        $tasksToDelete = $task->group_id
            ? Task::where('group_id', $task->group_id)->get()
            : collect([$task]);

        foreach ($tasksToDelete as $t) {
            if ($t->response) {
                if ($t->response->filename) {
                    \Illuminate\Support\Facades\Storage::delete('files/responses/' . $t->response->filename);
                }
                $t->response->delete();
            }

            if ($t->files) {
                foreach ($t->files as $file) {
                    \Illuminate\Support\Facades\Storage::delete('files/' . $file->name);
                    $file->delete();
                }
            }

            if ($t->repeat) {
                $t->repeat->delete();
            }

            $t->delete();
        }

        $this->dispatch('toastr:success', message: 'Задача удалена.');
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
            ->whereRaw('COALESCE(extended_deadline, deadline) BETWEEN ? AND ?', [$start, $end])
            ->whereIn('sector_id', $allowedSectors)
            ->orderBy('id')
            ->get()
            ->groupBy(fn ($task) => $task->group_id ?? $task->id);

        $groupedBySector = [];
        $sectorOrder = [];

        foreach ($tasks as $group) {
            $main = $group->first();
            $sectorName = $sectorNames[$main->sector_id] ?? 'Без сектора';
            $groupedBySector[$sectorName][] = $group->toArray();
            if (!isset($sectorOrder[$sectorName])) {
                $sectorOrder[$sectorName] = $main->sector_id;
            }
        }

        uksort($groupedBySector, fn ($a, $b) => $sectorOrder[$a] <=> $sectorOrder[$b]);

        $user = Auth::user();
        if ($user->isHead() && $user->sector_id) {
            $ownSector = $sectorNames[$user->sector_id] ?? null;
            if ($ownSector && isset($groupedBySector[$ownSector])) {
                $groupedBySector = [$ownSector => $groupedBySector[$ownSector]] + $groupedBySector;
            }
        }

        return view('livewire.reports.weekly-tasks-overview', [
            'groupedTasks' => $groupedBySector,
            'sectors' => $this->sectors,
            'scoresGrouped' => $this->scoresGrouped,
        ]);
    }
}
