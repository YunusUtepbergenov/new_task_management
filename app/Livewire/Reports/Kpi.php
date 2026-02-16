<?php

namespace App\Livewire\Reports;

use App\Models\{Scores, Task, User};
use Livewire\Attributes\Lazy;
use Livewire\Component;

#[Lazy]
class Kpi extends Component
{
    public function placeholder(): \Illuminate\Contracts\View\View
    {
        return view('livewire.placeholders.loading');
    }

    public $startDate, $endDate, $users;

    public function mount(): void
    {
        $this->startDate = date('Y-m-01');
        $this->endDate = date('Y-m-t');
        $this->fetchUsers();
    }

    public function updatedStartDate(): void
    {
        $this->fetchUsers();
    }

    public function updatedEndDate(): void
    {
        $this->fetchUsers();
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        return view('livewire.reports.kpi');
    }

    private function fetchUsers(): void
    {
        $this->users = User::whereIn('sector_id', [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 12, 13, 14, 15, 16])
            ->where('leave', 0)
            ->get();

        $limits = Scores::pluck('limit', 'id');

        $stats = Task::whereIn('user_id', $this->users->pluck('id'))
            ->where('status', 'Выполнено')
            ->whereBetween('deadline', [$this->startDate, $this->endDate])
            ->whereNotNull('score_id')
            ->selectRaw('user_id, score_id, SUM(total) as cat_score')
            ->groupBy('user_id', 'score_id')
            ->get()
            ->groupBy('user_id');

        foreach ($this->users as $user) {
            $userScores = $stats->get($user->id, collect());
            $capped = 0;
            $uncapped = 0;
            foreach ($userScores as $row) {
                $uncapped += $row->cat_score;
                $limit = $limits[$row->score_id] ?? null;
                $capped += ($limit && $row->cat_score > $limit) ? $limit : $row->cat_score;
            }
            $user->kpi_score = $capped;
            $user->ovr_kpi = $uncapped;
        }

        $this->users = $this->users->sortByDesc('kpi_score');
    }
}
