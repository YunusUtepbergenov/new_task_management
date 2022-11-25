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
                            <th>Ф.И.О 
                                <span wire:click="sortBy('name')" class="arrowss">
                                    <i class="fa fa-arrow-up {{ $sortColumnName === 'name' && $sortDirection === 'asc' ? '' : 'text-muted' }}" aria-hidden="true"></i>
                                    <i class="fa fa-arrow-down {{ $sortColumnName === 'name' && $sortDirection === 'desc' ? '' : 'text-muted' }}" aria-hidden="true"></i>
                                </span>
                            </th>
                            <th>Сектор
                                <span wire:click="sortBy('sector_name')" class="arrowss">
                                    <i class="fa fa-arrow-up {{ $sortColumnName === 'sector_name' && $sortDirection === 'asc' ? '' : 'text-muted' }}" aria-hidden="true"></i>
                                    <i class="fa fa-arrow-down {{ $sortColumnName === 'sector_name' && $sortDirection === 'desc' ? '' : 'text-muted' }}" aria-hidden="true"></i>
                                </span>
                            </th>
                            <th>Эффективность: 
                                <span wire:click="sortBy('efficiency')" style="cursor: pointer">
                                    <i class="fa fa-arrow-up {{ $sortColumnName === 'efficiency' && $sortDirection === 'asc' ? '' : 'text-muted' }}" aria-hidden="true"></i>
                                    <i class="fa fa-arrow-down {{ $sortColumnName === 'efficiency' && $sortDirection === 'desc' ? '' : 'text-muted' }}" aria-hidden="true"></i>
                                </span>
                            </th>
                            <th>Все задачи
                                <span wire:click="sortBy('tasks_cnt')" style="cursor: pointer">
                                    <i class="fa fa-arrow-up {{ $sortColumnName === 'tasks_cnt' && $sortDirection === 'asc' ? '' : 'text-muted' }}" aria-hidden="true"></i>
                                    <i class="fa fa-arrow-down {{ $sortColumnName === 'tasks_cnt' && $sortDirection === 'desc' ? '' : 'text-muted' }}" aria-hidden="true"></i>
                                </span>
                            </th>
                            <th>Выполнено
                                <span wire:click="sortBy('done_cnt')" style="cursor: pointer">
                                    <i class="fa fa-arrow-up {{ $sortColumnName === 'done_cnt' && $sortDirection === 'asc' ? '' : 'text-muted' }}" aria-hidden="true"></i>
                                    <i class="fa fa-arrow-down {{ $sortColumnName === 'done_cnt' && $sortDirection === 'desc' ? '' : 'text-muted' }}" aria-hidden="true"></i>
                                </span>
                            </th>
                            <th>Новое
                                <span wire:click="sortBy('new_cnt')" style="cursor: pointer">
                                    <i class="fa fa-arrow-up {{ $sortColumnName === 'new_cnt' && $sortDirection === 'asc' ? '' : 'text-muted' }}" aria-hidden="true"></i>
                                    <i class="fa fa-arrow-down {{ $sortColumnName === 'new_cnt' && $sortDirection === 'desc' ? '' : 'text-muted' }}" aria-hidden="true"></i>
                                </span>
                            </th>
                            <th>Выполняется
                                <span wire:click="sortBy('doing_cnt')" style="cursor: pointer">
                                    <i class="fa fa-arrow-up {{ $sortColumnName === 'doing_cnt' && $sortDirection === 'asc' ? '' : 'text-muted' }}" aria-hidden="true"></i>
                                    <i class="fa fa-arrow-down {{ $sortColumnName === 'doing_cnt' && $sortDirection === 'desc' ? '' : 'text-muted' }}" aria-hidden="true"></i>
                                </span>
                            </th>
                            <th>Просроченный
                                <span wire:click="sortBy('overdue_cnt')" style="cursor: pointer">
                                    <i class="fa fa-arrow-up {{ $sortColumnName === 'overdue_cnt' && $sortDirection === 'asc' ? '' : 'text-muted' }}" aria-hidden="true"></i>
                                    <i class="fa fa-arrow-down {{ $sortColumnName === 'overdue_cnt' && $sortDirection === 'desc' ? '' : 'text-muted' }}" aria-hidden="true"></i>
                                </span>
                            </th>
                            <th>Ждет подтверждения
                                <span wire:click="sortBy('confirm_cnt')" style="cursor: pointer">
                                    <i class="fa fa-arrow-up {{ $sortColumnName === 'confirm_cnt' && $sortDirection === 'asc' ? '' : 'text-muted' }}" aria-hidden="true"></i>
                                    <i class="fa fa-arrow-down {{ $sortColumnName === 'confirm_cnt' && $sortDirection === 'desc' ? '' : 'text-muted' }}" aria-hidden="true"></i>
                                </span>
                            </th>
                        </tr>
                    </thead>
                    <tbody style="overflow: auto;">
                        @php
                            $counter = 0;
                        @endphp
                        @foreach ($users as $employee)
                            @if (!$employee->isDirector() && !$employee->isDeputy())
                                <tr>
                                    <td>{{ ++$counter }}</td>
                                    <td>
                                        <h2 class="table-avatar">
                                            <a href="{{ route('user.report', [$employee->id, $startDate, $endDate]) }}">{{ $employee->name }}</a>
                                        </h2>
                                    </td>
                                    <td class="text-wrap">{{ $employee->sector->name }}</td>
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
