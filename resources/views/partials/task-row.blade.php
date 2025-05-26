<tr>
    <td>{{ $key + 1 }}</td>
    <td>
        @if ($task->creator_id == Auth::user()->id)
            <div class="dropdown dropdown-action profile-action">
                <a href="#" class="action-icon dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                    <i class="material-icons">more_vert</i>
                </a>
                <div class="dropdown-menu dropdown-menu-right">
                    @if ($task->status != "Выполнено" && $task->status != "Ждет подтверждения")
                        <a class="dropdown-item" href="javascript:void(0)" onclick="editTask({{ $task->id }})" data-toggle="modal" data-target="#edit_task"><i class="fa fa-pencil m-r-5"></i> Изменить</a>
                    @endif
                    @if ($task->repeat_id)
                        <form action="{{ route('task.destroy', $task->id) }}" method="POST">
                            @method('DELETE')
                            @csrf
                            <button class="dropdown-item"><i class="fa fa-trash-o m-r-5"></i>Удалить текущую задачу</button>
                        </form>
                        <form action="{{ route('repeat.delete', $task->repeat_id) }}" method="POST">
                            @method('DELETE')
                            @csrf
                            <button class="dropdown-item"><i class="fa fa-trash-o m-r-5"></i>Остановить цикл</button>
                        </form>
                    @else
                        <form action="{{ route('task.destroy', $task->id) }}" method="POST">
                            @method('DELETE')
                            @csrf
                            <button class="dropdown-item"><i class="fa fa-trash-o m-r-5"></i>Удалить</button>
                        </form>
                    @endif
                </div>
            </div>
        @endif

    </td>
    <td>
        @if ($task->status == "Выполнено")
            <a href="#" wire:click.prevent="view({{ $task->id }})"><del>{{ $task->name }}</del></a>
        @else
            <a href="#" wire:click.prevent="view({{ $task->id }})">{{ $task->name }}</a>
        @endif
    </td>
    <td>
        @if ($task->extended_deadline)
            <span class="badge bg-inverse-warning" title="Оригинальный срок: {{ $task->deadline }}">
                {{ \Carbon\Carbon::parse($task->extended_deadline)->format('Y-m-d') }} <i class="fa fa-clock-o text-danger" title="Срок продлен"></i>
            </span>
        @else
            <span class="badge bg-inverse-warning">{{ \Carbon\Carbon::parse($task->deadline)->format('Y-m-d') }}</span>
        @endif

        @if ($task->repeat)
            <i class="fa fa-refresh text-info" title="Повторяющаяся задача"></i>
        @endif
    </td>
    <td>{{ $task->employee_name() }}</td>
    <td>
        @if ($task->overdue)
            <span class="badge bg-inverse-warning">Просроченный</span>
        @else
            <span class="badge bg-inverse-{{ ($task->status == 'Не прочитано') ? 'success' : (($task->status == 'Выполняется') ? 'primary' : (($task->status == 'Ждет подтверждения') ? 'danger' : (($task->status == 'Выполнено') ? 'purple' : ''))) }}">{{ $task->status }}</span>
        @endif
    </td>
</tr>
