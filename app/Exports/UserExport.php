<?php

namespace App\Exports;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class UserExport implements FromView
{
    public function __construct(
        public string $startDate = '',
        public string $endDate = '',
    ) {
        if (!$this->startDate) {
            $this->startDate = date('Y-m-01');
        }
        if (!$this->endDate) {
            $this->endDate = date('Y-m-t');
        }
    }

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

        $users = User::with('tasks')->where('leave', 0)->get();
        foreach ($users as $user) {
            $user->kpi_score = $user->kpiCalculate($this->startDate, $this->endDate);
            $user->ovr_kpi = $user->ovrKpiCalculate($this->startDate, $this->endDate);
        }

        $users = $users->sortByDesc('kpi_score');
        return view('exports.users', [
            'users' => $users,
            'norms' => $norms,
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
        ]);
    }
}
