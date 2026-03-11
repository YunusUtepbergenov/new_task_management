<div>
    {{-- Task loading overlay --}}
    <div
        wire:loading.flex
        wire:target="view"
        style="position:fixed;inset:0;z-index:1060;align-items:center;justify-content:center;background:rgba(15,23,42,0.45);backdrop-filter:blur(2px);"
    >
        <div style="background:var(--card-bg);border-radius:16px;padding:36px 48px;display:flex;flex-direction:column;align-items:center;gap:16px;box-shadow:0 20px 60px rgba(0,0,0,0.18);border:1px solid var(--border-color);">
            <div style="width:44px;height:44px;border-radius:50%;border:3px solid var(--border-color);border-top-color:var(--sidebar-active-bg);animation:vm-spin 0.7s linear infinite;"></div>
            <span style="font-size:14px;font-weight:500;color:var(--text-secondary);letter-spacing:0.01em;">Загрузка задачи...</span>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-4">
            <input type="text" wire:model.live="search" class="form-control" placeholder="Поиск задачи...">
        </div>
        @if (!Auth::user()->isResearcher())
            <div class="col-md-2">
                <select wire:model.live="worker_id" class="form-control">
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
