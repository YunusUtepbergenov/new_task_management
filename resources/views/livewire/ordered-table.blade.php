<div>
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
            @foreach ($tasks as $key=>$task)
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
                    <td>{{ $task->username($task->creator_id) }}</td>
                    <td>{{ $task->username($task->user_id) }}</td>
                    <td><span class="badge bg-inverse-{{ ($task->status == "Новое") ? 'success' : (($task->status == "Выполняется") ? 'primary' : (($task->status == "Ждет подтверждения") ? 'danger' : (($task->status == "Выполнено") ? 'purple' : 'primary') )) }}">{{ $task->status }}</span></td>
                </tr>
            @endforeach
        </tbody>
            @foreach ($user_projects as $project)
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
                @foreach ($project['tasks'] as $key=>$task)
                    @if ($task['creator_id'] == Auth::user()->id)
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
                                </td>                                <td>{{ substr($task['created_at'], 0, 10) }}</td>
                                <td><span class="badge bg-inverse-warning">{{ $task['deadline'] }}</span></td>
                                <td>{{ \App\Helpers\AppHelper::username($task['creator_id']) }}</td>
                                <td>{{ \App\Helpers\AppHelper::username($task['user_id']) }}</td>
                        <td><span class="badge bg-inverse-{{ ($task['status'] == "Новое") ? 'success' : (($task['status'] == "Выполняется") ? 'primary' : (($task['status'] == "Ждет подтверждения") ? 'danger' : (($task['status'] == "Выполнено") ? 'purple' : 'primary') )) }}">{{ $task['status'] }}</span></td>
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