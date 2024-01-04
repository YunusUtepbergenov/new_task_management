<?php

namespace App\Exports\Sheets;

use App\Models\Task;
use App\Models\Sector;
use Maatwebsite\Excel\Concerns\FromQuery;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnWidths;

class ReportsPerMonthSheet implements WithTitle,FromView
{
    private $sector_id, $start, $end;
    public $executers;
    // FromQuery, WithTitle, WithHeadings, WithMapping, WithColumnWidths, WithStyles, 
    public function __construct($sector_id, $start, $end){
        $this->sector_id = $sector_id;
        $this->start = $start;
        $this->end = $end;
    }

    public function view(): View
    {
        $tasks = Task::query()->with('type')->where('sector_id', $this->sector_id)->whereBetween('deadline', [$this->start, $this->end])->orderBy('user_id')->orderBy('deadline');
        return view('exports.sectors', [
            'tasks' => $tasks,
            'sector'=> Sector::where('id', $this->sector_id)->first(),
            'start_date' => $this->start,
            'end_date' => $this->end
        ]);
    }

    // public function query(){
    //     return Task::query()->where('sector_id', $this->sector_id)->whereBetween('deadline', [$this->start, $this->end]);
    // }

    // public function map($task): array
    // {
    //     $sector = Sector::where('id', $this->sector_id)->first();
    //     if($task->executers){
    //         $executers = '';
    //         foreach($task->executers as $exec){
    //             $executers = $executers.$exec->name.', ';
    //         }
    //         $this->executers = $executers;
    //     }
    //     if($task->overdue == 1){
    //         return [
    //             $task->name,
    //             $task->description,
    //             'Просроченный',
    //             $task->deadline,
    //             $task->user->name,
    //             $this->executers
    //         ];
    //     }else{
    //         return [
    //             $task->name,
    //             $task->description,
    //             $task->status,
    //             $task->deadline,
    //             $task->user->name,
    //             $this->executers
    //         ];
    //     }
    // }

    public function title(): string
    {
        $sector = Sector::where('id', $this->sector_id)->first();
        return $sector->name;
    }

    // public function headings(): array
    // {
    //     return [
    //         'Название',
    //         'Поручение / Комментария',
    //         'Состояние',
    //         'Крайний срок',
    //         'Ответственный',
    //         'Соисполнители'
    //     ];
    // }

    // public function columnWidths(): array
    // {
    //     return [
    //         'A' => 50,
    //         'B' => 50,
    //         'C' => 20,
    //         'D' => 15,
    //         'E' => 25,
    //         'F' => 30
    //     ];
    // }

    // public function styles(Worksheet $sheet)
    // {
    //     return [
    //         // Style the first row as bold text.
    //         1    => ['font' => ['bold' => true]],
    //     ];
    // }
}
