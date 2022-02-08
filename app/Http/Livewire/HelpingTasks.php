<?php

namespace App\Http\Livewire;

use Livewire\Component;

class HelpingTasks extends Component
{
    public $tasks, $helping_projects, $tasks_id;

    public function view($task_id){
        $this->emit('taskClicked', $task_id);
    }

    public function render()
    {
        return view('livewire.helping-tasks');
    }
}
