<?php

namespace App\Exports;

use App\Models\Task;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;

class DoneTasksExport implements FromView, WithTitle
{
    use Exportable;

    /**
     * @var array<int, string>
     */
    private array $statuses = ['Выполнено', 'Ждет подтверждения'];

    public function __construct(
        private string $start,
        private string $end
    ) {
    }

    public function view(): View
    {
        $tasks = Task::query()
            ->with(['user', 'sector', 'score'])
            ->whereIn('status', $this->statuses)
            ->whereBetween('deadline', [$this->start, $this->end])
            ->orderBy('sector_id')
            ->orderBy('deadline')
            ->get()
            ->groupBy(fn (Task $task): string => $task->group_id ?? (string) $task->id)
            ->map(function ($group): Task {
                /** @var Task $main */
                $main = $group->first();
                $main->name = $this->clean($main->name);
                $main->merged_responsibles = $group
                    ->pluck('user')
                    ->filter()
                    ->map(fn ($user) => $this->clean($user->name))
                    ->unique()
                    ->join(', ');

                return $main;
            })
            ->values();

        return view('exports.done_tasks', [
            'tasks' => $tasks,
            'start' => $this->start,
            'end' => $this->end,
        ]);
    }

    public function title(): string
    {
        return 'Выполненные задачи';
    }

    /**
     * Strip control characters that are illegal in XML/HTML and cause
     * PhpSpreadsheet's DOM parser to reject the exported sheet.
     */
    private function clean(?string $value): string
    {
        return preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/u', '', (string) $value) ?? '';
    }
}
