<?php

namespace App\Http\Livewire\Reports;

// use App\Models\Task;
use Livewire\Component;

class ResultSection extends Component
{
    public $sectors, $users, $param;

    protected $listeners = ["updateFilters"];

    public function updateFilters($param){
        $this->param = $param;
        // $tasks = Task::whereBetween('created_at', [$param['start'], $param['end']])->get();
        $this->users = User::with(['task', function($query){
            $query->whereBetween('created_at', [$this->param['start'], $this->param['end']]);
        }])->get();
        dd($this->users);
    }

    public function render()
    {
        return view('livewire.reports.result-section');
    }
}
