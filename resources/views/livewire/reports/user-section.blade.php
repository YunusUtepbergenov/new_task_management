<div>
    <div class="card">
        <div class="card-header">
            <h4 class="card-title mb-0">Сотрудники</h4>
        </div>
        <div class="card-body">
            <div class="row users_section">
                <div wire:loading wire:target="userId">
                    <div class="loading">Loading&#8230;</div>
                </div>
                @foreach ($users as $key => $user)
                    <div class="col-lg-5 user_name">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="user" wire:model="userId" value="{{ $user->id }}" id="flexCheckDefault{{ $key }}">
                            <label class="form-check-label" for="flexCheckDefault{{ $key }}">
                                {{ $user->name }}
                            </label>
                        </div>
                    </div>
                    <div class="col-lg-6 progress_indicator">
                        <div class="progress">
                            {{-- @if ($user->tasks()->count())
                                <a class="nav-link dropdown-toggle">Эффективность: {{ round(((1 - ($user->overdueTasks()->count() / auth()->user()->tasks()->count())) * 100), 1) }}%</a>
                            @else
                                <a class="nav-link dropdown-toggle">Эффективность: 100%</a>
                            @endif --}}
                            @if ($user->tasks()->count() > 0)
                                <div class="progress-bar bg-progress" role="progressbar" style="width: {{ round( ((1 - ( $user->overdueTasks()->count()
                                    + (0.5 * $user->newTasks()->count()) ) / $user->tasks()->count() )) * 100, 1) }}%" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100">{{ round( ((1 - ( $user->overdueTasks()->count()
                            + (0.5 * $user->newTasks()->count()) ) / $user->tasks()->count() )) * 100, 1) }}%</div>
                            @else
                            <div class="progress-bar bg-progress" role="progressbar" style="width: 100%" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100">100%</div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
