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
                <table class="table table-nowrap mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th></th>
                            <th>Название</th>
                            <th>Срок</th>
                            <th>Ответственный</th>
                            <th>Статус</th>
                            @if (Auth::user()->isDeputy())
                                <th>Для протокола</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($tasks as $index => $task)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    @if (Auth::user()->isDeputy())
                                        <div class="dropdown dropdown-action profile-action">
                                            <a href="#" class="action-icon dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                                                <i class="material-icons">more_vert</i>
                                            </a>
                                            <div class="dropdown-menu dropdown-menu-right">
                                                <a class="dropdown-item" href="javascript:void(0)" onclick="editTask({{ $task->id }})" data-toggle="modal" data-target="#edit_task"><i class="fa fa-pencil m-r-5"></i> Изменить</a>
                                                <form action="{{ route('task.destroy', $task->id) }}" method="POST">
                                                    @method('DELETE')
                                                    @csrf
                                                    <button class="dropdown-item"><i class="fa fa-trash-o m-r-5"></i>Удалить</button>
                                                </form>
                                            </div>
                                        </div>
                                    @endif
                                </td>
                                <td>{{ $task->name }}</td>
                                <td>{{ \Carbon\Carbon::parse($task->deadline)->format('d.m.Y') }}</td>
                                <td>{{ $task->user->employee_name() }}</td>
                                <td>{{ $task->status }}</td>
                                @if (Auth::user()->isDeputy())
                                    <td>
                                        <input type="checkbox" wire:click="toggleProtocol({{ $task->id }})"
                                            @if($task->for_protocol) checked @endif />
                                    </td>                                    
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endforeach
</div>
