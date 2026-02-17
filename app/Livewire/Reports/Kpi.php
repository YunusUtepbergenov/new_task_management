<?php

namespace App\Livewire\Reports;

use App\Exports\UserExport;
use App\Models\{Scores, Task, User};
use Livewire\Attributes\{Lazy, Url};
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

#[Lazy]
class Kpi extends Component
{
    public function placeholder(): \Illuminate\Contracts\View\View
    {
        return view('livewire.placeholders.loading');
    }

    public $startDate, $endDate;

    #[Url(as: 'month')]
    public $selectedMonth;

    public function mount(): void
    {
        $this->selectedMonth = $this->selectedMonth ?: date('Y-m');
        $this->applyMonth();
    }

    public function updatedSelectedMonth(): void
    {
        $this->applyMonth();
    }

    private function applyMonth(): void
    {
        $date = \Carbon\Carbon::createFromFormat('Y-m', $this->selectedMonth);
        $this->startDate = $date->startOfMonth()->toDateString();
        $this->endDate = $date->endOfMonth()->toDateString();
    }

    public function export()
    {
        $fileName = 'KPI_' . $this->selectedMonth . '.xlsx';
        return Excel::download(new UserExport($this->startDate, $this->endDate), $fileName);
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        $users = User::whereIn('sector_id', [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 12, 13, 14, 15, 16])
            ->where('leave', 0)
            ->get();

        $limits = Scores::pluck('limit', 'id');

        $stats = Task::whereIn('user_id', $users->pluck('id'))
            ->where('status', 'Выполнено')
            ->whereBetween('deadline', [$this->startDate, $this->endDate])
            ->whereNotNull('score_id')
            ->selectRaw('user_id, score_id, SUM(total) as cat_score')
            ->groupBy('user_id', 'score_id')
            ->get()
            ->groupBy('user_id');

        foreach ($users as $user) {
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

        $users = $users->sortByDesc('kpi_score');

        return view('livewire.reports.kpi', [
            'users' => $users,
        ]);
    }
}
