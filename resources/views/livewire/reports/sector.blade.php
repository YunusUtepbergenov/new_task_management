<div>
    <div class="card">
        <div class="card-header">
            <h4 class="card-title mb-0">Секторы</h4>
        </div>
        <div class="card-body">
            <div class="row sectors_section" wire:ignore>
                <div wire:loading wire:target="sect">
                    <div class="loading">Loading&#8230;</div>
                </div>
                @foreach ($sectors as $sector)
                    <div class="col-lg-7" style="margin-top: 10px;">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="sectorId" wire:model="sect"  value="{{ $sector->id }}" id="flexRadioDefault{{ $sector->id }}">

                            <label class="form-check-label" for="flexRadioDefault{{ $sector->id }}">
                                {{ $sector->name }}
                            </label>
                        </div>
                    </div>
                    <div class="col-lg-5" style="margin-top: 10px">
                        <div class="progress">
                            @php $avg = 0; @endphp
                            @foreach ($sector->users as $user)
                                @if (!$user->isDirector())
                                    @if ($user->tasks()->count() > 0)
                                        @php  $avg = $avg + round( ((1 - ( $user->overdueTasks()->count()
                                            + (0.5 * $user->newTasks()->count()) ) / $user->tasks()->count() )) * 100, 1);
                                        @endphp
                                    @else
                                        @php $avg = $avg + 100 @endphp
                                    @endif
                                @endif
                            @endforeach
                            @if ($sector->id == 1)
                                <div class="progress-bar bg-progress" role="progressbar" style="width: {{ round(($avg) / ($sector->users()->count() - 1), 1) }}%" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100">{{ round(($avg) / ($sector->users()->count()- 1), 1) }}%</div>
                            @else
                                <div class="progress-bar bg-progress" role="progressbar" style="width: {{ round(($avg) / ($sector->users()->count()), 1) }}%" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100">{{ round(($avg) / ($sector->users()->count()), 1) }}%</div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
