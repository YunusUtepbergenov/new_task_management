<div>

    <h4 class="mb-4">Еженедельный план по секторам</h4>
    @foreach ($sectors as $sector)
        @if ($sector->tasks->count())
            <div class="card mb-3">
                <div class="card-header bg-progress text-white text-center">
                    <strong>{{ $sector->name }}</strong>
                </div>
                <div class="card-body p-0">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Название</th>
                                <th>Категория</th>
                                <th>Сотрудник</th>
                                <th>Срок</th>
                                <th>Состояние</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($sector->tasks as $index => $task)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $task->name }}</td>
                                    <td>{{ optional($task->score)->name }}</td>
                                    <td>{{ optional($task->user)->employee_name() }}</td>
                                    <td>{{ \Carbon\Carbon::parse($task->deadline)->format('d.m.Y') }}</td>
                                    <td>{{ $task->status }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    @endforeach

</div>
