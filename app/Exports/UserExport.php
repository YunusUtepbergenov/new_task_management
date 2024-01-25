<?php

namespace App\Exports;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class UserExport implements FromView
{
    // /**
    // * @return \Illuminate\Support\Collection
    // */
    // public function collection()
    // {
    //     return User::all();
    // }
    public $startDate, $endDate;
    public $users;
    public function view(): View
    {
        $this->startDate = date('Y-m-01');
        $this->endDate = date('Y-m-t');

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
        return view('exports.users', [
            'users' => $this->users
        ]);
    }
}
