<?php

namespace App\Livewire;

use App\Models\Project;
use App\Models\TaskUser;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class HelpingTasks extends Component
{
    public $helping_projects, $tasks_without_project;

    public function mount(): void
    {
        $taskUsers = TaskUser::with('task.project')
            ->where('user_id', Auth::id())
            ->get();

        $withProject = collect();
        $this->tasks_without_project = collect();

        foreach ($taskUsers as $taskUser) {
            if ($taskUser->task && $taskUser->task->project_id) {
                $withProject->push($taskUser->task->project_id);
            } elseif ($taskUser->task) {
                $this->tasks_without_project->push($taskUser->task);
            }
        }

        $this->helping_projects = Project::with('tasks')
            ->whereIn('id', $withProject->unique())
            ->get();
    }

    public function view($task_id): void
    {
        $this->dispatch('taskClicked', id: $task_id);
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        return view('livewire.helping-tasks');
    }
}
