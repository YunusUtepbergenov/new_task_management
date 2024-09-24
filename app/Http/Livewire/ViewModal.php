<?php

namespace App\Http\Livewire;

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
use Livewire\Component;
use Livewire\WithFileUploads;

class ViewModal extends Component
{
    use WithFileUploads;
    public $task, $comment, $comments, $profile;
    public $description, $upload;
    public $phone, $internal;
    public $oldPassword, $newPassword, $confirmPassword;
    public $errorMsg, $taskScore;
    
    protected $listeners = ['taskClicked', 'profileClicked'];

    public function mount(){
        $this->errorMsg = Null;
        $this->phone = auth()->user()->phone;
        $this->internal = auth()->user()->internal;
    }

    public function taskClicked($id){
        $this->dispatchBrowserEvent('show-modal');
        $this->task = Task::with(['comments', 'files'])->where('id', $id)->first();

        if ($this->task->status == "Новое") {
            if (Auth::user()->id == $this->task->user_id) {
                $this->task->update(['status' => "Выполняется"]);
            }
        }
        $this->comments = $this->task->comments()->latest()->get();
    }

    public function profileClicked($id){
        $this->profile = User::where('id', $id)->first();
        $this->dispatchBrowserEvent('profile-show-modal');
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
        $this->dispatchBrowserEvent('success', ['msg' => "Задача выполнена. Пожалуйста, дождитесь подтверждения."]);

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
        $this->dispatchBrowserEvent('success', ['msg' => "Комментарий успешно отправлен"]);
        $this->comments = Comment::with('user')->where('task_id', $id)->latest()->get();

        $this->comment = '';
        if(Auth::user()->id == $comment->task->creator_id){
            event(new CommentStoredEvent($comment, $comment->task->user_id));
        }else{
            event(new CommentStoredEvent($comment, $comment->task->creator_id));
        }
    }

    public function taskConfirmed($id){
        $this->errorMsg = Null;
        $task = Task::where('id', $id)->first();
        
        if(!isset($task->score)){
            $task->update(['status' => "Выполнено"]);
            $this->task = Task::with(['comments', 'files'])->where('id', $this->task->id)->first();
            event(new TaskConfirmedEvent($task));
        }
        else if(!isset($task->score->max_score)){
            $task->update([
                'status' => "Выполнено",
                'total'  => floatval($this->taskScore)
            ]);
            $this->task = Task::with(['comments', 'files'])->where('id', $this->task->id)->first();
            event(new TaskConfirmedEvent($task));
        }
        else if(!isset($this->taskScore) or floatval($this->taskScore) > $task->score->max_score or floatval($this->taskScore) < $task->score->min_score){
            $this->errorMsg = 'Please enter number between '. $task->score->min_score.'-'.$this->task->score->max_score;
        }
        else{
            $task->update([
                'status' => "Выполнено",
                'total'  => floatval($this->taskScore)
            ]);
            $this->task = Task::with(['comments', 'files'])->where('id', $this->task->id)->first();
            event(new TaskConfirmedEvent($task));    
        }
    }

    public function taskRejected($id){
        $task = Task::where('id', $id)->first();

        if($task->response->filename)
            Storage::delete('files/responses/'.$task->response->filename);

        $task->response->delete();
        $task->update(['status' => "Выполняется"]);
        $this->task = Task::with(['comments', 'files'])->where('id', $this->task->id)->first();
        event(new TaskRejectedEvent($task));
    }

    public function deleteComment($id){
        $comment = Comment::where('id', $id)->first();
        $comment->delete();

        $this->comments = Comment::with('user')->where('task_id', $this->task->id)->latest()->get();
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

        $this->dispatchBrowserEvent('success', ['msg' => "Информация профиля успешно изменена."]);
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
            $this->dispatchBrowserEvent('success', ['msg' => "Пароль успешно изменен"]);
        }else{
            $this->dispatchBrowserEvent('danger', ['msg' => "Неправильный пароль"]);
        }
    }

    public function render(){
        return view('livewire.view-modal');
    }
}
