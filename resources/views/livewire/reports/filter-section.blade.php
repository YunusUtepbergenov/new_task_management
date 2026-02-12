<div>
    <div wire:loading>
        <div class="loading">Loading&#8230;</div>
    </div>

    <div class="row filter-row">
        <div class="col-sm-4 col-md-2">
            <label for="input">От</label>
            <div class="form-group cal-icon">
                <input class="form-control datetimepicker" id="startDate" name="startDate" wire:model="startDate" >
            </div>
        </div>
        <div class="col-sm-4 col-md-2">
            <label>До</label>
            <div class="form-group cal-icon">
                <input class="form-control datetimepicker" id="endDate" name="endDate" name="endDate" wire:model="endDate">
            </div>
        </div>
        <div class="col-sm-4 col-md-5">
            <label style="color: #f7f7f7">S</label>
            <div class="">
                <a id="btnExport" class="btn btn-primary search_button" onclick="fnExcelReport();"> Скачать таблицу </a>
            </div>
        </div>
        <div class="col-sm-1 col-md-3" >
            <label style="color: #f7f7f7">S</label>
            <div>
                <a href="{{ route('download.report', [$startDate, $endDate]) }}" class="btn btn-primary search_button">Отчёт по секторам</a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="table-responsive" id="employeeTable1">
                <table class="table custom-table" id="filteredTasks" style="overflow-y: auto; height: 110px;">
                    <thead id="employee_header">
                        <tr>
                            <th>#</th>
                            <th>Ф.И.О </th>
                            <th>Эффективность: </th>
                            <th>Все задачи</th>
                            <th>Выполнено</th>
                            <th>Не прочитано</th>
                            <th>Выполняется</th>
                            <th>Просроченный</th>
                            <th>Ждет подтверждения</th>
                        </tr>
                    </thead>
                    <tbody style="overflow: auto;">
                        @php
                            $counter = 0;
                        @endphp
                        @foreach ($sectors as $sector)
                        @if ($sector->id != 1)
                        <tr>
                            <td colspan="9" style="text-align:center;font-weight:bold">{{$sector->name}}</td>
                        </tr>
                        @endif
                            @foreach($sector->users as $employee)
                            @if (!$employee->isDirector() && !$employee->isDeputy())
                                <tr>
                                    <td>{{ ++$counter }}</td>
                                    <td>
                                        <h2 class="table-avatar">
                                            <a href="{{ route('user.report', [$employee->id, $startDate, $endDate]) }}">{{ $employee->name }}</a>
                                        </h2>
                                    </td>
                                    {{-- <td class="text-wrap">{{ $employee->sector->name }}</td> --}}
                                    <td style="text-align: center">{{ $employee->efficiency }}%</td>
                                    <td style="text-align: center">{{$employee->tasks_cnt}}</td>
                                    <td style="text-align: center">{{$employee->done_cnt}}</td>
                                    <td style="text-align: center">{{$employee->new_cnt}}</td>
                                    <td style="text-align: center">{{ $employee->doing_cnt }}</td>
                                    <td style="text-align: center">{{ $employee->overdue_cnt }}</td>
                                    <td style="text-align: center">{{ $employee->confirm_cnt }}</td>
                                </tr>
                            @endif
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@script
<script>
    $('#startDate').on('dp.change', function (e) {
        $wire.$set('startDate', e.target.value);
    });

    $('#endDate').on('dp.change', function (e) {
        $wire.$set('endDate', e.target.value);
    });
</script>
@endscript
