<div>
    @include('partials.task-loading-overlay')

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
                <a href="#" class="btn add-btn" wire:click.prevent="$dispatch('openCreateTaskModal')"> {{ __('tasks.add_task') }}</a>
            @endif
        </div>
    </div>
    <br>
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header"><strong>{{ __('tasks.weekly_tasks') }} </strong>
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
                                    <th>{{ __('tasks.name') }}</th>
                                    <th>{{ __('tasks.deadline') }}</th>
                                    <th>{{ __('tasks.responsible') }}</th>
                                    <th>{{ __('tasks.status') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($weeklyTasks as $key => $task)
                                    @include('partials.task-row', ['task' => $task, 'key' => $loop->index + 1])
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
                    <a data-toggle="collapse" href="#unplannedTasksCollapse" role="button" aria-expanded="false" aria-controls="unplannedTasksCollapse" class="d-block w-100 text-decoration-none text-dark">
                        <div class="d-flex justify-content-between align-items-center">
                            <strong>{{ __('tasks.all_tasks') }}</strong>
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
                                        <th>{{ __('tasks.name') }}</th>
                                        <th>{{ __('tasks.deadline') }}</th>
                                        <th>{{ __('tasks.responsible') }}</th>
                                        <th>{{ __('tasks.status') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($all_tasks as $key => $task)
                                        @include('partials.task-row', ['task' => $task, 'key' => $loop->index + 1])
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
