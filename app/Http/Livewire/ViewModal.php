<?php

namespace App\Http\Livewire;

use App\Events\CommentStoredEvent;
use App\Events\TaskConfirmedEvent;
use App\Events\TaskRejectedEvent;
use App\Events\TaskSubmittedEvent;
use App\Models\Comment;
use App\Models\Response;
use App\Models\Task;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class ViewModal extends Component
{
    use WithFileUploads;
    public $task, $comment, $comments;
    public $description, $upload;

    protected $listeners = ['taskClicked'];

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

    public function updatedUpload(){
        $this->validate([
            'upload' => 'nullable|file|max:5000'
        ]);
    }

    public function storeResponse(){
        $this->validate([
            'description' => 'required|min:3',
            'upload' => 'nullable|file|max:5000'
        ]);

        $response = new Response;
        $response->task_id = $this->task->id;
        $response->user_id = Auth::user()->id;
        $response->description = $this->description;

        if($this->upload){
            $uploadedFile = $this->upload;
            $filename = time().$uploadedFile->getClientOriginalName();

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
        $task = Task::where('id', $id)->first();

        $task->update(['status' => "Выполнено"]);
        $this->task = Task::with(['comments', 'files'])->where('id', $this->task->id)->first();
        event(new TaskConfirmedEvent($task));
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

    public function render()
    {
        return view('livewire.view-modal');
    }
}
