<?php

namespace App\Http\Livewire\Reports;

use App\Models\User;
use Livewire\Component;

class TestReport extends Component
{
    public $users;
    public $startDate = "2022-10-01", $endDate = "2022-10-31";
    public function mount(){
        $this->users = User::with('tasks')->where('leave', 0)->get();
        foreach($this->users as $employee){
            if ($employee->simple_priority_filterTasks($this->startDate, $this->endDate)->count() > 0){
                $employee->simple_score = 0.1 * round((( $employee->simple_priority_doneFilter($this->startDate, $this->endDate)->count() /
                                $employee->simple_priority_filterTasks($this->startDate, $this->endDate)->count() )) * 100, 1);
            }else{
                $employee->simple_score = 0;
            }

            if ($employee->mid_priority_filterTasks($this->startDate, $this->endDate)->count() > 0){
                $employee->mid_score = 0.2 * round((( $employee->mid_priority_doneFilter($this->startDate, $this->endDate)->count() /
                                $employee->mid_priority_filterTasks($this->startDate, $this->endDate)->count() )) * 100, 1);
            }else{
                $employee->mid_score = 0;
            }

            if ($employee->high_priority_filterTasks($this->startDate, $this->endDate)->count() > 0){
                $employee->high_score = 0.3 * round((( $employee->high_priority_doneFilter($this->startDate, $this->endDate)->count() /
                                $employee->high_priority_filterTasks($this->startDate, $this->endDate)->count() )) * 100, 1);
            }else{
                $employee->high_score = 0;
            }

            if ($employee->very_high_priority_filterTasks($this->startDate, $this->endDate)->count() > 0){
                $employee->very_high_score = 0.3 * round((( $employee->very_high_priority_doneFilter($this->startDate, $this->endDate)->count() /
                                $employee->very_high_priority_filterTasks($this->startDate, $this->endDate)->count() )) * 100, 1);
            }else{
                $employee->very_high_score = 0;
            }


            $employee->kpi_score = $employee->simple_score + $employee->mid_score + $employee->high_score + $employee->very_high_score + 10;
        }
        $this->users = $this->users->sortByDesc('kpi_score');
    }
    public function render()
    {
        return view('livewire.reports.test-report');
    }
}
