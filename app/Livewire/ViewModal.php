<?php

namespace App\Livewire;

use App\Events\CommentStoredEvent;
use App\Events\TaskConfirmedEvent;
use App\Events\TaskRejectedEvent;
use App\Events\TaskSubmittedEvent;
use App\Models\Comment;
use App\Models\Response;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

class ViewModal extends Component
{
    use WithFileUploads;

    public $task, $comment, $comments, $profile;
    public $description, $upload;
    public $phone, $internal;
    public $oldPassword, $newPassword, $confirmPassword;
    public $errorMsg, $taskScore, $coTasks;
    public $groupScores = [];

    private const TASK_EAGER_LOAD = ['user', 'comments', 'files', 'score'];

    public function mount(): void
    {
        $this->errorMsg = null;
        $this->phone = auth()->user()->phone;
        $this->internal = auth()->user()->internal;
    }

    #[On('task-updated')]
    public function onTaskUpdated(): void
    {
        $this->task = null;
        $this->coTasks = [];
    }

    #[On('taskClicked')]
    public function taskClicked($id): void
    {
        $this->reset(['errorMsg', 'taskScore', 'groupScores', 'upload', 'description', 'comment']);
        $this->dispatch('show-modal');
        $this->task = Task::with(self::TASK_EAGER_LOAD)->find($id);

        $this->coTasks = $this->getCoTasks($this->task);

        if ($this->task->status === 'Не прочитано' && Auth::id() === $this->task->user_id) {
            $this->task->update(['status' => 'Выполняется']);
        }

        $this->comments = $this->task->comments()->with('user')->oldest()->get();
    }

    public function getCoTasks($task): array|\Illuminate\Database\Eloquent\Collection
    {
        if ($task?->group_id) {
            return Task::with(['user', 'score'])->where('group_id', $task->group_id)->get();
        }

        return [];
    }

    #[On('profileClicked')]
    public function profileClicked($id): void
    {
        $this->profile = User::find($id);
        $this->dispatch('profile-show-modal');
    }

    public function updatedUpload(): void
    {
        $this->validate([
            'upload' => 'nullable|file|max:500000',
        ]);
    }

    public function storeResponse(): void
    {
        $this->validate([
            'description' => 'required|min:3',
            'upload' => 'nullable|file|max:500000',
        ]);

        $responseData = [
            'task_id' => $this->task->id,
            'user_id' => Auth::id(),
            'description' => $this->description,
        ];

        if ($this->upload) {
            $chars = ['+', ' ', '?', '[', ']', '/', '=', '<', '>', ':', ';', ',', "'", '"', '&', '$', '#', '*', '(', ')', '|', '~', '`', '!', '{', '}', '%'];
            $filename = time() . $this->upload->getClientOriginalName();
            $filename = str_replace($chars, '_', $filename);

            Storage::disk('local')->putFileAs('files/responses/', $this->upload, $filename);
            $responseData['filename'] = $filename;
        }

        $response = Response::create($responseData);
        $this->dispatch('success', msg: 'Задача выполнена. Пожалуйста, дождитесь подтверждения.');

        event(new TaskSubmittedEvent($response->task));

        $this->task->update(['status' => 'Ждет подтверждения']);
        $this->refreshTask();
        $this->description = '';
    }

    public function storeComment($id): void
    {
        $comment = Comment::create([
            'task_id' => $id,
            'user_id' => Auth::id(),
            'comment' => $this->comment,
        ]);

        $this->comments = Comment::with('user')->where('task_id', $id)->oldest()->get();
        $this->comment = '';
        $this->dispatch('comment-added');

        $comment->load('task');
        $recipientId = Auth::id() === $comment->task->creator_id
            ? $comment->task->user_id
            : $comment->task->creator_id;

        event(new CommentStoredEvent($comment, $recipientId));
    }

    public function taskConfirmed($id): void
    {
        $this->errorMsg = null;
        $task = Task::with('score')->findOrFail($id);

        if ($task->group_id) {
            $groupTasks = Task::where('group_id', $task->group_id)
                ->with(['score', 'user'])
                ->get();

            $totalGroupScore = 0;

            foreach ($groupTasks as $groupTask) {
                $scoreValue = $this->groupScores[$groupTask->id] ?? null;

                if (!is_numeric($scoreValue)) {
                    $this->errorMsg = 'Введите балл для пользователя: ' . $groupTask->user->name;
                    return;
                }

                if ($groupTask->score) {
                    if ($scoreValue < $groupTask->score->min_score || $scoreValue > $groupTask->score->max_score) {
                        $this->errorMsg = 'Оценка для ' . $groupTask->user->name . ' должна быть между ' .
                            $groupTask->score->min_score . ' и ' . $groupTask->score->max_score;
                        return;
                    }
                }

                $totalGroupScore += floatval($scoreValue);
            }

            if ($task->score && $totalGroupScore > $task->score->max_score) {
                $this->errorMsg = 'Сумма всех оценок (' . $totalGroupScore . ') превышает максимально допустимое значение (' . $task->score->max_score . ').';
                return;
            }

            foreach ($groupTasks as $groupTask) {
                $groupTask->update([
                    'status' => 'Выполнено',
                    'total' => floatval($this->groupScores[$groupTask->id]),
                ]);
                event(new TaskConfirmedEvent($groupTask));
            }
        } else {
            if ($task->score) {
                if (!isset($this->taskScore) ||
                    floatval($this->taskScore) < $task->score->min_score ||
                    floatval($this->taskScore) > $task->score->max_score) {
                    $this->errorMsg = 'Оценка должна быть между ' .
                        $task->score->min_score . ' и ' . $task->score->max_score;
                    return;
                }
            }

            $task->update([
                'status' => 'Выполнено',
                'total' => $task->score ? floatval($this->taskScore) : null,
            ]);
            event(new TaskConfirmedEvent($task));
        }

        $this->task = Task::with(self::TASK_EAGER_LOAD)->find($id);
        $this->coTasks = $this->getCoTasks($this->task);
    }

    public function taskRejected($id): void
    {
        $task = Task::with('response')->findOrFail($id);

        if ($task->response->filename) {
            Storage::delete('files/responses/' . $task->response->filename);
        }

        $task->response->delete();
        $task->update(['status' => 'Дорабатывается']);
        $this->refreshTask();
        event(new TaskRejectedEvent($task));
    }

    public function deleteComment($id): void
    {
        Comment::findOrFail($id)->delete();
        $this->comments = Comment::with('user')->where('task_id', $this->task->id)->oldest()->get();
    }

    public function reSubmit($id): void
    {
        $task = Task::with('response')->findOrFail($id);

        if ($task->response->filename) {
            Storage::delete('files/responses/' . $task->response->filename);
        }

        $task->response->delete();
        $task->update(['status' => 'Выполняется']);
        $this->refreshTask();
    }

    public function changeUserInfo(): void
    {
        $this->validate([
            'phone' => 'required|min:9',
            'internal' => 'nullable|max:3',
        ]);

        $user = Auth::user();
        $user->phone = $this->phone;
        $user->internal = $this->internal;
        $user->save();

        $this->dispatch('success', msg: 'Информация профиля успешно изменена.');
    }

    public function updatePassword(): void
    {
        $this->validate([
            'oldPassword' => 'required|min:6|max:20',
            'newPassword' => 'required|min:6|max:20',
            'confirmPassword' => 'required|same:newPassword',
        ]);

        if (Hash::check($this->oldPassword, auth()->user()->password)) {
            auth()->user()->update(['password' => bcrypt($this->newPassword)]);
            $this->dispatch('success', msg: 'Пароль успешно изменен');
        } else {
            $this->dispatch('danger', msg: 'Неправильный пароль');
        }
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        return view('livewire.view-modal');
    }

    private function refreshTask(): void
    {
        $this->task = Task::with(self::TASK_EAGER_LOAD)->find($this->task->id);
    }
}
