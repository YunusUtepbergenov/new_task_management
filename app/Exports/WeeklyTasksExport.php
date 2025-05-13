<?php

namespace App\Exports;

use App\Models\Sector;
use App\Models\Task;
use Maatwebsite\Excel\Concerns\FromCollection;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class WeeklyTasksExport implements FromView
{
    public function view(): View
    {
        $startOfWeek = now()->startOfWeek();
        $endOfWeek = now()->endOfWeek(); 

        // $tasks = Task::with(['user', 'score'])
        //     ->whereBetween('deadline', [$startOfWeek, $endOfWeek])
        //     ->orderBy('deadline')
        //     ->get();
        $sectors = Sector::whereIn('id', [2,3,4,5,6,7,8,9,10,12,13,14,15,16])->get();
        

        return view('exports.weekly_tasks', [
            'sectors' => $sectors
        ]);
    }
}
