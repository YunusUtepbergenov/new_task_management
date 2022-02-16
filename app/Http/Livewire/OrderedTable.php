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
        // $project_tasks = Task::with('project')->where('creator_id', Auth::user()->id)->where('project_id', '<>', null)->get();
        // $projects_arr = array();

        // $user_projects = collect([]);

        // foreach($project_tasks as $task){
        //     array_push($projects_arr, $task->project->name);
        // }

        // $unique_projects = array_unique($projects_arr);

        // foreach($unique_projects as $project){
        //     $project_collection = Project::where('name', $project)->first();
        //     $user_projects = $user_projects->merge([$project_collection]);
        // }

        // $this->projects = $user_projects;

        $this->projectId = "Empty";
        $this->status = "Empty";
        $this->username = Auth::user()->name;
        $this->chosen_project = Null;
        $this->project = Project::all();
        $this->tasks = Task::with('user:id,name,sector_id,role_id')->where('creator_id', Auth::user()->id)->whereIn('status', ['Новое' ,'Выполняется'])
                        ->orderBy('deadline', 'ASC')->get();
    }

    public function view($task_id){
        $this->emit('taskClicked', $task_id);
    }

    public function updatedProjectId(){
        if($this->projectId == Null){
            if($this->status == "Empty"){
                $this->tasks = Task::with(['user:id,name,sector_id,role_id'])->where('creator_id', Auth::user()->id)->where('project_id', null)->orderBy('deadline', 'ASC')->get();
            }else{
                $this->tasks = Task::with(['user:id,name,sector_id,role_id'])->where('creator_id', Auth::user()->id)->where('project_id', null)->where('status', $this->status)->orderBy('deadline', 'ASC')->get();
            }
            $this->chosen_project = Null;
        }elseif($this->projectId == "Empty"){
            $this->chosen_project = Null;
            if($this->status == "Empty"){
                $this->tasks = Task::with('user:id,name,sector_id,role_id')->where('creator_id', Auth::user()->id)->whereIn('status', ['Новое' ,'Выполняется'])
                                ->orderBy('deadline', 'ASC')->get();
            }else{
                $this->tasks = Task::with(['user:id,name,sector_id,role_id'])->where('creator_id', Auth::user()->id)->where('status', $this->status)->orderBy('deadline', 'ASC')->get();
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

    public function updatedStatus(){
        if($this->projectId == Null){
            if($this->status == "Empty"){
                $this->tasks = Task::with(['user:id,name,sector_id,role_id'])->where('creator_id', Auth::user()->id)->where('project_id', null)->orderBy('deadline', 'ASC')->get();
            }else{
                $this->tasks = Task::with(['user:id,name,sector_id,role_id'])->where('creator_id', Auth::user()->id)->where('project_id', null)->where('status', $this->status)->orderBy('deadline', 'ASC')->get();
            }
            $this->chosen_project = Null;
        }elseif($this->projectId == "Empty"){
            $this->chosen_project = Null;
            if($this->status == "Empty"){
                $this->tasks = Task::with('user:id,name,sector_id,role_id')->where('creator_id', Auth::user()->id)->whereIn('status', ['Новое' ,'Выполняется'])
                                ->orderBy('deadline', 'ASC')->get();
            }else{
                $this->tasks = Task::with(['user:id,name,sector_id,role_id'])->where('creator_id', Auth::user()->id)->where('status', $this->status)->orderBy('deadline', 'ASC')->get();
            }
        }
        else{
            $this->tasks = NULL;
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
