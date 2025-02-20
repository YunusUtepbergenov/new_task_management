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
        $norms = [
            '2' => 100,
            '3' => 90,
            '4' => 80,
            '5' => 90,
            '6' => 90,
            '7' => 80,
            '8' => 100,
            '9' => 80,
            '10' => 80,
            '11' => 80,
            '12' => 80,
            '13' => 80,
            '14' => 80,
            '15' => 80,
            '16' => 80,
            '17' => 80,
            '18' => 80           
        ];

        $this->startDate = date('Y-m-01');
        $this->endDate = date('Y-m-t');

        $this->users = User::with('tasks')->where('leave', 0)->get();
        foreach($this->users as $user){
            $user->kpi_score = $user->kpiCalculate();
            $user->ovr_kpi = $user->ovrKpiCalculate();
        }

        $this->users = $this->users->sortByDesc('ovr_kpi');
        return view('exports.users', [
            'users' => $this->users,
            'norms' => $norms,
        ]);
    }
}
