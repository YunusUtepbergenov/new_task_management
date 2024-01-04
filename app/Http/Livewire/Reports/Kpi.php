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
        $this->users = User::where('leave', 0)->get();

        foreach($this->users as $user){
            $score = 0;
            // $userTasks = $user->tasks()->whereBetween('deadline', [$this->startDate, $this->endDate])->whereIn('status', 'Выполнено')->get();
            $categories = Scores::all();
            foreach($categories as $category){
                $cat_score = $user->kpiFilter($this->startDate, $this->endDate, $category->id);
                if(isset($category->limit) && $cat_score > $category->limit)
                    $score += $category->limit;
                else
                    $score += $cat_score;
            }
            $user->kpi_score = $score;
        }

        $this->users = $this->users->sortByDesc('kpi_score');
        return view('livewire.reports.kpi');
    }
}