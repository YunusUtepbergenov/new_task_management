<?php

namespace App\Livewire\Documents;

use Livewire\Component;
use Illuminate\Support\Facades\Http;

class DigestWords extends Component
{
    public $blue, $red, $green, $violet;
    public $words, $temp;
    public $color;

    public function mount(){
        $this->words = Http::get('http://192.168.1.161:8888/all')['data'];
        $this->temp = $this->words;
        $this->color = 'blue';
    }
    public function updatedBlue(){
        $this->color = 'blue';
        if(strlen($this->blue) == 0){
            $this->words = Http::get('http://192.168.1.161:8888/all')['data'];
            $this->color = 'blue';
        }else{
            $this->words = Http::get('http://192.168.1.161:8888/search?format=blue&word='.$this->blue)['data'];
        }
    }

    public function updatedGreen(){
        $this->color = 'green';
        if(strlen($this->green) == 0){
            $this->words = Http::get('http://192.168.1.161:8888/all')['data'];
            $this->color = 'blue';
        }else{
            $this->words = Http::get('http://192.168.1.161:8888/search?format=green&word='.$this->green)['data'];
        }
    }

    public function updatedRed(){
        $this->color = 'red';
        if(strlen($this->red) == 0){
            $this->words = Http::get('http://192.168.1.161:8888/all')['data'];
            $this->color = 'blue';
        }else{
            $this->words = Http::get('http://192.168.1.161:8888/search?format=red&word='.$this->red)['data'];
        }
    }

    public function updatedViolet(){
        $this->color = 'violet';
        if(strlen($this->violet) == 0){
            $this->words = Http::get('http://192.168.1.161:8888/all')['data'];
            $this->color = 'blue';
        }else{
            $this->words = Http::get('http://192.168.1.161:8888/search?format=violet&word='.$this->violet)['data'];
        }
    }

    public function saveBlue(){
        $response = Http::asForm()->post('http://192.168.1.161:8888/add', [
            'format' => $this->color,
            'word' => $this->blue,
            'username' => auth()->user()->email
        ]);
        if($response->status() == 204){
            $this->dispatch('existing-word');
        }else{
            $this->words = Http::get('http://192.168.1.161:8888/all')['data'];
            $this->color = 'blue';
            $this->blue = '';
        }        
    }

    public function saveRed(){
        $response = Http::asForm()->post('http://192.168.1.161:8888/add', [
            'format' => $this->color,
            'word' => $this->red,
            'username' => auth()->user()->email
        ]);
        if($response->status() == 204){
            $this->dispatch('existing-word');
        }else{
            $this->words = Http::get('http://192.168.1.161:8888/all')['data'];
            $this->color = 'blue';
            $this->red = '';
        }
    }

    public function saveGreen(){
        $response = Http::asForm()->post('http://192.168.1.161:8888/add', [
            'format' => $this->color,
            'word' => $this->green,
            'username' => auth()->user()->email
        ]);
        if($response->status() == 204){
            $this->dispatch('existing-word');
        }else{
            $this->words = Http::get('http://192.168.1.161:8888/all')['data'];
            $this->color = 'blue';
            $this->green = '';

        }
    }

    public function saveViolet(){
        $response = Http::asForm()->post('http://192.168.1.161:8888/add', [
            'format' => $this->color,
            'word' => $this->violet,
            'username' => auth()->user()->email
        ]);
        if($response->status() == 204){
            $this->dispatch('existing-word');
        }else{
            $this->words = Http::get('http://192.168.1.161:8888/all')['data'];
            $this->color = 'blue';
            $this->violet = '';    
        }
    }

    public function render()
    {
        return view('livewire.documents.digest-words');
    }
}
