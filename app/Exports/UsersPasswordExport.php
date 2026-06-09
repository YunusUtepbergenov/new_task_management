<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class UsersPasswordExport implements FromCollection, WithHeadings, WithMapping
{
    /**
     * @param  Collection<int, array{name: string, email: ?string, password: string}>  $rows
     */
    public function __construct(protected Collection $rows)
    {
    }

    public function collection(): Collection
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return ['№', 'Ф.И.О', 'Email', 'Новый пароль'];
    }

    /**
     * @param  array{name: string, email: ?string, password: string}  $row
     */
    public function map($row): array
    {
        static $index = 1;

        return [
            $index++,
            $row['name'],
            $row['email'],
            $row['password'],
        ];
    }
}
