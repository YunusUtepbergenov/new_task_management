<?php

namespace App\Livewire\Reports;

use App\Models\Sector;
use Livewire\Attributes\On;
use Livewire\Component;

class UserSection extends Component
{
    public $users, $userId;

    public function mount(): void
    {
        $this->users = Sector::first()->users->where('leave', 0);
    }

    #[On('updateUsersList')]
    public function updateUsersList($id): void
    {
        $sector = Sector::with('users')->find($id);
        $this->users = $sector->users->where('leave', 0);
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
