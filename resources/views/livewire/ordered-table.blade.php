<div>
    <div wire:loading wire:target="view">
        <div class="loading">Loading&#8230;</div>
    </div>
    <div class="row filter-row">
        {{-- <div class="col-sm-4 col-md-2">
            <div class="form-group">
                <label for="select">Проекты</label>
                <select class="form-control" wire:model="projectId" aria-hidden="true">
                    <option value="Empty">Все</option>
                    <option value="">Не проект</option>
                    @foreach ($projects as $project)
                        <option value="{{ $project->id }}">{{ $project->name }}</option>
                    @endforeach
                </select>
            </div>
        </div> --}}
        <div class="col-sm-4 col-md-2">
            <div class="form-group">
                <label for="select">Состояние</label>
                <select class="form-control" wire:model="status" aria-hidden="true">
                    <option value="Empty">Все</option>
                    <option value="Новое">Новое</option>
                    <option value="Выполняется">Выполняется</option>
                    <option value="Ждет подтверждения">Ждет подтверждения</option>
                    <option value="Выполнено">Выполнено</option>
                    <option value="Просроченный">Просроченный</option>
                </select>
            </div>
        </div>
        <div class="col-auto float-right ml-auto" style="margin-top: 10px;">
            @if(Auth::user()->isDirector() || Auth::user()->isMailer() || Auth::user()->isHead() || Auth::user()->isDeputy() )
            <a href="#" class="btn add-btn" data-toggle="modal" data-target="#create_project"> Добавить Проект</a>
            <a href="#" class="btn add-btn" data-toggle="modal" data-target="#create_task"> Добавить Задачу</a>
            @endif
        </div>
    </div>
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
                                    <th>Крайний срок</th>
                                    <th>Постановщик</th>
                                    <th>Ответственный</th>
                                    <th>Состояние</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if ($tasks)
                                @forelse ($tasks as $key=>$task)
                                <tr>
                                    <td>{{ $key+1 }}</td>
                                    <td>
                                        <div class="dropdown dropdown-action profile-action">
                                            <a href="#" class="action-icon dropdown-toggle" data-toggle="dropdown" aria-expanded="false"><i class="material-icons">more_vert</i></a>
                                            <div class="dropdown-menu dropdown-menu-right">
                                                <a class="dropdown-item" href="javascript:void(0)" onclick="editTask({{ $task->id }})" data-toggle="modal" data-target="#edit_task"><i class="fa fa-pencil m-r-5"></i> Изменить</a>
                                                @if ($task->repeat_id)
                                                    <form action="{{ route('task.destroy', $task->id) }}" method="POST">
                                                        <input type="hidden" name="_method" value="DELETE">
                                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                                        <button class="dropdown-item"><i class="fa fa-trash-o m-r-5"></i>Удалить текущую задачу</button>
                                                    </form>
                                                    <form action="{{ route('task.destroy', $task->repeat_id) }}" method="POST">
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
                                    <td>{{ $username }}</td>
                                    <td>{{ $task->user->name }}</td>
                                    <td><span class="badge bg-inverse-{{ ($task->status == "Новое") ? 'success' : (($task->status == "Выполняется") ? 'primary' : (($task->status == "Ждет подтверждения") ? 'danger' : (($task->status == "Выполнено") ? 'purple' : 'warning') )) }}">{{ $task->status }}</span></td>
                                </tr>
                            @empty

                            @endforelse
                                @endif
                            </tbody>
                            @if ($chosen_project)
                            @foreach ($chosen_project as $prj)
                            <thead>
                                <tr>
                                    <th></th>
                                    <th></th>
                                    <th>{{ $prj['name'] }}</th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                </tr>
                            </thead>
                            @php
                                $cnt = 1;
                            @endphp

                            @foreach ($prj['tasks'] as $key=>$task)
                                @if ($task['creator_id'] == Auth::user()->id)
                                    <tbody>
                                        <tr>
                                            <td>{{ $cnt }}</td>
                                            <td><div class="dropdown dropdown-action profile-action">
                                                <a href="#" class="action-icon dropdown-toggle" data-toggle="dropdown" aria-expanded="false"><i class="material-icons">more_vert</i></a>
                                                <div class="dropdown-menu dropdown-menu-right">
                                                    <a class="dropdown-item" href="javascript:void(0)" onclick="editTask({{ $task->id }})" data-toggle="modal" data-target="#edit_task"><i class="fa fa-pencil m-r-5"></i> Изменить</a>
                                                    @if ($task->repeat_id)
                                                        <form action="{{ route('task.destroy', $task->id) }}" method="POST">
                                                            <input type="hidden" name="_method" value="DELETE">
                                                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                                            <button class="dropdown-item"><i class="fa fa-trash-o m-r-5"></i>Удалить текущую задачу</button>
                                                        </form>
                                                        <form action="{{ route('task.destroy', $task->repeat_id) }}" method="POST">
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
                                            </div></td>
                                            <td>
                                                @if ($task['status'] == "Выполнено")
                                                    <a href="#" wire:click.prevent="view({{ $task->id }})"><del>{{ $task['name'] }}</del></a>
                                                @else
                                                    <a href="#" wire:click.prevent="view({{ $task['id'] }})">{{ $task['name'] }}</a>
                                                @endif
                                            </td>
                                            <td>{{ substr($task['created_at'], 0, 10) }}</td>
                                            <td><span class="badge bg-inverse-warning">{{ $task['deadline'] }}</span></td>
                                            <td>{{ $username }}</td>
                                            <td>{{ $task->user->name }}</td>
                                    <td><span class="badge bg-inverse-{{ ($task['status'] == "Новое") ? 'success' : (($task['status'] == "Выполняется") ? 'primary' : (($task['status'] == "Ждет подтверждения") ? 'danger' : (($task['status'] == "Выполнено") ? 'purple' : 'warning') )) }}">{{ $task['status'] }}</span></td>
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
