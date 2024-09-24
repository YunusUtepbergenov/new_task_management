<?php

namespace App\Exports;

use App\Models\Sector;
use Maatwebsite\Excel\Concerns\FromCollection;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class MediaExport implements FromView
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function view(): View
    {
        $sectors = Sector::with(['users.tasks' => function($query){
            $query->whereIn('score_id', [4,12]);
        }])->get();

        // foreach($sectors as $s){
        //     if($s->id == 8)
        //         dd($s->users);
        // }
        return view('exports.media',[
            'sectors' => $sectors
        ]);
    }
}
