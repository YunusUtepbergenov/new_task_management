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

                <div class="form-group col-lg-4">
                    <label>Название</label>
                    <textarea type="text" class="form-control" rows="1" wire:model.defer="task_name" required></textarea>
                    @error('task_name')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror                    
                </div>
                <div class="form-group col-lg-2">
                    <label>Срок</label>
                        <input
                        type="date"
                            wire:model.defer="deadline"
                            class="form-control"
                            @if($is_repeating) value="" disabled @endif
                             placeholder="{{ $is_repeating ? 'Определяется автоматически' : '' }}"
                            required
                        >
                        @error('deadline')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror                           
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
                                        @if (!$user->isDirector() && (! $user->isDeputy() || $user->id == Auth::id()))
                                            <option value="{{ $user->id }}">{{ $user->employee_name() }}</option>                                                        
                                        @endif
                                    @endforeach
                                </optgroup>
                            @endforeach
                            
                        @elseif(Auth::user()->isHead())
                            @foreach ($sectors as $sector)
                                <optgroup label="{{ $sector->name }}">
                                    @foreach ($sector->users as $user)
                                        @if (!$user->isDirector() && (! $user->isDeputy()))
                                            <option value="{{ $user->id }}">{{ $user->employee_name() }}</option>                                                        
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

                                
                <div class="form-group col-lg-2">
                    <label><br>
                        <input type="checkbox" wire:model="is_repeating">
                        Повторяется?
                    </label>
                </div>

                @if($is_repeating)
                    <div class="form-group col-lg-2">
                        <label>Частота</label>
                        <select wire:model="repeat_type" class="form-control">
                            <option value="null" disabled selected>Выберите</option>
                            <option value="weekly">Еженедельно</option>
                            <option value="monthly">Ежемесячно</option>
                            <option value="quarterly">Ежеквартально</option>
                        </select>
                    </div>

                    @if($repeat_type === 'weekly')
                        <div class="form-group col-lg-2">
                            <label>День недели</label>
                            <select wire:model="repeat_day" class="form-control">
                                <option value="" disabled selected>Выберите</option>
                                <option value="1">Понедельник</option>
                                <option value="2">Вторник</option>
                                <option value="3">Среда</option>
                                <option value="4">Четверг</option>
                                <option value="5">Пятница</option>
                                <option value="6">Суббота</option>
                                <option value="7">Воскресенье</option>
                            </select>
                        </div>
                    @elseif($repeat_type === 'monthly')
                        <div class="form-group col-lg-2">
                            <label>День месяца</label>
                            <input type="number" min="1" max="31" wire:model="repeat_day" class="form-control">
                        </div>
                    @elseif($repeat_type === 'quarterly')
                        <div class="form-group col-lg-2">
                            <label>Дней после конца квартала</label>
                            <input type="number" min="1" max="30" wire:model="repeat_day" class="form-control">
                        </div>
                    @endif
                @endif

                @error('repeat_day')
                    <div class="text-danger">{{ $message }}</div>
                @enderror   

                <div class="form-group col-lg-12 text-right mt-1">
                    <button class="btn btn-primary create-task-btn">Создать Задачу</button>
                </div>

            </div>
        </div>
    </form>

    @php
        use Carbon\Carbon;

        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();
    @endphp

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header"><strong>Задачи на неделю 
                        ({{ \Carbon\Carbon::parse($startOfWeek)->format('d M Y') }} -
                        {{ \Carbon\Carbon::parse($endOfWeek)->endOfWeek()->format('d M Y') }})</strong></div>
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

        <div class="col-lg-12 mt-4">
            <div class="card">
                <div class="card-header">
                    <a data-toggle="collapse" href="#unplannedTasksCollapse" role="button" aria-expanded="false" aria-controls="unplannedTasksCollapse" class="d-block w-100 text-decoration-none text-dark">
                        <div class="d-flex justify-content-between align-items-center">
                            <strong>Все задачи</strong>
                            <i class="fa fa-chevron-down ml-2 transition" id="unplannedTasksChevron"></i>
                        </div>
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
                                    @forelse ($all_tasks as $key => $task)
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
</div>

@push('scripts')
    <script>
        $(document).ready(function () {
            $('#unplannedTasksCollapse').on('show.bs.collapse', function () {
                $('#unplannedTasksChevron').removeClass('fa-chevron-right').addClass('fa-chevron-down');
            });
            $('#unplannedTasksCollapse').on('hide.bs.collapse', function () {
                $('#unplannedTasksChevron').removeClass('fa-chevron-down').addClass('fa-chevron-right');
            });
        });
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

        window.addEventListener('toastr:success', event => {
            toastr.options = {
                "closeButton" : true,
                "progressBar" : true
            };
            toastr.success(event.detail.message);
        });
    </script>
@endpush