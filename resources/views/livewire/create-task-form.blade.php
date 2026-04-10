<div>
    @if (Auth::user()->isDirector() || Auth::user()->isMailer() || Auth::user()->isDeputy() || Auth::user()->isHead())
        <form wire:submit="taskStore" class="mb-4"
            x-data="{
                empIds: [],
                init() {
                    this.$nextTick(() => {
                        if (!window.jQuery || !jQuery.fn.select2) return;

                        const $score = jQuery('#ctf_task_score');
                        if ($score.length) {
                            $score.select2({ width: '100%' });
                            $score.on('change', () => this.$wire.set('task_score', $score.val()));
                        }

                        const $emp = jQuery('#ctf_task_employee');
                        if ($emp.length) {
                            $emp.select2({ width: '100%', closeOnSelect: false });
                            $emp.on('select2:select', (e) => {
                                this.empIds.push(e.params.data.id);
                                this.$wire.set('task_employee', [...this.empIds]);
                                const $el = jQuery(e.params.data.element);
                                $el.detach();
                                $emp.append($el);
                                $emp.trigger('change.select2');
                            });
                            $emp.on('select2:unselect', (e) => {
                                this.empIds = this.empIds.filter(id => id !== e.params.data.id);
                                this.$wire.set('task_employee', [...this.empIds]);
                            });
                        }

                        this.$wire.on('form-reset', () => {
                            this.empIds = [];
                            if ($score.length) $score.val(null).trigger('change.select2');
                            if ($emp.length) $emp.val(null).trigger('change.select2');
                        });
                    });
                }
            }"
        >
            <div class="task-group">
                <div class="row">
                    <div class="form-group col-lg-2" wire:ignore>
                        <label>{{ __('tasks.category') }}</label>
                        <select class="form-control" id="ctf_task_score">
                            <option value="" disabled selected>{{ __('tasks.select') }}</option>
                            @foreach ($this->scoresGrouped as $group => $items)
                                <optgroup label="{{ $group }}">
                                    @foreach ($items as $type)
                                        <option value="{{ $type->id }}">{{ $type->name }} ({{ __('tasks.max') }} {{ $type->max_score }})</option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                        @error('task_score')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group col-lg-4">
                        <label>{{ __('tasks.name') }}</label>
                        <textarea class="form-control" rows="1" wire:model="task_name" required></textarea>
                        @error('task_name')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group col-lg-2">
                        <label>{{ __('tasks.deadline') }}</label>
                        <input
                            type="date"
                            wire:model="deadline"
                            class="form-control"
                            @if($is_repeating) value="" disabled @endif
                            placeholder="{{ $is_repeating ? __('tasks.auto_determined') : '' }}"
                            required
                        >
                        @error('deadline')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group col-lg-2" wire:ignore>
                        <label>{{ __('tasks.responsible') }}</label>
                        <select id="ctf_task_employee" class="form-control" multiple>
                            @if (Auth::user()->isDirector() || Auth::user()->isMailer())
                                @foreach ($this->sectors as $sector)
                                    <optgroup label="{{ $sector->name }}">
                                        @foreach ($sector->users as $user)
                                            <option value="{{ $user->id }}">{{ $user->short_name }}</option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            @elseif (Auth::user()->isDeputy())
                                @foreach ($this->sectors as $sector)
                                    <optgroup label="{{ $sector->name }}">
                                        @foreach ($sector->users as $user)
                                            @if (!$user->isDirector() && (!$user->isDeputy() || $user->id == Auth::id()))
                                                <option value="{{ $user->id }}">{{ $user->short_name }}</option>
                                            @endif
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            @elseif (Auth::user()->isHead())
                                @foreach ($this->sectors as $sector)
                                    <optgroup label="{{ $sector->name }}">
                                        @foreach ($sector->users as $user)
                                            @if (!$user->isDirector() && !$user->isDeputy())
                                                <option value="{{ $user->id }}">{{ $user->short_name }}</option>
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

                    <div class="form-group col-lg-2 d-flex align-items-end">
                        <button class="btn btn-primary create-task-btn w-100">{{ __('tasks.create_task') }}</button>
                    </div>
                </div>
            </div>
        </form>
    @endif
</div>
