<?php

namespace App\Http\Livewire;

use App\Models\Project;
use App\Models\Task;
use App\Services\ProjectService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class TasksTable extends Component
{
    public $tasks, $projects, $chosen_project, $username;
    public $projectId, $status, $project_tasks;

    public function mount(){
        $this->project_tasks = Task::with('project')->select('project_id')->where('user_id', Auth::user()->id)
            ->where('project_id', '<>', null)->distinct('project_id')->get();
        $this->projects = (new ProjectService())->projectsList($this->project_tasks);

        $this->projectId = "Empty";
        $this->status = "Empty";
        $this->username = Auth::user()->name;
        $this->chosen_project = Project::with(['tasks' => function($query){
                $query->latest();
                }])->whereHas('tasks', function($query){
                $query->where('user_id', Auth::user()->id)->where('project_id', '<>', Null);
        })->latest()->get();
        $this->tasks = Task::with('creator:id,name,sector_id,role_id')->where('user_id', Auth::user()->id)
                            ->where('project_id', Null)->latest()->get();
    }

    public function updated(){
        if($this->projectId == Null){
            if($this->status == "Empty"){
                $this->tasks = Task::with(['creator:id,name,sector_id,role_id'])->where('user_id', Auth::user()->id)
                    ->where('project_id', null)->latest()->get();
            }else{
                if($this->status == "Просроченный"){
                    $this->tasks = Task::with(['creator:id,name,sector_id,role_id'])->where('user_id', Auth::user()->id)
                    ->where('project_id', null)->where('overdue', 1)->latest()->get();
                }else{
                    $this->tasks = Task::with(['creator:id,name,sector_id,role_id'])->where('user_id', Auth::user()->id)
                    ->where('project_id', null)->where('status', $this->status)->where('overdue', 0)->latest()->get();
                }
            }
            $this->chosen_project = Null;
        }
        elseif($this->projectId == "Empty"){
            if($this->status == "Empty"){
                $this->tasks = Task::with(['creator:id,name,sector_id,role_id'])->where('user_id', Auth::user()->id)->where('project_id', null)->latest()->get();
                $this->chosen_project = Project::whereHas('tasks', function($query){
                    $query->with('user')->where('user_id', Auth::user()->id)->where('project_id', '<>', Null)->latest();
                })->latest()->get();
            }elseif($this->status == "Просроченный"){
                $this->tasks = Task::with(['user', 'creator'])->where('project_id', Null)->where('user_id', Auth::user()->id)->where('overdue', 1)->orderBy('created_at', 'DESC')->get();
                $this->chosen_project = Project::with(['tasks' => function($query){
                    $query->with('user')->where('user_id', Auth::user()->id)->where('overdue', 1)->latest();
                }])->latest()->get();
            }else{
                $this->tasks = Task::with(['user', 'creator'])->where('project_id', Null)->where('user_id', Auth::user()->id)->where('overdue', 0)->where('status', $this->status)->latest()->get();
                $this->chosen_project = Project::whereHas('tasks', function($query){
                    $query->with('user')->where('user_id', Auth::user()->id)->where('status', $this->status)->where('overdue', 0)->latest();
                })->latest()->get();
            }
        }
        else{
            $this->tasks = Null;
            if($this->status == "Empty"){
                $this->chosen_project = Project::with(['tasks' => function($query){
                    $query->where('user_id', Auth::user()->id)->latest();
                }])->where('id', $this->projectId)->get();
            }else{
                if($this->status == "Просроченный"){
                    $this->chosen_project = Project::with(['tasks' => function($query){
                        $query->where('user_id', Auth::user()->id)->where('overdue', 1)->latest();
                    }])->where('id', $this->projectId)->get();
                }else{
                    $this->chosen_project = Project::with(['tasks' => function($query){
                        $query->where('user_id', Auth::user()->id)->where('status', $this->status)->where('overdue', 0)->latest();
                    }])->where('id', $this->projectId)->get();
                }
            }
        }
    }

    public function render()
    {
        return view('livewire.tasks-table');
    }

    public function view($task_id){
        $this->emit('taskClicked', $task_id);
        $this->updated();
    }
}
