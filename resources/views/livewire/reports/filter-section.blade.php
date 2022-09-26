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
                            <th>Ф.И.О</th>
                            <th>Сектор</th>
                            <th>Эффективность:</th>
                            <th>Все задачи</th>
                            <th>Выполнено</th>
                            <th>Новое</th>
                            <th>Просроченный</th>
                            <th>Ждет подтверждения</th>
                        </tr>
                    </thead>
                    <tbody style="overflow: auto;">
                        @foreach ($users as $employee)
                            @if (!$employee->isDirector() && !$employee->isDeputy())
                                <tr>
                                    <td>
                                        <h2 class="table-avatar">
                                            <a href="{{ route('user.report', [$employee->id, $startDate, $endDate]) }}">{{ $employee->name }}</a>
                                        </h2>
                                    </td>
                                    <td class="text-wrap">{{ $employee->sector->name }}</td>
                                    @if ($employee->filterTasks($startDate, $endDate)->count() > 0)
                                        <td style="text-align: center">
                                            {{ round( ((1 - ( $employee->overdueFilter($startDate, $endDate)->count()
                                            + (0.5 * $employee->newFilter($startDate, $endDate)->count()) ) / $employee->filterTasks($startDate, $endDate)->count() )) * 100, 1) }}%
                                        </td>
                                    @else
                                        <td style="text-align: center">
                                            0%
                                        </td>
                                    @endif
                                    <td style="text-align: center">{{$employee->tasks->whereBetween('deadline', [$startDate, $endDate])->count()}}</td>
                                    <td style="text-align: center">{{$employee->tasks->whereBetween('deadline', [$startDate, $endDate])->where('status', 'Выполнено')->where('overdue', 0)->count()}}</td>
                                    <td style="text-align: center">{{$employee->tasks->whereBetween('deadline', [$startDate, $endDate])->where('status', 'Новое')->where('overdue', 0)->count()}}</td>
                                    <td style="text-align: center">{{ $employee->overdueFilter($startDate, $endDate)->count() }}</td>
                                    <td style="text-align: center">{{ $employee->confirmFilter($startDate, $endDate)->count() }}</td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('livewire:load', function () {
        $('#startDate').on('dp.change', function (e) {
            @this.set('startDate', e.target.value);
        });

        $('#endDate').on('dp.change', function (e) {
            @this.set('endDate', e.target.value);
        });
    });
</script>
