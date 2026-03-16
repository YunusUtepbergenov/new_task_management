<div>

    @include('partials.task-loading-overlay')

    <div class="wto-page-header mb-4">
        <div class="wto-page-header-icon">
            <i class="fa fa-calendar-check-o"></i>
        </div>
        <div>
            <h4 class="wto-page-title">Еженедельный план по секторам</h4>
            <p class="wto-page-subtitle">Задачи на неделю, сгруппированные по секторам</p>
        </div>
    </div>

    <livewire:create-task-form />

    <div class="wto-week-bar mb-4">
        <div class="wto-week-left">
            <i class="fa fa-calendar" style="color: var(--sidebar-active-bg);"></i>
            <select wire:model.live="selectedWeek" class="form-control wto-week-select">
                @foreach ($weeks as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <button wire:click="export" class="btn export-btn">
            <i class="fa fa-file-excel-o"></i> Экспорт в Excel
        </button>
    </div>

    @forelse ($groupedTasks as $sectorName => $groups)
        <div class="card mb-4">
            <div class="card-header wto-sector-header">
                <span class="wto-sector-name">
                    <i class="fa fa-users"></i> {{ $sectorName }}
                </span>
                <span class="wto-task-count">{{ count($groups) }}</span>
            </div>
            <div class="table-responsive">
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
                            @if (Auth::user()->isDeputy() || Auth::user()->isHR() || Auth::user()->isHead())
                                <th>Для протокола</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($groups as $index => $taskGroup)
                            @php
                                $main = $taskGroup[0];
                                $uniqueUsers = collect($taskGroup)->pluck('user.short_name')->unique()->filter()->values();
                                $users = $uniqueUsers->first();
                            @endphp
                            <tr wire:key="weekly-row-{{ $main['id'] }}">
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    @if (Auth::user()->isDeputy() || (Auth::user()->isHead() && $main['creator_id'] == Auth::id()))
                                        <div class="dropdown dropdown-action profile-action">
                                            <a href="#" class="action-icon dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                                                <i class="material-icons">more_vert</i>
                                            </a>
                                            <div class="dropdown-menu dropdown-menu-right">
                                                <a class="dropdown-item" href="javascript:void(0)" wire:click="$dispatch('editTaskClicked', { id: {{ $main['id'] }} })"><i class="fa fa-pencil m-r-5"></i> Изменить</a>
                                                <a class="dropdown-item" href="javascript:void(0)" wire:click="deleteTask({{ $main['id'] }})" wire:confirm="Удалить задачу?"><i class="fa fa-trash-o m-r-5"></i> Удалить</a>
                                            </div>
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <a href="#" wire:click.prevent="view({{ $main['id'] }})">{{ $main['name'] }}</a>
                                </td>
                                <td>
                                    @if ($main['extended_deadline'])
                                        <span class="badge bg-inverse-warning" title="Оригинальный срок: {{ $main['deadline'] }}">
                                            {{ \Carbon\Carbon::parse($main['extended_deadline'])->format('Y-m-d') }} <i class="fa fa-clock-o text-danger" title="Срок продлен"></i>
                                        </span>
                                    @else
                                        <span class="badge bg-inverse-warning">{{ \Carbon\Carbon::parse($main['deadline'])->format('Y-m-d') }}</span>
                                    @endif

                                    @if (!empty($main['repeat_id']))
                                        <i class="fa fa-refresh text-info" title="Повторяющаяся задача"></i>
                                    @endif
                                </td>
                                <td>
                                    {{ $users }}
                                    @if ($uniqueUsers->count() > 1)
                                        <span style="background:rgba(59,130,246,0.1);color:var(--sidebar-active-bg);border-radius:20px;padding:1px 7px;font-size:11px;font-weight:600;margin-left:4px;">
                                            <i class="fa fa-users"></i> {{ collect($taskGroup)->count() }}
                                        </span>
                                    @endif
                                </td>
                                <td>{{ $main['score']['name'] ?? '' }}</td>
                                <td>
                                    @php
                                        $statusClass = match($main['status']) {
                                            'Не прочитано'       => 'success',
                                            'Выполняется'        => 'primary',
                                            'Ждет подтверждения' => 'danger',
                                            'Выполнено'          => 'purple',
                                            default              => 'warning',
                                        };
                                    @endphp
                                    <span class="badge bg-inverse-{{ $statusClass }}">{{ $main['status'] }}</span>
                                </td>

                                @if (Auth::user()->isDeputy() || Auth::user()->isHR())
                                    <td>
                                        <input type="checkbox" wire:click="toggleProtocol({{ $main['id'] }})"
                                            @if($main['for_protocol']) checked @endif />
                                    </td>
                                @elseif (Auth::user()->isHead() && Auth::user()->sector_id == $main['sector_id'])
                                    <td>
                                        <input type="checkbox" wire:click="toggleProtocol({{ $main['id'] }})"
                                            @if($main['for_protocol']) checked @endif />
                                    </td>
                                @elseif (Auth::user()->isHead())
                                    <td></td>
                                @endif

                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @empty
        <div class="card mb-4">
            <div class="card-body text-center text-muted py-5">
                <i class="fa fa-calendar-o fa-3x mb-3"></i>
                <p class="mb-0">Нет задач на выбранную неделю.</p>
            </div>
        </div>
    @endforelse
</div>

