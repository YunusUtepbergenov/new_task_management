<?php

namespace App\Http\Livewire\Reports;

use App\Models\Task;
use App\Models\User;
use Livewire\Component;

class FilterSection extends Component
{
    public $startDate, $endDate, $users;

    public function mount(){
        $this->startDate = date('Y-m-01');
        $this->endDate = date('Y-m-t');
    }


    public function render()
    {
        $this->users = User::with('tasks')->where('leave', 0)->get();

        foreach($this->users as $employee){
            $employee->tasks_cnt =  $employee->filterTasks($this->startDate, $this->endDate)->count();
        }

        $this->users = $this->users->sortByDesc('tasks_cnt');

        return view('livewire.reports.filter-section');
    }
}
