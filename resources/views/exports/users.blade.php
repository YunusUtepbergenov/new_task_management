<html>
    <link rel="stylesheet" href="css/table.css">
    <table>
        <thead>
            {{-- @dd($norms) --}}
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
                <th style="text-align: center;font-family:Cambria;font-weight:bold; border:1px solid #000">Категория</th>
                <th style="text-align: center;font-family:Cambria;font-weight:bold; border:1px solid #000">Балл</th>
            </tr>
        </thead>
        <tbody>
            @php
                $count = 0;
            @endphp
            @foreach($users as $user)
                @if (!$user->isDirector() && !$user->isDeputy() && !$user->isEditor() && $user->role_id > 4)
                    <tr>
                        <td colspan="5" style="font-family:Cambria;text-align:center;font-weight:bold; border:1px solid #000">{{(++$count).'. '.$user->name}} ({{ $user->ovrKpiCalculate() }} балл / {{$norms[$user->role_id]}})</td>
                    </tr>
                    @foreach ($user->tasks()->whereBetween('deadline', [date('Y-m-01'), date('Y-m-t')])->get() as $key=>$task)
                        <tr>
                            <td width="5" style="font-family:Cambria; border:1px solid #000">{{$key + 1}}</td>
                            <td width="100" style="border:1px solid #000; font-family:Cambria">{{$task->name}}</td>
                                @if ($task->overdue)
                                    <td width="22" style="font-family:Cambria;color:#cb6546; background-color:#fff3e0; font-weight:bold; border: 2px solid #000;">Просроченный</td>
                                @elseif ($task->status == "Не прочитано")
                                    <td width="22" style="font-family:Cambria;color:#26af48; background-color:#e2eaed; font-weight:bold; border: 2px solid #000;">Не прочитано</td>
                                @elseif ($task->status == "Выполняется")
                                    <td width="22" style="font-family:Cambria;color:#4d8af0; background-color:#dbe8fc; font-weight:bold; border: 2px solid #000;">Выполняется</td>
                                @elseif ($task->status == "Выполнено")
                                    <td width="22" style="font-family:Cambria;color:#6c61f6; background-color:#e2dffd; font-weight:bold; border: 2px solid #000;">Выполнено</td>
                                @elseif ($task->status == "Ждет подтверждения")
                                    <td width="22" style="font-family:Cambria;color:#e63c3c; background-color:#fde2e7; font-weight:bold; border: 2px solid #000;">Ждет подтверждения</td>
                                @endif
                            <td width="15" style="font-family:Cambria;border:1px solid #000">{{$task->deadline}}</td>
                            <td width="60" style="font-family:Cambria;border:1px solid #000; font-weight:bold">{{(isset($task->score)) ? substr($task->score->name, strpos($task->score->name, '.') + 1) : ''}}</td>
                            <td width="10" style="font-family:Cambria;border:1px solid #000; font-weight:bold">{{ (isset($task->total)) ? $task->total.'/'.$task->score->max_score : ''}}</td>
                        </tr>
                    @endforeach
                @endif
            @endforeach
        </tbody>
    </table>
</html>