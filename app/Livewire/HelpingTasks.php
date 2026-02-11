<?php

namespace App\Livewire;

use App\Models\Project;
use App\Models\Task;
use App\Models\TaskUser;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class HelpingTasks extends Component
{
    public $tasks1, $helping_projects, $tasks_id, $tasks_without_project, $helping_task;
    // public $projectId, $status;

    public function mount(){
        $this->chosen_project = Null;
        $this->tasks1 = TaskUser::where('user_id', Auth::user()->id)->get();

        $projects_arr = array();
        $this->tasks_id = array();

        $this->helping_projects = collect([]);
        $this->tasks_without_project = collect([]);

        foreach($this->tasks1 as $task){
            $this->helping_task = Task::where('id', $task->task_id)->first();
            if($this->helping_task->project_id != NULL){
                array_push($projects_arr, $this->helping_task->project->id);
                array_push($this->tasks_id, $this->helping_task->id);
            }else{
                $this->tasks_without_project = $this->tasks_without_project->merge([$this->helping_task]);
            }
        }

        $unique_projects = array_unique($projects_arr);

        foreach($unique_projects as $project){
            $project_collection = Project::with('tasks')->where('id', $project)->first();
            $this->helping_projects = $this->helping_projects->merge([$project_collection]);
        }

    }

    // public function updatedProjectId(){
    //     if($this->projectId == Null){
    //         $this->tasks = Task::with(['user:id,name,sector_id,role_id'])->where('creator_id', Auth::user()->id)->where('project_id', null)->orderBy('deadline', 'ASC')->get();
    //         $this->chosen_project = Null;
    //     }else{
    //         $this->tasks = NULL;
    //         $this->chosen_project = Project::with(['tasks' => function($query){
    //             $query->where('creator_id', Auth::user()->id);
    //         }])->where('id', $this->projectId)->first();
    //     }
    // }

    // public function updatedStatus(){
    //     if($this->projectId == Null){
    //         $this->tasks = Task::with(['creator', 'user'])->where('creator_id', Auth::user()->id)->where('project_id', null)->where('status', $this->status)->orderBy('deadline', 'ASC')->get();
    //         $this->chosen_project = Null;
    //     }else{
    //         $this->tasks = NULL;
    //         $this->chosen_project = Project::with(['tasks' => function($query){
    //             $query->where('status', $this->status);
    //         }])->where('id', $this->projectId)->first();
    //     }
    // }

    public function view($task_id){
        $this->dispatch('taskClicked', $task_id);
    }

    public function render()
    {
        return view('livewire.helping-tasks');
    }
}
