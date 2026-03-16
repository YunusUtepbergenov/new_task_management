<?php

namespace App\Traits;

use App\Models\Task;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

trait HasTaskDeletion
{
    public function deleteTask(int $taskId): void
    {
        $task = Task::where('id', $taskId)->where('creator_id', Auth::id())->first();

        if (!$task) {
            return;
        }

        $tasksToDelete = $task->group_id
            ? Task::where('group_id', $task->group_id)->get()
            : collect([$task]);

        foreach ($tasksToDelete as $t) {
            if ($t->response?->filename) {
                Storage::delete('files/responses/' . $t->response->filename);
            }
            $t->response?->delete();

            foreach ($t->files ?? [] as $file) {
                Storage::delete('files/' . $file->name);
                $file->delete();
            }

            $t->repeat?->delete();
            $t->delete();
        }

        $this->dispatch('toastr:success', message: 'Задача удалена.');
    }
}
