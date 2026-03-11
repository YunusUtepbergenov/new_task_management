<table>
    @php
        use Carbon\Carbon;

        $months = [
            1 => 'январь', 2 => 'февраль', 3 => 'март', 4 => 'апрель',
            5 => 'май', 6 => 'июнь', 7 => 'июль', 8 => 'август',
            9 => 'сентябрь', 10 => 'октябрь', 11 => 'ноябрь', 12 => 'декабрь',
        ];

        $endCarbon  = Carbon::parse($end);
        $startDate  = Carbon::parse($start)->format('j');
        $endDate    = $endCarbon->format('j') . ' ' . $months[(int) $endCarbon->format('n')];
    @endphp
    <thead>
        <tr>
            <th colspan="4" align="center" height="50" style="font-family: Cambria; color: #0070C0; font-weight:bold; font-size: 14px">
                Иқтисодий тадқиқотлар ва ислоҳотлар маркази томонидан жорий йилнинг {{ $startDate }}-{{ $endDate }} кунлари тайёрланадиган таҳлилий материаллар РЎЙХАТИ
            </th>
        </tr>
        <tr>
            <th style="text-align: center;font-family:Cambria;font-weight:bold; border:1px solid #000; color:#000;font-size: 14px;">№</th>
            <th style="text-align: center;font-family:Cambria;font-weight:bold; border:1px solid #000; color:#000;font-size: 14px;">Амалга ошириладиган иш номи</th>
            <th style="text-align: center;font-family:Cambria;font-weight:bold; border:1px solid #000; color:#000;font-size: 14px;">Формати</th>
            <th style="text-align: center;font-family:Cambria;font-weight:bold; border:1px solid #000; color:#000;font-size: 14px;">Маъсул ижрочилар</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($tasks as $scoreName => $tasks)
            @php $i = 1; @endphp
            @foreach ($tasks as $task)
                <tr>
                    <td width="5" style="font-family:Calibri; border:1px solid #000;font-size: 12px;">{{ $i++ }}</td>
                    <td width="100" style="border:1px solid #000; font-family:Cambria;font-size: 12px;">{{ $task->name }}</td>
                    <td width="50" style="font-family:Cambria;border:1px solid #000;font-size: 12px;">{{ $task->score->name }}</td>
                    <td width="50" style="font-family:Cambria;border:1px solid #000;font-size: 12px;">{{ $task->merged_responsibles }}</td>
                </tr>
            @endforeach
        @endforeach
    </tbody>
</table>