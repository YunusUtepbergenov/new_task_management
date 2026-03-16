<?php

namespace App\Traits;

trait HasTaskView
{
    public function view(int $taskId): void
    {
        $this->dispatch('taskClicked', id: $taskId);
    }
}
