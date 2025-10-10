<?php

namespace App\Http\Livewire;

use App\Events\TaskCreatedEvent;
use App\Models\Sector;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Services\TaskService;
use Carbon\Carbon;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use App\Models\Repeat;
use App\Models\TelegramDb;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;

class OrderedTable extends Component
{
    public $weeklyTasks, $all_tasks, $username;
    public $scoresGrouped = [];
    public $sectors = [];

    public $task_score = 'def', $task_name, $deadline, $task_employee = [], $task_plan = 1;

    public $is_repeating = false;
    public $repeat_type = "null"; 
    public $repeat_day = null;

    public function taskStore()
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
            
            if($user->role_id == 2){
                $creator = 2;
            }else{
                $creator = Auth::id();
            }

            if (!$user || $user->leave) continue;

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

        $this->dispatchBrowserEvent('toastr:success', ['message' => 'Задача успешно создана']);
    }

    public function view($task_id){
        $this->emit('taskClicked', $task_id);
        $this->updated();
    }

    public function updatedTaskPlan($value)
    {
        $this->emit('task_plan_updated', $value);
    }

    public function updatePlanType($taskId, $newType)
    {
        $task = Task::where('id', $taskId)
            ->where('creator_id', Auth::id())
            ->firstOrFail();

        if (in_array($newType, ['weekly', 'unplanned'])) {
            $task->planning_type = $newType;
            $task->save();
        }

        $this->dispatchBrowserEvent('toastr:success', ['message' => 'Тип задачи обновлен.']);
    }

    public function render()
    {
        $this->username = Auth::user()->name;

        $this->sectors = Sector::with('users')->get();

        $this->weeklyTasks = Task::with('user:id,name,sector_id,role_id')
            ->where('creator_id', Auth::id())
            ->where('status', '<>', 'Выполнено')
            ->where(function ($query) {
                $query->where(function ($q) {
                    $q->whereNull('extended_deadline')->where('deadline', '<=', Carbon::now()->endOfWeek());
                })->orWhere(function ($q) {
                    $q->whereNotNull('extended_deadline')->where('extended_deadline', '<=', Carbon::now()->endOfWeek());
                });
            })
            ->orderByRaw('COALESCE(extended_deadline, deadline)')
            ->get()
            ->groupBy(function ($task) {
                return $task->group_id ?: $task->id;
            })->toArray();

        $this->all_tasks = Task::with('user:id,name,sector_id,role_id')
                        ->where('creator_id', Auth::id())
                        ->where('status', '<>', 'Выполнено')
                        ->whereNull('project_id')
                        ->orderByRaw('COALESCE(extended_deadline, deadline)')
                        ->get()
                        ->groupBy(function ($task) {
                            return $task->group_id ?: $task->id;
                        })
                        ->toArray();

        $this->scoresGrouped = ['Категории' => (new TaskService())->scoresList()];

        return view('livewire.ordered-table');
    }

    private function calculateInitialRepeatDeadline(string $type, int $day): ?\Carbon\Carbon
    {
        $today = now();

        if ($type === 'weekly') {
            $targetDay = $day;
            $currentDay = $today->dayOfWeekIso;

            $daysUntil = $targetDay - $currentDay;
            if ($daysUntil <= 0) {
                $daysUntil += 7; // Next week's target day
            }

            return $today->copy()->addDays($daysUntil)->startOfDay();
        }

        if ($type === 'monthly') {
            $targetDay = $day;
            $daysInMonth = $today->daysInMonth;

            if ($targetDay >= $today->day && $targetDay <= $daysInMonth) {
                return $today->copy()->startOfMonth()->addDays($targetDay - 1)->startOfDay();
            } else {
                $next = $today->copy()->addMonth();
                $safeDay = min($targetDay, $next->daysInMonth);
                return $next->startOfMonth()->addDays($safeDay - 1)->startOfDay();
            }
        }

        if ($type === 'quarterly') {
            $nextQuarterStart = $today->firstOfQuarter()->addMonths(3);
            return $nextQuarterStart->copy()->addDays($day)->startOfDay();
        }

        return null;
    }

}
