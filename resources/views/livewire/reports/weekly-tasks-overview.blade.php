<div>

    <h4 class="mb-4">Еженедельный план по секторам</h4>

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
            x-init="init()">
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

    <div class="d-flex justify-content-between mb-3">
        <div>
            <label>Выберите неделю:</label>
            <select wire:model.live="selectedWeek" class="form-control">
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

    @forelse ($groupedTasks as $sectorName => $groups)
        <div class="card mb-4">
            <div class="card-header" style="background: rgb(15 23 42 / var(--tw-text-opacity, 1));color:#000; text-align:center"><strong>{{ $sectorName }}</strong></div>
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
                            @if (Auth::user()->isDeputy() || Auth::user()->isHR() || Auth::user()->isHead())
                                <th>Для протокола</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($groups as $index => $taskGroup)
                            @php
                                $main = $taskGroup[0];
                                $users = collect($taskGroup)->pluck('user.short_name')->unique()->join(', ');
                            @endphp
                            <tr wire:key="weekly-row-{{ $main['id'] }}">
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    @if (Auth::user()->isDeputy())
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
