<?php

namespace App\Http\Livewire;

use App\Services\ProjectService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use App\Models\{Project, Task, Sector};
use App\Services\TaskService;
use Carbon\Carbon;

class TasksTable extends Component
{
    public $tasks, $username;
    public $sectors, $weeklyTasks, $all_tasks, $scoresGrouped;

    public function mount(){

        $this->tasks = Task::with('creator')->where('user_id', Auth::user()->id)
                            ->where('project_id', Null)->where('status', '<>', "Выполнено")->latest()->get();

        
        foreach ($this->tasks as $task) {
            if ($task->status === 'Не прочитано') {
                $task->update(['status' => 'Выполняется']);
            }
        }
    }

    public function render()
    {
        $this->username = Auth::user()->name;

        $this->sectors = Sector::with('users')->get();

        $this->weeklyTasks = Task::with('user:id,name,sector_id,role_id')
                    ->where('user_id', Auth::id())
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
                    })
                    ->toArray();

        $this->all_tasks = Task::with('user:id,name,sector_id,role_id')
                    ->where('user_id', Auth::id())
                    ->where('status', '<>', 'Выполнено')
                    ->whereNull('project_id')
                    ->orderByRaw('COALESCE(extended_deadline, deadline)')
                    ->get()
                    ->groupBy(function ($task) {
                        return $task->group_id ?: $task->id;
                    })
                    ->toArray();

        $this->scoresGrouped = ['Категории' => (new TaskService())->scoresList()];
        return view('livewire.tasks-table');
    }


    public function view($task_id){
        $this->emit('taskClicked', $task_id);
        $this->updated();
    }
}
