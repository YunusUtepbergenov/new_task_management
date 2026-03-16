<?php

namespace App\Livewire\Reports;

use App\Services\TaskService;
use Livewire\Attributes\On;
use Livewire\Component;

class UserSection extends Component
{
    public $users, $userId;

    public function mount(): void
    {
        $this->users = TaskService::cachedSectorsWithUsers()->first()?->users->where('leave', 0) ?? collect();
    }

    #[On('updateUsersList')]
    public function updateUsersList($id): void
    {
        $sector = TaskService::cachedSectorsWithUsers()->find($id);
        $this->users = $sector ? $sector->users->where('leave', 0) : collect();
        $this->userId = null;
        $this->dispatch('updateSectorTasks', id: $sector->id);
    }

    public function updatedUserId(): void
    {
        $this->dispatch('updateUserId', id: $this->userId);
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        return view('livewire.reports.user-section');
    }
}
