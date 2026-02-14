<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use App\Models\{Task, Sector};
use App\Services\TaskService;
use Carbon\Carbon;

class TasksTable extends Component
{
    public $username;
    public $sectors, $scoresGrouped;

    public function mount(): void
    {
        // Bulk update unread tasks to "Выполняется"
        Task::where('user_id', Auth::id())
            ->whereNull('project_id')
            ->where('status', 'Не прочитано')
            ->update(['status' => 'Выполняется']);

        // Static data that doesn't change between renders
        $this->username = Auth::user()->name;
        $this->sectors = Sector::with('users')->get();
        $this->scoresGrouped = ['Категории' => (new TaskService())->scoresList()];
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        $baseQuery = Task::with('user:id,name,sector_id,role_id')
            ->where('user_id', Auth::id())
            ->where('status', '<>', 'Выполнено')
            ->orderByRaw('COALESCE(extended_deadline, deadline)');

        $weeklyTasks = (clone $baseQuery)
            ->where(function ($query) {
                $query->where(function ($q) {
                    $q->whereNull('extended_deadline')
                      ->where('deadline', '<=', Carbon::now()->endOfWeek());
                })->orWhere(function ($q) {
                    $q->whereNotNull('extended_deadline')
                      ->where('extended_deadline', '<=', Carbon::now()->endOfWeek());
                });
            })
            ->get()
            ->groupBy(fn ($task) => $task->group_id ?: $task->id)
            ->map->toArray();

        $all_tasks = (clone $baseQuery)
            ->whereNull('project_id')
            ->get()
            ->groupBy(fn ($task) => $task->group_id ?: $task->id)
            ->map->toArray();

        return view('livewire.tasks-table', [
            'weeklyTasks' => $weeklyTasks,
            'all_tasks' => $all_tasks,
        ]);
    }

    public function view($task_id): void
    {
        $this->dispatch('taskClicked', id: $task_id);
    }
}
