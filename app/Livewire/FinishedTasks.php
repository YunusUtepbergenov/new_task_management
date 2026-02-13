<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\{Task, User};
use Illuminate\Support\Facades\Auth;

class FinishedTasks extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';


    public $search = '';
    public $worker_id = null;
    public $workers = [];

     public function mount()
    {
        $user = Auth::user();

        if ($user->isHead()) {
            $this->workers = User::where('sector_id', $user->sector_id)->get();
        } elseif ($user->isDeputy()) {
            $this->workers = User::all();
        }
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedWorkerId()
    {
        $this->resetPage();
    }

    public function view($task_id): void
    {
        $this->dispatch('taskClicked', id: $task_id);
    }

    public function render()
    {
        $user = Auth::user();

        $query = Task::where('status', 'Выполнено');

        if ($user->isResearcher()) {
            $query->where('user_id', $user->id);
        } elseif ($user->isHead()) {
            $query->whereHas('user', function ($q) use ($user) {
                $q->where('sector_id', $user->sector_id);
            });
            if ($this->worker_id) {
                $query->where('user_id', $this->worker_id);
            }
        } elseif ($user->isDeputy() && $this->worker_id) {
            $query->where('user_id', $this->worker_id);
        }

        if ($this->search) {
            $query->where('name', 'LIKE', '%' . $this->search . '%');
        }

        $tasks = $query->latest()->paginate(10);
        
        return view('livewire.finished-tasks', compact('tasks'));
    }
}
