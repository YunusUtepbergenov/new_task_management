<?php

namespace App\Livewire\Reports;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Services\ProjectService;
use Livewire\Attributes\On;
use Livewire\Component;

class TasksSection extends Component
{
    public $tasks, $user, $filter = "Null", $projects;

    public function mount()
    {
        $this->tasks = Null;
        $this->user = Null;
    }

    public function view($task_id): void
    {
        $this->dispatch('taskClicked', id: $task_id);
    }

    #[On('updateUserId')]
    public function updateUserId($id): void
    {
        if($id == Null){
            $this->tasks = Null;
            $this->user = Null;
        }
        else{
            $this->user = User::where('id', $id)->first();

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

    #[On('updateSectorTasks')]
    public function updateSectorTasks($id): void
    {
        $this->user = Null;
        $this->projects = Null;
        $this->tasks = Task::where('sector_id', $id)->get();
    }

    public function render()
    {
        return view('livewire.reports.tasks-section');
    }
}
