<?php

namespace App\Http\Livewire;

use App\Events\TaskCreatedEvent;
use App\Models\Project;
use App\Models\Sector;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Services\TaskService;
use Carbon\Carbon;
use Livewire\Component;

class OrderedTable extends Component
{
    public $weeklyTasks, $unplannedTasks, $projects, $username;
    public $projectId="Empty", $status="Empty";
    public $scoresGrouped = [];
    public $sectors = [];

    public $task_score = 'def', $task_name, $deadline, $task_employee = [], $task_plan = 1;

    public $is_repeating = false;
    public $repeat_type = null; 
    public $repeat_day = null;

    public function taskStore()
    {
        $this->validate([
            'task_name' => 'required|string|max:255',
            'deadline' => 'required|date|after:yesterday',
            'task_plan' => 'required|string',
            'task_employee' => 'required|array|min:1',
            'task_score' => 'required|integer',
        ]);

        foreach ($this->task_employee as $usr) {
            $user = User::find($usr);

            $task = Task::create([
                'creator_id' => Auth::id(),
                'user_id' => $user->id,
                'project_id' => null,
                'sector_id' => $user->sector->id,
                'type_id' => 1,
                'priority_id' => 1,
                'score_id' => $this->task_score,
                'name' => $this->task_name,
                'description' => null,
                'deadline' => $this->deadline,
                'status' => 'Новое',
                'planning_type' => $this->task_plan,
            ]);

            event(new TaskCreatedEvent($task));
        }

        $this->reset(['task_score', 'task_name', 'task_employee', 'deadline', 'task_plan']);
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

        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();

        $this->weeklyTasks = Task::with('user:id,name,sector_id,role_id')
            ->where('creator_id', Auth::id())
            ->whereBetween('deadline', [
                Carbon::now()->startOfWeek(),
                Carbon::now()->endOfWeek(),
            ])
            ->orWhere('planning_type', 'weekly')
            ->latest()->get();


        $this->weeklyTasks = Task::where(function($query) use ($startOfWeek, $endOfWeek) {
                $query->where('planning_type', 'unplanned')
                    ->whereBetween('deadline', [$startOfWeek, $endOfWeek]);
            })
            ->orWhere(function($query) {
                $query->where('planning_type', 'weekly')
                    ->where('status', '<>', 'Выполнено');
            })
            ->latest()
            ->get();

        $weeklyTaskIds = $this->weeklyTasks->pluck('id');

        $this->unplannedTasks = Task::with('user:id,name,sector_id,role_id')
            ->where('creator_id', Auth::id())
            ->where('status', '<>', 'Выполнено')
            ->whereNull('project_id')
            ->whereNotIn('id', $weeklyTaskIds)
            ->latest()
            ->get();

        $this->scoresGrouped = ['Категории' => (new TaskService())->scoresList()];

        return view('livewire.ordered-table');
    }
}
