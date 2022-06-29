<?php

namespace App\Exports;

use App\Models\Task;
use App\Models\Sector;
use Maatwebsite\Excel\Concerns\Exportable;
use App\Exports\Sheets\ReportsPerMonthSheet;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class TasksExport implements FromCollection, WithMultipleSheets
{
    use Exportable;
    /**
    * @return \Illuminate\Support\Collection
    */

    public $start, $end;

    public function __construct($param1, $param2)
    {
        $this->start = $param1;
        $this->end = $param2;
    }

    public function collection()
    {
        return Task::all();
    }

    public function sheets(): array{
        $sheets = [];
        $sectors = Sector::all();
        foreach($sectors as $sector){
            $sheets[] = new ReportsPerMonthSheet($sector->id, $this->start, $this->end);
        }

        return $sheets;
    }
}
