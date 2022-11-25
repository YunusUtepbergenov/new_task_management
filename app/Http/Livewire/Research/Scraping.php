<?php

namespace App\Http\Livewire\Research;

use App\Models\Scraper;
use Livewire\Component;

class Scraping extends Component
{

    public $type = 'houses';
    public $results;

    public function mount(){
        $this->results = Scraper::where('category', 'houses')->orderBy('date', 'DESC')->get();
    }

    public function updatedType(){
        $this->results = Scraper::where('category', $this->type)->orderBy('date', 'DESC')->get();
    }

    public function render()
    {
        return view('livewire.research.scraping');
    }
}
