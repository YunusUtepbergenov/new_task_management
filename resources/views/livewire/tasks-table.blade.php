<div>
    <div wire:loading wire:target="view">
        <div class="loading">Loading&#8230;</div>
    </div>
    <div class="row filter-row">
        <div class="col-auto float-right ml-auto" style="margin-top: 10px;">
            @if(Auth::user()->isDirector() || Auth::user()->isMailer() || Auth::user()->isHead() || Auth::user()->isDeputy() || Auth::user()->isResearcher())
                <a href="#" class="btn add-btn" data-toggle="modal" data-target="#create_task"> Добавить Задачу</a>
            @endif
        </div>
    </div>
    <br>
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-nowrap mb-0" wire.ignore.self>
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th></th>
                                    <th>Название</th>
                                    <th>Дата Создание</th>
                                    <th>Cрок</th>
                                    <th>Постановщик</th>
                                    <th>Cтатус</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if ($tasks)
                                @forelse ($tasks as $key=>$task)
                                <tr>
                                    <td>{{ $key+1 }}</td>
                                    <td>
                                        @can('creator', $task)
                                            <div class="dropdown dropdown-action profile-action">
                                                <a href="#" class="action-icon dropdown-toggle" data-toggle="dropdown" aria-expanded="false"><i class="material-icons">more_vert</i></a>
                                                <div class="dropdown-menu dropdown-menu-right">
                                                    @if ($task->status != "Выполнено" && $task->status != "Ждет подтверждения")
                                                        <a class="dropdown-item" href="javascript:void(0)" onclick="editTask({{ $task->id }})" data-toggle="modal" data-target="#edit_task"><i class="fa fa-pencil m-r-5"></i> Изменить</a>
                                                    @endif
                                                    @if ($task->repeat_id)
                                                        <form action="{{ route('task.destroy', $task->id) }}" method="POST">
                                                            <input type="hidden" name="_method" value="DELETE">
                                                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                                            <button class="dropdown-item"><i class="fa fa-trash-o m-r-5"></i>Удалить текущую задачу</button>
                                                        </form>
                                                        <form action="{{ route('repeat.delete', $task->repeat_id) }}" method="POST">
                                                            <input type="hidden" name="_method" value="DELETE">
                                                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                                            <button class="dropdown-item"><i class="fa fa-trash-o m-r-5"></i>Остановить цикл</button>
                                                        </form>
                                                    @else
                                                        <form action="{{ route('task.destroy', $task->id) }}" method="POST">
                                                            <input type="hidden" name="_method" value="DELETE">
                                                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                                            <button class="dropdown-item"><i class="fa fa-trash-o m-r-5"></i>Удалить</button>
                                                        </form>
                                                    @endif
                                                </div>
                                            </div>                                            
                                        @endcan
                                    </td>
                                    <td>
                                        @if ($task->status == "Выполнено")
                                            <a href="#" wire:click.prevent="view({{ $task->id }})"><del>{{ $task->name }}</del></a>
                                        @else
                                            <a href="#" wire:click.prevent="view({{ $task->id }})">{{ $task->name }}</a>
                                        @endif
                                    </td>
                                    <td>{{ $task->created_at->format('Y-m-d') }}</td>
                                    <td><span class="badge bg-inverse-warning">{{ $task->deadline }}</span></td>
                                    <td>{{ $task->creator_name() }}</td>
                                    <td>
                                        @if ($task->overdue)
                                            <span class="badge bg-inverse-warning">Просроченный</span>
                                        @else
                                            <span class="badge bg-inverse-{{ ($task->status == "Новое") ? 'success' : (($task->status == "Выполняется") ? 'primary' : (($task->status == "Ждет подтверждения") ? 'danger' : (($task->status == "Выполнено") ? 'purple' : 'warning') )) }}">{{ $task->status }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty

                            @endforelse
                                @endif

                            </tbody>
                            @if ($chosen_project)
                            @foreach ($chosen_project as $prj)
                                @if(isset($prj['tasks']))
                                    <thead>
                                        <tr>
                                            <th></th>
                                            <th colspan="7">{{ $prj['name'] }}</th>
                                        </tr>
                                    </thead>
                                    @php
                                        $cnt = 1;
                                    @endphp
                                @endif
                                    @foreach ($prj['tasks'] as $key=>$task)
                                        @if ($task['user_id'] == Auth::user()->id)
                                            <tbody>
                                                <tr>
                                                    <td>{{ $cnt }}</td>
                                                    <td></td>
                                                    <td>
                                                        @if ($task['status'] == "Выполнено")
                                                            <a href="#" wire:click.prevent="view({{ $task['id'] }})"><del>{{ $task['name'] }}</del></a>
                                                        @else
                                                            <a href="#" wire:click.prevent="view({{ $task['id'] }})">{{ $task['name'] }}</a>
                                                        @endif
                                                    </td>
                                                    <td>{{ substr($task['created_at'], 0, 10) }}</td>
                                                    <td><span class="badge bg-inverse-warning">{{ $task['deadline'] }}</span></td>
                                                    <td>{{ $task->creator->name }}</td>
                                                    <td>
                                                        @if ($task['overdue'])
                                                            <span class="badge bg-inverse-warning">Просроченный</span>
                                                        @else
                                                            <span class="badge bg-inverse-{{ ($task['status'] == "Новое") ? 'success' : (($task['status'] == "Выполняется") ? 'primary' : (($task['status'] == "Ждет подтверждения") ? 'danger' : (($task['status'] == "Выполнено") ? 'purple' : 'warning') )) }}">{{ $task['status'] }}</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            </tbody>
                                            @php
                                                $cnt++
                                            @endphp
                                        @endif
                                    @endforeach
                                @endforeach
                            @endif
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
