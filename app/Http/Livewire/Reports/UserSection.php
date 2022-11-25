<?php

namespace App\Http\Livewire\Reports;

use App\Models\Sector;
use Livewire\Component;

class UserSection extends Component
{
    public $users, $userId;

    protected $listeners = ['updateUsersList'];

    public function mount(){
        $this->users = Sector::first()->users->where('leave', 0);
    }

    public function updateUsersList($id){
        $sector = Sector::with('users')->where('id', $id)->first();
        $this->users = $sector->users->where('leave', 0);
        $this->userId = Null;
        $this->emit('updateSectorTasks', $sector->id);
    }

    public function updatedUserId(){
        $this->emit('updateUserId', $this->userId);
    }

    public function render()
    {
        return view('livewire.reports.user-section');
    }
}
