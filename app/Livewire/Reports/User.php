<?php

namespace App\Livewire\Reports;

use App\Models\Task;
use App\Models\User as ModelsUser;
use Livewire\Component;

class User extends Component
{
    public $userId, $start, $end, $user, $tasks;
    public $editedScores = [];

    public function saveScore($taskId)
    {
        $task = Task::with('score')->find($taskId);

        if (!$task || !$task->score) {
            return;
        }

        $score = isset($this->editedScores[$taskId]) ? (int) $this->editedScores[$taskId] : null;
        $max = $task->score->max_score;

        if (is_null($score)) {
            $this->addError("editedScores.$taskId", "Score is required.");
            return;
        }

        if ($score > $max) {
            $this->addError("editedScores.$taskId", "Score cannot exceed maximum: $max");
            return;
        }

        $task->total = $score;
        $task->save();

        $this->reset('editedScores');

        session()->flash('success', "Score for task '{$task->name}' changed successfully.");
    }

    public function render()
    {
        $this->user = ModelsUser::where('id', $this->userId)->first();
        $this->tasks = Task::where('user_id', $this->userId)->whereBetween('deadline', [$this->start, $this->end])->orderBy('score_id')->get();

        return view('livewire.reports.user');
    }

    public function view($task_id){
        $this->dispatch('taskClicked', $task_id);
    }
}
