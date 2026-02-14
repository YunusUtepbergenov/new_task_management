<?php

namespace App\Livewire\Reports;

use App\Models\User;
use Livewire\Attributes\On;
use Livewire\Component;

class ResultSection extends Component
{
    public $sectors, $users, $param;

    #[On('updateFilters')]
    public function updateFilters($param): void
    {
        $this->param = $param;
        $this->users = User::with(['tasks' => function ($query) {
            $query->whereBetween('created_at', [$this->param['start'], $this->param['end']]);
        }])->get();
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        return view('livewire.reports.result-section');
    }
}
