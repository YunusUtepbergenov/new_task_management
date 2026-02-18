<?php

namespace App\Livewire;

use App\Events\TaskCreatedEvent;
use App\Models\File;
use App\Models\Sector;
use App\Models\Task;
use App\Models\User;
use App\Services\TaskService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

class CreateTaskModal extends Component
{
    use WithFileUploads;

    public string $name = '';
    public string $deadline = '';
    public $scoreId = null;
    public $creatorId = null;
    public array $userIds = [];
    public $files = [];
    public ?string $errorMsg = null;
    public array $filteredSectors = [];
    public array $creators = [];
    public array $scoresGrouped = [];

    public function mount(): void
    {
        $user = Auth::user();

        $this->loadFilteredSectors($user);
        $this->loadScoresGrouped($user);
        $this->loadCreators($user);

        $this->creatorId = $user->isResearcher()
            ? $user->sector->head()?->id ?? $user->id
            : $user->id;
    }

    #[On('openCreateTaskModal')]
    public function openModal(): void
    {
        $user = Auth::user();
        $this->reset(['name', 'deadline', 'scoreId', 'userIds', 'files', 'errorMsg']);
        $this->creatorId = $user->isResearcher()
            ? $user->sector->head()?->id ?? $user->id
            : $user->id;

        $this->dispatch('show-create-modal');
    }

    public function taskStore(): void
    {
        $this->validate([
            'name' => 'required|min:3|max:255',
            'userIds' => 'required|array|min:1',
            'userIds.*' => 'exists:users,id',
            'deadline' => 'required|date',
            'scoreId' => 'required|exists:scores,id',
            'files.*' => 'nullable|file|max:5000',
        ]);

        $isMultiple = count($this->userIds) > 1;
        $groupId = $isMultiple ? Str::uuid()->toString() : null;

        foreach ($this->userIds as $userId) {
            $user = User::findOrFail($userId);

            $task = Task::create([
                'creator_id' => $this->creatorId ?? Auth::id(),
                'user_id' => $userId,
                'sector_id' => $user->sector_id,
                'project_id' => null,
                'type_id' => 1,
                'priority_id' => 1,
                'score_id' => $this->scoreId,
                'name' => $this->name,
                'deadline' => $this->deadline,
                'status' => 'Не прочитано',
                'overdue' => 0,
                'group_id' => $groupId,
            ]);

            if (!empty($this->files)) {
                foreach ($this->files as $file) {
                    $filename = time() . $file->getClientOriginalName();
                    $filename = preg_replace('/[\r\n\t -]+/', '-', $filename);
                    Storage::disk('local')->putFileAs('files/', $file, $filename);

                    File::create([
                        'task_id' => $task->id,
                        'name' => $filename,
                    ]);
                }
            }

            event(new TaskCreatedEvent($task));
        }

        $this->reset(['name', 'deadline', 'scoreId', 'userIds', 'files', 'errorMsg']);
        $this->creatorId = Auth::id();

        $this->dispatch('close-create-modal');
        $this->dispatch('task-created');
        $this->dispatch('success', msg: 'Задача успешно создана.');
    }

    public function updatedFiles(): void
    {
        $this->validate([
            'files.*' => 'file|max:5000',
        ]);
    }

    public function removeFile(int $index): void
    {
        array_splice($this->files, $index, 1);
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        return view('livewire.create-task-modal');
    }

    private function loadFilteredSectors($user): void
    {
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
                    return $u->sector_id == $user->sector_id;
                }
                if ($user->isResearcher()) {
                    return $u->id === $user->id;
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
    }

    private function loadScoresGrouped($user): void
    {
        $service = new TaskService();

        if ($user->isDirector() || $user->isMailer() || $user->isDeputy()) {
            $this->scoresGrouped = [
                'Научные сотрудники' => $service->scoresList()->map(fn ($s) => [
                    'id' => $s->id, 'name' => $s->name, 'max_score' => $s->max_score,
                ])->toArray(),
                'Специалиста по работе с персоналом' => $service->hrList()->map(fn ($s) => [
                    'id' => $s->id, 'name' => $s->name, 'max_score' => $s->max_score,
                ])->toArray(),
                'Главный бухгалтер' => $service->accountantList()->map(fn ($s) => [
                    'id' => $s->id, 'name' => $s->name, 'max_score' => $s->max_score,
                ])->toArray(),
                'Юристконсульт' => $service->lawyerList()->map(fn ($s) => [
                    'id' => $s->id, 'name' => $s->name, 'max_score' => $s->max_score,
                ])->toArray(),
                'Заведующий хозяйством' => $service->maintainerList()->map(fn ($s) => [
                    'id' => $s->id, 'name' => $s->name, 'max_score' => $s->max_score,
                ])->toArray(),
                'Специалист ИКТ' => $service->ictList()->map(fn ($s) => [
                    'id' => $s->id, 'name' => $s->name, 'max_score' => $s->max_score,
                ])->toArray(),
            ];
        } else {
            $this->scoresGrouped = [
                'Категории' => $service->scoresList()->map(fn ($s) => [
                    'id' => $s->id, 'name' => $s->name, 'max_score' => $s->max_score,
                ])->toArray(),
            ];
        }
    }

    private function loadCreators($user): void
    {
        $creatorsList = collect();

        if ($user->isDirector() || $user->isMailer() || $user->isHead()) {
            $creatorsList->push($user);
        } elseif ($user->isDeputy()) {
            $sectors = Sector::with(['users' => function ($query) {
                $query->where('leave', 0);
            }])->get();

            foreach ($sectors as $sector) {
                foreach ($sector->users->whereIn('role_id', [2, 14, 19]) as $u) {
                    $creatorsList->push($u);
                }
            }
        } elseif ($user->isResearcher()) {
            $head = $user->sector->head();
            if ($head) {
                $creatorsList->push($head);
            }
        }

        $this->creators = $creatorsList->unique('id')->map(fn ($u) => ['id' => $u->id, 'name' => $u->name])->values()->toArray();
    }
}
