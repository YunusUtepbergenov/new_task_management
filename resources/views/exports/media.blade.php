<html>
    <link rel="stylesheet" href="css/table.css">
    <table>
        <thead>
            <tr>
            </tr>
            <tr>
                <th style="text-align: center;font-family:Cambria;font-weight:bold; border:1px solid #000">№</th>
                <th style="text-align: center;font-family:Cambria;font-weight:bold; border:1px solid #000">Название</th>
                <th style="text-align: center;font-family:Cambria;font-weight:bold; border:1px solid #000">Ответственный</th>                
                <th style="text-align: center;font-family:Cambria;font-weight:bold; border:1px solid #000">Крайний срок</th>
                <th style="text-align: center;font-family:Cambria;font-weight:bold; border:1px solid #000">Категория</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sectors as $sector)
                @php
                    $count = 1;
                @endphp
                @if ($sector->id != 1)
                    <tr>
                        <td colspan="5" style="font-family:Cambria;text-align:center;font-weight:bold; border:1px solid #000">{{$sector->name}}</td>
                    </tr>
                @endif
                @foreach($sector->users as $user)
                    @if ($sector->id != 1)
                    @foreach ($user->tasks->whereBetween('deadline', ['2023-01-01', '2023-04-10']) as $task)
                            <tr>
                                <td style="font-family:Cambria; border:1px solid #000">{{$count}}</td>
                                <td width="120" style="border:1px solid #000; font-family:Cambria">{{$task->name}}</td>
                                <td width="30" style="border:1px solid #000; font-family:Cambria">{{$task->user->name}}</td>
                                
                                    {{-- @if ($task->overdue)
                                        <td width="25" style="font-family:Cambria;color:#cb6546; background-color:#fff3e0; font-weight:bold; border: 2px solid #000;">Просроченный</td>
                                    @elseif ($task->status == "Не прочитано")
                                        <td width="25" style="font-family:Cambria;color:#26af48; background-color:#e2eaed; font-weight:bold; border: 2px solid #000;">Не прочитано</td>
                                    @elseif ($task->status == "Выполняется")
                                        <td width="25" style="font-family:Cambria;color:#4d8af0 ; background-color:#dbe8fc; font-weight:bold; border: 2px solid #000;">Выполняется</td>
                                    @elseif ($task->status == "Выполнено")
                                        <td width="25" style="font-family:Cambria;color:#6c61f6 ; background-color:#e2dffd; font-weight:bold; border: 2px solid #000;">Выполнено</td>
                                    @elseif ($task->status == "Ждет подтверждения")
                                        <td width="25" style="font-family:Cambria;color:#e63c3c ; background-color:#fde2e7; font-weight:bold; border: 2px solid #000;">Ждет подтверждения</td>
                                    @endif --}}
                                <td width="20" style="font-family:Cambria;border:1px solid #000">{{$task->deadline}}</td>
                                <td width="20" style="font-family:Cambria;border:1px solid #000; font-weight:bold">{{$task->score->name}}</td>
                            </tr>
                            {{$count++}}
                        @endforeach
                    @endif
                @endforeach
            @endforeach
        </tbody>
    </table>
</html>