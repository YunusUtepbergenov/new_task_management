@php
    $main_task = $task[0];
    $responsibles = collect($task)->pluck('user.name')->filter()->join(', ');
@endphp

<tr>
    <td>{{ $key }}</td>
    <td>
        @if ($main_task['creator_id'] == Auth::user()->id)
            <div class="dropdown dropdown-action profile-action">
                <a href="#" class="action-icon dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                    <i class="material-icons">more_vert</i>
                </a>
                <div class="dropdown-menu dropdown-menu-right">
                    @if ($main_task['status'] != "Выполнено" && $main_task['status'] != "Ждет подтверждения")
                        <a class="dropdown-item" href="javascript:void(0)" onclick="editTask({{ $main_task['id'] }})" data-toggle="modal" data-target="#edit_task"><i class="fa fa-pencil m-r-5"></i> Изменить</a>
                    @endif
                    @if ($main_task['repeat_id'])
                        <form action="{{ route('task.destroy', $main_task['id']) }}" method="POST">
                            @method('DELETE')
                            @csrf
                            <button class="dropdown-item"><i class="fa fa-trash-o m-r-5"></i>Удалить текущую задачу</button>
                        </form>
                        <form action="{{ route('repeat.delete', $main_task['repeat_id']) }}" method="POST">
                            @method('DELETE')
                            @csrf
                            <button class="dropdown-item"><i class="fa fa-trash-o m-r-5"></i>Остановить цикл</button>
                        </form>
                    @else
                        <form action="{{ route('task.destroy', $main_task['id']) }}" method="POST">
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
        @if ($main_task['status'] == "Выполнено")
            <a href="#" wire:click.prevent="view({{ $main_task['id'] }})"><del>{{ $main_task['name'] }}</del></a>
        @else
            <a href="#" wire:click.prevent="view({{ $main_task['id'] }})">{{ $main_task['name'] }}</a>
        @endif
    </td>
    <td>
        @if ($main_task['extended_deadline'])
            <span class="badge bg-inverse-warning" title="Оригинальный срок: {{ $main_task['deadline'] }}">
                {{ \Carbon\Carbon::parse($main_task['extended_deadline'])->format('Y-m-d') }} <i class="fa fa-clock-o text-danger" title="Срок продлен"></i>
            </span>
        @else
            <span class="badge bg-inverse-warning">{{ \Carbon\Carbon::parse($main_task['deadline'])->format('Y-m-d') }}</span>
        @endif

        @if (!empty($main_task['repeat']))
            <i class="fa fa-refresh text-info" title="Повторяющаяся задача"></i>
        @endif
    </td>
    <td>{{ $responsibles }}</td>
    <td>
        @if ($main_task['overdue'])
            <span class="badge bg-inverse-warning">Просроченный</span>
        @else
        @php
            if ($main_task['status'] == 'Не прочитано') {
                $statusClass = 'success';
            } elseif ($main_task['status'] == 'Выполняется') {
                $statusClass = 'primary';
            } elseif ($main_task['status'] == 'Ждет подтверждения') {
                $statusClass = 'danger';
            } elseif ($main_task['status'] == 'Выполнено') {
                $statusClass = 'purple';
            } else {
                $statusClass = '';
            }
        @endphp
            <span class="badge bg-inverse-{{ $statusClass }}">{{ $main_task['status'] }}</span>
        @endif
    </td>
</tr>