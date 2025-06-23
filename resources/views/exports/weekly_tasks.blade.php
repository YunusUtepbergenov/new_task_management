<table>
    @php
        use Carbon\Carbon;

        Carbon::setLocale('ru');
        $startDate = Carbon::parse($start)->translatedFormat('d');
        $endDate = Carbon::parse($end)->translatedFormat('d F');
    @endphp
    <thead>
        <tr>
            <th colspan="4" align="center" height="50" style="font-family: Cambria; color: #0070C0; font-size: 14px">
                <span style="font-weight:bold; text-transform:uppercase;">СПИСОК</span><br>
                поручений, запланированных к выполнению в период с {{ $startDate }} по {{ $endDate }} текущего года.
            </th>
        </tr>
        <tr>
            <th style="text-align: center;font-family:Cambria;font-weight:bold; border:1px solid #000; color:#0070C0;font-size: 14px;">№</th>
            <th style="text-align: center;font-family:Cambria;font-weight:bold; border:1px solid #000; color:#0070C0;font-size: 14px;">Название</th>
            <th style="text-align: center;font-family:Cambria;font-weight:bold; border:1px solid #000; color:#0070C0;font-size: 14px;">Срок</th>
            <th style="text-align: center;font-family:Cambria;font-weight:bold; border:1px solid #000; color:#0070C0;font-size: 14px;">Ответственный</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($tasks as $scoreName => $tasks)
            <tr>
                <td colspan="4" style="font-family:Cambria; text-align:center; font-weight:bold; border:1px solid #000; color:#C00000;font-size: 12px;">
                    {{ preg_replace('/^\d+\.\s*/', '', $scoreName) }}
                </td>
            </tr>
            @php $i = 1; @endphp
            @foreach ($tasks as $task)
                <tr>
                    <td width="5" style="font-family:Cambria; border:1px solid #000;font-size: 12px;">{{ $i++ }}</td>
                    <td width="100" style="border:1px solid #000; font-family:Cambria;font-size: 12px;">{{ $task->name }}</td>
                    <td width="15" style="font-family:Cambria;border:1px solid #000;font-size: 12px;">
                        @if ($task->extended_deadline)
                            {{ \Carbon\Carbon::parse($task->extended_deadline)->format('Y-m-d') }}
                        @else
                            {{ \Carbon\Carbon::parse($task->deadline)->format('Y-m-d') }}
                        @endif    
                    </td>
                    <td width="40" style="font-family:Cambria;border:1px solid #000;font-size: 12px; font-weight:bold">{{ $task->merged_responsibles }}</td>
                </tr>
            @endforeach
        @endforeach
    </tbody>
</table>