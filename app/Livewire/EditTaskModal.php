<?php

namespace App\Livewire;

use App\Models\File;
use App\Models\Sector;
use App\Models\Task;
use App\Models\User;
use App\Services\TaskService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Attributes\On;
use Livewire\Component;

class EditTaskModal extends Component
{
    public $taskId, $name, $deadline, $scoreId, $creatorId;
    public $userIds = [];
    public $errorMsg;
    public $filteredSectors = [];
    public $creators = [];
    public $scoresGrouped = [];

    public function mount(): void
    {
        $user = Auth::user();
        $sectors = Sector::with(['users' => function ($query) {
            $query->where('leave', 0);
        }])->get();

        $this->filteredSectors = [];
        foreach ($sectors as $sector) {
            $sectorUsers = $sector->users->filter(function ($u) use ($user) {
                if ($user->isDirector() || $user->isMailer()) {
                    return true;
                }
                if ($user->isDeputy()) {
                    return !$u->isDirector() && (!$u->isDeputy() || $u->id == $user->id);
                }
                if ($user->isHead()) {
                    return !$u->isDirector() && !$u->isDeputy();
                }
                return false;
            });

            if ($sectorUsers->isNotEmpty()) {
                $this->filteredSectors[] = [
                    'name' => $sector->name,
                    'users' => $sectorUsers->map(fn ($u) => ['id' => $u->id, 'name' => $u->name])->values()->toArray(),
                ];
            }
        }

        $creatorsList = collect();
        if ($user->isDirector() || $user->isMailer() || $user->isHead()) {
            $creatorsList->push($user);
        } elseif ($user->isDeputy()) {
            foreach ($sectors as $sector) {
                foreach ($sector->users->whereIn('role_id', [2, 14, 19]) as $u) {
                    $creatorsList->push($u);
                }
            }
        }

        $this->creators = $creatorsList->unique('id')->map(fn ($u) => ['id' => $u->id, 'name' => $u->name])->values()->toArray();

        $this->scoresGrouped = [
            'Категории' => (new TaskService())->scoresList()->map(fn ($s) => [
                'id' => $s->id,
                'name' => $s->name,
                'max_score' => $s->max_score,
            ])->toArray(),
        ];
    }

    #[On('editTaskClicked')]
    public function loadTask(int $id): void
    {
        $this->reset(['taskId', 'name', 'deadline', 'scoreId', 'creatorId', 'userIds', 'errorMsg']);

        $task = Task::find($id);

        if (!$task) {
            $this->dispatch('task-updated');
            return;
        }

        $this->taskId = $task->id;
        $this->name = $task->name;
        $this->scoreId = $task->score_id;
        $this->creatorId = $task->creator_id;
        $this->deadline = $task->extended_deadline
            ? Carbon::parse($task->extended_deadline)->format('Y-m-d')
            : $task->deadline;

        if ($task->group_id) {
            $this->userIds = Task::where('group_id', $task->group_id)
                ->pluck('user_id')
                ->toArray();
        } else {
            $this->userIds = [$task->user_id];
        }

        $this->dispatch('show-edit-modal');
        $this->dispatch('edit-form-loaded', [
            'scoreId' => $this->scoreId,
            'userIds' => $this->userIds,
            'creatorId' => $this->creatorId,
            'name' => $this->name,
            'deadline' => $this->deadline,
        ]);
    }

    public function taskUpdate(): void
    {
        $this->validate([
            'name' => 'required|min:3|max:255',
            'userIds' => 'required|array|min:1',
            'userIds.*' => 'exists:users,id',
            'deadline' => 'required|date',
            'scoreId' => 'required|exists:scores,id',
        ]);

        $baseTask = Task::findOrFail($this->taskId);
        $groupId = $baseTask->group_id;

        $newDeadline = Carbon::parse($this->deadline);
        $isExtended = $newDeadline->gt(Carbon::parse($baseTask->deadline));

        $groupTasks = $groupId
            ? Task::where('group_id', $groupId)->get()
            : collect([$baseTask]);

        $existingUserIds = $groupTasks->pluck('user_id')->toArray();
        $newUserIds = $this->userIds;

        if (array_diff($existingUserIds, $newUserIds) || array_diff($newUserIds, $existingUserIds)) {
            foreach ($groupTasks as $task) {
                File::where('task_id', $task->id)->delete();
                $task->delete();
            }

            $isMultiple = count($newUserIds) > 1;
            $newGroupId = $groupId ?? ($isMultiple ? Str::uuid()->toString() : null);
            $creatorId = $isMultiple ? Auth::id() : $this->creatorId;

            foreach ($newUserIds as $userId) {
                $user = User::findOrFail($userId);
                Task::create([
                    'group_id' => $newGroupId,
                    'creator_id' => $creatorId,
                    'user_id' => $userId,
                    'sector_id' => $user->sector_id,
                    'score_id' => $this->scoreId,
                    'name' => $this->name,
                    'deadline' => $baseTask->deadline,
                    'extended_deadline' => $isExtended ? $newDeadline : null,
                    'status' => 'Не прочитано',
                    'overdue' => 0,
                ]);
            }
        } else {
            foreach ($groupTasks as $task) {
                $user = User::findOrFail($task->user_id);
                $task->update([
                    'creator_id' => $this->creatorId,
                    'sector_id' => $user->sector_id,
                    'score_id' => $this->scoreId,
                    'name' => $this->name,
                    'extended_deadline' => $isExtended ? $newDeadline : null,
                    'status' => in_array($task->status, ['Ждет подтверждения', 'Выполнено'])
                        ? $task->status
                        : 'Не прочитано',
                    'overdue' => 0,
                ]);
            }
        }

        $this->dispatch('close-edit-modal');
        $this->dispatch('task-updated');
        $this->dispatch('success', msg: 'Задача успешно изменена.');
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        return view('livewire.edit-task-modal');
    }
}
