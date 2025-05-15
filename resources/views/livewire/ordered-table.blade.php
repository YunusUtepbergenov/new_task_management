<div>
    <div wire:loading wire:target="view">
        <div class="loading">Loading&#8230;</div>
    </div>
    {{-- <div class="row filter-row">

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
            <a href="#" class="btn add-btn" data-toggle="modal" data-target="#create_task"> Добавить Внеплановые Задачи</a>
            @if(Auth::user()->isDirector() || Auth::user()->isMailer() || Auth::user()->isHead() || Auth::user()->isDeputy() )
            <a href="#" class="btn add-btn" data-toggle="modal" data-target="#create_project"> Добавить Недельный план</a>
            @endif
        </div>
    </div> --}}
        <form action="{{ route('tasks.bulk_store') }}" method="POST" id="createProject">
            @csrf
            <div class="task-group border p-3 mb-3 bg-light rounded">
                <div class="row">
                    <!-- Task row group -->
                    <div class="form-group col-lg-2">
                        <label>Категория</label>
                        <select class="form-control select2" name="tasks[0][task_score]">
                            @foreach ($scoresGrouped as $group => $items)
                                <optgroup label="{{ $group }}">
                                    @foreach ($items as $type)
                                        <option value="{{ $type->id }}">{{ $type->name }} (Макс: {{ $type->max_score }})</option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-lg-4">
                        <label>Название</label>
                        <textarea type="text" name="tasks[0][name]" class="form-control" rows="1" required></textarea>
                    </div>
                    <div class="form-group col-lg-2">
                        <label>Срок</label>
                        <div class="cal-icon">
                            <input name="tasks[0][deadline]" class="form-control datetimepicker" required>
                        </div>
                    </div>
                    <div class="form-group col-lg-2">
                        <label>Ответственный</label>
                        <select name="tasks[0][workers][]" class="form-control select2" multiple required>
                            @if (Auth::user()->isDirector() || Auth::user()->isMailer())
                                @foreach ($sectors as $sector)
                                    <optgroup label="{{ $sector->name }}">
                                        @foreach ($sector->users as $user)
                                            <option value="{{ $user->id }}">{{ $user->employee_name() }}</option>
                                        @endforeach
                                    </optgroup>
                                @endforeach

                            @elseif(Auth::user()->isDeputy())
                                @foreach ($sectors as $sector)
                                    <optgroup label="{{ $sector->name }}">
                                        @foreach ($sector->users as $user)
                                            @if (!$user->isDirector() && !$user->isDeputy())
                                                <option value="{{ $user->id }}">{{ $user->employee_name() }}</option>                                                        
                                            @endif
                                        @endforeach
                                    </optgroup>
                                @endforeach
                                
                                @elseif(Auth::user()->isHead())
                                    @foreach (Auth::user()->sector->users()->where('leave', 0)->orderBy('role_id', 'ASC')->get() as $user)
                                        <option value="{{ $user->id }}">{{ $user->employee_name() }}</option>
                                    @endforeach
                            @endif
                        </select>
                    </div>
                    <div class="form-group col-lg-2">
                        <label>Тип</label>
                        <select name="plan_type" class="form-control" required>
                            <option value="" disabled selected>Выберите</option>
                            <option value="weekly">Еженедельный план</option>
                            <option value="unplanned">Внепланровая задача</option>
                        </select>
                    </div>
                    {{-- <div class="form-group col-lg-1 text-right">
                        <button type="button" class="btn btn-success remove-task">+</button>
                    </div> --}}
                </div>
            </div>
        </form>

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-nowrap mb-0" id="myTable">
                            <thead>
                                <tr>
                                    <th class="skip-filter">#</th>
                                    <th class="skip-filter"></th>
                                    <th class="skip-filter">Название</th>
                                    <th class="skip-filter">Дата Создание</th>
                                    <th class="skip-filter">Срок</th>
                                    <th class="skip-filter">Ответственный</th>
                                    <th class="skip-filter">Статус</th>
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
                                    </td>
                                    <td>
                                        @if ($task->status == "Выполнено")
                                            <a href="#" wire:click.prevent="view({{ $task->id }})"><del>{{ $task->name }}</del></a>
                                        @else
                                            <a href="#" wire:click.prevent="view({{ $task->id }})">{{ $task->name }}</a>
                                        @endif
                                    </td>
                                    <td>{{ $task->created_at->format('Y-m-d') }}</td>
                                    <td>
                                        @if ($task->extended_deadline)
                                            <span class="badge bg-inverse-warning" title="Оригинальный срок: {{ $task->deadline }}">
                                                {{ \Carbon\Carbon::parse($task->extended_deadline)->format('Y-m-d') }} <i class="fa fa-clock-o text-danger" title="Срок продлен"></i>
                                            </span>
                                        @else
                                            <span class="badge bg-inverse-warning">{{ \Carbon\Carbon::parse($task->deadline)->format('Y-m-d') }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $task->employee_name() }}</td>
                                    <td>
                                        @if ($task->overdue)
                                            <span class="badge bg-inverse-warning">Просроченный</span>
                                        @else
                                            <span class="badge bg-inverse-{{ ($task->status == "Новое") ? 'success' : (($task->status == "Выполняется") ? 'primary' : (($task->status == "Ждет подтверждения") ? 'danger' : (($task->status == "Выполнено") ? 'purple' : '') )) }}">{{ $task->status }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty

                            @endforelse
                                @endif
                            </tbody>
                            @if ($chosen_project)
                            @foreach ($chosen_project as $prj)
                                @if($prj['tasks']->count())
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
                                                <td>{{ $task->user->name }}</td>
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
