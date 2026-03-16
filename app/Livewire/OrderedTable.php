<?php

namespace App\Livewire;

use App\Models\Task;
use App\Models\User;
use App\Traits\HasTaskDeletion;
use App\Traits\HasTaskView;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Livewire\Attributes\On;
use Livewire\Component;
use App\Models\Repeat;

class OrderedTable extends Component
{
    use HasTaskView, HasTaskDeletion;
    public string $weeklySearch = '';

    #[On('task-updated')]
    #[On('task-created')]
    public function refreshTasks(): void
    {
        // Re-render triggers fresh data from render()
    }

    public function deleteRepeat(int $repeatId): void
    {
        $repeat = Repeat::where('id', $repeatId)->first();

        if ($repeat) {
            $task = Task::where('repeat_id', $repeat->id)->where('creator_id', Auth::id())->first();
            if ($task) {
                $task->update(['repeat_id' => null]);
            }
            $repeat->delete();
        }

        $this->dispatch('toastr:success', message: 'Повторяющаяся задача остановлена.');
    }

    public function updatePlanType($taskId, $newType)
    {
        $task = Task::where('id', $taskId)
            ->where('creator_id', Auth::id())
            ->firstOrFail();

        if (in_array($newType, ['weekly', 'unplanned'])) {
            $task->planning_type = $newType;
            $task->save();
        }

        $this->dispatch('toastr:success', message: 'Тип задачи обновлен.');
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        $baseQuery = Task::with('user:id,name,sector_id,role_id')
            ->where('creator_id', Auth::id())
            ->where('status', '<>', 'Выполнено')
            ->orderByRaw('COALESCE(extended_deadline, deadline)');

        $weeklyQuery = (clone $baseQuery)
            ->where(function ($query) {
                $query->where(function ($q) {
                    $q->whereNull('extended_deadline')->where('deadline', '<=', Carbon::now()->endOfWeek());
                })->orWhere(function ($q) {
                    $q->whereNotNull('extended_deadline')->where('extended_deadline', '<=', Carbon::now()->endOfWeek());
                });
            });

        if ($this->weeklySearch !== '') {
            $search = $this->weeklySearch;
            $weeklyQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('status', 'like', "%{$search}%")
                  ->orWhere('deadline', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $weeklyTasks = $weeklyQuery
            ->get()
            ->groupBy(fn ($task) => $task->group_id ?: $task->id)
            ->map->toArray();

        $all_tasks = (clone $baseQuery)
            ->whereNull('project_id')
            ->get()
            ->groupBy(fn ($task) => $task->group_id ?: $task->id)
            ->map->toArray();

        return view('livewire.ordered-table', [
            'username' => Auth::user()->name,
            'weeklyTasks' => $weeklyTasks,
            'all_tasks' => $all_tasks,
        ]);
    }
}
