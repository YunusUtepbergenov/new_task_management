<div>
    <div class="row mb-3">
        <div class="col-md-4">
            <input type="text" wire:model.debounce.500ms="search" class="form-control" placeholder="Поиск задачи...">
        </div>
        @if (!Auth::user()->isResearcher())
            <div class="col-md-2">
                <select wire:model="worker_id" class="form-control">
                    <option value="">Все сотрудники</option>
                    @foreach($workers as $worker)
                        <option value="{{ $worker->id }}">{{ $worker->name }}</option>
                    @endforeach
                </select>
            </div>
        @endif
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="table-responsive" id="employeeTable">
                <table class="table custom-table article-table" id="myTable" style="overflow-y: auto;">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Название</th>
                            <th>Сотрудник</th>
                            <th>Срок</th>
                            <th>Категория</th>
                            <th>Балл</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($tasks as $index => $task)
                            <tr>
                                <td>{{ $tasks->firstItem() + $index }}</td>
                                <td><a href="#" wire:click.prevent="view({{ $task->id }})">{{ $task->name }}</a></td>
                                <td>{{ $task->user->name ?? '-' }}</td>
                                <td>{{ $task->extended_deadline ?? $task->deadline }}</td>
                                <td>{{ $task->score->name ?? '-' }}</td>
                                <td>{{$task->total}}/{{$task->score->max_score ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6">Нет завершенных задач</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div>
        {{ $tasks->links() }}
    </div>

</div>
