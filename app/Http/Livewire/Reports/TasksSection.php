<?php

namespace App\Http\Livewire\Reports;

use App\Models\Task;
use App\Models\User;
use Livewire\Component;

class TasksSection extends Component
{
    public $tasks, $user, $filter = "Null";

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
            if($this->filter == "Null"){
                $this->tasks = Task::with(['user', 'creator'])->where('user_id', $id)->orderBy('created_at', 'DESC')->get();
            }
            elseif($this->filter == "Просроченный")
                $this->tasks = Task::with(['user', 'creator'])->where('user_id', $id)->where('overdue', 1)->orderBy('created_at', 'DESC')->get();
            else
                $this->tasks = Task::with(['user', 'creator'])->where('user_id', $id)->where('overdue', 0)->where('status', $this->filter)->orderBy('created_at', 'DESC')->get();
        }
    }

    public function updatedFilter(){
        if($this->user == Null){
            $this->tasks = Null;
        }elseif($this->filter == "Null")
            $this->tasks = Task::with(['user', 'creator'])->where('user_id', $this->user->id)->orderBy('created_at', 'DESC')->get();
        elseif($this->filter == "Просроченный")
            $this->tasks = Task::with(['user', 'creator'])->where('user_id', $this->user->id)->where('overdue', 1)->orderBy('created_at', 'DESC')->get();
        else
            $this->tasks = Task::with(['user', 'creator'])->where('user_id', $this->user->id)->where('overdue', 0)->where('status', $this->filter)->orderBy('created_at', 'DESC')->get();
    }

    public function render()
    {
        return view('livewire.reports.tasks-section');
    }
}
