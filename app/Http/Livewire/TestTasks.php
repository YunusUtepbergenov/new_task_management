<?php

namespace App\Http\Livewire;

use Livewire\Component;

class TestTasks extends Component
{

    public $task_score;
    public $task_employee = [];

    public function taskStore()
    {
        dd([
            'task_score' => $this->task_score,
            'task_employee' => $this->task_employee,
        ]);
    }
    
    public function render()
    {
        return view('livewire.test-tasks');
    }
}
