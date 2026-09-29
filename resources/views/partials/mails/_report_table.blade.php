{{-- Plain table for the Excel export, same columns as the report page. Expects $report (rows + total) and $mode ('sector'|'employee'). --}}
@php
    $statusColumns = \App\Services\MailReportService::STATUS_COLUMNS;
    $isEmployee = $mode === 'employee';
@endphp
<table>
    <thead>
        <tr>
            <th rowspan="2">№</th>
            <th rowspan="2">{{ $isEmployee ? __('mails.report.employee') : __('mails.report.sector') }}</th>
            <th rowspan="2">{{ __('mails.report.documents') }}</th>
            @unless ($isEmployee)
                <th rowspan="2">{{ __('mails.report.executors') }}</th>
            @endunless
            <th rowspan="2">{{ __('mails.report.required') }}</th>
            <th rowspan="2">{{ __('mails.report.past_due') }}</th>
            <th colspan="{{ count($statusColumns) }}">{{ __('mails.report.group_status') }}</th>
            <th rowspan="2">{{ __('mails.report.multi') }}</th>
        </tr>
        <tr>
            @foreach ($statusColumns as $status)
                <th>{{ __('mails.statuses.'.$status) }}</th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        <tr style="font-weight: bold;">
            <td></td>
            <td>{{ __('mails.report.total') }}</td>
            <td>{{ $report['total']['documents'] }}</td>
            @unless ($isEmployee)
                <td>{{ $report['total']['executors'] }}</td>
            @endunless
            <td>{{ $report['total']['required'] }}</td>
            <td>{{ $report['total']['past_due'] }}</td>
            @foreach ($statusColumns as $status)
                <td>{{ $report['total']['statuses'][$status] }}</td>
            @endforeach
            <td>{{ $report['total']['multi'] }}</td>
        </tr>
        @foreach ($report['rows'] as $row)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $row['name'] }}{{ ($row['left'] ?? false) ? ' ('.__('mails.fields.left').')' : '' }}</td>
                <td>{{ $row['documents'] }}</td>
                @unless ($isEmployee)
                    <td>{{ $row['executors'] }}</td>
                @endunless
                <td>{{ $row['required'] }}</td>
                <td>{{ $row['past_due'] }}</td>
                @foreach ($statusColumns as $status)
                    <td>{{ $row['statuses'][$status] }}</td>
                @endforeach
                <td>{{ $row['multi'] }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
