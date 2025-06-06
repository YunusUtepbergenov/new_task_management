<div>
    <div class="card">
        <div class="card-header">
            <div class="row">
                <div class="col-sm-2">
                    <h4 class="card-title mb-0">Задачи</h4>
                </div>
                @if ($user)
                <div class="col-sm-10">
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" wire:model="filter" name="filter" id="inlineRadio0" value="Null">
                        <label class="form-check-label" for="inlineRadio0" style="color: #34444c; font-weight:bold">Все ({{ ($user) ? $user->tasks()->count() : '0' }})</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" wire:model="filter" name="filter" id="inlineRadio1" value="Не прочитано">
                        <label class="form-check-label" for="inlineRadio1" style="color: #55ce63; font-weight:bold">Не прочитано ({{ ($user) ? $user->newTasks()->count() : '0' }})</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" wire:model="filter" name="filter" id="inlineRadio2" value="Выполняется">
                        <label class="form-check-label" for="inlineRadio2" style="color: #4d8af0; font-weight:bold">Выполняется ({{ ($user) ? $user->doingTasks()->count() : '0' }})</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" wire:model="filter" name="filter" id="inlineRadio3" value="Ждет подтверждения">
                        <label class="form-check-label" for="inlineRadio3" style="color: #e63c3c; font-weight:bold">Ждет подтверждения ({{ ($user) ? $user->confirmTasks()->count() : '0' }})</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" wire:model="filter" name="filter" id="inlineRadio4" value="Выполнено">
                        <label class="form-check-label" for="inlineRadio4" style="color: #6c61f6; font-weight:bold">Выполнено ({{ ($user) ? $user->finishedTasks()->count() : '0' }})</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" wire:model="filter" name="filter" id="inlineRadio5" value="Просроченный">
                        <label class="form-check-label" for="inlineRadio5" style="color: #ffbc34; font-weight:bold">Просроченный ({{ ($user) ? $user->overdueTasks()->count() : '0' }})</label>
                    </div>
                </div>
                @endif

            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive reports_table">
                <table class="table table-nowrap mb-0" wire.ignore.self="">
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
                                    <td>{{ $task->user->name }}</td>
                                    <td>
                                        @if ($task->overdue)
                                            <span class="badge bg-inverse-warning">Просроченный</span>
                                        @else
                                            <span class="badge bg-inverse-{{ ($task->status == "Не прочитано") ? 'success' : (($task->status == "Выполняется") ? 'primary' : (($task->status == "Ждет подтверждения") ? 'danger' : (($task->status == "Выполнено") ? 'purple' : 'warning') )) }}">{{ $task->status }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                            @endforelse
                        @endif
                        @if ($projects)
                            @foreach ($projects as $prj)
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
                                @foreach ($prj['tasks'] as $key=>$task)
                                    @if ($task['user_id'] == $user['id'])
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
                                                <td>{{ $task['user']['name'] }}</td>
                                                <td>
                                                    @if ($task['overdue'])
                                                        <span class="badge bg-inverse-warning">Просроченный</span>
                                                    @else
                                                        <span class="badge bg-inverse-{{ ($task['status'] == "Не прочитано") ? 'success' : (($task['status'] == "Выполняется") ? 'primary' : (($task['status'] == "Ждет подтверждения") ? 'danger' : (($task['status'] == "Выполнено") ? 'purple' : 'warning') )) }}">{{ $task['status'] }}</span>
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
                    </tbody>
                </table>                                                                                                                                                                                </table>
            </div>
        </div>
    </div>
</div>
