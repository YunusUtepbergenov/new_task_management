<?php

namespace App\Http\Livewire\Reports;

use App\Models\Scores;
use App\Models\User;
use Livewire\Component;

class Kpi extends Component
{
    public $startDate, $endDate, $users;

    public function mount(){
        $this->startDate = date('Y-m-01');
        $this->endDate = date('Y-m-t');
    }
    public function render()
    {
        $this->users = User::whereIn('sector_id', [1,2,3,4,5,6,7,8,9,10,12,13,14,15,16])->where('leave', 0)->get();

        foreach($this->users as $user){
            $user->kpi_score = $user->kpiCalculate();
            $user->ovr_kpi = $user->ovrKpiCalculate();
        }

        $this->users = $this->users->sortByDesc('kpi_score');
        return view('livewire.reports.kpi');
    }
}