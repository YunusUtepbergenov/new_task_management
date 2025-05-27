<div>
    <div wire:loading wire:target="view">
        <div class="loading">Loading&#8230;</div>
    </div>

    @php
        use Carbon\Carbon;

        setlocale(LC_TIME, 'ru_RU.UTF-8');
        Carbon::setLocale('ru');

        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();
    @endphp
<div class="row filter-row">
    <div class="col-auto float-right ml-auto" style="margin-top: 10px;">
        @if(Auth::user()->isResearcher())
            <a href="#" class="btn add-btn" data-toggle="modal" data-target="#create_task"> Добавить Задачу</a>
        @endif
    </div>
</div>
<br>
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
