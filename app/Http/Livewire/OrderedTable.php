<?php

namespace App\Http\Livewire;

use Livewire\Component;

class OrderedTable extends Component
{
    public $tasks, $projects, $user_projects;

    public function view($task_id){
        $this->emit('taskClicked', $task_id);
    }

    public function render()
    {
        return view('livewire.ordered-table');
    }
}
