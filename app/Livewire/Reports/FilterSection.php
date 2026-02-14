<?php

namespace App\Livewire\Reports;

use App\Models\Sector;
use Livewire\Component;

class FilterSection extends Component
{
    public $startDate, $endDate, $sectors;
    public $sortColumnName = "tasks_cnt", $sortDirection = "desc";

    public function mount(): void
    {
        $this->startDate = date('Y-m-01');
        $this->endDate = date('Y-m-t');
    }

    public function sortBy($columnName): void
    {
        if ($this->sortColumnName === $columnName) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortDirection = 'desc';
        }
        $this->sortColumnName = $columnName;
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        $this->sectors = Sector::with(['users' => function ($query) {
            $query->where('leave', 0)->orderBy('role_id');
        }, 'users.tasks' => function ($query) {
            $query->whereBetween('deadline', [$this->startDate, $this->endDate]);
        }])->get();

        foreach ($this->sectors as $sector) {
            foreach ($sector->users as $employee) {
                $tasks = $employee->tasks;
                $employee->tasks_cnt = $tasks->count();

                if ($employee->tasks_cnt > 0) {
                    $overdueCnt = $tasks->where('overdue', 1)->count();
                    $newCnt = $tasks->where('status', 'Не прочитано')->where('overdue', 0)->count();

                    $employee->efficiency = round(((1 - ($overdueCnt + (0.5 * $newCnt)) / $employee->tasks_cnt)) * 100, 1);
                    $employee->done_cnt = $tasks->where('status', 'Выполнено')->where('overdue', 0)->count();
                    $employee->new_cnt = $newCnt;
                    $employee->doing_cnt = $tasks->where('status', 'Выполняется')->where('overdue', 0)->count();
                    $employee->overdue_cnt = $overdueCnt;
                    $employee->confirm_cnt = $tasks->where('status', 'Ждет подтверждения')->count();
                } else {
                    $employee->efficiency = 0;
                    $employee->done_cnt = 0;
                    $employee->new_cnt = 0;
                    $employee->doing_cnt = 0;
                    $employee->overdue_cnt = 0;
                    $employee->confirm_cnt = 0;
                }

                $employee->sector_name = $sector->name;
            }
        }

        return view('livewire.reports.filter-section');
    }
}
