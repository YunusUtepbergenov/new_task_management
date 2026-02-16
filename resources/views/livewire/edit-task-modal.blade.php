<div>
    <div class="modal custom-modal fade" id="edit_task" role="dialog" wire:ignore.self>
        <div class="modal-dialog modal-dialog-centered" role="document" style="max-width: 620px;">
            <div class="modal-content">
                <div class="vm-header">
                    <div class="vm-header-left">
                        <i class="fa fa-pencil vm-header-icon"></i>
                        <h5 class="vm-header-title">Изменить задачу</h5>
                    </div>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body" style="padding: 0;">
                    <div class="vm-section">
                        @if ($errorMsg)
                            <div class="alert alert-danger mb-3">{{ $errorMsg }}</div>
                        @endif

                        <div class="vm-section-header">
                            <i class="fa fa-list-alt"></i>
                            <span class="vm-section-title">Категория</span>
                        </div>
                        <div class="form-group mb-3" wire:ignore>
                            <select class="form-control select2" id="edit_score">
                                <option value="" disabled selected>Выберите</option>
                                @foreach ($scoresGrouped as $group => $items)
                                    <optgroup label="{{ $group }}">
                                        @foreach ($items as $type)
                                            <option value="{{ $type['id'] }}">{{ $type['name'] }} (Макс: {{ $type['max_score'] }})</option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                        </div>
                        @error('scoreId')
                            <div class="text-danger mb-2">{{ $message }}</div>
                        @enderror

                        <div class="vm-section-header">
                            <i class="fa fa-pencil-square-o"></i>
                            <span class="vm-section-title">Название</span>
                        </div>
                        <div class="form-group mb-3">
                            <input type="text" class="form-control" wire:model="name" placeholder="Введите название задачи">
                        </div>
                        @error('name')
                            <div class="text-danger mb-2">{{ $message }}</div>
                        @enderror

                        <div class="vm-section-header">
                            <i class="fa fa-calendar"></i>
                            <span class="vm-section-title">Срок</span>
                        </div>
                        <div class="form-group mb-3">
                            <input type="date" class="form-control" wire:model="deadline">
                        </div>
                        @error('deadline')
                            <div class="text-danger mb-2">{{ $message }}</div>
                        @enderror

                        <div class="vm-section-header">
                            <i class="fa fa-users"></i>
                            <span class="vm-section-title">Ответственный</span>
                        </div>
                        <div class="form-group mb-3" wire:ignore>
                            <select class="form-control select2" id="edit_users" multiple>
                                @foreach ($filteredSectors as $sector)
                                    <optgroup label="{{ $sector['name'] }}">
                                        @foreach ($sector['users'] as $user)
                                            <option value="{{ $user['id'] }}">{{ $user['name'] }}</option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                        </div>
                        @error('userIds')
                            <div class="text-danger mb-2">{{ $message }}</div>
                        @enderror

                        @if (count($creators) > 1)
                            <div class="vm-section-header">
                                <i class="fa fa-user"></i>
                                <span class="vm-section-title">Постановщик</span>
                            </div>
                            <div class="form-group mb-3" wire:ignore>
                                <select class="form-control" id="edit_creator">
                                    @foreach ($creators as $creator)
                                        <option value="{{ $creator['id'] }}">{{ $creator['name'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="vm-footer">
                    <button class="vm-btn-submit" wire:click="taskUpdate" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="taskUpdate">Изменить</span>
                        <span wire:loading wire:target="taskUpdate">Сохранение...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@script
    <script>
        let pendingFormData = null;

        function initEditSelect2() {
            const $modal = $('#edit_task');
            const $score = $('#edit_score');
            const $users = $('#edit_users');
            const $creator = $('#edit_creator');

            if ($score.length && !$score.hasClass('select2-hidden-accessible')) {
                $score.select2({ dropdownParent: $modal });
                $score.on('change', function () {
                    $wire.$set('scoreId', $(this).val());
                });
            }

            if ($users.length && !$users.hasClass('select2-hidden-accessible')) {
                $users.select2({ dropdownParent: $modal });
                $users.on('change', function () {
                    $wire.$set('userIds', $(this).val());
                });
            }

            if ($creator.length) {
                $creator.off('change.editCreator').on('change.editCreator', function () {
                    $wire.$set('creatorId', $(this).val());
                });
            }

            if (pendingFormData) {
                applyFormData(pendingFormData);
                pendingFormData = null;
            }
        }

        function applyFormData(data) {
            const $score = $('#edit_score');
            const $users = $('#edit_users');
            const $creator = $('#edit_creator');

            if ($score.length && data.scoreId) {
                $score.val(data.scoreId).trigger('change.select2');
            }
            if ($users.length && data.userIds) {
                $users.val(data.userIds.map(String)).trigger('change.select2');
            }
            if ($creator.length && data.creatorId) {
                $creator.val(data.creatorId);
            }
        }

        $wire.on('show-edit-modal', () => {
            const $modal = $('#edit_task');

            function openAndInit() {
                $modal.modal('show');
                $modal.one('shown.bs.modal', function () {
                    initEditSelect2();
                });
            }

            if ($modal.hasClass('show') || $modal.hasClass('in')) {
                $modal.modal('hide');
                $modal.one('hidden.bs.modal', () => openAndInit());
            } else {
                openAndInit();
            }
        });

        $wire.on('close-edit-modal', () => {
            $('#edit_task').modal('hide');
        });

        $wire.on('edit-form-loaded', (params) => {
            pendingFormData = params[0];
        });
    </script>
@endscript
