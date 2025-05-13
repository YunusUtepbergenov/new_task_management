<table>
    <thead>
        <tr>
            <th style="font-family:Cambria;text-align:center;font-weight:bold; border:1px solid #000">#</th>
            <th style="font-family:Cambria;text-align:center;font-weight:bold; border:1px solid #000">Название</th>
            <th style="font-family:Cambria;text-align:center;font-weight:bold; border:1px solid #000">Категория</th>
            <th style="font-family:Cambria;text-align:center;font-weight:bold; border:1px solid #000">Крайний срок</th>
            <th style="font-family:Cambria;text-align:center;font-weight:bold; border:1px solid #000">Ответственный</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($sectors as $sector)
        <tr>
            <td colspan="5" style="font-family:Cambria;text-align:center;font-weight:bold; border:1px solid #000">{{$sector->name}}</td>
        </tr>
            @foreach ($sector->weeklyTasks() as $index=>$task)
                <tr>
                    <td width="5" style="font-family:Cambria;border:1px solid #000">{{ $index + 1 }}</td>
                    <td width="100" style="font-family:Cambria;border:1px solid #000">{{ $task->name }}</td>
                    <td width="32" style="font-family:Cambria;border:1px solid #000">{{ $task->score->name ?? '—' }}</td>
                    <td width="15" style="font-family:Cambria;border:1px solid #000">{{ $task->deadline }}</td>
                    <td width="36" style="font-family:Cambria;border:1px solid #000">{{ $task->user->name ?? '—' }}</td>
                </tr>                
            @endforeach
        @endforeach
    </tbody>
</table>
