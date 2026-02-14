<?php

namespace App\Livewire\Documents;

use Livewire\Component;
use Illuminate\Support\Facades\Http;

class DigestWords extends Component
{
    public $blue, $red, $green, $violet;
    public $words;
    public $color = 'blue';

    public function mount(): void
    {
        $this->words = Http::get($this->baseUrl('/all'))['data'];
    }

    public function updatedBlue(): void
    {
        $this->searchWord('blue', $this->blue);
    }

    public function updatedGreen(): void
    {
        $this->searchWord('green', $this->green);
    }

    public function updatedRed(): void
    {
        $this->searchWord('red', $this->red);
    }

    public function updatedViolet(): void
    {
        $this->searchWord('violet', $this->violet);
    }

    public function saveBlue(): void
    {
        $this->saveWord('blue', $this->blue);
        $this->blue = '';
    }

    public function saveRed(): void
    {
        $this->saveWord('red', $this->red);
        $this->red = '';
    }

    public function saveGreen(): void
    {
        $this->saveWord('green', $this->green);
        $this->green = '';
    }

    public function saveViolet(): void
    {
        $this->saveWord('violet', $this->violet);
        $this->violet = '';
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        return view('livewire.documents.digest-words');
    }

    private function searchWord(string $color, ?string $word): void
    {
        $this->color = $color;

        if (empty($word)) {
            $this->words = Http::get($this->baseUrl('/all'))['data'];
            $this->color = 'blue';
        } else {
            $this->words = Http::get($this->baseUrl("/search?format={$color}&word={$word}"))['data'];
        }
    }

    private function saveWord(string $color, ?string $word): void
    {
        $response = Http::asForm()->post($this->baseUrl('/add'), [
            'format' => $this->color,
            'word' => $word,
            'username' => auth()->user()->email,
        ]);

        if ($response->status() == 204) {
            $this->dispatch('existing-word');
        } else {
            $this->words = Http::get($this->baseUrl('/all'))['data'];
            $this->color = 'blue';
        }
    }

    private function baseUrl(string $path): string
    {
        return config('services.digest.url', 'http://192.168.1.161:8888') . $path;
    }
}
