<?php

namespace App\Http\Livewire\Reports;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Services\ProjectService;
use Livewire\Component;

class TasksSection extends Component
{
    public $tasks, $user, $filter = "Null", $projects;

    protected $listeners = ['updateUserId'];

    public function mount()
    {
        $this->tasks = Null;
        $this->user = Null;
    }

    public function view($task_id){
        $this->emit('taskClicked', $task_id);
    }

    public function updateUserId($id){
        if($id == Null){
            $this->tasks = Null;
            $this->user = Null;
        }
        else{
            $this->user = User::where('id', $id)->first();

            // $project_tasks = Task::with('project')->select('project_id')->where('user_id', $id)->where('project_id', '<>', null)->distinct('project_id')->get();
            // $this->projects = (new ProjectService())->projectsList($project_tasks);

            // $this->tasks = Task::with('creator:id,name,sector_id,role_id')->where('user_id', $this->user->id)->where('project_id', Null)
            //                 ->latest()->get();

            if($this->filter == "Null"){
                $project_tasks = Task::with('project')->select('project_id')->where('user_id', $id)->where('project_id', '<>', null)->distinct('project_id')->get();
                $this->projects = (new ProjectService())->projectsList($project_tasks);

                $this->tasks = Task::with('creator:id,name,sector_id,role_id')->where('user_id', $id)->where('project_id', Null)
                                ->latest()->get();
            }
            elseif($this->filter == "Просроченный"){
                $this->tasks = Task::with(['user', 'creator'])->where('user_id', $id)->where('overdue', 1)->orderBy('created_at', 'DESC')->get();
                $this->projects = Null;
            }
            else{
                $this->tasks = Task::with(['user', 'creator'])->where('user_id', $id)->where('overdue', 0)->where('status', $this->filter)->orderBy('created_at', 'DESC')->get();
                $this->projects = Null;
            }
        }
    }

    public function updatedFilter(){
        if($this->user == Null){
            $this->tasks = Null;
        }elseif($this->filter == "Null"){
            $project_tasks = Task::with('project')->select('project_id')->where('user_id', $this->user->id)->where('project_id', '<>', null)->distinct('project_id')->get();
            $this->projects = (new ProjectService())->projectsList($project_tasks);

            $this->tasks = Task::with('creator:id,name,sector_id,role_id')->where('user_id', $this->user->id)->where('project_id', Null)
                            ->latest()->get();
        }
        elseif($this->filter == "Просроченный"){
            $this->tasks = Task::with(['user', 'creator'])->where('user_id', $this->user->id)->where('overdue', 1)->orderBy('created_at', 'DESC')->get();
            $this->projects = Null;
        }
        else{
            $this->tasks = Task::with(['user', 'creator'])->where('user_id', $this->user->id)->where('overdue', 0)->where('status', $this->filter)->orderBy('created_at', 'DESC')->get();
            $this->projects = Null;
        }
    }

    public function render()
    {
        return view('livewire.reports.tasks-section');
    }
}
