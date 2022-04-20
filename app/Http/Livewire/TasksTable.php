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
    public $projectId, $status;

    public function mount(){
        $project_tasks = Task::with('project')->select('project_id')->where('user_id', Auth::user()->id)->where('project_id', '<>', null)->distinct('project_id')->get();
        $this->projects = (new ProjectService())->projectsList($project_tasks);;

        $this->projectId = "Empty";
        $this->status = "Empty";
        $this->username = Auth::user()->name;
        $this->chosen_project = $this->projects;
        $this->tasks = Task::with('creator:id,name,sector_id,role_id')->where('user_id', Auth::user()->id)->where('project_id', Null)
                        ->latest()->get();
    }

    public function updated(){
        if($this->projectId == Null){
            if($this->status == "Empty"){
                $this->tasks = Task::with(['creator:id,name,sector_id,role_id'])->where('user_id', Auth::user()->id)->where('project_id', null)->latest()->get();
            }else{
                $this->tasks = Task::with(['creator:id,name,sector_id,role_id'])->where('user_id', Auth::user()->id)->where('project_id', null)->where('status', $this->status)->latest()->get();
            }
            $this->chosen_project = Null;
        }elseif($this->projectId == "Empty"){
            $this->chosen_project = Null;
            if($this->status == "Empty"){
                // $this->tasks = Task::with(['creator:id,name,sector_id,role_id'])->where('user_id', Auth::user()->id)->where('project_id', null)->latest()->get();
                $this->tasks = Task::with(['creator:id,name,sector_id,role_id'])->where('user_id', Auth::user()->id)->latest()->get();
            }elseif($this->status == "Просроченный")
                $this->tasks = Task::with(['user', 'creator'])->where('user_id', Auth::user()->id)->where('overdue', 1)->orderBy('created_at', 'DESC')->get();
            else
                $this->tasks = Task::with(['user', 'creator'])->where('user_id', Auth::user()->id)->where('overdue', 0)->where('status', $this->status)->orderBy('created_at', 'DESC')->get();
        }else{
            $this->tasks = Null;
            if($this->status == "Empty"){
                $this->chosen_project = Project::with(['tasks' => function($query){
                    $query->where('user_id', Auth::user()->id);
                }])->where('id', $this->projectId)->first();
            }else{
                $this->chosen_project = Project::with(['tasks' => function($query){
                    $query->where('user_id', Auth::user()->id)->where('status', $this->status);
                }])->where('id', $this->projectId)->first();
            }
        }
    }

    public function render()
    {
        return view('livewire.tasks-table');
    }

    public function view($task_id){
        $this->emit('taskClicked', $task_id);
    }
}
