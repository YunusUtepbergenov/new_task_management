<div>
    <form wire:submit="taskStore">
        <div class="form-group" wire:ignore>
            <label>Категория</label>
            <select id="task_score" class="form-control select2">
                <option value="1">Score A</option>
                <option value="2">Score B</option>
            </select>
        </div>

        <div class="form-group" wire:ignore>
            <label>Ответственные</label>
            <select id="task_employee" class="form-control select2" multiple>
                <option value="1">User A</option>
                <option value="2">User B</option>
            </select>
        </div>

        <button class="btn btn-primary">Создать</button>
    </form>
</div>

@script
<script>
    const $score = $('#task_score');
    if ($score.length) {
        $score.select2();
        $score.on('change', function () {
            $wire.$set('task_score', $(this).val());
        });
    }

    const $employee = $('#task_employee');
    if ($employee.length) {
        $employee.select2();
        $employee.on('change', function () {
            $wire.$set('task_employee', $(this).val());
        });
    }
</script>
@endscript
