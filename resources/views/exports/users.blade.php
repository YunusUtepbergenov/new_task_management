<html>
    <link rel="stylesheet" href="css/table.css">
    <table>
        <thead>
            <tr>
                <td colspan="5" height="40" style="vertical-align:middle;text-align: center; font-weight:bold; font-family:Cambria;font-size:14px;border:1px solid #000">
                    {{date('01:m:Y')}} - {{date('t:m:Y')}} оралиғида Марказ ходимлари томонидан бажарилган топшириқлари тўғрисида маълумот<br>
                    (ijro.cerr.uz портали маълумотлари асосида)
                </td>
            </tr>
            <tr>
                <th style="text-align: center;font-family:Cambria;font-weight:bold; border:1px solid #000">№</th>
                <th style="text-align: center;font-family:Cambria;font-weight:bold; border:1px solid #000">Название</th>
                <th style="text-align: center;font-family:Cambria;font-weight:bold; border:1px solid #000">Состояние</th>
                <th style="text-align: center;font-family:Cambria;font-weight:bold; border:1px solid #000">Крайний срок</th>
                <th style="text-align: center;font-family:Cambria;font-weight:bold; border:1px solid #000">Важность</th>
            </tr>
        </thead>
        <tbody>
            @foreach($users as $count=>$user)
                @if (!$user->isDirector() && !$user->isDeputy())
                    <tr>
                        <td colspan="5" style="font-family:Cambria;text-align:center;font-weight:bold; border:1px solid #000">{{($loop->index + 1).'. '.$user->name}}</td>
                    </tr>
                    @foreach ($user->tasks()->whereBetween('deadline', ['2023-01-01', '2023-12-18'])->get() as $key=>$task)
                        <tr>
                            <td style="font-family:Cambria; border:1px solid #000">{{$key + 1}}</td>
                            <td width="120" style="border:1px solid #000; font-family:Cambria">{{$task->name}}</td>
                                @if ($task->overdue)
                                    <td width="25" style="font-family:Cambria;color:#cb6546; background-color:#fff3e0; font-weight:bold; border: 2px solid #000;">Просроченный</td>
                                @elseif ($task->status == "Новое")
                                    <td width="25" style="font-family:Cambria;color:#26af48; background-color:#e2eaed; font-weight:bold; border: 2px solid #000;">Новое</td>
                                @elseif ($task->status == "Выполняется")
                                    <td width="25" style="font-family:Cambria;color:#4d8af0; background-color:#dbe8fc; font-weight:bold; border: 2px solid #000;">Выполняется</td>
                                @elseif ($task->status == "Выполнено")
                                    <td width="25" style="font-family:Cambria;color:#6c61f6; background-color:#e2dffd; font-weight:bold; border: 2px solid #000;">Выполнено</td>
                                @elseif ($task->status == "Ждет подтверждения")
                                    <td width="25" style="font-family:Cambria;color:#e63c3c; background-color:#fde2e7; font-weight:bold; border: 2px solid #000;">Ждет подтверждения</td>
                                @endif
                            <td width="20" style="font-family:Cambria;border:1px solid #000">{{$task->deadline}}</td>
                            <td width="20" style="font-family:Cambria;border:1px solid #000; font-weight:bold">{{$task->priority->name}}</td>
                        </tr>
                    @endforeach
                @endif
            @endforeach
        </tbody>
    </table>
</html>