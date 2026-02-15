<?php

namespace App\Livewire\Reports;

use App\Models\{Task, User};
use Livewire\Attributes\Lazy;
use Livewire\Component;

#[Lazy]
class TestReport extends Component
{
    public function placeholder(): \Illuminate\Contracts\View\View
    {
        return view('livewire.placeholders.loading');
    }

    public $users;
    public $startDate, $endDate;

    public function mount(): void
    {
        $this->startDate = date('Y-m-01');
        $this->endDate = date('Y-m-t');

        $this->users = User::where('leave', 0)->get();

        $stats = Task::whereBetween('deadline', [$this->startDate, $this->endDate])
            ->whereIn('user_id', $this->users->pluck('id'))
            ->selectRaw('user_id, priority_id, COUNT(*) as total_cnt')
            ->selectRaw("SUM(CASE WHEN status IN ('Выполнено','Ждет подтверждения') THEN 1 ELSE 0 END) as done_cnt")
            ->groupBy('user_id', 'priority_id')
            ->get()
            ->groupBy('user_id');

        foreach ($this->users as $employee) {
            $userStats = $stats->get($employee->id, collect());
            $byPriority = $userStats->keyBy('priority_id');

            $priorities = [1 => 0.1, 2 => 0.2, 3 => 0.3];
            $totalScore = 10;

            foreach ($priorities as $pid => $coeff) {
                $p = $byPriority->get($pid);
                $totalScore += $p && $p->total_cnt > 0
                    ? $coeff * round(($p->done_cnt / $p->total_cnt) * 100, 1)
                    : 0;
            }

            $p4 = $byPriority->get(4);
            $totalScore += $p4 && $p4->total_cnt > 0
                ? round(($p4->done_cnt / $p4->total_cnt) * 30, 1)
                : 0;

            $employee->kpi_score = $totalScore;
        }

        $this->users = $this->users->sortByDesc('kpi_score');
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        return view('livewire.reports.test-report');
    }
}
