<?php

namespace App\Http\Livewire\Reports;

use Livewire\Component;
use App\Models\Sector;

class WeeklyTasksOverview extends Component
{

    public $sectors;

    public function render()
    {
        $this->sectors = Sector::with(['tasks' => function ($query) {
            $query->where('planning_type', 'weekly')->with('user', 'score');
        }])->get();

        return view('livewire.reports.weekly-tasks-overview');
    }
}
