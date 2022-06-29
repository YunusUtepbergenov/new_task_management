<?php

namespace App\Http\Livewire\Reports;

use App\Models\Task;
use App\Models\User;
use Livewire\Component;

class FilterSection extends Component
{
    public $startDate, $endDate, $users;

    public function mount(){
        $this->startDate = date('Y-m-d',strtotime('-1 months'));
        $this->endDate = date('Y-m-d');

        $this->users = User::with('tasks')->get();
    }

    public function updated(){
        if($this->startDate != Null && $this->endDate != Null){

        }
    }

    // public function updateFilters($param){
    //     $this->param = $param;
    //     // $tasks = Task::whereBetween('created_at', [$param['start'], $param['end']])->get();
    //     $this->users = User::with(['task', function($query){
    //         $query->whereBetween('created_at', [$this->param['start'], $this->param['end']]);
    //     }])->get();
    //     dd($this->users);
    // }

    public function render()
    {
        return view('livewire.reports.filter-section');
    }
}
