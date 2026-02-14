<?php

namespace App\Livewire\Documents;

use App\Models\Digest;
use Livewire\Component;
use Livewire\WithPagination;


class DigestFilter extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $users, $search='';

    public function updatingSearch(){
        $this->resetPage();
    }

    public function render()
    {
        return view('livewire.documents.digest-filter', [
            'digests' => Digest::whereHas('user', function($query){
                $query->where('name', 'like', '%'.$this->search.'%');
            })->orWhereHas('sector', function($query){
                $query->where('name', 'like', '%'.$this->search.'%');
            })->orWhere('name', 'like', '%'.$this->search.'%')
            ->orderBy('created_at', 'DESC')->paginate(10),
        ]);
    }
}
