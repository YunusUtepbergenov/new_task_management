<?php

namespace App\Livewire;

use App\Models\User;
use Livewire\Attributes\On;
use Livewire\Component;

class ProfileModal extends Component
{
    public $profile;

    #[On('profileClicked')]
    public function profileClicked($id): void
    {
        $this->profile = User::where('id', $id)->first();
        $this->dispatch('profile-show-modal');
    }

    public function render()
    {
        return view('livewire.profile-modal');
    }
}
