<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use App\Models\Task;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;


class TopUsersExport implements FromCollection, WithHeadings, WithMapping
{
    protected $year;

    public function __construct($year)
    {
        $this->year = $year;
    }

    public function collection()
    {
        return Task::query()
            ->selectRaw('user_id, COUNT(*) as reports_count')
            ->where('score_id', 6)
            ->whereYear('created_at', $this->year)
            ->where('status', 'Выполнено')
            ->groupBy('user_id')
            ->orderByDesc('reports_count')
            ->with('user')
            ->get();
    }

    public function headings(): array
    {
        return [
            '№',
            'Ф.И.О',
            'Сони',
            'Йил'
        ];
    }

    public function map($row): array
    {
        static $index = 1;

        return [
            $index++,
            $row->user->name ?? 'Unknown',
            $row->reports_count,
            $this->year
        ];
    }

}
