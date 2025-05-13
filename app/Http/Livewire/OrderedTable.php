<?php

namespace App\Http\Livewire;

use App\Models\Project;
use App\Models\Task;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class OrderedTable extends Component
{
    public $tasks, $projects, $chosen_project, $username;
    public $projectId="Empty", $status="Empty";

    public function mount(){
        $this->username = Auth::user()->name;
        $this->project = Project::all();

        $this->tasks = Task::with('user:id,name,sector_id,role_id')->where('creator_id', Auth::user()->id)->where('status', '<>', "Выполнено")->where('project_id', Null)
                        ->latest()->get();

        $this->chosen_project = Project::with(['tasks' => function($query){
            $query->with('user')->where('status', '<>', "Выполнено")->where('creator_id', Auth::user()->id)->latest();
        }])->where('user_id', Auth::user()->id)->latest()->get();
    }

    public function view($task_id){
        $this->emit('taskClicked', $task_id);
        $this->updated();
    }

    public function updated(){
        if($this->projectId == Null){
            if($this->status == "Empty"){
                $this->tasks = Task::with(['user:id,name,sector_id,role_id'])->where('creator_id', Auth::user()->id)
                                ->where('project_id', null)->latest()->get();
            }else{
                if($this->status == "Просроченный"){
                    $this->tasks = Task::with(['user:id,name,sector_id,role_id'])->where('creator_id', Auth::user()->id)
                                    ->where('project_id', null)->where('overdue', 1)->latest()->get();
                }else{
                    $this->tasks = Task::with(['user:id,name,sector_id,role_id'])->where('creator_id', Auth::user()->id)
                                    ->where('project_id', null)->where('status', $this->status)->where('overdue', 0)->latest()->get();
                }
            }
            $this->chosen_project = Null;
        }elseif($this->projectId == "Empty"){
            if($this->status == "Empty"){
                $this->tasks = Task::with('user:id,name,sector_id,role_id')->where('creator_id', Auth::user()->id)->where('status', '<>', "Выполнено")
                ->where('project_id', null)->latest()->get();

                $this->chosen_project = Project::with(['tasks' => function($query){
                    $query->with('user')->where('creator_id', Auth::user()->id)->where('status', '<>', "Выполнено")->latest();
                }])->where('user_id', Auth::user()->id)->latest()->get();
            }else{
                if($this->status == "Просроченный"){
                    $this->tasks = Task::with(['user:id,name,sector_id,role_id'])->where('creator_id', Auth::user()->id)
                    ->where('project_id', null)->where('overdue', 1)->latest()->get();

                    $this->chosen_project = Project::with(['tasks' => function($query){
                        $query->with('user')->where('creator_id', Auth::user()->id)->where('overdue', 1)->latest();
                    }])->where('user_id', Auth::user()->id)->latest()->get();
                }else{
                   $this->tasks = Task::with(['user:id,name,sector_id,role_id'])->where('creator_id', Auth::user()->id)
                                        ->where('project_id', null)->where('status', $this->status)->where('overdue', 0)->latest()->get();
                    $this->chosen_project = Project::with(['tasks' => function($query){
                        $query->with('user')->where('creator_id', Auth::user()->id)->where('status', $this->status)->where('overdue', 0)->latest();
                    }])->where('user_id', Auth::user()->id)->latest()->get();
                }
            }
        }else{
            $this->tasks = Null;
            if($this->status == "Empty"){
                $this->chosen_project = Project::with(['tasks' => function($query){
                    $query->with('user')->where('creator_id', Auth::user()->id)->latest();
                }])->where('id', $this->projectId)->get();
            }else{
                if($this->status == "Просроченный"){
                    $this->chosen_project = Project::with(['tasks' => function($query){
                        $query->with('user')->where('creator_id', Auth::user()->id)->where('overdue', 1)->latest();
                    }])->where('id', $this->projectId)->get();
                }
                else{
                    $this->chosen_project = Project::with(['tasks' => function($query){
                        $query->with('user')->where('creator_id', Auth::user()->id)->where('status', $this->status)->where('overdue', 0)->latest();
                    }])->where('id', $this->projectId)->get();
                }
            }
        }
    }

    public function render()
    {
        return view('livewire.ordered-table');
    }
}
