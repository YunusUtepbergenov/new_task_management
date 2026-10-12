<?php

namespace App\Exports\Sheets;

use App\Services\MailReportService;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * One "Свод" sheet (by sector or by employee), laid out and coloured like the report page:
 * a title, a two-row header with the "not due" and "past due" groups, the totals row, then
 * one row per sector or employee.
 */
class MailReportSheet implements FromView, WithEvents, WithTitle
{
    /**
     * Column letters of the table: №, name, total, then the two groups and the closed count.
     */
    private const NUMBER = 'A';

    private const NAME = 'B';

    private const REQUIRED = 'C';

    private const NOT_DUE_FROM = 'D';

    private const NOT_DUE_TO = 'H';

    private const PAST_DUE_FROM = 'I';

    private const PAST_DUE_TO = 'M';

    private const CLOSED = 'N';

    private const TITLE_ROWS = 2;

    private const HEADER_ROWS = 2;

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

    /**
     * @return array<class-string, callable>
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => fn (AfterSheet $event) => $this->style($event->sheet->getDelegate()),
        ];
    }

    private function style(Worksheet $sheet): void
    {
        $last = self::CLOSED;
        $headerFrom = self::TITLE_ROWS + 1;
        $headerTo = self::TITLE_ROWS + self::HEADER_ROWS;
        $totalRow = $headerTo + 1;
        $lastRow = $sheet->getHighestRow() + self::TITLE_ROWS;

        // Title and date above the table.
        $sheet->insertNewRowBefore(1, self::TITLE_ROWS);
        $sheet->setCellValue('A1', __('mails.report.export_title'));
        $sheet->setCellValue('A2', __('mails.report.subtitle', ['date' => today()->format('d.m.Y')]).' · '.$this->title());
        $sheet->mergeCells("A1:{$last}1");
        $sheet->mergeCells("A2:{$last}2");
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A2')->getFont()->setItalic(true)->setSize(10)->getColor()->setRGB('64748B');
        $sheet->getStyle('A1:A2')->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getRowDimension(1)->setRowHeight(26);
        $sheet->getRowDimension(2)->setRowHeight(18);

        // Header: bold, centred, wrapped, with the two groups tinted like the report page.
        $sheet->getStyle("A{$headerFrom}:{$last}{$headerTo}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 10],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F1F5F9']],
        ]);
        $sheet->getStyle(self::NOT_DUE_FROM."{$headerFrom}:".self::NOT_DUE_TO."{$headerTo}")->getFill()
            ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('EFF6FF');
        $sheet->getStyle(self::PAST_DUE_FROM."{$headerFrom}:".self::PAST_DUE_TO."{$headerTo}")->applyFromArray([
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FEF2F2']],
            'font' => ['color' => ['rgb' => 'B91C1C']],
        ]);
        $sheet->getRowDimension($headerFrom)->setRowHeight(32);
        $sheet->getRowDimension($headerTo)->setRowHeight(44);

        // Totals row.
        $sheet->getStyle("A{$totalRow}:{$last}{$totalRow}")->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E0F2FE']],
        ]);

        // Body: borders, alignment and number formats.
        $sheet->getStyle("A{$headerFrom}:{$last}{$lastRow}")->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('CBD5E1');
        $sheet->getStyle("A{$totalRow}:{$last}{$lastRow}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle(self::NUMBER."{$totalRow}:".self::NUMBER."{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle(self::NAME."{$totalRow}:".self::NAME."{$lastRow}")->getAlignment()->setWrapText(true);
        $sheet->getStyle(self::REQUIRED."{$totalRow}:{$last}{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        // Zeros read as a dash, as on the report page; the cells stay numeric for sums.
        $sheet->getStyle(self::REQUIRED."{$totalRow}:{$last}{$lastRow}")->getNumberFormat()->setFormatCode('0;-0;"–"');
        $sheet->getStyle(self::PAST_DUE_FROM."{$totalRow}:".self::PAST_DUE_FROM."{$lastRow}")->getFont()->setBold(true)->getColor()->setRGB('B91C1C');
        $sheet->getStyle(self::CLOSED."{$totalRow}:".self::CLOSED."{$lastRow}")->getFont()->setBold(true)->getColor()->setRGB('047857');

        foreach (['A' => 6, 'B' => 46, 'C' => 13, 'D' => 12, 'E' => 13, 'F' => 16, 'G' => 13, 'H' => 12, 'I' => 12, 'J' => 13, 'K' => 16, 'L' => 13, 'M' => 12, 'N' => 14] as $column => $width) {
            $sheet->getColumnDimension($column)->setWidth($width);
        }

        // Keep the titles, header and totals in view while scrolling, and repeat the header when printing.
        $sheet->freezePane(self::REQUIRED.($totalRow + 1));
        $sheet->getPageSetup()
            ->setOrientation(PageSetup::ORIENTATION_LANDSCAPE)
            ->setFitToWidth(1)
            ->setFitToHeight(0)
            ->setRowsToRepeatAtTop([$headerFrom, $totalRow]);
        $sheet->getPageMargins()->setLeft(0.4)->setRight(0.4)->setTop(0.5)->setBottom(0.5);
    }
}
