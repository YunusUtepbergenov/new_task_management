<div>
    @include('partials.task-loading-overlay')

    <livewire:create-task-form />

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between flex-wrap" style="gap: 10px;">
                    <div>
                        <strong>{{ __('tasks.weekly_tasks') }} </strong>
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
                            placeholder="{{ __('tasks.search') }}"
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
                                    <th>{{ __('tasks.name') }}</th>
                                    <th>{{ __('tasks.deadline') }}</th>
                                    <th>{{ __('tasks.responsible') }}</th>
                                    <th>{{ __('tasks.status') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $key = 1;
                                @endphp
                                @forelse ($weeklyTasks as $task)
                                    @include('partials.task-row', ['task' => $task, 'key'=> $key++])
                                @empty
                                    <tr><td colspan="7">{{ __('tasks.no_weekly_tasks') }}</td></tr>
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
                        <strong>{{ __('tasks.all_tasks') }}</strong>
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
                                        <th>{{ __('tasks.name') }}</th>
                                        <th>{{ __('tasks.deadline') }}</th>
                                        <th>{{ __('tasks.responsible') }}</th>
                                        <th>{{ __('tasks.status') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $cnt = 1;
                                    @endphp
                                    @forelse ($all_tasks as $key => $task)
                                        @include('partials.task-row', ['task' => $task, 'key' => $cnt++])
                                    @empty
                                        <tr><td colspan="7">{{ __('tasks.no_unplanned_tasks') }}</td></tr>
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