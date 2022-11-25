<div>
    <div wire:loading wire:target="view">
        <div class="loading">Loading&#8230;</div>
    </div>
    <div class="row filter-row">
        <div class="col-sm-4 col-md-2">
            <div class="form-group">
                <label for="select">Проекты</label>
                <select class="form-control" wire:model="projectId" aria-hidden="true">
                    <option value="Empty">Все</option>
                    <option value="">Не проект</option>
                    @foreach ($projects as $project)
                        <option value="{{ $project['id'] }}">{{ $project['name'] }}</option>
                    @endforeach
                </select>
            </div>
        </div>
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
                                        @if ($task->status == "Выполнено")
                                            <a href="#" wire:click.prevent="view({{ $task->id }})"><del>{{ $task->name }}</del></a>
                                        @else
                                            <a href="#" wire:click.prevent="view({{ $task->id }})">{{ $task->name }}</a>
                                        @endif
                                    </td>
                                    <td>{{ $task->created_at->format('Y-m-d') }}</td>
                                    <td><span class="badge bg-inverse-warning">{{ $task->deadline }}</span></td>
                                    <td>{{ $task->creator->name }}</td>
                                    <td>{{ $username }}</td>
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
                                @endif
                                @foreach ($prj['tasks'] as $key=>$task)
                                    @if ($task['user_id'] == Auth::user()->id)
                                        <tbody>
                                            <tr>
                                                <td>{{ $cnt }}</td>
                                                <td>
                                                    @if ($task['status'] == "Выполнено")
                                                        <a href="#" wire:click.prevent="view({{ $task['id'] }})"><del>{{ $task['name'] }}</del></a>
                                                    @else
                                                        <a href="#" wire:click.prevent="view({{ $task['id'] }})">{{ $task['name'] }}</a>
                                                    @endif
                                                </td>
                                                <td>{{ substr($task['created_at'], 0, 10) }}</td>
                                                <td><span class="badge bg-inverse-warning">{{ $task['deadline'] }}</span></td>
                                                <td>{{ $task['creator']['name'] }}</td>
                                                <td>{{ $username }}</td>
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
