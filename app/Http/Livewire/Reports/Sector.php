<?php

namespace App\Http\Livewire\Reports;

use App\Models\Sector as ModelsSector;
use Livewire\Component;

class Sector extends Component
{
    public $sect, $sectors;

    public function mount(){
        $this->sectors = ModelsSector::select(['id', 'name'])->get();
    }

    public function updatedSect(){
        $this->emit('updateUsersList', $this->sect);
    }

    public function render()
    {
        return view('livewire.reports.sector');
    }
}
