<?php

namespace App\Http\Livewire\Users;

use App\Models\Vacation as VacationModel;
use Livewire\Component;

class Vacation extends Component
{
    public $vacations;

    public function render()
    {
        $this->vacations = VacationModel::where('year', date('Y'))->orderBy('month')->get();

        return view('livewire.users.vacation');
    }
}
