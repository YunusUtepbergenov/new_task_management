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

    <div class="wto-page-header mb-4">
        <div class="wto-page-header-icon">
            <i class="fa fa-calendar-check-o"></i>
        </div>
        <div>
            <h4 class="wto-page-title">Еженедельный план по секторам</h4>
            <p class="wto-page-subtitle">Задачи на неделю, сгруппированные по секторам</p>
        </div>
    </div>

    @if (Auth::user()->isDirector() || Auth::user()->isMailer() || Auth::user()->isDeputy() || Auth::user()->isHead())
        <form wire:submit="taskStore" class="mb-4"
            x-data="{
                empIds: [],
                init() {
                    this.$nextTick(() => {
                        if (!window.jQuery || !jQuery.fn.select2) return;

                        const $score = jQuery('#weekly_task_score');
                        if ($score.length) {
                            $score.select2({ width: '100%' });
                            $score.on('change', () => this.$wire.set('task_score', $score.val()));
                        }

                        const $emp = jQuery('#weekly_task_employee');
                        if ($emp.length) {
                            $emp.select2({ width: '100%', closeOnSelect: false });
                            $emp.on('select2:select', (e) => {
                                this.empIds.push(e.params.data.id);
                                this.$wire.set('task_employee', [...this.empIds]);
                                const $el = jQuery(e.params.data.element);
                                $el.detach();
                                $emp.append($el);
                                $emp.trigger('change.select2');
                            });
                            $emp.on('select2:unselect', (e) => {
                                this.empIds = this.empIds.filter(id => id !== e.params.data.id);
                                this.$wire.set('task_employee', [...this.empIds]);
                            });
                        }

                        this.$wire.on('form-reset', () => {
                            this.empIds = [];
                            if ($score.length) $score.val(null).trigger('change.select2');
                            if ($emp.length) $emp.val(null).trigger('change.select2');
                        });
                    });
                }
            }"
>
            <div class="task-group">
                <div class="row">
                    <div class="form-group col-lg-2" wire:ignore>
                        <label>Категория</label>
                        <select class="form-control" id="weekly_task_score">
                            <option value="" disabled selected>Выберите</option>
                            @foreach ($scoresGrouped as $group => $items)
                                <optgroup label="{{ $group }}">
                                    @foreach ($items as $type)
                                        <option value="{{ $type->id }}">{{ $type->name }} (Макс: {{ $type->max_score }})</option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                        @error('task_score')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group col-lg-4">
                        <label>Название</label>
                        <textarea class="form-control" rows="1" wire:model="task_name" required></textarea>
                        @error('task_name')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group col-lg-2">
                        <label>Срок</label>
                        <input
                            type="date"
                            wire:model="deadline"
                            class="form-control"
                            @if($is_repeating) value="" disabled @endif
                            required
                        >
                        @error('deadline')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group col-lg-2" wire:ignore>
                        <label>Ответственный</label>
                        <select id="weekly_task_employee" class="form-control" multiple>
                            @if (Auth::user()->isDirector() || Auth::user()->isMailer())
                                @foreach ($sectors as $sector)
                                    <optgroup label="{{ $sector->name }}">
                                        @foreach ($sector->users as $user)
                                            <option value="{{ $user->id }}">{{ $user->short_name }}</option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            @elseif (Auth::user()->isDeputy())
                                @foreach ($sectors as $sector)
                                    <optgroup label="{{ $sector->name }}">
                                        @foreach ($sector->users as $user)
                                            @if (!$user->isDirector() && (!$user->isDeputy() || $user->id == Auth::id()))
                                                <option value="{{ $user->id }}">{{ $user->short_name }}</option>
                                            @endif
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            @elseif (Auth::user()->isHead())
                                @foreach ($sectors as $sector)
                                    <optgroup label="{{ $sector->name }}">
                                        @foreach ($sector->users as $user)
                                            @if (!$user->isDirector() && !$user->isDeputy())
                                                <option value="{{ $user->id }}">{{ $user->short_name }}</option>
                                            @endif
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            @endif
                        </select>
                        @error('task_employee')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group col-lg-2 d-flex align-items-end">
                        <button class="btn btn-primary create-task-btn w-100">Создать Задачу</button>
                    </div>
                </div>
            </div>
        </form>
    @endif

    <div class="wto-week-bar mb-4">
        <div class="wto-week-left">
            <i class="fa fa-calendar" style="color: var(--sidebar-active-bg);"></i>
            <select wire:model.live="selectedWeek" class="form-control wto-week-select">
                @foreach ($weeks as $weekStart)
                    <option value="{{ $weekStart }}">
                        {{ \Carbon\Carbon::parse($weekStart)->format('d M Y') }} -
                        {{ \Carbon\Carbon::parse($weekStart)->endOfWeek()->format('d M Y') }}
                    </option>
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
                                                <form action="{{ route('task.destroy', $main['id']) }}" method="POST">
                                                    @method('DELETE')
                                                    @csrf
                                                    <button class="dropdown-item"><i class="fa fa-trash-o m-r-5"></i>Удалить</button>
                                                </form>
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

                                    @if (!empty($main['repeat']))
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

@script
    <script>
        Livewire.on('toastr:success', (params) => {
            toastr.options = { "closeButton": true, "progressBar": true };
            toastr.success(params.message);
        });
    </script>
@endscript
