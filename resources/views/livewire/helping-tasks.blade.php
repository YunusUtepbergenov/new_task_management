<div>

      <div class="row filter-row">
        {{-- <div class="col-sm-4 col-md-2">
            <div class="form-group">
                <label for="select">Проекты</label>
                <select class="form-control" wire:model="projectId" aria-hidden="true">
                    <option value="">Not project</option>
                    @if ($helping_projects)
                        @foreach ($helping_projects as $project)
                            <option value="{{ $project->id }}">{{ $project->name }}</option>
                        @endforeach
                    @endif
                </select>
            </div>
        </div>
        <div class="col-sm-4 col-md-2">
            <div class="form-group">
                <label for="select">Состояние</label>
                <select class="form-control" wire:model="status" aria-hidden="true">
                    <option value="Новое">Новое</option>
                    <option value="Выполняется">Выполняется</option>
                    <option value="Ждет подтверждения">Ждет подтверждения</option>
                    <option value="Выполнено">Выполнено</option>
                </select>
            </div>
        </div> --}}
        <div class="col-auto float-right ml-auto" style="margin: 10px 0 10px 0;">
            @if(Auth::user()->isDirector() || Auth::user()->isMailer() || Auth::user()->isHead() || Auth::user()->isDeputy() || Auth::user()->isResearcher())
                <a href="#" class="btn add-btn" data-toggle="modal" data-target="#create_task"> Добавить Задачу</a>
            @endif        
            @if(Auth::user()->isDirector() || Auth::user()->isMailer() || Auth::user()->isHead() || Auth::user()->isDeputy())
                <a href="#" class="btn add-btn" data-toggle="modal" data-target="#create_project"> Добавить Проект</a>
            @endif
        </div>
    </div>
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-nowrap mb-0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Название</th>
                                    <th>Дата создания</th>
                                    <th>Крайний срок</th>
                                    <th>Постановщик</th>
                                    <th>Ответственный</th>
                                    <th>Состояние</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($tasks_without_project as $key=>$task)
                                    <tr>
                                        <td>{{ $key+1 }}</td>
                                        <td>
                                            @if ($task['status'] == "Выполнено")
                                                <a href="#" wire:click.prevent="view({{ $task['id'] }})"><del>{{ $task['name'] }}</del></a>
                                                <div wire:loading>
                                                    <div class="loading">Loading&#8230;</div>
                                                </div>
                                            @else
                                                <a href="#" wire:click.prevent="view({{ $task['id'] }})">{{ $task['name'] }}</a>
                                                <div wire:loading>
                                                    <div class="loading">Loading&#8230;</div>
                                                </div>
                                            @endif
                                        </td>
                                        <td>{{ substr($task['created_at'], 0, 10) }}</td>
                                        <td><span class="badge bg-inverse-warning">{{ $task['deadline'] }}</span></td>
                                        <td>{{ \App\Helpers\AppHelper::username($task['creator_id']) }}</td>
                                        <td>{{ \App\Helpers\AppHelper::username($task['user_id']) }}</td>
                                        <td>
                                            @if ($task['overdue'])
                                                <span class="badge bg-inverse-warning">Просроченный</span>
                                            @else
                                                <span class="badge bg-inverse-{{ ($task['status'] == "Новое") ? 'success' : (($task['status'] == "Выполняется") ? 'primary' : (($task['status'] == "Ждет подтверждения") ? 'danger' : (($task['status'] == "Выполнено") ? 'purple' : 'warning') )) }}">{{ $task['status'] }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            @foreach ($helping_projects as $project)
                                <thead>
                                    <tr>
                                        <th></th>
                                        <th>{{ $project['name'] }}</th>
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

                                @foreach ($project['tasks'] as $task)
                                    @if (in_array($task['id'], $tasks_id))
                                        <tbody>
                                            <tr>
                                                <td>{{ $cnt }}</td>
                                                <td>
                                                    @if ($task['status'] == "Выполнено")
                                                        <a href="#" wire:click.prevent="view({{ $task['id'] }})"><del>{{ $task['name'] }}</del></a>
                                                        <div wire:loading>
                                                            <div class="loading">Loading&#8230;</div>
                                                        </div>
                                                    @else
                                                        <a href="#" wire:click.prevent="view({{ $task['id'] }})">{{ $task['name'] }}</a>
                                                        <div wire:loading>
                                                            <div class="loading">Loading&#8230;</div>
                                                        </div>
                                                    @endif
                                                </td>
                                                <td>{{ substr($task['created_at'], 0, 10) }}</td>
                                                <td><span class="badge bg-inverse-warning">{{ $task['deadline'] }}</span></td>
                                                <td>{{ \App\Helpers\AppHelper::username($task['creator_id']) }}</td>
                                                <td>{{ \App\Helpers\AppHelper::username($task['user_id']) }}</td>
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
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
