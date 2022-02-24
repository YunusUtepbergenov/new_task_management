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
        $this->projectId = "Empty";
        $this->status = "Empty";
        $this->username = Auth::user()->name;
        $this->project = Project::all();
        $this->tasks = Task::with('user:id,name,sector_id,role_id')->where('creator_id', Auth::user()->id)->where('project_id', Null)
                        ->orderBy('deadline', 'ASC')->get();
        $this->chosen_project = Project::with(['tasks' => function($query){
            $query->with('user')->where('creator_id', Auth::user()->id);
        }])->where('user_id', Auth::user()->id)->get();
    }

    public function view($task_id){
        $this->emit('taskClicked', $task_id);
    }

    public function updated(){
        if($this->projectId == Null){
            if($this->status == "Empty"){
                $this->tasks = Task::with(['user:id,name,sector_id,role_id'])->where('creator_id', Auth::user()->id)->where('project_id', null)->orderBy('deadline', 'ASC')->get();
            }else{
                $this->tasks = Task::with(['user:id,name,sector_id,role_id'])->where('creator_id', Auth::user()->id)->where('project_id', null)->where('status', $this->status)->orderBy('deadline', 'ASC')->get();
            }
            $this->chosen_project = Null;
        }elseif($this->projectId == "Empty"){
            if($this->status == "Empty"){
                $this->tasks = Task::with('user:id,name,sector_id,role_id')->where('creator_id', Auth::user()->id)->where('project_id', null)
                            ->orderBy('deadline', 'ASC')->get();
                $this->chosen_project = Project::with(['tasks' => function($query){
                    $query->with('user')->where('creator_id', Auth::user()->id);
                }])->where('user_id', Auth::user()->id)->get();
            }else{
                $this->tasks = Task::with(['user:id,name,sector_id,role_id'])->where('creator_id', Auth::user()->id)->where('project_id', null)->where('status', $this->status)->orderBy('deadline', 'ASC')->get();
                $this->chosen_project = Project::with(['tasks' => function($query){
                    $query->with('user')->where('creator_id', Auth::user()->id)->where('status', $this->status);
                }])->where('user_id', Auth::user()->id)->get();
            }
        }else{
            $this->tasks = Null;
            if($this->status == "Empty"){
                $this->chosen_project = Project::with(['tasks' => function($query){
                    $query->with('user')->where('creator_id', Auth::user()->id);
                }])->where('id', $this->projectId)->first();
            }else{
                $this->chosen_project = Project::with(['tasks' => function($query){
                    $query->with('user')->where('creator_id', Auth::user()->id)->where('status', $this->status);
                }])->where('id', $this->projectId)->first();
            }
        }
    }

    public function render()
    {
        return view('livewire.ordered-table');
    }
}
