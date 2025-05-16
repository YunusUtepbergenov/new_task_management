<div>
    <form wire:submit.prevent="taskStore">
        <div class="form-group">
            <label>Категория</label>
            <select wire:model.defer="task_score" id="task_score" class="form-control select2">
                <option value="1">Score A</option>
                <option value="2">Score B</option>
            </select>
        </div>

        <div class="form-group">
            <label>Ответственные</label>
            <select wire:model.defer="task_employee" id="task_employee" class="form-control select2" multiple>
                <option value="1">User A</option>
                <option value="2">User B</option>
            </select>
        </div>

        <button class="btn btn-primary">Создать</button>
    </form>
</div>

@push('scripts')
<script>
    function initSelect2Bindings() {
        setTimeout(() => {
            const $score = $('#task_score');
            if ($score.length && !$score.hasClass('select2-initialized')) {
                $score.select2();
                $score.addClass('select2-initialized');
                $score.on('change', function () {
                    @this.set('task_score', $(this).val());
                });
            }

            const $employee = $('#task_employee');
            if ($employee.length && !$employee.hasClass('select2-initialized')) {
                $employee.select2();
                $employee.addClass('select2-initialized');
                $employee.on('change', function () {
                    @this.set('task_employee', $(this).val());
                });
            }
        }, 0);
    }

    document.addEventListener('livewire:load', function () {
        initSelect2Bindings();
        Livewire.hook('message.processed', initSelect2Bindings);
    });
</script>
@endpush