<table>
    <thead>
        <tr>
            <th colspan="4" align="center"><strong>Еженедельные задачи</strong></th>
        </tr>
        <tr>
            <th style="text-align: center;font-family:Cambria;font-weight:bold; border:1px solid #000">№</th>
            <th style="text-align: center;font-family:Cambria;font-weight:bold; border:1px solid #000">Название</th>
            <th style="text-align: center;font-family:Cambria;font-weight:bold; border:1px solid #000">Срок</th>
            <th style="text-align: center;font-family:Cambria;font-weight:bold; border:1px solid #000">Ответственный</th>
        </tr>
    </thead>
    <tbody>
        @php $i = 1; @endphp
        @foreach ($sectors as $sector)
        <tr>
            <td colspan="4" style="font-family:Cambria;text-align:center;font-weight:bold; border:1px solid #000">
                {{$sector->name}}
            </td>
        </tr>
            @foreach ($sector->tasks as $task)
                <tr>
                    <td width="5" style="font-family:Cambria; border:1px solid #000">{{ $i++ }}</td>
                    <td width="100" style="border:1px solid #000; font-family:Cambria">{{ $task->name }}</td>
                    <td width="15" style="font-family:Cambria;border:1px solid #000">{{ \Carbon\Carbon::parse($task->deadline)->format('d.m.Y') }}</td>
                    <td width="40" style="font-family:Cambria;border:1px solid #000; font-weight:bold">{{ $task->employee_name() }}</td>
                </tr>
            @endforeach
        @endforeach
    </tbody>
</table>