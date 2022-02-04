<?php

namespace App\Http\Livewire;

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
        // dd($this->task->response);

        if ($this->task->status == "Новое") {
            if (Auth::user()->id == $this->task->user_id) {
                $this->task->update(['status' => "Выполняется"]);
            }
        }
        $this->comments = $this->task->comments;
    }

    public function storeResponse(){
        $this->validate([
            'description' => 'required|min:3',
            'upload' => 'nullable|file|max:40000'
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

        $this->task->update(['status' => "Ждет подтверждения"]);
    }

    public function storeComment($id){
        $comment = Comment::create([
            'task_id' => $id,
            'user_id' => Auth::user()->id,
            'comment' => $this->comment
        ]);

        $this->comments = Comment::with('user')->where('task_id', $id)->latest()->get();

        $this->comment = '';

    }

    public function submitTask($id){
        $this->dispatchBrowserEvent('submit-task');
    }

    public function render()
    {
        return view('livewire.view-modal', [
            'task' => $this->task,
            'comments' => $this->comments
        ]);
    }
}
