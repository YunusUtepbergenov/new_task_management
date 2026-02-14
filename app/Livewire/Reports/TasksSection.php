<?php

namespace App\Livewire\Reports;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Livewire\Attributes\On;
use Livewire\Component;

class TasksSection extends Component
{
    public $tasks, $user, $filter = null, $projects;

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
        $this->projects = null;
        $this->tasks = Task::with(['user', 'creator'])->where('sector_id', $id)->get();
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        return view('livewire.reports.tasks-section');
    }

    private function fetchTasks(int $userId): void
    {
        if (!$this->filter) {
            $projectIds = Task::where('user_id', $userId)
                ->whereNotNull('project_id')
                ->distinct()
                ->pluck('project_id');

            $this->projects = Project::with(['tasks' => function ($query) use ($userId) {
                $query->with(['user:id,name', 'creator:id,name'])->where('user_id', $userId);
            }])->whereIn('id', $projectIds)->get();

            $this->tasks = Task::with('creator:id,name,sector_id,role_id')
                ->where('user_id', $userId)
                ->whereNull('project_id')
                ->latest()
                ->get();
        } elseif ($this->filter === 'Просроченный') {
            $this->tasks = Task::with(['user:id,name', 'creator:id,name'])
                ->where('user_id', $userId)
                ->where('overdue', 1)
                ->latest()
                ->get();
            $this->projects = null;
        } else {
            $this->tasks = Task::with(['user:id,name', 'creator:id,name'])
                ->where('user_id', $userId)
                ->where('overdue', 0)
                ->where('status', $this->filter)
                ->latest()
                ->get();
            $this->projects = null;
        }
    }
}
