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

    <form wire:submit="taskStore"
        x-data="{
            empIds: [],
            init() {
                this.$nextTick(() => {
                    if (!window.jQuery || !jQuery.fn.select2) return;

                    const $score = jQuery('#task_score');
                    if ($score.length) {
                        $score.select2({ width: '100%' });
                        $score.on('change', () => this.$wire.set('task_score', $score.val()));
                    }

                    const $emp = jQuery('#task_employee');
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
        <div class="task-group mb-3">
            <div class="row">
                <!-- Task row group -->
                <div class="form-group col-lg-2" wire:ignore>
                    <label>Категория</label>
                    <select class="form-control select2" id="task_score">
                        <option value="def" disabled selected>Выберите</option>
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
                    <textarea type="text" class="form-control" rows="1" wire:model="task_name" required></textarea>
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
                             placeholder="{{ $is_repeating ? 'Определяется автоматически' : '' }}"
                            required
                        >
                        @error('deadline')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror                           
                </div>
                <div class="form-group col-lg-2" wire:ignore>
                    <label>Ответственный</label>
                    <select id="task_employee" class="form-control select2" multiple>
                        @if (Auth::user()->isDirector() || Auth::user()->isMailer())
                            @foreach ($sectors as $sector)
                                <optgroup label="{{ $sector->name }}">
                                    @foreach ($sector->users as $user)
                                        <option value="{{ $user->id }}">{{ $user->short_name }}</option>
                                    @endforeach
                                </optgroup>
                            @endforeach

                        @elseif(Auth::user()->isDeputy())
                            @foreach ($sectors as $sector)
                                <optgroup label="{{ $sector->name }}">
                                    @foreach ($sector->users as $user)
                                        @if (!$user->isDirector() && (! $user->isDeputy() || $user->id == Auth::id()))
                                            <option value="{{ $user->id }}">{{ $user->short_name }}</option>                                                        
                                        @endif
                                    @endforeach
                                </optgroup>
                            @endforeach
                            
                        @elseif(Auth::user()->isHead())
                            @foreach ($sectors as $sector)
                                <optgroup label="{{ $sector->name }}">
                                    @foreach ($sector->users as $user)
                                        @if (!$user->isDirector() && (! $user->isDeputy()))
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

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between flex-wrap" style="gap: 10px;">
                    <div>
                        <strong>Задачи на неделю </strong>
                        <span class="italic">
                            ({{ \Carbon\Carbon::now()->startOfWeek()->format('d M Y') }} -
                            {{ \Carbon\Carbon::now()->endOfWeek()->format('d M Y') }})
                        </span>
                    </div>
                    <div style="position: relative; min-width: 280px; max-width: 400px; flex: 1;">
                        <i class="fa fa-search" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:var(--text-secondary);font-size:13px;"></i>
                        <input
                            type="text"
                            class="form-control form-control-sm"
                            wire:model.live.debounce.300ms="weeklySearch"
                            placeholder="Поиск..."
                            style="padding-left:32px;border-radius:8px;"
                        >
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-nowrap mb-0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th></th>
                                    <th>Название</th>
                                    <th>Срок</th>
                                    <th>Ответственный</th>
                                    <th>Статус</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $key = 1;
                                @endphp
                                @forelse ($weeklyTasks as $task)
                                    @include('partials.task-row', ['task' => $task, 'key'=> $key++])
                                @empty
                                    <tr><td colspan="7">Нет еженедельных задач</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-12 mt-4">
            <div class="card">
                <div class="card-header">
                    <a data-toggle="collapse" href="#unplannedTasksCollapse" role="button" aria-expanded="false" aria-controls="unplannedTasksCollapse" class="collapse-toggle">
                        <strong>Все задачи</strong>
                        <i class="fa fa-chevron-down" id="unplannedTasksChevron"></i>
                    </a>
                </div>
                <div id="unplannedTasksCollapse" class="collapse">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-nowrap mb-0">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th></th>
                                        <th>Название</th>
                                        <th>Срок</th>
                                        <th>Ответственный</th>
                                        <th>Статус</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $cnt = 1;
                                    @endphp
                                    @forelse ($all_tasks as $key => $task)
                                        @include('partials.task-row', ['task' => $task, 'key' => $cnt++])
                                    @empty
                                        <tr><td colspan="7">Нет внеплановых задач</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@script
    <script>
        $('#unplannedTasksCollapse').on('show.bs.collapse', function () {
            $('#unplannedTasksChevron').removeClass('fa-chevron-right').addClass('fa-chevron-down');
        });
        $('#unplannedTasksCollapse').on('hide.bs.collapse', function () {
            $('#unplannedTasksChevron').removeClass('fa-chevron-down').addClass('fa-chevron-right');
        });

    </script>
@endscript