<?php

namespace App\Http\Livewire;

use App\Models\Project;
use App\Models\Task;
use App\Models\TaskUser;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class HelpingTasks extends Component
{
    public $tasks, $helping_projects, $tasks_id;
    public $projectId, $status;

    public function view($task_id){
        $this->emit('taskClicked', $task_id);
    }

    public function mount(){
        $this->username = Auth::user()->name;
        $this->chosen_project = Null;
        $this->tasks = TaskUser::where('user_id', Auth::user()->id)->orderBy('created_at', 'ASC')->get();

        $projects_arr = array();
        $this->tasks_id = array();

        $this->helping_projects = collect([]);

        foreach($this->tasks as $task){
            $helping_task = Task::with('project')->where('id', $task->task_id)->first();
            if($helping_task->project_id != NULL){
                array_push($projects_arr, $helping_task->project->name);
                array_push($this->tasks_id, $helping_task->id);
            }
        }

        $unique_projects = array_unique($projects_arr);
        foreach($unique_projects as $project){
            $project_collection = Project::with(['tasks' => function($query){
                $query->where('user_id', Auth::user()->id);
            }])->where('name', $project)->first();
            $this->helping_projects = $this->helping_projects->merge([$project_collection]);
        }
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
        return view('livewire.helping-tasks');
    }
}
