<?php

namespace App\Http\Livewire;

use App\Models\Project;
use App\Models\Task;
use App\Models\TaskUser;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class OrderedTable extends Component
{
    public $tasks, $projects, $chosen_project, $username;
    public $projectId, $status;

    public function mount(){
        $this->username = Auth::user()->name;
        $this->chosen_project = Null;
        $this->project = Project::all();
        $this->tasks = TaskUser::where('user_id', Auth::user()->id)->orderBy('created_at', 'ASC')->get();
    }

    public function view($task_id){
        $this->emit('taskClicked', $task_id);
    }

    public function updatedProjectId(){
        if($this->projectId == Null){
            $this->tasks = Task::with(['user:id,name,sector_id,role_id'])->where('creator_id', Auth::user()->id)->where('project_id', null)->orderBy('deadline', 'ASC')->get();
            $this->chosen_project = Null;
        }else{
            $this->tasks = NULL;
            $this->chosen_project = Project::with(['tasks' => function($query){
                $query->where('creator_id', Auth::user()->id);
            }])->where('id', $this->projectId)->first();
        }
    }

    public function updatedStatus(){
        if($this->projectId == Null){
            $this->tasks = Task::with(['creator', 'user'])->where('creator_id', Auth::user()->id)->where('project_id', null)->where('status', $this->status)->orderBy('deadline', 'ASC')->get();
            $this->chosen_project = Null;
        }else{
            $this->tasks = NULL;
            $this->chosen_project = Project::with(['tasks' => function($query){
                $query->where('status', $this->status);
            }])->where('id', $this->projectId)->first();
        }
    }

    public function render()
    {
        return view('livewire.ordered-table');
    }
}
