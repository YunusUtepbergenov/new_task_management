<?php

namespace App\Livewire\Reports;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Livewire\Attributes\On;
use Livewire\Component;

class TasksSection extends Component
{
    public $tasks, $user, $filter = null, $taskCounts = [];

    public function mount(): void
    {
        $this->tasks = null;
        $this->user = null;
    }

    public function view($task_id): void
    {
        $this->dispatch('taskClicked', id: $task_id);
    }

    #[On('updateUserId')]
    public function updateUserId($id): void
    {
        if (!$id) {
            $this->tasks = null;
            $this->user = null;
            return;
        }

        $this->user = User::find($id);
        $this->computeTaskCounts($id);
        $this->fetchTasks($id);
    }

    public function updatedFilter(): void
    {
        if (!$this->user) {
            $this->tasks = null;
            return;
        }

        $this->fetchTasks($this->user->id);
    }

    #[On('updateSectorTasks')]
    public function updateSectorTasks($id): void
    {
        $this->user = null;
        $this->tasks = Task::with(['user', 'creator'])->where('sector_id', $id)->get();
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        return view('livewire.reports.tasks-section');
    }

    private function computeTaskCounts(int $userId): void
    {
        $this->taskCounts = Task::where('user_id', $userId)
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("SUM(CASE WHEN status = 'Не прочитано' AND overdue = 0 THEN 1 ELSE 0 END) as new_cnt")
            ->selectRaw("SUM(CASE WHEN status = 'Выполняется' AND overdue = 0 THEN 1 ELSE 0 END) as doing_cnt")
            ->selectRaw("SUM(CASE WHEN status = 'Ждет подтверждения' THEN 1 ELSE 0 END) as confirm_cnt")
            ->selectRaw("SUM(CASE WHEN status = 'Выполнено' AND overdue = 0 THEN 1 ELSE 0 END) as finished_cnt")
            ->selectRaw("SUM(CASE WHEN overdue = 1 THEN 1 ELSE 0 END) as overdue_cnt")
            ->first()
            ->toArray();
    }

    private function fetchTasks(int $userId): void
    {
        if (!$this->filter) {
            $this->tasks = Task::with('creator:id,name,sector_id,role_id')
                ->where('user_id', $userId)
                ->latest()
                ->get();
        } elseif ($this->filter === 'Просроченный') {
            $this->tasks = Task::with(['user:id,name', 'creator:id,name'])
                ->where('user_id', $userId)
                ->where('overdue', 1)
                ->latest()
                ->get();
        } else {
            $this->tasks = Task::with(['user:id,name', 'creator:id,name'])
                ->where('user_id', $userId)
                ->where('overdue', 0)
                ->where('status', $this->filter)
                ->latest()
                ->get();
        }
    }

     public function placeholder()
    {
        return view('livewire.placeholders.loading');
    }
}
