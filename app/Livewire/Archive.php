<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\{Task, User, Scores};
use Illuminate\Support\Facades\Auth;

class Archive extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $month = '';
    public $score_id = null;
    public $user_id = null;

    public array $workers = [];
    public array $scoreTypes = [];

    public function mount(): void
    {
        $user = Auth::user();

        if ($user->isHead()) {
            $this->workers = User::where('sector_id', $user->sector_id)
                ->where('leave', 0)
                ->get(['id', 'name'])
                ->toArray();
        } elseif ($user->isDeputy()) {
            $this->workers = User::where('leave', 0)
                ->get(['id', 'name'])
                ->toArray();
        }

        $this->scoreTypes = Scores::all(['id', 'name'])->toArray();
    }

    public function updatedMonth(): void
    {
        $this->resetPage();
    }

    public function updatedScoreId(): void
    {
        $this->resetPage();
    }

    public function updatedUserId(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->month = '';
        $this->score_id = null;
        $this->user_id = null;
        $this->resetPage();
    }

    public function view(int $task_id): void
    {
        $this->dispatch('taskClicked', id: $task_id);
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        $user = Auth::user();

        $query = Task::with(['user:id,name', 'score:id,name,max_score'])
            ->where('status', 'Выполнено');

        if ($this->month) {
            $start = $this->month . '-01';
            $end = date('Y-m-t', strtotime($start));
            $query->whereBetween('deadline', [$start, $end]);
        }

        if ($user->isHead()) {
            $query->where('sector_id', $user->sector_id);
        }

        if ($this->user_id) {
            $query->where('user_id', $this->user_id);
        }

        if ($this->score_id) {
            $query->where('score_id', $this->score_id);
        }

        $tasks = $query->latest()->paginate(15);

        return view('livewire.archive', compact('tasks'));
    }
}
