{{-- Plain table for the Excel export, laid out like the "Свод" sheets. Expects $report (rows + total) and $mode ('sector'|'employee'). --}}
@php
    $statusColumns = \App\Services\MailReportService::STATUS_COLUMNS;
    $isEmployee = $mode === 'employee';
    $cells = fn (array $row): array => [
        $row['required'],
        $row['not_due'], ...array_values($row['not_due_statuses']),
        $row['past_due'], ...array_values($row['past_due_statuses']),
        $row['closed'],
    ];
@endphp
<table>
    <thead>
        <tr>
            <th rowspan="2">№</th>
            <th rowspan="2">{{ $isEmployee ? __('mails.report.employee') : __('mails.report.sector') }}</th>
            <th rowspan="2">{{ __('mails.report.required') }}</th>
            <th rowspan="2">{{ __('mails.report.not_due') }}</th>
            <th colspan="{{ count($statusColumns) }}">{{ __('mails.report.of_which') }}</th>
            <th rowspan="2">{{ __('mails.report.past_due') }}</th>
            <th colspan="{{ count($statusColumns) }}">{{ __('mails.report.of_which') }}</th>
            <th rowspan="2">{{ __('mails.report.closed') }}</th>
        </tr>
        <tr>
            @foreach ([1, 2] as $part)
                @foreach ($statusColumns as $status)
                    <th>{{ __('mails.statuses.'.$status) }}</th>
                @endforeach
            @endforeach
        </tr>
    </thead>
    <tbody>
        <tr style="font-weight: bold;">
            <td></td>
            <td>{{ __('mails.report.total') }}</td>
            @foreach ($cells($report['total']) as $value)
                <td>{{ $value }}</td>
            @endforeach
        </tr>
        @foreach ($report['rows'] as $row)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $row['name'] }}{{ ($row['left'] ?? false) ? ' ('.__('mails.fields.left').')' : '' }}</td>
                @foreach ($cells($row) as $value)
                    <td>{{ $value }}</td>
                @endforeach
            </tr>
        @endforeach
    </tbody>
</table>
