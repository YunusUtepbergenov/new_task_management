<?php

namespace App\Exports;

use App\Exports\Sheets\MailReportSheet;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class MailReportExport implements WithMultipleSheets
{
    use Exportable;

    /**
     * @return list<MailReportSheet>
     */
    public function sheets(): array
    {
        return [
            new MailReportSheet('sector'),
            new MailReportSheet('employee'),
        ];
    }
}
