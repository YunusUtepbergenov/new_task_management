<?php

namespace App\Livewire\Research;

use App\Models\Scraper;
use Livewire\Component;

class Survey extends Component
{

    public $results;

    public function mount(){
        $this->results = Scraper::where('category', 'corruption')->orderBy('date', 'DESC')->get();
    }

    public function render()
    {
        return view('livewire.research.survey');
    }
}
