<html>
    <link rel="stylesheet" href="css/table.css">
    <table>
        <thead>
            <tr>
                <td colspan="8" height="25" style="vertical-align:middle;text-align:center; font-weight:bold; font-family:Cambria;font-size:14px;border:1px solid #000">
                    Выполненные задачи ({{ $start }} — {{ $end }})
                </td>
            </tr>
            <tr>
                <th style="text-align:center;font-family:Cambria;font-weight:bold; border:1px solid #000">№</th>
                <th style="text-align:center;font-family:Cambria;font-weight:bold; border:1px solid #000">Название</th>
                <th style="text-align:center;font-family:Cambria;font-weight:bold; border:1px solid #000">Сектор</th>
                <th style="text-align:center;font-family:Cambria;font-weight:bold; border:1px solid #000">Ответственный</th>
                <th style="text-align:center;font-family:Cambria;font-weight:bold; border:1px solid #000">Крайний срок</th>
                <th style="text-align:center;font-family:Cambria;font-weight:bold; border:1px solid #000">Состояние</th>
                <th style="text-align:center;font-family:Cambria;font-weight:bold; border:1px solid #000">Категория</th>
                <th style="text-align:center;font-family:Cambria;font-weight:bold; border:1px solid #000">Балл</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($tasks as $key => $task)
                <tr>
                    <td style="font-family:Cambria; border:1px solid #000">{{ $key + 1 }}</td>
                    <td width="100" style="border:1px solid #000; font-family:Cambria">{{ $task->name }}</td>
                    <td width="40" style="font-family:Cambria;border:1px solid #000">{{ $task->sector?->name }}</td>
                    <td width="40" style="font-family:Cambria;border:1px solid #000">{{ $task->merged_responsibles }}</td>
                    <td width="20" style="font-family:Cambria;border:1px solid #000">{{ $task->deadline }}</td>
                    <td width="25" style="font-family:Cambria;font-weight:bold;border:1px solid #000">{{ $task->status }}</td>
                    <td width="40" style="font-family:Cambria;border:1px solid #000; font-weight:bold">{{ isset($task->score) ? substr($task->score->name, strpos($task->score->name, '.') + 1) : '' }}</td>
                    <td width="10" style="text-align:center;font-family:Cambria;border:1px solid #000; font-weight:bold">{{ isset($task->total) && isset($task->score) ? $task->total.'/'.$task->score->max_score : '' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</html>
