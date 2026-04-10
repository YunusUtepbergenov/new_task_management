<div>
    @include('partials.task-loading-overlay')

    <div class="row mb-3">
        <div class="col-md-2">
            <input type="month" wire:model.live="month" class="form-control">
        </div>
        <div class="col-md-3">
            <select wire:model.live="score_id" class="form-control">
                <option value="">{{ __('tasks.all_categories') }}</option>
                @foreach($scoreTypes as $score)
                    <option value="{{ $score['id'] }}">{{ $score['name'] }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <select wire:model.live="user_id" class="form-control">
                <option value="">{{ __('tasks.all_employees') }}</option>
                @foreach($workers as $worker)
                    <option value="{{ $worker['id'] }}">{{ $worker['name'] }}</option>
                @endforeach
            </select>
        </div>
        @if($month || $score_id || $user_id)
            <div class="col-md-2">
                <button wire:click="clearFilters" class="btn btn-secondary">{{ __('tasks.clear_filters') }}</button>
            </div>
        @endif
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="table-responsive" style="min-height: 80vh;">
                <table class="table custom-table article-table" id="archiveTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>{{ __('tasks.name') }}</th>
                            <th>{{ __('tasks.employee') }}</th>
                            <th>{{ __('tasks.deadline') }}</th>
                            <th>{{ __('tasks.category') }}</th>
                            <th>{{ __('tasks.score') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($tasks as $index => $task)
                            <tr>
                                <td>{{ $tasks->firstItem() + $index }}</td>
                                <td><a href="#" wire:click.prevent="view({{ $task->id }})">{{ $task->name }}</a></td>
                                <td>{{ $task->user->name ?? '-' }}</td>
                                <td>{{ $task->extended_deadline ?? $task->deadline }}</td>
                                <td>{{ $task->score->name ?? '-' }}</td>
                                <td>{{ $task->total }}/{{ $task->score->max_score ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6">{{ __('tasks.no_tasks_period') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-center mt-3">
        {{ $tasks->links() }}
    </div>
</div>
