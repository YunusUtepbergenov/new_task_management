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
            ->selectRaw('tasks.*, (SELECT COUNT(*) FROM tasks AS t2 WHERE t2.group_id = tasks.group_id AND tasks.group_id IS NOT NULL) as group_member_count')
            ->where('creator_id', Auth::id())
            ->where('status', '<>', 'Выполнено')
            ->orderByRaw('COALESCE(extended_deadline, deadline)')
            ->orderBy('id');

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

        $weeklyRaw = $weeklyQuery->get();
        $allRaw = (clone $baseQuery)->whereNull('project_id')->get();

        $groupIds = $weeklyRaw->pluck('group_id')
            ->merge($allRaw->pluck('group_id'))
            ->filter()
            ->unique()
            ->values()
            ->all();

        $groupMains = collect();
        if (!empty($groupIds)) {
            $groupMains = Task::with('user:id,name,sector_id,role_id')
                ->selectRaw('tasks.*, (SELECT COUNT(*) FROM tasks AS t2 WHERE t2.group_id = tasks.group_id AND tasks.group_id IS NOT NULL) as group_member_count')
                ->whereIn('group_id', $groupIds)
                ->whereIn('id', function ($q) use ($groupIds) {
                    $q->selectRaw('MIN(id)')->from('tasks')->whereIn('group_id', $groupIds)->groupBy('group_id');
                })
                ->get()
                ->keyBy('group_id');
        }

        $buildGroups = function ($rawTasks) use ($groupMains) {
            return $rawTasks
                ->groupBy(fn ($task) => $task->group_id ?: $task->id)
                ->map(function ($group) use ($groupMains) {
                    $filteredFirst = $group->first();
                    $main = $filteredFirst->group_id && isset($groupMains[$filteredFirst->group_id])
                        ? $groupMains[$filteredFirst->group_id]
                        : $filteredFirst;

                    return [
                        'main' => $main->toArray(),
                        'members' => $group->toArray(),
                    ];
                })
                ->values()
                ->toArray();
        };

        return view('livewire.ordered-table', [
            'username' => Auth::user()->name,
            'weeklyTasks' => $buildGroups($weeklyRaw),
            'all_tasks' => $buildGroups($allRaw),
        ]);
    }
}
