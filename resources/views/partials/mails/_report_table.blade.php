{{-- Plain table for the Excel export. Expects $report (rows + total) and $mode ('sector'|'employee'). --}}
@php $pastDueStatuses = \App\Models\MailDeadline::STATUSES; @endphp
<table>
    <thead>
        <tr>
            <th rowspan="2">№</th>
            <th rowspan="2">{{ $mode === 'sector' ? __('mails.report.sector') : __('mails.report.employee') }}</th>
            <th rowspan="2">{{ $mode === 'sector' ? __('mails.fields.executors') : __('mails.report.main_items') }}</th>
            <th rowspan="2">{{ __('mails.report.items') }}</th>
            <th rowspan="2">{{ __('mails.report.deadlines') }}</th>
            <th rowspan="2">{{ __('mails.report.overdue') }}</th>
            <th colspan="{{ count($pastDueStatuses) }}">{{ __('mails.report.overdue_status') }}</th>
            <th rowspan="2">{{ __('mails.report.multi') }}</th>
        </tr>
        <tr>
            @foreach ($pastDueStatuses as $status)
                <th>{{ __('mails.statuses.'.$status) }}</th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        <tr style="font-weight: bold;">
            <td></td>
            <td>{{ __('mails.report.total') }}</td>
            <td>{{ $mode === 'sector' ? $report['total']['employees'] : collect($report['rows'])->sum('main_items') }}</td>
            <td>{{ $report['total']['items'] }}</td>
            <td>{{ $report['total']['deadlines'] }}</td>
            <td>{{ $report['total']['past_due'] }}</td>
            @foreach ($pastDueStatuses as $status)
                <td>{{ $report['total']['past_due_by_status'][$status] }}</td>
            @endforeach
            <td>{{ $report['total']['multi'] }}</td>
        </tr>
        @foreach ($report['rows'] as $row)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $row['name'] }}{{ ($row['left'] ?? false) ? ' ('.__('mails.fields.left').')' : '' }}</td>
                <td>{{ $mode === 'sector' ? $row['employees'] : $row['main_items'] }}</td>
                <td>{{ $row['items'] }}</td>
                <td>{{ $row['deadlines'] }}</td>
                <td>{{ $row['past_due'] }}</td>
                @foreach ($pastDueStatuses as $status)
                    <td>{{ $row['past_due_by_status'][$status] }}</td>
                @endforeach
                <td>{{ $row['multi'] }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
