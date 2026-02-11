<?php

namespace App\Livewire;

use App\Models\User;
use Livewire\Component;

class ProfileModal extends Component
{
    protected $listeners = ['profileClicked'];

    public $profile;

    public function profileClicked($id){
        $this->profile = User::where('id', $id)->first();
        $this->dispatchBrowserEvent('profile-show-modal');
    }

    public function render()
    {
        return view('livewire.profile-modal');
    }
}
