<html>
    <link rel="stylesheet" href="css/table.css">
    <table>
        <thead>
            <tr>
                <td colspan="5" height="25" style="vertical-align:middle;text-align: center; font-weight:bold; font-family:Cambria;font-size:14px;border:1px solid #000">
                    {{$sector->name}}
                </td>
            </tr>
            <tr>
                <th style="text-align: center;font-family:Cambria;font-weight:bold; border:1px solid #000">№</th>
                <th style="text-align: center;font-family:Cambria;font-weight:bold; border:1px solid #000">Название</th>
                {{-- <th style="text-align: center;font-family:Cambria;font-weight:bold; border:1px solid #000">Состояние</th> --}}
                <th style="text-align: center;font-family:Cambria;font-weight:bold; border:1px solid #000">Ответственный</th>
                <th style="text-align: center;font-family:Cambria;font-weight:bold; border:1px solid #000">Крайний срок</th>
                <th style="text-align: center;font-family:Cambria;font-weight:bold; border:1px solid #000">Категория</th>
            </tr>
        </thead>
        <tbody>
            @php
                $count = 1
            @endphp
            @foreach ($tasks->whereBetween('deadline', ['2023-01-01', '2023-12-18'])->get() as $key=>$task)
                @if($task->user->leave != 1)
                    <tr>
                        <td style="font-family:Cambria; border:1px solid #000">{{$count}}</td>
                        <td width="120" style="border:1px solid #000; font-family:Cambria">{{$task->name}}</td>
                        {{-- @if ($task->overdue)
                            <td width="25" style="font-family:Cambria;color:#cb6546; background-color:#fff3e0; font-weight:bold; border: 2px solid #000;">Просроченный</td>
                        @elseif ($task->status == "Новое")
                            <td width="25" style="font-family:Cambria;color:#26af48; background-color:#e2eaed; font-weight:bold; border: 2px solid #000;">Новое</td>
                        @elseif ($task->status == "Выполняется")
                            <td width="25" style="font-family:Cambria;color:#4d8af0 ; background-color:#dbe8fc; font-weight:bold; border: 2px solid #000;">Выполняется</td>
                        @elseif ($task->status == "Выполнено")
                            <td width="25" style="font-family:Cambria;color:#6c61f6 ; background-color:#e2dffd; font-weight:bold; border: 2px solid #000;">Выполнено</td>
                        @elseif ($task->status == "Ждет подтверждения")
                            <td width="25" style="font-family:Cambria;color:#e63c3c ; background-color:#fde2e7; font-weight:bold; border: 2px solid #000;">Ждет подтверждения</td>
                        @endif --}}
                        <td width="30" style="font-family:Cambria;border:1px solid #000">{{$task->user->name}}</td>
                        <td width="20" style="font-family:Cambria;border:1px solid #000">{{$task->deadline}}</td>
                        <td width="30" style="font-family:Cambria;border:1px solid #000; font-weight:bold">{{$task->type->name}}</td>
                    </tr>
                    {{$count++}}
                @endif
            @endforeach
        </tbody>
    </table>
</html>