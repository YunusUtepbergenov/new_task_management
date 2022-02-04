<?php

namespace App\Http\Livewire;

use App\Models\Project;
use App\Models\Task;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class TasksTable extends Component
{
    public $tasks, $projects, $user_projects;
    public function render()
    {
        return view('livewire.tasks-table');
    }

    public function view($task_id){
        $this->emit('taskClicked', $task_id);
    }
}
