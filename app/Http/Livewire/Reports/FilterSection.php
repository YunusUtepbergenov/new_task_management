<?php

namespace App\Http\Livewire\Reports;

use App\Models\Task;
use App\Models\User;
use Livewire\Component;

class FilterSection extends Component
{
    public $startDate, $endDate, $users;
    public $sortColumnName = "tasks_cnt", $sortDirection = "desc";

    public function mount(){
        $this->startDate = date('Y-m-01');
        $this->endDate = date('Y-m-t');
    }

    public function sortBy($columnName){
        if($this->sortColumnName === $columnName){
            $this->sortDirection = $this->swapSortDirection();
        }else{
            $this->sortDirection = 'desc';
        }
        $this->sortColumnName = $columnName;
    }

    public function swapSortDirection(){
        return $this->sortDirection === 'asc' ? 'desc' : 'asc';
    }

    public function render()
    {
        $this->users = User::with('tasks')->where('leave', 0)->get();

        foreach($this->users as $employee){
            $employee->tasks_cnt =  $employee->filterTasks($this->startDate, $this->endDate)->count();
            if($employee->tasks_cnt != 0){
                $employee->efficiency = round( ((1 - ( $employee->overdueFilter($this->startDate, $this->endDate)->count()
                + (0.5 * $employee->newFilter($this->startDate, $this->endDate)->count()) ) / $employee->filterTasks($this->startDate, $this->endDate)->count() )) * 100, 1);
            $employee->done_cnt = $employee->tasks->whereBetween('deadline', [$this->startDate, $this->endDate])->where('status', 'Выполнено')->where('overdue', 0)->count();
            $employee->new_cnt = $employee->tasks->whereBetween('deadline', [$this->startDate, $this->endDate])->where('status', 'Новое')->where('overdue', 0)->count();
            $employee->doing_cnt = $employee->tasks->whereBetween('deadline', [$this->startDate, $this->endDate])->where('status', 'Выполняется')->where('overdue', 0)->count();            
            $employee->overdue_cnt = $employee->overdueFilter($this->startDate, $this->endDate)->count();
            $employee->confirm_cnt = $employee->confirmFilter($this->startDate, $this->endDate)->count();
            }else{
                $employee->efficiency = 0;
                $employee->done_cnt = 0;
                $employee->new_cnt = 0;
                $employee->doing_cnt = 0;
                $employee->overdue_cnt = 0;
                $employee->confirm_cnt = 0;
            }
            $employee->sector_name = $employee->sector->name; 
        }

        $this->users = $this->users->sortBy([[$this->sortColumnName, $this->sortDirection]]);
        // dump($this->sortDirection);

        return view('livewire.reports.filter-section');
    }
}
