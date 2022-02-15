<?php

namespace App\Http\Livewire;

use App\Models\Project;
use App\Models\Task;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class TasksTable extends Component
{
    public $tasks, $projects, $chosen_project, $username;
    public $projectId, $status;

    public function mount(){
        $this->projects = Project::select(['id', 'name'])->get();
        $this->projectId = "Empty";
        $this->status = "Empty";
        $this->username = Auth::user()->name;
        $this->chosen_project = Null;
        $this->project = Project::all();
        $this->tasks = Task::with('creator:id,name,sector_id,role_id')->where('user_id', Auth::user()->id)->whereIn('status', ['Новое' ,'Выполняется'])
                        ->orderBy('deadline', 'ASC')->limit(15)->get();
    }

    public function updatedProjectId(){
        if($this->projectId == Null){
            if($this->status == "Empty"){
                $this->tasks = Task::with(['creator:id,name,sector_id,role_id'])->where('user_id', Auth::user()->id)->where('project_id', null)->orderBy('deadline', 'ASC')->get();
            }else{
                $this->tasks = Task::with(['creator:id,name,sector_id,role_id'])->where('user_id', Auth::user()->id)->where('project_id', null)->where('status', $this->status)->orderBy('deadline', 'ASC')->get();
            }
            $this->chosen_project = Null;
        }elseif($this->projectId == "Empty"){
            $this->chosen_project = Null;
            if($this->status == "Empty"){
                $this->tasks = Task::with('creator:id,name,sector_id,role_id')->where('user_id', Auth::user()->id)->whereIn('status', ['Новое' ,'Выполняется'])
                                ->orderBy('deadline', 'ASC')->limit(15)->get();
            }else{
                $this->tasks = Task::with(['creator:id,name,sector_id,role_id'])->where('user_id', Auth::user()->id)->where('status', $this->status)->orderBy('deadline', 'ASC')->limit(15)->get();
            }
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

    public function updatedStatus(){
        if($this->projectId == Null){
            if($this->status == "Empty"){
                $this->tasks = Task::with(['creator:id,name,sector_id,role_id'])->where('user_id', Auth::user()->id)->where('project_id', null)->orderBy('deadline', 'ASC')->get();
            }else{
                $this->tasks = Task::with(['creator:id,name,sector_id,role_id'])->where('user_id', Auth::user()->id)->where('project_id', null)->where('status', $this->status)->orderBy('deadline', 'ASC')->get();
            }
            $this->chosen_project = Null;
        }elseif($this->projectId == "Empty"){
            $this->chosen_project = Null;
            if($this->status == "Empty"){
                $this->tasks = Task::with('creator:id,name,sector_id,role_id')->where('user_id', Auth::user()->id)->whereIn('status', ['Новое' ,'Выполняется'])
                                ->orderBy('deadline', 'ASC')->limit(15)->get();
            }else{
                $this->tasks = Task::with(['creator:id,name,sector_id,role_id'])->where('user_id', Auth::user()->id)->where('status', $this->status)->orderBy('deadline', 'ASC')->limit(15)->get();
            }
        }
        else{
            $this->tasks = NULL;
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
