<?php

namespace App\Livewire\Reports;

use App\Models\{Sector, Task};
use Livewire\Attributes\Lazy;
use Livewire\Component;

#[Lazy]
class FilterSection extends Component
{
    public function placeholder(): \Illuminate\Contracts\View\View
    {
        return view('livewire.placeholders.loading');
    }

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
        }])->get();

        $stats = Task::whereBetween('deadline', [$this->startDate, $this->endDate])
            ->selectRaw('user_id')
            ->selectRaw('COUNT(*) as tasks_cnt')
            ->selectRaw("SUM(CASE WHEN overdue = 1 THEN 1 ELSE 0 END) as overdue_cnt")
            ->selectRaw("SUM(CASE WHEN status = 'Не прочитано' AND overdue = 0 THEN 1 ELSE 0 END) as new_cnt")
            ->selectRaw("SUM(CASE WHEN status = 'Выполняется' AND overdue = 0 THEN 1 ELSE 0 END) as doing_cnt")
            ->selectRaw("SUM(CASE WHEN status = 'Выполнено' AND overdue = 0 THEN 1 ELSE 0 END) as done_cnt")
            ->selectRaw("SUM(CASE WHEN status = 'Ждет подтверждения' THEN 1 ELSE 0 END) as confirm_cnt")
            ->groupBy('user_id')
            ->get()
            ->keyBy('user_id');

        foreach ($this->sectors as $sector) {
            foreach ($sector->users as $employee) {
                $s = $stats->get($employee->id);
                $employee->tasks_cnt = $s ? (int) $s->tasks_cnt : 0;

                if ($employee->tasks_cnt > 0) {
                    $employee->overdue_cnt = (int) $s->overdue_cnt;
                    $employee->new_cnt = (int) $s->new_cnt;
                    $employee->doing_cnt = (int) $s->doing_cnt;
                    $employee->done_cnt = (int) $s->done_cnt;
                    $employee->confirm_cnt = (int) $s->confirm_cnt;
                    $employee->efficiency = round(((1 - ($employee->overdue_cnt + (0.5 * $employee->new_cnt)) / $employee->tasks_cnt)) * 100, 1);
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
