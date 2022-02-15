<div>
    <div class="row filter-row">
        <div class="col-sm-4 col-md-2">
            <div class="form-group">
                <label for="select">Проекты</label>
                <select class="form-control" wire:model="projectId" aria-hidden="true">
                    <option value="Empty"></option>
                    <option value="">Не проект</option>
                    @foreach ($projects as $project)
                        <option value="{{ $project->id }}">{{ $project->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-sm-4 col-md-2">
            <div class="form-group">
                <label for="select">Состаяние</label>
                <select class="form-control" wire:model="status" aria-hidden="true">
                    <option value="Empty"></option>
                    <option value="Новое">Новое</option>
                    <option value="Выполняется">Выполняется</option>
                    <option value="Ждет подтверждения">Ждет подтверждения</option>
                    <option value="Выполнено">Выполнено</option>
                </select>
            </div>
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
                                    <th>Состаяние</th>
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
                                            <div wire:loading>
                                                <div class="loading">Loading&#8230;</div>
                                            </div>
                                        @else
                                            <a href="#" wire:click.prevent="view({{ $task->id }})">{{ $task->name }}</a>
                                            <div wire:loading>
                                                <div class="loading">Loading&#8230;</div>
                                            </div>
                                        @endif
                                    </td>
                                    <td>{{ $task->created_at->format('Y-m-d') }}</td>
                                    <td><span class="badge bg-inverse-warning">{{ $task->deadline }}</span></td>
                                    <td>{{ $task->creator->name }}</td>
                                    <td>{{ $username }}</td>
                                    <td><span class="badge bg-inverse-{{ ($task->status == "Новое") ? 'success' : (($task->status == "Выполняется") ? 'primary' : (($task->status == "Ждет подтверждения") ? 'danger' : (($task->status == "Выполнено") ? 'purple' : 'primary') )) }}">{{ $task->status }}</span></td>
                                </tr>
                            @empty

                            @endforelse
                                @endif

                            </tbody>
                            @if ($chosen_project)
                            <thead>
                                <tr>
                                    <th></th>
                                    <th>{{ $chosen_project->name }}</th>
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
                            @foreach ($chosen_project->tasks as $key=>$task)
                                @if ($task['user_id'] == Auth::user()->id)
                                    <tbody>
                                        <tr>
                                            <td>{{ $cnt }}</td>
                                            <td>
                                                @if ($task['status'] == "Выполнено")
                                                    <a href="#" wire:click.prevent="view({{ $task->id }})"><del>{{ $task->name }}</del></a>
                                                    <div wire:loading>
                                                        <div class="loading">Loading&#8230;</div>
                                                    </div>
                                                @else
                                                    <a href="#" wire:click.prevent="view({{ $task['id'] }})">{{ $task['name'] }}</a>
                                                    <div wire:loading>
                                                        <div class="loading">Loading&#8230;</div>
                                                    </div>
                                                @endif
                                            </td>                                <td>{{ substr($task['created_at'], 0, 10) }}</td>
                                            <td><span class="badge bg-inverse-warning">{{ $task['deadline'] }}</span></td>
                                            <td>{{ $task->creator->name }}</td>
                                            <td>{{ $username }}</td>
                                    <td><span class="badge bg-inverse-{{ ($task['status'] == "Новое") ? 'success' : (($task['status'] == "Выполняется") ? 'primary' : (($task['status'] == "Ждет подтверждения") ? 'danger' : (($task['status'] == "Выполнено") ? 'purple' : 'primary') )) }}">{{ $task['status'] }}</span></td>
                                        </tr>
                                    </tbody>
                                    @php
                                        $cnt++
                                    @endphp
                                @endif
                            @endforeach

                            @endif
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
