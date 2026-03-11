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
                <a href="#" class="btn add-btn" wire:click.prevent="$dispatch('openCreateTaskModal')"> Добавить Задачу</a>
            @endif
        </div>
    </div>
    <br>
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header"><strong>Задачи на неделю </strong>
                    <span class="italic">
                        ({{ \Carbon\Carbon::parse($startOfWeek)->format('d M Y') }} -
                        {{ \Carbon\Carbon::parse($endOfWeek)->endOfWeek()->format('d M Y') }})    
                    </span>
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
                                @forelse ($weeklyTasks as $key => $task)
                                    @include('partials.task-row', ['task' => $task, 'key' => $loop->index + 1])
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
                                        @include('partials.task-row', ['task' => $task, 'key' => $loop->index + 1])
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
