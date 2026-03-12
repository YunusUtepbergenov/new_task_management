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

    <livewire:create-task-form />

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