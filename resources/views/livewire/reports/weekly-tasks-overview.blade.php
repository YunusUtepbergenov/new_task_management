<div>

    <h4 class="mb-4">Еженедельный план по секторам</h4>
    <div class="d-flex justify-content-between mb-3">
        <div>
            <label>Выберите неделю:</label>
            <select wire:model="selectedWeek" class="form-control">
                @foreach ($weeks as $weekStart)
                    <option value="{{ $weekStart }}">
                        {{ \Carbon\Carbon::parse($weekStart)->format('d M Y') }} -
                        {{ \Carbon\Carbon::parse($weekStart)->endOfWeek()->format('d M Y') }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="align-self-end">
            <button wire:click="export" class="btn btn-primary">Экспорт в Excel</button>
        </div>
    </div>

    @foreach ($groupedTasks as $sector => $tasks)
        <div class="card mb-4">
            <div class="card-header" style="background: #34444c;color:#fff; text-align:center"><strong>{{ $sector }}</strong></div>
            <div class="card-body table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Название</th>
                            <th>Срок</th>
                            <th>Ответственный</th>
                            <th>Статус</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($tasks as $index => $task)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $task->name }}</td>
                                <td>{{ \Carbon\Carbon::parse($task->deadline)->format('d.m.Y') }}</td>
                                <td>{{ $task->user->employee_name() }}</td>
                                <td>{{ $task->status }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endforeach

</div>
