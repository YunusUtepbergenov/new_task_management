<div>

    <h4 class="mb-4">Еженедельный план по секторам</h4>
    <div class="d-flex justify-content-between mb-3">
        <div>
            <label>Выберите неделю:</label>
            <select wire:model="selectedWeek" class="form-control">
                @foreach ($weeks as $weekStart)
                    <option value="{{ $weekStart }}">
                        {{ \Carbon\Carbon::parse($weekStart)->format('d M Y') }} -
                        {{ \Carbon\Carbon::parse($weekStart)->endOfWeek()->format('d M Y') }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="align-self-end">
            <button wire:click="export" class="btn btn-primary">Экспорт в Excel</button>
        </div>
    </div>

    @foreach ($groupedTasks as $sector => $groups)
        <div class="card mb-4">
            <div class="card-header" style="background: rgb(15 23 42 / var(--tw-text-opacity, 1));color:#fff; text-align:center"><strong>{{ $sector }}</strong></div>
            <div class="card-body table-responsive">
                <table class="table table-nowrap mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th></th>
                            <th>Название</th>
                            <th>Срок</th>
                            <th>Ответственный</th>
                            <th>Категория</th>
                            <th>Статус</th>
                            @if (Auth::user()->isDeputy())
                                <th>Для протокола</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($groups as $index => $taskGroup)
                            @php
                                $main = $taskGroup[0];
                                $users = collect($taskGroup)->pluck('user.name')->unique()->join(', ');
                            @endphp
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    @if (Auth::user()->isDeputy())
                                        <div class="dropdown dropdown-action profile-action">
                                            <a href="#" class="action-icon dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                                                <i class="material-icons">more_vert</i>
                                            </a>
                                            <div class="dropdown-menu dropdown-menu-right">
                                                <a class="dropdown-item" href="javascript:void(0)" onclick="editTask({{ $main['id'] }})" data-toggle="modal" data-target="#edit_task"><i class="fa fa-pencil m-r-5"></i> Изменить</a>
                                                <form action="{{ route('task.destroy', $main['id']) }}" method="POST">
                                                    @method('DELETE')
                                                    @csrf
                                                    <button class="dropdown-item"><i class="fa fa-trash-o m-r-5"></i>Удалить</button>
                                                </form>
                                            </div>
                                        </div>
                                    @endif
                                </td>
                                <td>{{ $main['name'] }}</td>
                                <td>
                                    @if ($main['extended_deadline'])
                                        <span class="badge bg-inverse-warning" title="Оригинальный срок: {{ $main['deadline'] }}">
                                            {{ \Carbon\Carbon::parse($main['extended_deadline'])->format('Y-m-d') }} <i class="fa fa-clock-o text-danger" title="Срок продлен"></i>
                                        </span>
                                    @else
                                        <span class="badge bg-inverse-warning">{{ \Carbon\Carbon::parse($main['deadline'])->format('Y-m-d') }}</span>
                                    @endif

                                    @if (!empty($main['repeat']))
                                        <i class="fa fa-refresh text-info" title="Повторяющаяся задача"></i>
                                    @endif
                                </td>
                                <td>{{ $users }}</td>
                                <td>{{ $main['score']['name'] ?? '' }}</td>
                                <td>{{ $main['status'] }}</td>

                                @if (Auth::user()->isDeputy())
                                    <td>
                                        <input type="checkbox" wire:click="toggleProtocol({{ $main['id'] }})"
                                            @if($main['for_protocol']) checked @endif />
                                    </td>
                                @endif

                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endforeach
</div>
