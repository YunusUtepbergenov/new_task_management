<div>
    <div wire:loading>
        <div class="loading">Loading&#8230;</div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div id="employeeTable1">
                <table class="table custom-table" id="mytable" style="overflow-y: auto; height: 110px;">
                    <thead id="employee_header">
                        <tr>
                            <th>#</th>
                            <th data-type="string">Ф.И.О</th>
                            <th data-type="string">Сектор</th>
                            <th data-type="number">KPI</th>
                            <th data-type="number">Задачи <br>(макс: 60)</th>
                            <th data-type="number">Особо важный <br>(макс: 30)</th>
                            <th data-type="number">Дисциплина <br>(макс: 10)</th>
                        </tr>
                    </thead>
                    <tbody style="overflow: auto;" id="table1">
                        @php
                            $counter = 0;
                        @endphp
                        @foreach ($users as $key=>$employee)
                            @if (!$employee->isDirector() && !$employee->isDeputy())
                            <tr>
                                <td>{{ ++$counter }}</td>
                                <td>
                                    <h2 class="table-avatar">
                                        <a href="{{ route('user.report', [$employee->id, $startDate, $endDate]) }}">{{ $employee->name }}</a>
                                    </h2>
                                </td>
                                <td class="text-wrap">{{ $employee->sector->name }}</td>
                                <td style="text-align: center" class="score">
                                    {{ $employee->kpi_score }}
                                </td>
                                <td style="text-align: center">{{ $employee->simple_score + $employee->mid_score + $employee->high_score }}</td>
                                <td style="text-align: center">{{ $employee->very_high_score }}</td>
                                <td style="text-align: center">10</td>
                            </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
