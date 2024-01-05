<?php

namespace App\Http\Livewire\Reports;

use App\Models\Task;
use App\Models\User as ModelsUser;
use Livewire\Component;

class User extends Component
{
    public $userId, $start, $end, $user, $tasks;

    public function mount(){
        $this->user = ModelsUser::where('id', $this->userId)->first();
        $this->tasks = Task::where('user_id', $this->userId)->whereBetween('deadline', [$this->start, $this->end])->orderBy('score_id')->get();
    }
    public function render()
    {
        return view('livewire.reports.user');
    }

    public function view($task_id){
        $this->emit('taskClicked', $task_id);
    }
}
