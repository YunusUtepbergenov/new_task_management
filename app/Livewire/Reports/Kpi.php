<?php

namespace App\Livewire\Reports;

use App\Models\User;
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

        foreach ($this->users as $user) {
            $user->kpi_score = $user->kpiCalculate();
            $user->ovr_kpi = $user->ovrKpiCalculate();
        }

        $this->users = $this->users->sortByDesc('kpi_score');
    }
}
