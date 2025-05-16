<div>
    <div wire:loading wire:target="view">
        <div class="loading">Loading&#8230;</div>
    </div>

    <form wire:submit.prevent="taskStore">
        <div class="task-group border p-3 mb-2 bg-light rounded">
            <div class="row">
                <!-- Task row group -->
                <div class="form-group col-lg-2">
                    <label>Категория</label>
                    <select class="form-control select2" id="task_score" wire:model.defer="task_score">
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
                <div class="form-group col-lg-2">
                    <label>Тип</label>
                    <select wire:model.defer="task_plan" class="form-control" required>
                        <option value="1" selected disabled>Выберите</option>
                        <option value="weekly">Еженедельный план</option>
                        <option value="unplanned">Внеплановая задача</option>
                    </select>
                    @error('task_plan')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                    
                </div>
                <div class="form-group col-lg-4">
                    <label>Название</label>
                    <textarea type="text" class="form-control" rows="1" wire:model.defer="task_name" required></textarea>
                    @error('task_name')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror                    
                </div>
                <div class="form-group col-lg-2">
                    <label>Срок</label>
                    <div class="cal-icon">
                        <input wire:model.defer="deadline" class="form-control datetimepicker" required>
                        @error('deadline')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror                           
                    </div>
                </div>
                <div class="form-group col-lg-2">
                    <label>Ответственный</label>
                    <select wire:model.defer="task_employee" id="task_employee" class="form-control select2" multiple>
                        @if (Auth::user()->isDirector() || Auth::user()->isMailer())
                            @foreach ($sectors as $sector)
                                <optgroup label="{{ $sector->name }}">
                                    @foreach ($sector->users as $user)
                                        <option value="{{ $user->id }}">{{ $user->employee_name() }}</option>
                                    @endforeach
                                </optgroup>
                            @endforeach

                        @elseif(Auth::user()->isDeputy())
                            @foreach ($sectors as $sector)
                                <optgroup label="{{ $sector->name }}">
                                    @foreach ($sector->users as $user)
                                        @if (!$user->isDirector() && !$user->isDeputy())
                                            <option value="{{ $user->id }}">{{ $user->employee_name() }}</option>                                                        
                                        @endif
                                    @endforeach
                                </optgroup>
                            @endforeach
                            
                        @elseif(Auth::user()->isHead())
                            @foreach (Auth::user()->sector->users()->where('leave', 0)->orderBy('role_id', 'ASC')->get() as $user)
                                <option value="{{ $user->id }}">{{ $user->employee_name() }}</option>
                            @endforeach
                        @endif
                    </select>
                    @error('task_employee')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror                         
                </div>

                <div class="form-group col-lg-12 text-right mt-1">
                    <button class="btn btn-primary create-task-btn">Создать Задачу</button>
                </div>
                {{-- <div class="form-group col-lg-1 text-right">
                    <button type="button" class="btn btn-success remove-task">+</button>
                </div> --}}
            </div>
        </div>
    </form>

<div class="row">
    <!-- Weekly Tasks Table -->
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header"><strong>Еженедельный план</strong></div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-nowrap mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th></th>
                                <th>Название</th>
                                <th>Дата Создание</th>
                                <th>Срок</th>
                                <th>Ответственный</th>
                                <th>Статус</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($weeklyTasks as $key => $task)
                                @include('partials.task-row', ['task' => $task, 'key' => $key])
                            @empty
                                <tr><td colspan="7">Нет еженедельных задач</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Unplanned Tasks Table -->
    <div class="col-lg-12 mt-4">
        <div class="card">
            <div class="card-header"><strong>Внеплановые задачи</strong></div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-nowrap mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th></th>
                                <th>Название</th>
                                <th>Дата Создание</th>
                                <th>Срок</th>
                                <th>Ответственный</th>
                                <th>Статус</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($unplannedTasks as $key => $task)
                                @include('partials.task-row', ['task' => $task, 'key' => $key])
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

@push('scripts')
<script>
    function initSelect2Bindings() {
        const $taskScore = $('#task_score');
        if ($taskScore.length && !$taskScore.hasClass('select2-initialized')) {
            $taskScore.select2().addClass('select2-initialized');
            $taskScore.on('change', function () {
                @this.set('task_score', $(this).val());
            });
        }

        const $taskEmployee = $('#task_employee');
        if ($taskEmployee.length && !$taskEmployee.hasClass('select2-initialized')) {
            $taskEmployee.select2().addClass('select2-initialized');
            $taskEmployee.on('change', function () {
                @this.set('task_employee', $(this).val());
            });
        }
    }

    function initDateTimePicker() {
        const $deadline = $('.datetimepicker');
        if ($deadline.length && !$deadline.hasClass('datetimepicker-initialized')) {
            $deadline.addClass('datetimepicker-initialized');
            $deadline.datetimepicker({
                format: 'YYYY-MM-DD',
                useCurrent: false,
                icons: {
                    up: "fa fa-angle-up",
                    down: "fa fa-angle-down",
                    next: 'fa fa-angle-right',
                    previous: 'fa fa-angle-left'
                }
            });

            $deadline.on('dp.change', function (e) {
                @this.set('deadline', e.date ? e.date.format('YYYY-MM-DD') : null);
            });
        }
    }

    document.addEventListener('livewire:load', function () {
        initSelect2Bindings();
        initDateTimePicker();

        Livewire.hook('message.processed', () => {
            initSelect2Bindings();
            initDateTimePicker();
        });
    });
</script>
@endpush





