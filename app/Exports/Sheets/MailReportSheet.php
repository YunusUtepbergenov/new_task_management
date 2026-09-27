<?php

namespace App\Exports\Sheets;

use App\Services\MailReportService;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;

class MailReportSheet implements FromView, WithTitle
{
    /**
     * @param  'sector'|'employee'  $mode
     */
    public function __construct(private string $mode)
    {
    }

    public function view(): View
    {
        $reports = app(MailReportService::class);

        return view('partials.mails._report_table', [
            'report' => $this->mode === 'sector' ? $reports->bySector() : $reports->byEmployee(),
            'mode' => $this->mode,
        ]);
    }

    public function title(): string
    {
        return $this->mode === 'sector' ? __('mails.report.by_sector') : __('mails.report.by_employee');
    }
}
