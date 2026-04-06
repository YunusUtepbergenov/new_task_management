<?php

namespace App\Livewire;

use App\Events\TaskCreatedEvent;
use App\Models\Repeat;
use App\Models\Task;
use App\Models\TaskLog;
use App\Models\User;
use App\Services\TaskService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Component;

class CreateTaskForm extends Component
{
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

        $firstUser = $users->get($this->task_employee[0]);
        $creator = ($firstUser && $firstUser->role_id == 2) ? 2 : Auth::id();

        foreach ($this->task_employee as $userId) {
            $user = $users->get($userId);

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
        $this->dispatch('task-created');
        $this->dispatch('toastr:success', message: 'Задача успешно создана');
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

    public function render(): \Illuminate\Contracts\View\View
    {
        return view('livewire.create-task-form');
    }
}
