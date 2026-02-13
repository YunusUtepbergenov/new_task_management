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

    public function mount(){
        $this->errorMsg = Null;
        $this->phone = auth()->user()->phone;
        $this->internal = auth()->user()->internal;
    }

    #[On('taskClicked')]
    public function taskClicked($id): void
    {
        $this->reset(['errorMsg', 'taskScore', 'groupScores', 'upload', 'description', 'comment']);
        $this->dispatch('show-modal');
        $this->task = Task::with(['user','comments', 'files'])->where('id', $id)->first();

        $this->coTasks = $this->getCoTasks($this->task);

        if ($this->task->status == "Не прочитано") {
            if (Auth::user()->id == $this->task->user_id) {
                $this->task->update(['status' => "Выполняется"]);
            }
        }
        $this->comments = $this->task->comments()->with('user')->oldest()->get();
    }

    public function getCoTasks($task){
        if(isset($task->group_id)){
            return Task::with(['user', 'score'])->where('group_id', $task->group_id)->get();
        }else{
            return [];
        }
    }

    #[On('profileClicked')]
    public function profileClicked($id): void
    {
        $this->profile = User::where('id', $id)->first();
        $this->dispatch('profile-show-modal');
    }

    public function updatedUpload(){
        $this->validate([
            'upload' => 'nullable|file|max:500000'
        ]);
    }

    public function storeResponse(){
        $this->validate([
            'description' => 'required|min:3',
            'upload' => 'nullable|file|max:500000'
        ]);

        $response = new Response;
        $response->task_id = $this->task->id;
        $response->user_id = Auth::user()->id;
        $response->description = $this->description;

        if($this->upload){
            $uploadedFile = $this->upload;
            $chars = array("+", " ", "?", "[", "]", "/", "=", "<", ">", ":", ";", ",", "'", "\"", "&", "$", "#", "*", "(", ")", "|", "~", "`", "!", "{", "}", "%");
            $filename = time().$uploadedFile->getClientOriginalName();
            $filename = str_replace($chars, "_", $filename);

            Storage::disk('local')->putFileAs(
                'files/responses/',
                $uploadedFile,
                $filename
            );
            $response->filename = $filename;
        }

        $response->save();
        $this->dispatch('success', msg: "Задача выполнена. Пожалуйста, дождитесь подтверждения.");

        event(new TaskSubmittedEvent($response->task));

        $this->task->update(['status' => "Ждет подтверждения"]);
        $this->task = Task::with(['comments', 'files'])->where('id', $this->task->id)->first();
        $this->description  = '';
    }

    public function storeComment($id){
        $comment = Comment::create([
            'task_id' => $id,
            'user_id' => Auth::user()->id,
            'comment' => $this->comment
        ]);
        $this->comments = Comment::with('user')->where('task_id', $id)->oldest()->get();

        $this->comment = '';
        $this->dispatch('comment-added');
        if(Auth::user()->id == $comment->task->creator_id){
            event(new CommentStoredEvent($comment, $comment->task->user_id));
        }else{
            event(new CommentStoredEvent($comment, $comment->task->creator_id));
        }
    }

    public function taskConfirmed($id)
    {
        $this->errorMsg = null;
        $task = Task::with('score')->findOrFail($id);

        // Check if this is a grouped task
        if ($task->group_id) {
            $groupTasks = Task::where('group_id', $task->group_id)
                            ->with(['score', 'user'])
                            ->get();

            $totalGroupScore = 0;

            // Validate each user's score
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

            // Check if total exceeds the overall max_score
            if ($task->score && $totalGroupScore > $task->score->max_score) {
                $this->errorMsg = 'Сумма всех оценок (' . $totalGroupScore . ') превышает максимально допустимое значение (' . $task->score->max_score . ').';
                return;
            }

            // All good: Update all grouped tasks
            foreach ($groupTasks as $groupTask) {
                $groupTask->update([
                    'status' => 'Выполнено',
                    'total' => floatval($this->groupScores[$groupTask->id])
                ]);

                event(new TaskConfirmedEvent($groupTask));
            }

        } else {
            // Single task logic
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

        // Refresh modal task data
        $this->task = Task::with(['user', 'comments', 'files', 'score'])->find($id);
        $this->coTasks = $this->getCoTasks($this->task);
    }

    public function taskRejected($id){
        $task = Task::where('id', $id)->first();

        if($task->response->filename)
            Storage::delete('files/responses/'.$task->response->filename);

        $task->response->delete();
        $task->update(['status' => "Дорабатывается"]);
        $this->task = Task::with(['comments', 'files'])->where('id', $this->task->id)->first();
        event(new TaskRejectedEvent($task));
    }

    public function deleteComment($id){
        $comment = Comment::where('id', $id)->first();
        $comment->delete();

        $this->comments = Comment::with('user')->where('task_id', $this->task->id)->oldest()->get();
    }

    public function reSubmit($id){
        $task = Task::where('id', $id)->first();

        if($task->response->filename)
            Storage::delete('files/responses/'.$task->response->filename);

        $task->response->delete();
        $task->update(['status' => "Выполняется"]);
        $this->task = Task::with(['comments', 'files'])->where('id', $this->task->id)->first();
    }

    //Profile Information Modal

    public function changeUserInfo(){
        $this->validate([
            'phone' => 'required|min:9',
            'internal' => 'nullable|max:3'
        ]);

        $user = Auth::user();
        $user->phone = $this->phone;
        $user->internal = $this->internal;
        $user->save();

        $this->dispatch('success', msg: "Информация профиля успешно изменена.");
    }

    public function updatePassword(){
        $this->validate([
            'oldPassword' => 'required|min:6|max:20',
            'newPassword' => 'required|min:6|max:20',
            'confirmPassword' => 'required|same:newPassword'
        ]);

        if(Hash::check($this->oldPassword, auth()->user()->password)){
            auth()->user()->update([
                'password' => bcrypt($this->newPassword)
            ]);
            $this->dispatch('success', msg: "Пароль успешно изменен");
        }else{
            $this->dispatch('danger', msg: "Неправильный пароль");
        }
    }

    public function render(){
        return view('livewire.view-modal');
    }
}
