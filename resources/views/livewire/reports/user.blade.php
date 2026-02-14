<div>
    <div wire:loading wire:target="view">
        <div class="loading">Loading&#8230;</div>
    </div>
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="page-title">Задачи ({{ $user->name }})</h3>
            </div>
        </div>
    </div>
    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif
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
                                    <th>Постановщик</th>
                                    <th>Крайний срок</th>
                                    <th>Категория</th>
                                    <th>Балл</th>
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
                                    <td>{{ $task->creator->name }}</td>
                                    <td><span class="badge bg-inverse-warning">{{ $task->deadline }}</span></td>
                                    <td>{{(isset($task->score)) ? substr($task->score->name, strpos($task->score->name, '.') + 1) : ''}}</td>

                                    <td>
                                        @if(auth()->user()->isDeputy() && isset($task->total))
                                                <div class="d-flex align-items-center">
                                                    <input type="number"
                                                        wire:model="editedScores.{{ $task->id }}"
                                                        min="0"
                                                        max="{{ $task->score->max_score }}"
                                                        placeholder="{{ (isset($task->total)) ? $task->total.'/'.$task->score->max_score : '' }}"
                                                        class="form-control form-control-sm"
                                                        style="width: 70px; margin-right: 5px;">
                                                    <button class="btn btn-sm btn-primary"
                                                            wire:click="saveScore({{ $task->id }})">Сохранить</button>
                                                </div>
                                                @error("editedScores.{$task->id}")
                                                    <div class="text-danger small">{{ $message }}</div>
                                                @enderror
                                            @else
                                                {{ (isset($task->total)) ? $task->total.'/'.$task->score->max_score : '' }}
                                        @endif
                                    </td>
                                    
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
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
