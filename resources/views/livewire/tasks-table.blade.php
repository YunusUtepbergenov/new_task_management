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
                        <a href="#" wire:click.prevent="view({{ $task }})">{{ $task->name }}</a>
                        <div wire:loading>
                            <div class="loading">Loading&#8230;</div>
                        </div>
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
                        <th>{{ $project->name }}</th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                    </tr>
                </thead>
                @foreach ($project->tasks as $key=>$task)
                    @if ($task->user_id == Auth::user()->id)
                        <tbody>
                            <tr>
                                <td>{{ $key+1 }}</td>
                                <td>
                                    <a href="#" wire:click.prevent="view({{ $task->id }})">{{ $task->name }}</a>
                                    <div wire:loading>
                                        <div class="loading">Loading&#8230;</div>
                                    </div>
                                </td>
                                <td>{{ $task->created_at->format('Y-m-d') }}</td>
                                <td><span class="badge bg-inverse-warning">{{ $task->deadline }}</span></td>
                                <td>{{ $task->username($task->creator_id) }}</td>
                                <td>{{ $task->username($task->user_id) }}</td>
                        <td><span class="badge bg-inverse-{{ ($task->status == "Новое") ? 'success' : (($task->status == "Выполняется") ? 'primary' : (($task->status == "Ждет подтверждения") ? 'danger' : (($task->status == "Выполнено") ? 'purple' : 'primary') )) }}">{{ $task->status }}</span></td>
                            </tr>
                        </tbody>
                    @endif
                @endforeach
            @endforeach
    </table>
</div>
